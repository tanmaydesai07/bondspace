<?php
// Start session if you are using sessions
session_start();



// The rest of your page code here
?>

<!DOCTYPE html>

<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>BondSpace</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="css/index.css">
</head>

<body>
    <div class="hero">
        <video autoplay muted loop playsinline class="background-video">
            <source src="assets/blackhole-video.mp4" type="video/mp4">
        </video>
        <nav>

            <img src="assets/bondspacelogo.png" class="logo">
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="explore.php">Explore</a></li>
                <li><a href="createbond.php">Create Bond</a></li>
                <li><a href="joined_bonds.php">Joined Bonds</a></li>
            </ul>
        </nav>
        <div class="content">
            <h1>Welcome To <span class="bond">Bond</span><span class="space">Space</span></h1>
            <p>Your community platform to connect with like-minded individuals.</p>
            <a href="register.php" class="register-btn">Join</a>
            <a href="login.php" class="login-btn">Login</a>
        </div>
    </div>

    <div class="sections">

        <!-- Features Section -->
        <section class="features">
            <h2>What We Offer</h2>


            <div class="feature">

                <img src="assets/search.jpg" alt="Explore Communities">
                <div class="text">

                    <h3>Explore Communities</h3>
                    <p>Join groups that match your interests and hobbies. Discover new communities tailored to your passions, whether you’re into tech, arts, fitness, or learning something new. Connect with like-minded people and expand your social network.</p>

                </div>
            </div>

            <div class="feature">
                <img src="assets/community.jpg" alt="Communities">
                <div class="text">

                    <h3>Engage with Members</h3>
                    <p>Participate in discussions and connect with others. Dive into conversations that spark your curiosity, ask questions, and share your unique insights. Whether it’s a casual chat, a debate, or a knowledge-sharing session, BondSpace lets you engage in real-time with a community that values your voice. Make new friends, find mentors, or simply enjoy connecting with people who share your passions. Together, let’s create meaningful connections that last.</p>

                </div>
            </div>
            <div class="feature">
                <img src="assets/trend.jpg" alt="Stay Updated">
                <div class="text">

                    <h3>Stay Updated</h3>
                    <p>Follow trending topics and posts in real-time to keep up with what matters most. Get instant notifications on the latest discussions, popular posts, and community updates. With BondSpace, you’ll always be one step ahead, discovering new insights and exploring emerging topics as they happen. Never miss an important conversation—stay informed and stay connected with a live feed that brings the pulse of the community to your fingertips.</p>

                </div>
            </div>

        </section>
        <!-- Testimonials Section -->
        <section class="testimonials">
            <h2>What Our Users Say</h2>
            <div class="testimonial">
                <blockquote>
                    <p>"BondSpace helped me connect with people who share my passions!"</p>
                    <footer>- Seon Boadita</footer>
                </blockquote>
            </div>
            <div class="testimonial">
                <blockquote>
                    <p>"I love being part of such an engaging community."</p>
                    <footer>- Rhugved Dangui</footer>
                </blockquote>
            </div>
        </section>


        <!-- Call-to-Action Section -->
        <section class="cta">
    <div class="cta-content">
        <h2>Ready to Join the BondSpace Community?</h2>
        <p>Connect with like-minded individuals, discover new interests, and become a part of a growing community. Don’t miss out on exciting discussions and exclusive content!</p>
        <a href="register.php" class="btn">Sign Up Today</a>
    </div>
</section>

        <!-- Footer -->
        <footer>
            <div class="footer-container">
                <div class="footer-about">
                    <h3>About BondSpace</h3>
                    <p>Your gateway to connecting with communities and discovering your interests. BondSpace helps you explore, engage, and stay updated in a unique and interactive way.</p>
                </div>

                <div class="footer-links">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                        <li><a href="terms.php">Terms of Service</a></li>
                        <li><a href="privacy.php">Privacy Policy</a></li>
                    </ul>
                </div>

                <div class="footer-contact">
                    <h3>Contact Us</h3>
                    <p>Email: support@bondspace.com</p>
                    <p>Phone: +1 (123) 456-7890</p>
                    <div class="footer-social">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?php echo date("Y"); ?> BondSpace. All rights reserved.</p>
            </div>
        </footer>

    </div>


</body>

</html>