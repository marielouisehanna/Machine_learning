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

// Get user's journal entries
$user_id = $_SESSION["user_id"];
$entries = [];

$sql = "SELECT * FROM journal_entries WHERE user_id = ? ORDER BY created_at DESC LIMIT 5";
if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    
    if(mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        while($row = mysqli_fetch_assoc($result)) {
            $entry = $row;
            
            // Get emotions for this entry
            $emotions_sql = "SELECT e.emotion_name, ee.confidence_score 
                           FROM entry_emotions ee 
                           JOIN emotions e ON ee.emotion_id = e.emotion_id 
                           WHERE ee.entry_id = ? 
                           ORDER BY ee.confidence_score DESC";
            
            if($emotions_stmt = mysqli_prepare($conn, $emotions_sql)) {
                mysqli_stmt_bind_param($emotions_stmt, "i", $row["entry_id"]);
                
                if(mysqli_stmt_execute($emotions_stmt)) {
                    $emotions_result = mysqli_stmt_get_result($emotions_stmt);
                    $entry["emotions"] = [];
                    
                    while($emotion = mysqli_fetch_assoc($emotions_result)) {
                        $entry["emotions"][] = $emotion;
                    }
                }
                
                mysqli_stmt_close($emotions_stmt);
            }
            
            $entries[] = $entry;
        }
    }
    
    mysqli_stmt_close($stmt);
}

// Get recommendations
$recommendations = [];
$recommendations_sql = "SELECT ur.*, 
                      CASE ur.content_type 
                        WHEN 'song' THEN s.title 
                        WHEN 'movie' THEN m.title 
                      END AS title,
                      CASE ur.content_type 
                        WHEN 'song' THEN s.artist 
                        WHEN 'movie' THEN m.director 
                      END AS creator,
                      CASE ur.content_type 
                        WHEN 'song' THEN s.mood 
                        WHEN 'movie' THEN m.mood 
                      END AS mood
                    FROM user_recommendations ur
                    LEFT JOIN songs s ON ur.content_type = 'song' AND ur.content_id = s.song_id
                    LEFT JOIN movies m ON ur.content_type = 'movie' AND ur.content_id = m.movie_id
                    WHERE ur.user_id = ?
                    ORDER BY ur.recommended_at DESC
                    LIMIT 10";

if($stmt = mysqli_prepare($conn, $recommendations_sql)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    
    if(mysqli_stmt_execute($stmt)) {
        $result = mysqli_stmt_get_result($stmt);
        
        while($row = mysqli_fetch_assoc($result)) {
            $recommendations[] = $row;
        }
    }
    
    mysqli_stmt_close($stmt);
}

// Close connection
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MoodifyMe</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="dashboard.php" class="logo">
                    <i class="fas fa-brain logo-icon"></i>
                    MoodifyMe
                </a>
                <ul class="nav-menu">
                    <li class="nav-item"><a href="dashboard.php" class="nav-link active">Dashboard</a></li>
                    <li class="nav-item"><a href="journal.php" class="nav-link">Journal</a></li>
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
            <div class="dashboard-header">
                <h1><i class="fas fa-smile"></i> Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h1>
                <p>Track your emotions and discover personalized recommendations to enhance your mood</p>
                <a href="journal.php" class="btn"><i class="fas fa-pen"></i> Write New Journal Entry</a>
            </div>
            
            <div class="dashboard">
                <div class="card">
                    <h2 class="card-title"><i class="fas fa-book"></i> Recent Journal Entries</h2>
                    <?php if (count($entries) > 0): ?>
                        <div class="journal-entries">
                            <?php foreach ($entries as $entry): ?>
                                <div class="journal-entry">
                                    <p class="entry-date"><i class="far fa-calendar-alt"></i> <?php echo date("F j, Y, g:i a", strtotime($entry["created_at"])); ?></p>
                                    <p class="entry-content"><?php echo htmlspecialchars(substr($entry["entry_text"], 0, 150)) . (strlen($entry["entry_text"]) > 150 ? "..." : ""); ?></p>
                                    <?php if (isset($entry["emotions"]) && count($entry["emotions"]) > 0): ?>
                                        <div class="entry-emotions">
                                            <?php foreach (array_slice($entry["emotions"], 0, 3) as $emotion): ?>
                                                <span class="emotion-tag"><?php echo htmlspecialchars($emotion["emotion_name"]); ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-book-open empty-icon"></i>
                            <p>You haven't written any journal entries yet.</p>
                            <a href="journal.php" class="btn"><i class="fas fa-pen"></i> Start Journaling</a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="card">
                    <h2 class="card-title"><i class="fas fa-headphones"></i> Your Recommendations</h2>
                    <?php if (count($recommendations) > 0): ?>
                        <div class="recommendation-list">
                            <?php foreach ($recommendations as $recommendation): ?>
                                <div class="recommendation-item" data-id="<?php echo $recommendation["recommendation_id"]; ?>">
                                    <?php if ($recommendation["content_type"] === 'song'): ?>
                                        <i class="fas fa-music recommendation-icon"></i>
                                    <?php else: ?>
                                        <i class="fas fa-film recommendation-icon"></i>
                                    <?php endif; ?>
                                    <h3 class="recommendation-title"><?php echo htmlspecialchars($recommendation["title"]); ?></h3>
                                    <p class="recommendation-creator"><?php echo htmlspecialchars($recommendation["creator"]); ?></p>
                                    <p class="recommendation-type"><?php echo ucfirst($recommendation["content_type"]); ?> • <?php echo htmlspecialchars($recommendation["mood"]); ?></p>
                                    <div class="recommendation-actions">
                                        <button class="like-btn <?php echo ($recommendation["liked"] === 1) ? "active" : ""; ?>" data-id="<?php echo $recommendation["recommendation_id"]; ?>" title="Like this recommendation">
                                            <i class="fas fa-thumbs-up"></i>
                                        </button>
                                        <button class="dislike-btn <?php echo ($recommendation["liked"] === 0) ? "active" : ""; ?>" data-id="<?php echo $recommendation["recommendation_id"]; ?>" title="Dislike this recommendation">
                                            <i class="fas fa-thumbs-down"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-music empty-icon"></i>
                            <p>No recommendations yet. Write a journal entry to get personalized suggestions!</p>
                            <a href="journal.php" class="btn"><i class="fas fa-pen"></i> Write Journal Entry</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="emotional-stats-section">
                <h2><i class="fas fa-chart-line"></i> Your Emotional Journey</h2>
                <p>Keep journaling to see patterns in your emotions over time.</p>
                <div class="emotion-stats-cta">
                    <a href="journal.php" class="btn"><i class="fas fa-pen"></i> Write New Entry</a>
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