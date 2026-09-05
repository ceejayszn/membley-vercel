<?php
require_once 'includes/db.php';
require_once 'includes/header.php';
?>

<section style="background-color: var(--primary-dark); color: white; padding: 4rem 0; text-align: center; background-image: linear-gradient(rgba(4,25,40,0.8), rgba(4,25,40,0.8)), url('https://images.unsplash.com/photo-1438232992991-995b7058bbb3?auto=format&fit=crop&q=80&w=1200'); background-size: cover; background-position: center;">
    <div class="container">
        <h1 style="color: white; font-size: 2.75rem; margin-bottom: 0.5rem;">About Our Church</h1>
        <p style="color: var(--accent); font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Who we are, what we believe, and our mission</p>
    </div>
</section>

<section class="section-padding container">
    <div class="responsive-grid-about">
        <div>
            <h2 style="font-size: 2rem; color: var(--primary); margin-bottom: 1.5rem;">Our History & Journey</h2>
            <p style="margin-bottom: 1.25rem; color: var(--text-muted);">
                Membley Seventh-day Adventist Church was established to cater to the growing spiritual needs of the residents of Membley Estate and the surrounding areas in Ruiru, Kiambu County. What started as a small home cell group of faithful believers has, by God’s grace, grown into a thriving congregation.
            </p>
            <p style="margin-bottom: 1.25rem; color: var(--text-muted);">
                Through active community outreach, robust youth engagement, and a focus on Bible-based teachings, our church has become a lighthouse in the community. We are currently undergoing key development projects to expand our sanctuary and build facilities that serve the neighborhood's social, educational, and spiritual needs.
            </p>
            
            <div class="responsive-grid-2" style="margin-top: 3rem;">
                <div style="background-color: var(--bg-white); padding: 2rem; border-radius: 8px; border-left: 4px solid var(--accent); box-shadow: var(--shadow-sm);">
                    <h3 style="color: var(--primary); margin-bottom: 0.75rem;"><i class="fa-solid fa-bullseye" style="color: var(--accent);"></i> Our Mission</h3>
                    <p style="font-size: 0.95rem; color: var(--text-muted);">To proclaim the everlasting gospel of Jesus Christ in the context of the three angels' messages of Revelation 14:6-12 to all people in Membley and beyond, leading them to accept Jesus as personal Savior.</p>
                </div>
                <div style="background-color: var(--bg-white); padding: 2rem; border-radius: 8px; border-left: 4px solid var(--primary); box-shadow: var(--shadow-sm);">
                    <h3 style="color: var(--primary); margin-bottom: 0.75rem;"><i class="fa-solid fa-eye" style="color: var(--primary);"></i> Our Vision</h3>
                    <p style="font-size: 0.95rem; color: var(--text-muted);">To see a loving, Christ-centered, and active congregation in Membley prepared for the second coming of our Lord Jesus Christ, making disciples of all nations.</p>
                </div>
            </div>
        </div>

        <div style="background-color: var(--bg-white); padding: 2.5rem; border-radius: 12px; box-shadow: var(--shadow-md); border: 1px solid var(--border-color);">
            <h3 style="color: var(--primary); margin-bottom: 1.5rem; text-align: center; border-bottom: 1px solid var(--border-color); padding-bottom: 0.75rem;">Fundamental Beliefs</h3>
            <p style="font-size: 0.95rem; color: var(--text-muted); margin-bottom: 1.25rem;">
                Seventh-day Adventists accept the Bible as their only creed and hold certain fundamental beliefs to be the teaching of the Holy Scriptures. These beliefs, currently 28 in number, describe the character of God, His relationship with humanity, and His plan of salvation.
            </p>
            <p style="font-size: 0.95rem; color: var(--text-muted); margin-bottom: 1.5rem;">
                Our core pillars include the Sabbath (worship on the Seventh day), the Second Coming of Jesus, the State of the Dead, and Salvation by Grace through faith in Jesus Christ.
            </p>
            <a href="https://gc.adventist.org/beliefs/" target="_blank" class="btn btn-outline" style="width: 100%;"><i class="fa-solid fa-up-right-from-square"></i> Read 28 Beliefs on GC Website</a>
        </div>
    </div>
</section>

<section style="background-color: var(--bg-white);" class="section-padding">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Leadership</span>
            <h2 class="section-title">Our Leaders & Servants</h2>
        </div>
        
        <div class="grid-3">
                        <div class="card" style="text-align: center;">
                <div style="height: 250px; background-image: url('https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=400'); background-size: cover; background-position: center;"></div>
                <div class="card-body">
                    <h3 style="color: var(--primary); margin-bottom: 0.25rem;">Pr. Joseph Mwaniki</h3>
                    <span style="color: var(--accent); font-weight: 700; font-size: 0.85rem; text-transform: uppercase;">District Pastor</span>
                    <p style="font-size: 0.9rem; color: var(--text-muted); margin-top: 1rem;">Responsible for spiritual direction, counseling, and district administration.</p>
                </div>
            </div>

                        <div class="card" style="text-align: center;">
                <div style="height: 250px; background-image: url('https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&q=80&w=400'); background-size: cover; background-position: center;"></div>
                <div class="card-body">
                    <h3 style="color: var(--primary); margin-bottom: 0.25rem;">Elder Daniel Mwangi</h3>
                    <span style="color: var(--primary-light); font-weight: 700; font-size: 0.85rem; text-transform: uppercase;">Head Elder</span>
                    <p style="font-size: 0.9rem; color: var(--text-muted); margin-top: 1rem;">Supports pastoral work, coordinates local church ministries, and chairs the board.</p>
                </div>
            </div>

                        <div class="card" style="text-align: center;">
                <div style="height: 250px; background-image: url('https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&q=80&w=400'); background-size: cover; background-position: center;"></div>
                <div class="card-body">
                    <h3 style="color: var(--primary); margin-bottom: 0.25rem;">Sis. Grace Wambui</h3>
                    <span style="color: var(--primary-light); font-weight: 700; font-size: 0.85rem; text-transform: uppercase;">Church Treasurer</span>
                    <p style="font-size: 0.9rem; color: var(--text-muted); margin-top: 1rem;">Oversees tithe records, offering allocations, building funds, and financial reports.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
