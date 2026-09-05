<?php
require_once 'includes/db.php';

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);
    $pledge_type = trim($_POST['pledge_type'] ?? 'Development (DV)');
    $notes = trim($_POST['notes'] ?? '');

    if (empty($name) || empty($email) || $amount <= 0) {
        $error_msg = 'Please fill in all required fields and enter a valid amount.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO submissions (type, name, email, phone, subject_message, amount) VALUES ('pledge', :name, :email, :phone, :message, :amount)");
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':message' => "Pledge Allocation: $pledge_type. Notes: $notes",
                ':amount' => $amount
            ]);
            $success_msg = 'Thank you! Your pledge has been registered successfully. May God bless you abundantly.';
        } catch (PDOException $e) {
            $error_msg = 'Failed to submit pledge. Please try again later.';
        }
    }
}

require_once 'includes/header.php';
?>

<section style="background-color: var(--primary-dark); color: white; padding: 3.5rem 0; text-align: center; background-image: linear-gradient(rgba(4,25,40,0.85), rgba(4,25,40,0.85)), url('https://images.unsplash.com/photo-1438232992991-995b7058bbb3?auto=format&fit=crop&q=80&w=1200'); background-size: cover; background-position: center;">
    <div class="container">
        <h1 style="color: white; font-size: 2.5rem; margin-bottom: 0.5rem;">Worship Through Giving</h1>
        <p style="color: var(--accent); font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">"Bring the whole tithe into the storehouse..." — Malachi 3:10</p>
    </div>
</section>

