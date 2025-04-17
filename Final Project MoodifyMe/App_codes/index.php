<?php
// Start session
session_start();

// Check if the user is already logged in
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoodifyMe - Understand Your Emotions</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="index.php" class="logo">
                    <i class="fas fa-brain logo-icon"></i>
                    MoodifyMe
                </a>
                <ul class="nav-menu">
                    <li class="nav-item"><a href="index.php" class="nav-link active">Home</a></li>
                    <li class="nav-item"><a href="#about" class="nav-link">About</a></li>
                    <li class="nav-item"><a href="login.php" class="nav-link">Login</a></li>
                    <li class="nav-item"><a href="register.php" class="nav-link">Register</a></li>
                </ul>
                <div class="mobile-menu-toggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero">
            <div class="container">
                <div class="hero-content">
                    <h1>Understand Your Emotions with MoodifyMe</h1>
                    <p>Journal your thoughts, analyze your moods, and get personalized recommendations to improve your emotional well-being</p>
                    <div class="hero-buttons">
                        <a href="register.php" class="btn">Get Started</a>
                        <a href="#about" class="btn btn-outline">Learn More</a>
                    </div>
                </div>
            </div>
        </section>

        <section id="about" class="about-section">
            <div class="container">
                <h2>How MoodifyMe Works</h2>
                <div class="features">
                    <div class="feature">
                        <div class="feature-icon">
                            <i class="fas fa-pen-fancy"></i>
                        </div>
                        <h3>Journal Your Thoughts</h3>
                        <p>Express yourself freely in our intuitive journaling interface. No rules, just write what comes to mind and let our AI do the rest.</p>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">
                            <i class="fas fa-brain"></i>
                        </div>
                        <h3>AI Mood Analysis</h3>
                        <p>Our advanced AI analyzes your journal entries to understand your emotional state using natural language processing technology.</p>
                    </div>
                    <div class="feature">
                        <div class="feature-icon">
                            <i class="fas fa-headphones"></i>
                        </div>
                        <h3>Personalized Recommendations</h3>
                        <p>Get tailored music and movie suggestions that complement or improve your mood, based on your unique emotional patterns.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="cta-section">
            <div class="container">
                <h2>Ready to Start Your Emotional Wellness Journey?</h2>
                <p>Join thousands of users who are improving their mental well-being with MoodifyMe. Our AI-powered journaling platform helps you understand your emotions and provides personalized recommendations to enhance your mood.</p>
                <a href="register.php" class="btn btn-large">Sign Up Now - It's Free!</a>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <h3>MoodifyMe</h3>
                    <p>Understanding emotions, one journal at a time.</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
                <div class="footer-links">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h4>Contact Us</h4>
                    <p><i class="fas fa-envelope"></i> info@moodifyme.com</p>
                    <p><i class="fas fa-phone"></i>76 497 921 </p>
                    <p><i class="fas fa-map-marker-alt"></i> USJ ESIB</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 MoodifyMe. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>