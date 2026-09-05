<?php
require_once 'includes/db.php';
require_once 'includes/header.php';

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'youth';
?>

<section style="background-color: var(--primary-dark); color: white; padding: 4rem 0; text-align: center; background-image: linear-gradient(rgba(4,25,40,0.8), rgba(4,25,40,0.8)), url('https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&q=80&w=1200'); background-size: cover; background-position: center;">
    <div class="container">
        <h1 style="color: white; font-size: 2.75rem; margin-bottom: 0.5rem;">Church Departments & Ministries</h1>
        <p style="color: var(--accent); font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Discover a place to grow and serve</p>
    </div>
</section>

<section class="section-padding container">
    <div class="grid-3">
        
                <div class="card" style="text-align: center;">
            <div class="card-img" style="background-image: url('assets/images/adventurer_logo.png'); background-size: 50%; background-repeat: no-repeat; background-color: var(--bg-light); background-position: center; border-bottom: 1px solid var(--border-color);"></div>
            <div class="card-body">
                <h3 class="card-title">Adventurous Club</h3>
                <span class="section-subtitle" style="font-size: 0.75rem; margin-bottom: 1rem;">Ages 0 - 9</span>
                <p class="card-desc" style="font-size: 0.9rem;">
                    Engaging our youngest members in fun, welcoming, and life-changing activities to introduce them to the character of God.
                </p>
            </div>
        </div>

                <div class="card" style="text-align: center;">
            <div class="card-img" style="background-image: url('assets/images/pathfinder_logo.png'); background-size: 50%; background-repeat: no-repeat; background-color: var(--bg-light); background-position: center; border-bottom: 1px solid var(--border-color);"></div>
            <div class="card-body">
                <h3 class="card-title">Pathfinder Club</h3>
                <span class="section-subtitle" style="font-size: 0.75rem; margin-bottom: 1rem;">Ages 10 - 15</span>
                <p class="card-desc" style="font-size: 0.9rem;">
                    Developing life-saving skills, camping, honors classes, and exploring God's creation through a worldwide club movement.
                </p>
            </div>
        </div>

                <div class="card" style="text-align: center;">
            <div class="card-img" style="background-image: url('https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&q=80&w=600'); border-bottom: 1px solid var(--border-color);"></div>
            <div class="card-body">
                <h3 class="card-title">Adventist Youth (AY)</h3>
                <span class="section-subtitle" style="font-size: 0.75rem; margin-bottom: 1rem;">Ages 16+</span>
                <p class="card-desc" style="font-size: 0.9rem;">
                    Empowering young adults for service with lively discussions, community outreach, and powerful praise sessions every Sabbath afternoon.
                </p>
            </div>
        </div>

                <div class="card" style="text-align: center;">
            <div class="card-img" style="background-image: url('https://images.unsplash.com/photo-1438232992991-995b7058bbb3?auto=format&fit=crop&q=80&w=600'); border-bottom: 1px solid var(--border-color);"></div>
            <div class="card-body">
                <h3 class="card-title">Music & Choir</h3>
                <span class="section-subtitle" style="font-size: 0.75rem; margin-bottom: 1rem;">All Ages</span>
                <p class="card-desc" style="font-size: 0.9rem;">
                    Enhancing our worship experience through uplifting hymns, spiritual songs, and dedicated choir groups.
                </p>
            </div>
        </div>

                <div class="card" style="text-align: center;">
            <div class="card-img" style="background-image: url('https://images.unsplash.com/photo-1573497019940-1c28c88b4f3e?auto=format&fit=crop&q=80&w=600'); border-bottom: 1px solid var(--border-color);"></div>
            <div class="card-body">
                <h3 class="card-title">Women's Ministries</h3>
                <span class="section-subtitle" style="font-size: 0.75rem; margin-bottom: 1rem;">Adult Women</span>
                <p class="card-desc" style="font-size: 0.9rem;">
                    Nurturing, supporting, and inspiring women in their spiritual walk through prayer cells, seminars, and charity programs.
                </p>
            </div>
        </div>

                <div class="card" style="text-align: center;">
            <div class="card-img" style="background-image: url('https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=600'); border-bottom: 1px solid var(--border-color);"></div>
            <div class="card-body">
                <h3 class="card-title">Adventist Men (AMO)</h3>
                <span class="section-subtitle" style="font-size: 0.75rem; margin-bottom: 1rem;">Adult Men</span>
                <p class="card-desc" style="font-size: 0.9rem;">
                    Uniting men in service to strengthen their roles as spiritual leaders in their families, church, and local communities.
                </p>
            </div>
        </div>

    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