<section class="section-padding container">
    
    <?php if (!empty($success_msg)): ?>
        <div style="background-color: rgba(46,133,64,0.1); color: #2e8540; border: 1px solid rgba(46,133,64,0.2); padding: 1rem; border-radius: 8px; margin-bottom: 2rem; font-weight: 600;">
            <i class="fa-solid fa-circle-check"></i> <?php echo htmlspecialchars($success_msg); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_msg)): ?>
        <div style="background-color: rgba(217,83,79,0.1); color: #d9534f; border: 1px solid rgba(217,83,79,0.2); padding: 1rem; border-radius: 8px; margin-bottom: 2rem; font-weight: 600;">
            <i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error_msg); ?>
        </div>
    <?php endif; ?>

    <div class="giving-grid">
                <div>
                        <div style="background: linear-gradient(135deg, #1b4332 0%, #2d6a4f 100%); color: #ffffff; border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: var(--shadow-md);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <span style="background: #25d366; color: #041928; font-weight: 800; padding: 0.25rem 0.75rem; border-radius: 50px; font-size: 0.85rem;">
                        <i class="fa-solid fa-mobile-screen-button"></i> M-PESA PAYBILL
                    </span>
                    <span style="font-size: 0.85rem; opacity: 0.9;">Membley SDA</span>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; background: rgba(0,0,0,0.2); padding: 1rem 1.25rem; border-radius: 8px;">
                    <div>
                        <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.8;">Pay Bill No</div>
                        <div style="font-family: monospace; font-size: 2.2rem; font-weight: 800; color: #ffdd67; letter-spacing: 2px;">4141491</div>
                    </div>
                    <button type="button" class="copy-btn" data-copy="4141491" style="background: #ffdd67; color: #041928; border: none; font-weight: 700; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer;">
                        <i class="fa-regular fa-copy"></i> Copy Paybill
                    </button>
                </div>
            </div>

                        <div style="background: var(--bg-white); border-radius: 12px; padding: 1.5rem; border: 1px solid var(--border-color); margin-bottom: 1.5rem;">
                <h3 style="color: var(--primary); font-size: 1.2rem; margin-bottom: 1rem;">
                    <i class="fa-solid fa-bolt" style="color: var(--accent);"></i> How to Pay (3 Easy Steps)
                </h3>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem; text-align: center;">
                    <div style="background: var(--bg-light); padding: 1rem; border-radius: 8px;">
                        <div style="background: var(--primary); color: #fff; width: 28px; height: 28px; border-radius: 50%; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.5rem;">1</div>
                        <div style="font-weight: 700; font-size: 0.9rem;">Select Pay Bill</div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">In Lipa na M-PESA</div>
                    </div>

                    <div style="background: var(--bg-light); padding: 1rem; border-radius: 8px;">
                        <div style="background: var(--primary); color: #fff; width: 28px; height: 28px; border-radius: 50%; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.5rem;">2</div>
                        <div style="font-weight: 700; font-size: 0.9rem;">Paybill: 4141491</div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">Enter Business No</div>
                    </div>

                    <div style="background: var(--bg-light); padding: 1rem; border-radius: 8px;">
                        <div style="background: var(--primary); color: #fff; width: 28px; height: 28px; border-radius: 50%; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 0.5rem;">3</div>
                        <div style="font-weight: 700; font-size: 0.9rem;">Account: Amount + Code</div>
                        <div style="font-size: 0.8rem; color: var(--text-muted);">e.g. 5000T or 2000LCB</div>
                    </div>
                </div>
            </div>

                        <div style="background: var(--bg-white); border-radius: 12px; padding: 1.5rem; border: 1px solid var(--border-color); margin-bottom: 1.5rem;">
                <h3 style="color: var(--primary); font-size: 1.2rem; margin-bottom: 0.5rem;">
                    <i class="fa-solid fa-list" style="color: var(--accent);"></i> Purpose Codes (Use in Account line)
                </h3>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">Put your amount followed by any of these codes:</p>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; background: var(--bg-light); padding: 0.5rem 0.75rem; border-radius: 6px;">
                        <strong style="background: var(--primary); color: #fff; padding: 0.15rem 0.4rem; border-radius: 4px; font-family: monospace; font-size: 0.9rem; min-width: 45px; text-align: center;">T</strong>
                        <span style="font-size: 0.9rem;">Tithe</span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.5rem; background: var(--bg-light); padding: 0.5rem 0.75rem; border-radius: 6px;">
                        <strong style="background: var(--primary); color: #fff; padding: 0.15rem 0.4rem; border-radius: 4px; font-family: monospace; font-size: 0.9rem; min-width: 45px; text-align: center;">WB</strong>
                        <span style="font-size: 0.9rem;">Wages & Bills</span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.5rem; background: var(--bg-light); padding: 0.5rem 0.75rem; border-radius: 6px;">
                        <strong style="background: var(--primary); color: #fff; padding: 0.15rem 0.4rem; border-radius: 4px; font-family: monospace; font-size: 0.9rem; min-width: 45px; text-align: center;">LCB</strong>
                        <span style="font-size: 0.9rem;">Local Church Budget</span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.5rem; background: var(--bg-light); padding: 0.5rem 0.75rem; border-radius: 6px;">
                        <strong style="background: var(--primary); color: #fff; padding: 0.15rem 0.4rem; border-radius: 4px; font-family: monospace; font-size: 0.9rem; min-width: 45px; text-align: center;">DV</strong>
                        <span style="font-size: 0.9rem;">Development</span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.5rem; background: var(--bg-light); padding: 0.5rem 0.75rem; border-radius: 6px;">
                        <strong style="background: var(--primary); color: #fff; padding: 0.15rem 0.4rem; border-radius: 4px; font-family: monospace; font-size: 0.9rem; min-width: 45px; text-align: center;">CO</strong>
                        <span style="font-size: 0.9rem;">Combined Offering</span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.5rem; background: var(--bg-light); padding: 0.5rem 0.75rem; border-radius: 6px;">
                        <strong style="background: var(--primary); color: #fff; padding: 0.15rem 0.4rem; border-radius: 4px; font-family: monospace; font-size: 0.9rem; min-width: 45px; text-align: center;">CME</strong>
                        <span style="font-size: 0.9rem;">Camp Meeting Expenses</span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.5rem; background: var(--bg-light); padding: 0.5rem 0.75rem; border-radius: 6px;">
                        <strong style="background: var(--primary); color: #fff; padding: 0.15rem 0.4rem; border-radius: 4px; font-family: monospace; font-size: 0.9rem; min-width: 45px; text-align: center;">CMO</strong>
                        <span style="font-size: 0.9rem;">Camp Meeting Offering</span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.5rem; background: var(--bg-light); padding: 0.5rem 0.75rem; border-radius: 6px;">
                        <strong style="background: var(--primary); color: #fff; padding: 0.15rem 0.4rem; border-radius: 4px; font-family: monospace; font-size: 0.9rem; min-width: 45px; text-align: center;">TS</strong>
                        <span style="font-size: 0.9rem;">Thirteenth Sabbath</span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.5rem; background: var(--bg-light); padding: 0.5rem 0.75rem; border-radius: 6px;">
                        <strong style="background: var(--primary); color: #fff; padding: 0.15rem 0.4rem; border-radius: 4px; font-family: monospace; font-size: 0.9rem; min-width: 45px; text-align: center;">AWR</strong>
                        <span style="font-size: 0.9rem;">Evangelism</span>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.5rem; background: var(--bg-light); padding: 0.5rem 0.75rem; border-radius: 6px;">
                        <strong style="background: var(--primary); color: #fff; padding: 0.15rem 0.4rem; border-radius: 4px; font-family: monospace; font-size: 0.9rem; min-width: 45px; text-align: center;">TG</strong>
                        <span style="font-size: 0.9rem;">Thanksgiving / Special</span>
                    </div>
                </div>

                                <div style="margin-top: 1rem; background: #eef6ff; border-left: 4px solid var(--primary-light); padding: 0.75rem 1rem; border-radius: 0 6px 6px 0;">
                    <div style="font-weight: 700; font-size: 0.85rem; color: var(--primary);">Quick Examples:</div>
                    <div style="font-size: 0.85rem; margin-top: 0.2rem;">
                        • <code>2000LCB</code> (2,000 for Local Church Budget)<br>
                        • <code>5000T 2000WB 1000DV</code> (5,000 Tithe, 2,000 Wages & Bills, 1,000 Development)
                    </div>
                </div>
            </div>

                        <div style="background: var(--bg-white); border-radius: 12px; padding: 1.25rem; border: 1px solid var(--border-color);">
                <div style="font-weight: 700; font-size: 0.95rem; color: var(--primary);"><i class="fa-solid fa-building-columns"></i> Cooperative Bank Transfer</div>
                <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;">
                    <strong>Account Name:</strong> Membley SDA Church | <strong>Branch:</strong> Ruiru Branch
                </div>
            </div>
        </div>

                <div style="background-color: var(--bg-white); padding: 2rem; border-radius: 12px; box-shadow: var(--shadow-md); border: 1px solid var(--border-color);">
            <h2 style="color: var(--primary); margin-bottom: 0.5rem; font-size: 1.6rem; text-align: center;">Submit a Pledge</h2>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.5rem; text-align: center;">Commit to supporting church projects or tithes.</p>

            <form action="giving.php" method="POST">
                <div class="form-group">
                    <label class="form-label" for="name">Full Name *</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="e.g. John Doe" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">Email Address *</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="e.g. john@example.com" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" placeholder="e.g. +254 700 123456">
                </div>
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="amount">Pledge Amount (KES) *</label>
                        <input type="number" id="amount" name="amount" class="form-control" min="1" step="any" placeholder="5000" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="pledge_type">Allocation</label>
                        <select id="pledge_type" name="pledge_type" class="form-control" style="background-color: white;">
                            <option value="Tithe (T)">Tithe (T)</option>
                            <option value="Wages & Bills (WB)">Wages & Bills (WB)</option>
                            <option value="Local Church Budget (LCB)">Local Church Budget (LCB)</option>
                            <option value="Development (DV)" selected>Development (DV)</option>
                            <option value="Combined Offering (CO)">Combined Offering (CO)</option>
                            <option value="Camp Meeting Expenses (CME)">Camp Meeting Expenses (CME)</option>
                            <option value="Camp Meeting Offering (CMO)">Camp Meeting Offering (CMO)</option>
                            <option value="Thirteenth Sabbath (TS)">Thirteenth Sabbath (TS)</option>
                            <option value="Evangelism (AWR)">Evangelism (AWR)</option>
                            <option value="Thanksgiving / Special (TG)">Thanksgiving / Special (TG)</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" for="notes">Notes / Prayer Requests</label>
                    <textarea id="notes" name="notes" class="form-control" placeholder="Optional note or prayer request..." style="min-height: 80px;"></textarea>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1rem; padding: 0.8rem;"><i class="fa-solid fa-paper-plane"></i> Submit Pledge</button>
            </form>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
