<?php
require_once 'includes/db.php';

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $gender = trim($_POST['gender'] ?? 'Male');
    $address = trim($_POST['address'] ?? '');
    $ss_class = trim($_POST['ss_class'] ?? '');
    $prev_church = trim($_POST['prev_church'] ?? '');
    $baptised = trim($_POST['baptised'] ?? 'No');
    $baptism_date = trim($_POST['baptism_date'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (empty($name) || empty($email) || empty($phone) || empty($address)) {
        $error_msg = 'Please fill in all required fields marked with (*).';
    } else {
        try {
            $details = "Gender: $gender\n"
                     . "Date of Birth: $dob\n"
                     . "Address: $address\n"
                     . "Sabbath School Class: " . (!empty($ss_class) ? $ss_class : 'None') . "\n"
                     . "Previous Church: " . (!empty($prev_church) ? $prev_church : 'None') . "\n"
                     . "Baptised: $baptised" . (!empty($baptism_date) ? " (Date: $baptism_date)" : "") . "\n"
                     . "Notes: " . (!empty($notes) ? $notes : 'None');

            $stmt = $pdo->prepare("INSERT INTO submissions (type, name, email, phone, subject_message, amount) VALUES ('member_registration', :name, :email, :phone, :message, 0.00)");
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':message' => $details
            ]);
            $success_msg = 'Registration form submitted successfully! Welcome to the Membley SDA Church family.';
        } catch (PDOException $e) {
            $error_msg = 'An error occurred while submitting. Please try again later. (' . $e->getMessage() . ')';
        }
    }
}

require_once 'includes/header.php';
?>

<section style="background-color: var(--primary-dark); color: white; padding: 4rem 0; text-align: center; background-image: linear-gradient(rgba(4,25,40,0.8), rgba(4,25,40,0.8)), url('https://images.unsplash.com/photo-1438232992991-995b7058bbb3?auto=format&fit=crop&q=80&w=1200'); background-size: cover; background-position: center;">
    <div class="container">
        <h1 style="color: white; font-size: 2.75rem; margin-bottom: 0.5rem;">Church Member Portal</h1>
        <p style="color: var(--accent); font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Update your church records & register as a member</p>
    </div>
</section>

<section class="section-padding container" style="max-width: 800px;">
    
    <?php if (!empty($success_msg)): ?>
        <div style="background-color: rgba(46,133,64,0.1); color: #2e8540; border: 1px solid rgba(46,133,64,0.2); padding: 1rem; border-radius: 8px; margin-bottom: 2rem; font-weight: 600;">
            <i class="fa-solid fa-circle-check"></i> <?php echo $success_msg; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_msg)): ?>
        <div style="background-color: rgba(217,83,79,0.1); color: #d9534f; border: 1px solid rgba(217,83,79,0.2); padding: 1rem; border-radius: 8px; margin-bottom: 2rem; font-weight: 600;">
            <i class="fa-solid fa-triangle-exclamation"></i> <?php echo $error_msg; ?>
        </div>
    <?php endif; ?>

    <div style="background-color: var(--bg-white); padding: 3rem 2.5rem; border-radius: 12px; box-shadow: var(--shadow-md); border: 1px solid var(--border-color);">
        <h2 style="color: var(--primary); margin-bottom: 1rem; font-size: 1.8rem; text-align: center;">New Member Registration Form</h2>
        <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 2.5rem; text-align: center; max-width: 600px; margin-left: auto; margin-right: auto;">
            Please fill in this form to register as a regular member of the Membley Seventh-day Adventist Church. Your information will be kept confidential.
        </p>

        <form action="members.php" method="POST">
            
            <h3 style="color: var(--primary); font-size: 1.2rem; border-bottom: 2px solid var(--border-color); padding-bottom: 0.5rem; margin-bottom: 1.5rem;"><i class="fa-solid fa-user"></i> Personal Identification</h3>
            
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label" for="name">Full Name *</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="First Middle Last Name" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="dob">Date of Birth *</label>
                    <input type="date" id="dob" name="dob" class="form-control" required>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label" for="gender">Gender *</label>
                    <select id="gender" name="gender" class="form-control" style="background-color: white;">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number *</label>
                    <input type="tel" id="phone" name="phone" class="form-control" placeholder="e.g. +254 700 123456" required>
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label" for="email">Email Address *</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="e.g. john@example.com" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="address">Physical Address / Estate *</label>
                    <input type="text" id="address" name="address" class="form-control" placeholder="e.g. Ruiru, Membley, Court B" required>
                </div>
            </div>

            <h3 style="color: var(--primary); font-size: 1.2rem; border-bottom: 2px solid var(--border-color); padding-bottom: 0.5rem; margin-top: 2rem; margin-bottom: 1.5rem;"><i class="fa-solid fa-church"></i> Church Affiliation Details</h3>
            
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label" for="ss_class">Sabbath School Class Name</label>
                    <input type="text" id="ss_class" name="ss_class" class="form-control" placeholder="e.g. Bereans, Youth, etc.">
                </div>
                <div class="form-group">
                    <label class="form-label" for="prev_church">Previous Church / Transfer From</label>
                    <input type="text" id="prev_church" name="prev_church" class="form-control" placeholder="e.g. Lavington SDA Church">
                </div>
            </div>

            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label" for="baptised">Baptised? *</label>
                    <select id="baptised" name="baptised" class="form-control" style="background-color: white;">
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="baptism_date">Baptism Date (approximate)</label>
                    <input type="date" id="baptism_date" name="baptism_date" class="form-control">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="notes">Special Notes or Pastoral Request</label>
                <textarea id="notes" name="notes" class="form-control" placeholder="Any information or transfer request details you would like to pass to the clerk..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.1rem; padding: 0.85rem; margin-top: 1.5rem;">
                <i class="fa-solid fa-circle-check"></i> Submit Registration
            </button>
        </form>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
