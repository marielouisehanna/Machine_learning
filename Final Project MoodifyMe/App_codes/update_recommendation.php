<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then return error
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    echo json_encode(["success" => false, "message" => "User not logged in"]);
    exit;
}

// Check if request method is POST
if($_SERVER["REQUEST_METHOD"] != "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

// Check if ID and liked parameters are set
if(!isset($_POST["id"]) || !isset($_POST["liked"])) {
    echo json_encode(["success" => false, "message" => "Missing parameters"]);
    exit;
}

// Include database connection
require_once "includes/db_connection.php";

// Get parameters
$recommendation_id = intval($_POST["id"]);
$liked = intval($_POST["liked"]);
$user_id = $_SESSION["user_id"];

// Verify that the recommendation belongs to the current user
$sql = "SELECT * FROM user_recommendations WHERE recommendation_id = ? AND user_id = ?";

if($stmt = mysqli_prepare($conn, $sql)) {
    mysqli_stmt_bind_param($stmt, "ii", $recommendation_id, $user_id);
    
    if(mysqli_stmt_execute($stmt)) {
        mysqli_stmt_store_result($stmt);
        
        if(mysqli_stmt_num_rows($stmt) > 0) {
            // Update the recommendation
            $update_sql = "UPDATE user_recommendations SET liked = ? WHERE recommendation_id = ?";
            
            if($update_stmt = mysqli_prepare($conn, $update_sql)) {
                mysqli_stmt_bind_param($update_stmt, "ii", $liked, $recommendation_id);
                
                if(mysqli_stmt_execute($update_stmt)) {
                    echo json_encode(["success" => true]);
                } else {
                    echo json_encode(["success" => false, "message" => "Error updating recommendation"]);
                }
                
                mysqli_stmt_close($update_stmt);
            } else {
                echo json_encode(["success" => false, "message" => "Error preparing update statement"]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Recommendation not found or does not belong to user"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Error executing statement"]);
    }
    
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(["success" => false, "message" => "Error preparing statement"]);
}

// Close connection
mysqli_close($conn);
?>