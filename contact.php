<?php
require_once 'includes/db.php';

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = trim($_POST['form_type'] ?? 'contact'); // 'contact' or 'prayer'
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $error_msg = 'Please fill in all required fields (Name, Email, and Message).';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO submissions (type, name, email, phone, subject_message) VALUES (:type, :name, :email, :phone, :message)");
            $stmt->execute([
                ':type' => $type,
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':message' => $message
            ]);
            $success_msg = $type == 'prayer' 
                ? 'Your prayer request has been submitted. Our Elders and Prayer Band will lift you up in prayer.'
                : 'Thank you for reaching out! We have received your message and will respond shortly.';
        } catch (PDOException $e) {
            $error_msg = 'Failed to submit. Please try again later.';
        }
    }
}

require_once 'includes/header.php';
?>

<section style="background-color: var(--primary-dark); color: white; padding: 4rem 0; text-align: center; background-image: linear-gradient(rgba(4,25,40,0.8), rgba(4,25,40,0.8)), url('https://images.unsplash.com/photo-1544427920-c49ccfb85579?auto=format&fit=crop&q=80&w=1200'); background-size: cover; background-position: center;">
    <div class="container">
        <h1 style="color: white; font-size: 2.75rem; margin-bottom: 0.5rem;">Contact & Prayer Request</h1>
        <p style="color: var(--accent); font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">We would love to hear from you or pray with you</p>
    </div>
</section>

<section class="section-padding container">
    
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

    <div style="display: grid; grid-template-columns: 1fr; gap: 4rem; align-items: start;">
                <div style="background-color: var(--bg-white); padding: 2.5rem; border-radius: 12px; box-shadow: var(--shadow-md); border: 1px solid var(--border-color);">
            <h2 style="color: var(--primary); margin-bottom: 1rem; font-size: 1.8rem;"><i class="fa-regular fa-envelope" style="color: var(--accent);"></i> Send Us a Message</h2>
            <p style="font-size: 0.95rem; color: var(--text-muted); margin-bottom: 2rem;">If you have general inquiries, membership questions, or comments, please write to us.</p>
            
            <form action="contact.php" method="POST">
                <input type="hidden" name="form_type" value="contact">
                <div class="form-group">
                    <label class="form-label" for="contact_name">Name *</label>
                    <input type="text" id="contact_name" name="name" class="form-control" placeholder="John Doe" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="contact_email">Email Address *</label>
                    <input type="email" id="contact_email" name="email" class="form-control" placeholder="john@example.com" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="contact_phone">Phone Number</label>
                    <input type="tel" id="contact_phone" name="phone" class="form-control" placeholder="e.g. +254 700 123456">
                </div>
                <div class="form-group">
                    <label class="form-label" for="contact_message">Message *</label>
                    <textarea id="contact_message" name="message" class="form-control" placeholder="How can we help you?" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;"><i class="fa-regular fa-paper-plane"></i> Send Message</button>
            </form>
        </div>

                <div style="display: flex; flex-direction: column; gap: 2rem;">
            
                        <div style="background-color: var(--bg-white); padding: 2.5rem; border-radius: 12px; box-shadow: var(--shadow-md); border: 1px solid var(--border-color);">
                <h2 style="color: var(--primary); margin-bottom: 1rem; font-size: 1.8rem;"><i class="fa-solid fa-hands-praying" style="color: var(--accent);"></i> Prayer Request</h2>
                <p style="font-size: 0.95rem; color: var(--text-muted); margin-bottom: 2rem;">We believe in the power of prayer. Share your burdens or praise reports with our prayer team. Requests can be confidential.</p>
                
                <form action="contact.php" method="POST">
                    <input type="hidden" name="form_type" value="prayer">
                    <div class="form-group">
                        <label class="form-label" for="prayer_name">Name (or 'Anonymous') *</label>
                        <input type="text" id="prayer_name" name="name" class="form-control" placeholder="Your Name or Anonymous" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="prayer_email">Email Address *</label>
                        <input type="email" id="prayer_email" name="email" class="form-control" placeholder="your@email.com" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="prayer_phone">Phone Number (Optional)</label>
                        <input type="tel" id="prayer_phone" name="phone" class="form-control" placeholder="+254 700 123456">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="prayer_message">Prayer Request Details *</label>
                        <textarea id="prayer_message" name="message" class="form-control" placeholder="Share details here..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-accent" style="width: 100%;"><i class="fa-solid fa-heart"></i> Submit Prayer Request</button>
                </form>
            </div>

                        <div style="background-color: var(--bg-white); padding: 1.5rem; border-radius: 12px; box-shadow: var(--shadow-sm); border: 1px solid var(--border-color);">
                <h3 style="color: var(--primary); margin-bottom: 1rem;"><i class="fa-solid fa-location-dot" style="color: var(--accent);"></i> Find Us</h3>
                                <div style="height: 250px; background-color: #e2e8f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-direction: column; text-align: center; padding: 2rem; background-image: linear-gradient(rgba(240,244,248,0.9), rgba(240,244,248,0.9)), url('https://images.unsplash.com/photo-1524661135-423995f22d0b?auto=format&fit=crop&q=80&w=400'); background-size: cover;">
                    <i class="fa-solid fa-map-location-dot" style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem;"></i>
                    <h4 style="color: var(--primary); margin-bottom: 0.25rem;">Membley Seventh-day Adventist Church</h4>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">Membley Estate, off Clayworks, Ruiru, Kiambu County, Kenya</p>
                    <a href="https://maps.google.com" target="_blank" class="btn btn-outline btn-sm"><i class="fa-solid fa-location-arrow"></i> Open in Google Maps</a>
                </div>
            </div>

        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
