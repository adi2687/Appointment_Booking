<?php
session_start();
require "../dbh.inc.php";

// Get doctor name and rating from POST
$name = $_POST['doc_name'];
$rating = $_POST['rating'];

// Split the name into first and last name
$name_parts = explode(" ", $name);
$fname = isset($name_parts[0]) ? $name_parts[0] : '';
$lname = isset($name_parts[1]) ? $name_parts[1] : '';

// Query to get the current rating of the doctor
$query1 = "SELECT rating FROM doctors WHERE fname = :fname AND lname = :lname";
$stmt = $pdo->prepare($query1);
$stmt->bindParam(':fname', $fname);
$stmt->bindParam(':lname', $lname);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC); // Use fetch() to get a single result
$rating3=$rating;
if ($result) {
    // Get the previous rating of the doctor
    $rating_prev = $result['rating'];
    
    // Query to get the count of ratings for the doctor
    $doc = $fname . " " . $lname;
    $query = "SELECT COUNT(*) as count FROM rating WHERE doctor = :doc";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':doc', $doc);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch count

    $count = $result['count'];

    // If there are existing ratings, calculate the new rating
    if ($count > 0) {
        $rating = ($rating_prev * $count + $rating) / ($count + 1);
        if ($rating > 5) {
            $rating = 5;  // Ensure rating does not exceed 5
        }
    } else {
        // If no previous ratings, use the new rating
        $rating = $rating;
    }

    // Update the doctor's rating in the doctors table
    $query1 = "UPDATE doctors SET rating = :rating WHERE fname = :fname AND lname = :lname";
    $stmt1 = $pdo->prepare($query1);
    $stmt1->bindParam(':fname', $fname);
    $stmt1->bindParam(':lname', $lname);
    $stmt1->bindParam(':rating', $rating);
    $stmt1->execute();

    // Get the patient ID from the session
    $patient_id = $_SESSION['unique_id'];
    $doctor = $name;

    // Insert the new rating into the rating table
    $query2 = "INSERT INTO rating (patient_id, doctor, rate) VALUES (:patient_id, :doctor, :rating3)";
    $stmt = $pdo->prepare($query2);
    $stmt->bindParam(':patient_id', $patient_id);
    $stmt->bindParam(':doctor', $doctor);
    $stmt->bindParam(':rating3', $rating3);
    $stmt->execute();
header("Location: ../profile");
    echo "done";
} else {
    echo "Doctor not found.";
}
?>
