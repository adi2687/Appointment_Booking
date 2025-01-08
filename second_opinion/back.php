<?php

require "../dbh.inc.php";
session_start();

// Retrieve unique ID from session
$unique_id = $_SESSION['unique_id'] ?? null;

if (!$unique_id) {
    echo json_encode(["error" => "User is not logged in."]);
    exit;
}

try {
    // Fetch user details
    $query = "SELECT * FROM users WHERE unique_id = :unique_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':unique_id', $unique_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch appointments
    $query_app = "SELECT * FROM appointments WHERE pat_id = :unique_id AND dosage!=''";
    $stmt_app = $pdo->prepare($query_app);
    $stmt_app->bindParam(':unique_id', $unique_id);
    $stmt_app->execute();
    $appointments = $stmt_app->fetchAll(PDO::FETCH_ASSOC);

    // If no appointments found
    if (empty($appointments)) {
        echo json_encode(["message" => "No appointments found for this user."]);
        exit;
    }

    // Return the appointments as JSON
    echo json_encode($appointments);

} catch (PDOException $e) {
    // Error handling
    echo json_encode(["error" => $e->getMessage()]);
}
