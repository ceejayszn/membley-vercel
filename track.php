<?php
require_once 'includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$page = trim($input['page'] ?? '');
$type = trim($input['type'] ?? '');
$value = intval($input['value'] ?? 0);

if (empty($page) || !in_array($type, ['view', 'click', 'time'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO analytics (page, views, clicks, time_spent) VALUES (:page, 0, 0, 0)");
    $stmt->execute([':page' => $page]);

    if ($type === 'view') {
        $stmt = $pdo->prepare("UPDATE analytics SET views = views + 1 WHERE page = :page");
        $stmt->execute([':page' => $page]);
    } elseif ($type === 'click') {
        $stmt = $pdo->prepare("UPDATE analytics SET clicks = clicks + 1 WHERE page = :page");
        $stmt->execute([':page' => $page]);
    } elseif ($type === 'time' && $value > 0) {
        $stmt = $pdo->prepare("UPDATE analytics SET time_spent = time_spent + :value WHERE page = :page");
        $stmt->execute([':page' => $page, ':value' => $value]);
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    $ip = trim($ip);

    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';

    $device_type = 'Desktop';
    if (preg_match('/mobile|phone|ipod|android|blackberry|webos|iemobile/i', $user_agent)) {
        $device_type = 'Mobile';
    } elseif (preg_match('/tablet|ipad|playbook|silk/i', $user_agent)) {
        $device_type = 'Tablet';
    }

    $browser = 'Unknown';
    if (preg_match('/chrome|crios/i', $user_agent)) {
        $browser = 'Chrome';
    } elseif (preg_match('/safari/i', $user_agent) && !preg_match('/chrome|crios/i', $user_agent)) {
        $browser = 'Safari';
    } elseif (preg_match('/firefox|fxios/i', $user_agent)) {
        $browser = 'Firefox';
    } elseif (preg_match('/msie|trident/i', $user_agent)) {
        $browser = 'IE';
    } elseif (preg_match('/edge|edg/i', $user_agent)) {
        $browser = 'Edge';
    }

    $location = 'Localhost';
    $isp = 'Local Loopback';

    if ($ip !== '127.0.0.1' && $ip !== '::1' && $ip !== 'localhost' && $ip !== 'Unknown') {
        $ip_stmt = $pdo->prepare("SELECT location, network_isp FROM visitor_tracking WHERE ip_address = :ip LIMIT 1");
        $ip_stmt->execute([':ip' => $ip]);
        $cached = $ip_stmt->fetch();
        
        if ($cached && $cached['location'] !== 'Unknown' && $cached['location'] !== 'Lookup Failed') {
            $location = $cached['location'];
            $isp = $cached['network_isp'];
        } else {
            $ctx = stream_context_create(['http' => ['timeout' => 2]]);
            $geo_json = @file_get_contents("http://ip-api.com/json/" . urlencode($ip), false, $ctx);
            if ($geo_json) {
                $geo_data = json_decode($geo_json, true);
                if (($geo_data['status'] ?? '') === 'success') {
                    $city = $geo_data['city'] ?? '';
                    $country = $geo_data['country'] ?? '';
                    $location = (!empty($city) ? $city . ', ' : '') . $country;
                    $isp = $geo_data['isp'] ?? ($geo_data['as'] ?? 'Unknown');
                } else {
                    $location = 'Unknown Location';
                    $isp = 'Unknown Network';
                }
            } else {
                $location = 'Lookup Failed';
                $isp = 'Lookup Failed';
            }
        }
    }

    $session_stmt = $pdo->prepare("SELECT id FROM visitor_tracking WHERE ip_address = :ip AND user_agent = :ua AND last_seen > datetime('now', '-15 minutes') ORDER BY id DESC LIMIT 1");
    $session_stmt->execute([':ip' => $ip, ':ua' => $user_agent]);
    $existing_session = $session_stmt->fetch();

    if ($existing_session) {
        $visitor_id = $existing_session['id'];
        if ($type === 'time' && $value > 0) {
            $update_stmt = $pdo->prepare("UPDATE visitor_tracking SET time_spent = time_spent + :val, last_seen = datetime('now') WHERE id = :id");
            $update_stmt->execute([':val' => $value, ':id' => $visitor_id]);
        } else {
            $update_stmt = $pdo->prepare("UPDATE visitor_tracking SET last_seen = datetime('now') WHERE id = :id");
            $update_stmt->execute([':id' => $visitor_id]);
        }
    } else {
        $insert_stmt = $pdo->prepare("INSERT INTO visitor_tracking (ip_address, user_agent, device_type, browser, location, network_isp, time_spent) VALUES (:ip, :ua, :device, :browser, :loc, :isp, :time)");
        $insert_stmt->execute([
            ':ip' => $ip,
            ':ua' => $user_agent,
            ':device' => $device_type,
            ':browser' => $browser,
            ':loc' => $location,
            ':isp' => $isp,
            ':time' => ($type === 'time' ? $value : 0)
        ]);
    }

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
