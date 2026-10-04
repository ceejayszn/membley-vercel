<?php
require_once 'includes/db.php';
require_once 'includes/security.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$page  = substr(trim($input['page'] ?? ''), 0, 120); // max 120 chars
$type  = trim($input['type'] ?? '');
$value = max(0, min(3600, intval($input['value'] ?? 0))); // clamp 0..3600 secs

if (empty($page) || !in_array($type, ['view', 'click', 'time'], true)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

// Rate limit: max 60 analytics hits per IP per minute
if ($pdo && !rate_limit_check($pdo, 'track', membley_client_ip(), 60, 60)) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests']);
    exit;
}

try {
    // Upsert analytics row
    db_upsert_analytics($pdo, $isPostgres, $page);

    if ($type === 'view') {
        $pdo->prepare("UPDATE analytics SET views = views + 1 WHERE page = :page")
            ->execute([':page' => $page]);
    } elseif ($type === 'click') {
        $pdo->prepare("UPDATE analytics SET clicks = clicks + 1 WHERE page = :page")
            ->execute([':page' => $page]);
    } elseif ($type === 'time' && $value > 0) {
        $pdo->prepare("UPDATE analytics SET time_spent = time_spent + :value WHERE page = :page")
            ->execute([':page' => $page, ':value' => $value]);
    }

    $ip         = membley_client_ip();
    $user_agent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 512);

    $device_type = 'Desktop';
    if (preg_match('/mobile|phone|ipod|android|blackberry|webos|iemobile/i', $user_agent)) {
        $device_type = 'Mobile';
    } elseif (preg_match('/tablet|ipad|playbook|silk/i', $user_agent)) {
        $device_type = 'Tablet';
    }

    $browser = 'Unknown';
    if (preg_match('/edg|edge/i', $user_agent))          $browser = 'Edge';
    elseif (preg_match('/chrome|crios/i', $user_agent))  $browser = 'Chrome';
    elseif (preg_match('/firefox|fxios/i', $user_agent)) $browser = 'Firefox';
    elseif (preg_match('/safari/i', $user_agent))        $browser = 'Safari';

    $location = 'Localhost';
    $isp      = 'Local Loopback';

    if (!in_array($ip, ['127.0.0.1','::1','localhost','Unknown'], true)) {
        // Check visitor_tracking cache first to avoid repeated GeoIP API calls
        $ip_stmt = $pdo->prepare("SELECT location, network_isp FROM visitor_tracking WHERE ip_address = :ip ORDER BY id DESC LIMIT 1");
        $ip_stmt->execute([':ip' => $ip]);
        $cached = $ip_stmt->fetch();

        if ($cached && !in_array($cached['location'], ['Unknown','Lookup Failed',''], true)) {
            $location = $cached['location'];
            $isp      = $cached['network_isp'];
        } else {
            $ctx = stream_context_create(['http' => ['timeout' => 2]]);
            $geo_json = @file_get_contents("http://ip-api.com/json/" . urlencode($ip), false, $ctx);
            if ($geo_json) {
                $geo_data = json_decode($geo_json, true);
                if (($geo_data['status'] ?? '') === 'success') {
                    $city     = $geo_data['city']    ?? '';
                    $country  = $geo_data['country'] ?? '';
                    $location = (!empty($city) ? $city . ', ' : '') . $country;
                    $isp      = $geo_data['isp']     ?? ($geo_data['as'] ?? 'Unknown');
                } else {
                    $location = 'Unknown Location';
                    $isp      = 'Unknown Network';
                }
            } else {
                $location = 'Lookup Failed';
                $isp      = 'Lookup Failed';
            }
        }
    }

    // Use DB-compatible datetime expressions
    $windowAgo = $isPostgres ? "NOW() - INTERVAL '15 minutes'" : "datetime('now', '-15 minutes')";
    $nowExpr   = $isPostgres ? 'NOW()' : "datetime('now')";

    $session_stmt = $pdo->prepare(
        "SELECT id FROM visitor_tracking WHERE ip_address = :ip AND user_agent = :ua AND last_seen > $windowAgo ORDER BY id DESC LIMIT 1"
    );
    $session_stmt->execute([':ip' => $ip, ':ua' => $user_agent]);
    $existing = $session_stmt->fetch();

    if ($existing) {
        if ($type === 'time' && $value > 0) {
            $pdo->prepare("UPDATE visitor_tracking SET time_spent = time_spent + :val, last_seen = $nowExpr WHERE id = :id")
                ->execute([':val' => $value, ':id' => $existing['id']]);
        } else {
            $pdo->prepare("UPDATE visitor_tracking SET last_seen = $nowExpr WHERE id = :id")
                ->execute([':id' => $existing['id']]);
        }
    } else {
        $pdo->prepare("INSERT INTO visitor_tracking (ip_address, user_agent, device_type, browser, location, network_isp, time_spent) VALUES (:ip, :ua, :device, :browser, :loc, :isp, :time)")
            ->execute([
                ':ip'     => $ip,
                ':ua'     => $user_agent,
                ':device' => $device_type,
                ':browser'=> $browser,
                ':loc'    => $location,
                ':isp'    => $isp,
                ':time'   => ($type === 'time' ? $value : 0),
            ]);
    }

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    membley_log('error', 'track.php DB error: ' . $e->getMessage(), ['ip' => membley_client_ip()]);
    http_response_code(500);
    // Never expose raw DB error to public
    echo json_encode(['error' => 'Analytics update temporarily unavailable.']);
}
