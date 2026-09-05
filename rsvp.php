<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$event_title = "Homecoming Sabbath (10 Yrs Celebration)";
$submitted = false;
$error_msg = "";
$registered_name = "";

function detect_device_details($user_agent) {
    $device_type = 'Desktop';
    $phone_model = 'Desktop PC / Mac';
    $os = 'Unknown OS';
    $browser = 'Unknown Browser';

    if (preg_match('/windows nt 10.0/i', $user_agent)) $os = 'Windows 10/11';
    elseif (preg_match('/windows nt 6.3/i', $user_agent)) $os = 'Windows 8.1';
    elseif (preg_match('/windows nt 6.1/i', $user_agent)) $os = 'Windows 7';
    elseif (preg_match('/macintosh|mac os x/i', $user_agent)) $os = 'macOS';
    elseif (preg_match('/android/i', $user_agent)) $os = 'Android';
    elseif (preg_match('/iphone/i', $user_agent)) $os = 'iOS (iPhone)';
    elseif (preg_match('/ipad/i', $user_agent)) $os = 'iPadOS (iPad)';
    elseif (preg_match('/linux/i', $user_agent)) $os = 'Linux';

    if (preg_match('/ipad|tablet|playbook|silk/i', $user_agent)) {
        $device_type = 'Tablet';
    } elseif (preg_match('/mobile|phone|ipod|android|blackberry|webos|iemobile/i', $user_agent)) {
        $device_type = 'Mobile';
    }

    if (preg_match('/edg|edge/i', $user_agent)) $browser = 'Microsoft Edge';
    elseif (preg_match('/samsungbrowser/i', $user_agent)) $browser = 'Samsung Internet';
    elseif (preg_match('/opr|opera/i', $user_agent)) $browser = 'Opera';
    elseif (preg_match('/chrome|crios/i', $user_agent)) $browser = 'Chrome';
    elseif (preg_match('/firefox|fxios/i', $user_agent)) $browser = 'Firefox';
    elseif (preg_match('/safari/i', $user_agent)) $browser = 'Safari';

    if (preg_match('/iPhone/i', $user_agent)) {
        $phone_model = 'Apple iPhone';
        if (preg_match('/iPhone\s?OS\s?([\d_]+)/i', $user_agent, $m)) {
            $phone_model .= ' (iOS ' . str_replace('_', '.', $m[1]) . ')';
        }
    } elseif (preg_match('/iPad/i', $user_agent)) {
        $phone_model = 'Apple iPad';
    } elseif (preg_match('/Android/i', $user_agent)) {
        if (preg_match('/;\s*([^;]+?)\s*Build\//i', $user_agent, $matches)) {
            $raw_model = trim($matches[1]);
            $phone_model = $raw_model;
            if (stripos($raw_model, 'SM-') === 0 || stripos($raw_model, 'SAMSUNG') !== false) {
                $phone_model = 'Samsung ' . $raw_model;
            } elseif (stripos($raw_model, 'TECNO') !== false) {
                $phone_model = $raw_model;
            } elseif (stripos($raw_model, 'Infinix') !== false) {
                $phone_model = $raw_model;
            } elseif (stripos($raw_model, 'Redmi') !== false || stripos($raw_model, 'POCO') !== false || stripos($raw_model, 'Xiaomi') !== false) {
                $phone_model = 'Xiaomi ' . $raw_model;
            } elseif (stripos($raw_model, 'CPH') === 0 || stripos($raw_model, 'OPPO') !== false) {
                $phone_model = 'OPPO ' . $raw_model;
            } elseif (stripos($raw_model, 'V2') === 0 || stripos($raw_model, 'vivo') !== false) {
                $phone_model = 'Vivo ' . $raw_model;
            } elseif (stripos($raw_model, 'Pixel') !== false) {
                $phone_model = 'Google ' . $raw_model;
            }
        } else {
            $phone_model = 'Android Smartphone';
        }
    } else {
        if ($os === 'macOS') $phone_model = 'Apple Mac Computer';
        elseif (strpos($os, 'Windows') !== false) $phone_model = 'Windows PC';
        else $phone_model = 'Desktop Computer';
    }

    return [
        'device_type' => $device_type,
        'phone_model' => $phone_model,
        'os'          => $os,
        'browser'     => $browser
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_rsvp'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $is_membley_member = isset($_POST['is_membley_member']) ? intval($_POST['is_membley_member']) : 0;
    $church_from = trim($_POST['church_from'] ?? '');
    $attendees_count = intval($_POST['attendees_count'] ?? 1);
    $inquiry = trim($_POST['inquiry'] ?? '');

    $additional_names = [];
    if ($attendees_count > 1) {
        for ($i = 2; $i <= $attendees_count; $i++) {
            $att_name = trim($_POST["attendee_name_{$i}"] ?? '');
            if (!empty($att_name)) {
                $additional_names[] = "Attendee {$i}: {$att_name}";
            }
        }
    }

    if (empty($full_name) || empty($phone)) {
        $error_msg = "Please enter both your Full Name and Phone Number to confirm attendance.";
    } elseif ($attendees_count > 1 && count($additional_names) < ($attendees_count - 1)) {
        $error_msg = "Please provide the names for all additional attendees.";
    } else {
        if ($is_membley_member == 1 && empty($church_from)) {
            $church_from = "Membley SDA Church";
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        $ip = trim($ip);

        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $device_info = detect_device_details($user_agent);

        $location = 'Kenya / Local';
        $network_isp = 'Internet Network';
        if ($ip !== '127.0.0.1' && $ip !== '::1' && $ip !== 'localhost' && $ip !== 'Unknown') {
            $ctx = stream_context_create(['http' => ['timeout' => 2]]);
            $geo_json = @file_get_contents("http://ip-api.com/json/" . urlencode($ip), false, $ctx);
            if ($geo_json) {
                $geo_data = json_decode($geo_json, true);
                if (($geo_data['status'] ?? '') === 'success') {
                    $city = $geo_data['city'] ?? '';
                    $country = $geo_data['country'] ?? '';
                    $location = (!empty($city) ? $city . ', ' : '') . $country;
                    $network_isp = $geo_data['isp'] ?? ($geo_data['as'] ?? 'Unknown ISP');
                }
            }
        }

        $notes_text = $inquiry;
        if (!empty($additional_names)) {
            $names_list = implode(", ", $additional_names);
            $notes_text = (!empty($notes_text) ? $notes_text . " | " : "") . "[Group Members: " . $names_list . "]";
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO event_rsvps 
                (event_id, event_title, full_name, is_membley_member, church_from, phone, attendees_count, inquiry, ip_address, device_type, phone_model, browser, os, location, network_isp, user_agent)
                VALUES 
                (:event_id, :event_title, :full_name, :is_member, :church, :phone, :attendees, :inquiry, :ip, :device_type, :phone_model, :browser, :os, :location, :isp, :ua)");
            
            $stmt->execute([
                ':event_id'     => 1,
                ':event_title'  => $event_title,
                ':full_name'    => $full_name,
                ':is_member'    => $is_membley_member,
                ':church'       => !empty($church_from) ? $church_from : 'Visitor',
                ':phone'        => $phone,
                ':attendees'    => max(1, $attendees_count),
                ':inquiry'      => $notes_text,
                ':ip'           => $ip,
                ':device_type'  => $device_info['device_type'],
                ':phone_model'  => $device_info['phone_model'],
                ':browser'      => $device_info['browser'],
                ':os'           => $device_info['os'],
                ':location'     => $location,
                ':isp'          => $network_isp,
                ':ua'           => $user_agent
            ]);

            $submitted = true;
            $registered_name = $full_name;
        } catch (PDOException $e) {
            $error_msg = "Database error: " . $e->getMessage();
        }
    }
}
?>

<section style="background-color: var(--primary-dark); color: white; padding: 3rem 0; text-align: center; background-image: linear-gradient(rgba(0,26,53,0.9), rgba(0,26,53,0.9)), url('assets/images/church_banner.png'); background-size: cover; background-position: center;">
    <div class="container">
        <span style="background-color: var(--accent); color: var(--primary-dark); font-weight: 700; text-transform: uppercase; font-size: 0.8rem; padding: 0.35rem 0.85rem; border-radius: 4px; display: inline-block; margin-bottom: 0.75rem; letter-spacing: 0.5px;">
            <i class="fa-solid fa-calendar-check"></i> Homecoming Registration
        </span>
        <h1 style="color: white; font-size: 2.3rem; margin-bottom: 0.4rem;">Will You Be Attending?</h1>
        <p style="color: rgba(255,255,255,0.85); font-size: 1rem; max-width: 600px; margin: 0 auto;">
            <strong>Homecoming Sabbath</strong> — Celebrating 10 Yrs of Fellowship and Family<br>
            <span style="color: var(--accent); font-weight: 700;"><i class="fa-solid fa-calendar-day"></i> Sabbath, 31 OCT 2026 | Starts 8:00 AM</span>
        </p>
    </div>
</section>

<section class="section-padding container">

    <?php if ($submitted): ?>
        
                <div class="rsvp-thankyou-card">
            
            <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">🎉</div>
            
            <h2 style="color: var(--primary); font-size: 2.1rem; margin-bottom: 0.25rem;">
                Thank You, <?php echo htmlspecialchars($registered_name); ?>!
            </h2>
            
            <p style="font-size: 1.15rem; font-weight: 700; color: var(--primary); margin-bottom: 1.25rem;">
                ✨ Feel at the feet of Jesus ✨
            </p>

            <p style="font-size: 1rem; color: var(--text-dark); margin-bottom: 1.5rem; line-height: 1.6;">
                Your attendance for the <strong>Homecoming Sabbath (10 Yrs Celebration)</strong> is confirmed. We look forward to fellowshipping and praising God together with you!
            </p>

                        <div style="background-color: var(--primary-dark); color: #ffffff; border-left: 4px solid var(--accent); border-radius: 8px; padding: 1.5rem; margin: 1.5rem 0; text-align: left;">
                <div style="font-family: var(--font-heading); font-size: 1.05rem; font-style: italic; line-height: 1.6; color: #f1f5f9; margin-bottom: 0.5rem;">
                    "Come, let us sing for joy to the Lord; let us shout aloud to the Rock of our salvation. Let us come before him with thanksgiving and extol him with music and song."
                </div>
                <div style="color: var(--accent); font-weight: 700; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;">
                    <i class="fa-solid fa-book-bible"></i> Psalm 95:1–2
                </div>
            </div>

                        <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 1.25rem; margin: 1.5rem 0; text-align: left;">
                <h4 style="color: var(--primary); margin-bottom: 0.6rem; font-size: 0.95rem; border-bottom: 1px solid var(--border-color); padding-bottom: 0.4rem;">
                    <i class="fa-solid fa-location-dot" style="color: var(--accent);"></i> Event Information
                </h4>
                <p style="font-size: 0.9rem; margin-bottom: 0.25rem;"><strong>Date:</strong> Sabbath, 31 October 2026</p>
                <p style="font-size: 0.9rem; margin-bottom: 0.25rem;"><strong>Time:</strong> Starts at 8:00 AM</p>
                <p style="font-size: 0.9rem;"><strong>Venue:</strong> Membley Park Estate, Ruiru, Kenya</p>
            </div>

                        <div style="display: flex; flex-direction: column; gap: 0.75rem; align-items: center;">
                <a href="https://api.whatsapp.com/send?text=<?php echo urlencode("Hello! I just confirmed my attendance for the Membley SDA Homecoming Sabbath (Celebrating 10 Yrs of Fellowship & Family) on Oct 31, 2026. Will you be attending too? Register here: https://" . ($_SERVER['HTTP_HOST'] ?? 'membleyadventist.org') . "/rsvp.php"); ?>" target="_blank" class="btn-whatsapp-share" style="width: 100%; max-width: 380px;">
                    <i class="fa-brands fa-whatsapp" style="font-size: 1.25rem;"></i> Invite a Friend on WhatsApp
                </a>
                
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; justify-content: center; margin-top: 0.5rem;">
                    <a href="index.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-house"></i> Home</a>
                    <a href="events.php" class="btn btn-outline btn-sm"><i class="fa-solid fa-calendar-days"></i> All Events</a>
                </div>
            </div>
        </div>

    <?php else: ?>

                <div style="max-width: 480px; margin: 0 auto 2.5rem auto; text-align: center;">
            <img src="assets/images/homecoming_flyer.png" alt="Homecoming Sabbath 10 Yrs Poster" class="flyer-poster-img">
        </div>

                <div class="rsvp-box-card" id="rsvpCard">
            
            <?php if (!empty($error_msg)): ?>
                <div style="background-color: #fee2e2; border: 1px solid #ef4444; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                    <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error_msg); ?>
                </div>
            <?php endif; ?>

                        <div style="background: #f8fafc; border: 1px solid var(--border-color); border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.75rem; display: flex; align-items: center; gap: 0.85rem;">
                <span style="font-size: 1.8rem;">😊</span>
                <div>
                    <strong style="color: var(--primary); display: block; font-size: 1.05rem;">You are warmly welcomed!</strong>
                    <small style="color: var(--text-dark); font-size: 0.9rem;">Please enter your details below to confirm attendance for Homecoming Sabbath.</small>
                </div>
            </div>

                        <form action="rsvp.php" method="POST" id="mainRsvpForm">
                
                                <div class="form-group">
                    <label class="form-label" for="full_name">Your Full Name <span style="color: #e11d48;">*</span></label>
                    <input type="text" id="full_name" name="full_name" class="form-control" placeholder="e.g. John Doe / Sarah Mwangi" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                </div>

                                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number <span style="color: #e11d48;">*</span></label>
                    <input type="tel" id="phone" name="phone" class="form-control" placeholder="e.g. 0712 345 678" required value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                </div>

                                <div class="form-group">
                    <label class="form-label">Are you a Membley SDA Member?</label>
                    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                        <input type="hidden" name="is_membley_member" id="is_membley_member" value="<?php echo (isset($_POST['is_membley_member']) && $_POST['is_membley_member'] == 1) ? '1' : '0'; ?>">
                        <button type="button" id="btnMemberYes" class="choice-chip <?php echo (isset($_POST['is_membley_member']) && $_POST['is_membley_member'] == 1) ? 'active' : ''; ?>">
                            <i class="fa-solid fa-church"></i> I am a Membley SDA Member
                        </button>
                        <button type="button" id="btnMemberVisitor" class="choice-chip <?php echo (!isset($_POST['is_membley_member']) || $_POST['is_membley_member'] == 0) ? 'active' : ''; ?>">
                            <i class="fa-solid fa-hand-holding-heart"></i> I am a Visitor / Other Church
                        </button>
                    </div>
                </div>

                                <div class="form-group" id="churchSection">
                    <label class="form-label" for="church_from">Church / Congregation You Are From:</label>
                    <input type="text" id="church_from" name="church_from" class="form-control" placeholder="Type your church or home congregation (e.g. Ruiru SDA, Nairobi Central, Kahawa West, etc.)" value="<?php echo htmlspecialchars($_POST['church_from'] ?? ''); ?>">
                </div>

                                <div class="form-group">
                    <label class="form-label" for="attendees_count">Number of Attendees <small style="color: var(--text-muted); font-weight: normal;">(You + friends/family joining)</small></label>
                    <select id="attendees_count" name="attendees_count" class="form-control">
                        <option value="1" <?php echo (($_POST['attendees_count'] ?? 1) == 1) ? 'selected' : ''; ?>>1 Person (Just Me)</option>
                        <option value="2" <?php echo (($_POST['attendees_count'] ?? 1) == 2) ? 'selected' : ''; ?>>2 Persons</option>
                        <option value="3" <?php echo (($_POST['attendees_count'] ?? 1) == 3) ? 'selected' : ''; ?>>3 Persons</option>
                        <option value="4" <?php echo (($_POST['attendees_count'] ?? 1) == 4) ? 'selected' : ''; ?>>4 Persons</option>
                        <option value="5" <?php echo (($_POST['attendees_count'] ?? 1) >= 5) ? 'selected' : ''; ?>>5 Persons (Group / Family)</option>
                    </select>
                </div>

                                <div id="additionalAttendeesBox" style="display: none; background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: 10px; padding: 1.25rem; margin-bottom: 1.5rem;">
                    <h4 style="color: var(--primary); font-size: 0.95rem; margin-bottom: 0.75rem;">
                        <i class="fa-solid fa-users" style="color: #84cc16;"></i> Please enter the names of additional attendees:
                    </h4>
                    <div id="additionalNamesInputs" style="display: flex; flex-direction: column; gap: 0.75rem;">
                                            </div>
                </div>

                                <div class="form-group">
                    <label class="form-label" for="inquiry">Any Inquiries, Questions or Special Prayer Requests? <small style="color: var(--text-muted); font-weight: normal;">(Optional)</small></label>
                    <textarea id="inquiry" name="inquiry" class="form-control" rows="3" placeholder="Feel free to write any inquiry, question or special note here..."><?php echo htmlspecialchars($_POST['inquiry'] ?? ''); ?></textarea>
                </div>

                                <div style="margin-top: 2rem;">
                    <button type="submit" name="submit_rsvp" class="btn btn-lime" style="width: 100%; font-size: 1.1rem; padding: 0.95rem; justify-content: center;">
                        <i class="fa-solid fa-check-circle"></i> Confirm & Submit Attendance
                    </button>
                </div>

            </form>
        </div>

    <?php endif; ?>

</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnMemberYes = document.getElementById('btnMemberYes');
    const btnMemberVisitor = document.getElementById('btnMemberVisitor');
    const isMemberInput = document.getElementById('is_membley_member');
    const churchSection = document.getElementById('churchSection');
    const churchInput = document.getElementById('church_from');
    const attendeesSelect = document.getElementById('attendees_count');
    const additionalBox = document.getElementById('additionalAttendeesBox');
    const additionalInputs = document.getElementById('additionalNamesInputs');

    if (btnMemberYes && btnMemberVisitor) {
        btnMemberYes.addEventListener('click', function() {
            btnMemberYes.classList.add('active');
            btnMemberVisitor.classList.remove('active');
            isMemberInput.value = '1';
            churchInput.value = 'Membley SDA Church';
            churchSection.style.display = 'none';
        });

        btnMemberVisitor.addEventListener('click', function() {
            btnMemberVisitor.classList.add('active');
            btnMemberYes.classList.remove('active');
            isMemberInput.value = '0';
            if (churchInput.value === 'Membley SDA Church') churchInput.value = '';
            churchSection.style.display = 'block';
        });
    }

    function updateAttendeeFields() {
        if (!attendeesSelect || !additionalBox || !additionalInputs) return;
        const count = parseInt(attendeesSelect.value) || 1;
        
        if (count > 1) {
            additionalBox.style.display = 'block';
            additionalInputs.innerHTML = '';
            
            for (let i = 2; i <= count; i++) {
                const div = document.createElement('div');
                div.innerHTML = `
                    <label style="font-size: 0.85rem; font-weight: 600; color: var(--primary); display: block; margin-bottom: 0.25rem;">
                        Attendee ${i} Full Name <span style="color: #e11d48;">*</span>
                    </label>
                    <input type="text" name="attendee_name_${i}" class="form-control" placeholder="e.g. Full Name of Attendee ${i}" required>
                `;
                additionalInputs.appendChild(div);
            }
        } else {
            additionalBox.style.display = 'none';
            additionalInputs.innerHTML = '';
        }
    }

    if (attendeesSelect) {
        attendeesSelect.addEventListener('change', updateAttendeeFields);
        updateAttendeeFields();
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
