<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include database connection
require_once "includes/db_connection.php";

// Define variables
$entry_text = "";
$entry_err = "";
$success_msg = "";

// Process form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate entry
    if(empty(trim($_POST["entry_text"]))) {
        $entry_err = "Please write something in your journal.";
    } else {
        $entry_text = trim($_POST["entry_text"]);
    }
    
    // If there are no errors, save entry to database
    if(empty($entry_err)) {
        // Insert entry into database
        $sql = "INSERT INTO journal_entries (user_id, entry_text) VALUES (?, ?)";
        
        if($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "is", $param_user_id, $param_entry_text);
            
            // Set parameters
            $param_user_id = $_SESSION["user_id"];
            $param_entry_text = $entry_text;
            
            // Execute the statement
            if(mysqli_stmt_execute($stmt)) {
                // Get the ID of the inserted entry
                $entry_id = mysqli_insert_id($conn);
                
                // For this demo, we'll assign some random emotions
                // In a real implementation, you would call your ML model here
                $emotions = array("Joy", "Sadness", "Anger", "Fear", "Surprise", "Disgust", "Neutral");
                $assigned_emotions = array_rand(array_flip($emotions), min(3, count($emotions)));
                
                if(!is_array($assigned_emotions)) {
                    $assigned_emotions = array($assigned_emotions);
                }
                
                foreach($assigned_emotions as $emotion_name) {
                    // Get the emotion ID
                    $emotion_sql = "SELECT emotion_id FROM emotions WHERE emotion_name = ?";
                    
                    if($emotion_stmt = mysqli_prepare($conn, $emotion_sql)) {
                        mysqli_stmt_bind_param($emotion_stmt, "s", $emotion_name);
                        
                        if(mysqli_stmt_execute($emotion_stmt)) {
                            mysqli_stmt_store_result($emotion_stmt);
                            
                            if(mysqli_stmt_num_rows($emotion_stmt) > 0) {
                                mysqli_stmt_bind_result($emotion_stmt, $emotion_id);
                                mysqli_stmt_fetch($emotion_stmt);
                                
                                // Insert into entry_emotions table
                                $insert_emotion_sql = "INSERT INTO entry_emotions (entry_id, emotion_id, confidence_score) VALUES (?, ?, ?)";
                                
                                if($insert_emotion_stmt = mysqli_prepare($conn, $insert_emotion_sql)) {
                                    // Generate a random confidence score between 0.6 and 0.95
                                    $confidence_score = mt_rand(60, 95) / 100;
                                    
                                    mysqli_stmt_bind_param($insert_emotion_stmt, "iid", $entry_id, $emotion_id, $confidence_score);
                                    mysqli_stmt_execute($insert_emotion_stmt);
                                    mysqli_stmt_close($insert_emotion_stmt);
                                }
                            }
                            
                            mysqli_stmt_close($emotion_stmt);
                        }
                    }
                }
                
                // Generate recommendations based on emotions
                // For this demo, we'll just select random songs and movies
                // In a real implementation, you would use a recommendation algorithm
                
                // Select a random song
                $song_sql = "SELECT song_id FROM songs ORDER BY RAND() LIMIT 1";
                $song_result = mysqli_query($conn, $song_sql);
                
                if($song_row = mysqli_fetch_assoc($song_result)) {
                    $song_id = $song_row["song_id"];
                    
                    // Insert song recommendation
                    $song_rec_sql = "INSERT INTO user_recommendations (user_id, content_type, content_id) VALUES (?, 'song', ?)";
                    
                    if($song_rec_stmt = mysqli_prepare($conn, $song_rec_sql)) {
                        mysqli_stmt_bind_param($song_rec_stmt, "ii", $_SESSION["user_id"], $song_id);
                        mysqli_stmt_execute($song_rec_stmt);
                        mysqli_stmt_close($song_rec_stmt);
                    }
                }
                
                // Select a random movie
                $movie_sql = "SELECT movie_id FROM movies ORDER BY RAND() LIMIT 1";
                $movie_result = mysqli_query($conn, $movie_sql);
                
                if($movie_row = mysqli_fetch_assoc($movie_result)) {
                    $movie_id = $movie_row["movie_id"];
                    
                    // Insert movie recommendation
                    $movie_rec_sql = "INSERT INTO user_recommendations (user_id, content_type, content_id) VALUES (?, 'movie', ?)";
                    
                    if($movie_rec_stmt = mysqli_prepare($conn, $movie_rec_sql)) {
                        mysqli_stmt_bind_param($movie_rec_stmt, "ii", $_SESSION["user_id"], $movie_id);
                        mysqli_stmt_execute($movie_rec_stmt);
                        mysqli_stmt_close($movie_rec_stmt);
                    }
                }
                
                // Clear the entry text
                $entry_text = "";
                
                // Set success message
                $success_msg = "Journal entry saved successfully! We've analyzed your mood and created some recommendations for you.";
            } else {
                $entry_err = "Something went wrong. Please try again later.";
            }
            
            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
}

// Close connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Journal - MoodifyMe</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="dashboard.php" class="logo">
                    <img src="images/moodifyme-logo.svg" alt="MoodifyMe Logo" class="logo-img">
                    MoodifyMe
                </a>
                <ul class="nav-menu">
                    <li class="nav-item"><a href="dashboard.php" class="nav-link">Dashboard</a></li>
                    <li class="nav-item"><a href="journal.php" class="nav-link active">Journal</a></li>
                    <li class="nav-item"><a href="logout.php" class="nav-link">Logout</a></li>
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
        <div class="container">
            <div class="journal-container">
                <div class="journal-header">
                    <h2><i class="fas fa-book-open"></i> My Journal</h2>
                    <p class="journal-date"><?php echo date("l, F j, Y"); ?></p>
                </div>
                
                <?php if(!empty($success_msg)): ?>
                    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success_msg; ?></div>
                <?php endif; ?>
                
                <?php if(!empty($entry_err)): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $entry_err; ?></div>
                <?php endif; ?>
                    
                <form id="journal-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="form-group journal-paper">
                        <div class="journal-prompt">
                            <i class="fas fa-feather-alt"></i> <span id="writing-prompt">Express your thoughts and feelings. What's on your mind today?</span>
                        </div>
                        <textarea id="entry-content" name="entry_text" class="journal-textarea <?php echo (!empty($entry_err)) ? 'is-invalid' : ''; ?>" placeholder="Dear Diary..."><?php echo $entry_text; ?></textarea>
                        <div class="journal-page-lines"></div>
                    </div>
                    
                    <div class="journal-tools">
                        <div class="journal-prompts">
                            <button type="button" class="prompt-btn" onclick="changePrompt()"><i class="fas fa-lightbulb"></i> New Prompt</button>
                        </div>
                        <button type="submit" class="save-btn"><i class="fas fa-save"></i> Save Entry</button>
                    </div>
                </form>
                
                <div class="journaling-tips">
                    <h3><i class="fas fa-star"></i> Journaling Tips</h3>
                    <ul>
                        <li>Write freely without judgment - this is your safe space.</li>
                        <li>Try to journal at the same time each day to build a habit.</li>
                        <li>Include both positive and challenging experiences.</li>
                        <li>Consider how your emotions connect to your thoughts and actions.</li>
                        <li>End with something you're grateful for today.</li>
                    </ul>
                </div>
            </div>
        </div>
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
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="journal.php">Journal</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h4>Contact Us</h4>
                    <p><i class="fas fa-envelope"></i> info@moodifyme.com</p>
                    <p><i class="fas fa-phone"></i> +1 (555) 123-4567</p>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Emotion St, Mind City</p>
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