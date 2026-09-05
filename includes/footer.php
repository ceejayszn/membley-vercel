<?php
?>
        <footer class="main-footer">
        <div class="container">
            <div class="footer-grid">
                
                                <div class="footer-col">
                    <h3>Membley SDA Church</h3>
                    <p style="font-size: 0.95rem; color: rgba(255, 255, 255, 0.7); margin-bottom: 1.5rem;">
                        A Bible-believing Christian community located in Membley, Ruiru, dedicated to sharing the everlasting gospel of Jesus Christ in the light of the three angels' messages.
                    </p>
                    <div class="social-icons">
                        <a href="https://www.instagram.com/membleyadventist/" target="_blank" class="social-icon-btn"><i class="fa-brands fa-instagram"></i></a>
                        <a href="https://www.tiktok.com/@membleyadventist" target="_blank" class="social-icon-btn"><i class="fa-brands fa-tiktok"></i></a>
                        <a href="#" class="social-icon-btn"><i class="fa-brands fa-youtube"></i></a>
                    </div>
                </div>

                                <div class="footer-col">
                    <h3>Worship Services</h3>
                    <ul class="footer-links" style="color: rgba(255, 255, 255, 0.7); font-size: 0.95rem;">
                        <li style="margin-bottom: 0.5rem;"><strong>Sabbath School:</strong> Saturdays at 9:00 AM</li>
                        <li style="margin-bottom: 0.5rem;"><strong>Divine Worship:</strong> Saturdays at 11:00 AM</li>
                        <li style="margin-bottom: 0.5rem;"><strong>Adventist Youth (AY):</strong> Saturdays at 4:00 PM</li>
                    </ul>
                </div>

                                <div class="footer-col">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="about.php">Our Beliefs</a></li>
                        <li><a href="ministries.php">Church Departments</a></li>
                        <li><a href="events.php">Upcoming Events</a></li>
                        <li><a href="giving.php">Tithe & Offering</a></li>
                        <li><a href="admin/login.php"><i class="fa-solid fa-lock"></i> Admin Portal</a></li>
                    </ul>
                </div>

                                <div class="footer-col">
                    <h3>Get In Touch</h3>
                    <ul class="footer-links" style="color: rgba(255, 255, 255, 0.7); font-size: 0.95rem;">
                        <li style="margin-bottom: 0.5rem;">
                            <i class="fa-solid fa-location-dot" style="color: var(--accent); margin-right: 0.5rem;"></i> 
                            Membley Estate, Ruiru, Kenya
                        </li>
                        <li style="margin-bottom: 0.5rem;">
                            <i class="fa-solid fa-envelope" style="color: var(--accent); margin-right: 0.5rem;"></i> 
                            <a href="mailto:membleyadventist@gmail.com">membleyadventist@gmail.com</a>
                        </li>
                        <li style="margin-bottom: 0.5rem;">
                            <i class="fa-solid fa-phone" style="color: var(--accent); margin-right: 0.5rem;"></i> 
                            +254 700 000000
                        </li>
                    </ul>
                </div>

            </div>

                        <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Membley Seventh-day Adventist Church. All Rights Reserved. Prepared for God's Glory.</p>
            </div>
        </div>
    </footer>

        <script src="assets/js/main.js"></script>

        <script>
    (function() {
        const pagePath = window.location.pathname.split('/').pop() || 'index.php';
        
        fetch('track.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ page: pagePath, type: 'view' })
        }).catch(() => {});

        document.addEventListener('click', function(e) {
            if (e.target.closest('a, button, .btn')) {
                fetch('track.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ page: pagePath, type: 'click' })
                }).catch(() => {});
            }
        });

        setInterval(function() {
            if (document.visibilityState === 'visible') {
                fetch('track.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ page: pagePath, type: 'time', value: 10 })
                }).catch(() => {});
            }
        }, 10000);
    })();
    </script>
</body>
</html>
