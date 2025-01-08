<?php

require "../dbh.inc.php";

try {
    // Decode the incoming JSON data
    $data = json_decode(file_get_contents("php://input"), true);

    // Validate input fields
    if (!isset($data['appointment_id'], $data['selected_doctors'], $data['date'], $data['time'], $data['message']) || empty($data['selected_doctors'])) {
        throw new Exception("Invalid input data. Please provide all required fields.");
    }

    // Extract the data
    $appointment_id = $data['appointment_id'] ?? "";
    $selectedDoctors = $data['selected_doctors'];
    $date = $data['date'];
    $time = $data['time'];
    $message = $data['message'];
    $preferredDoctor = $selectedDoctors[0]; // Assuming the first doctor is the preferred one

    // Start session and validate user
    session_start();
    if (!isset($_SESSION['unique_id'])) {
        throw new Exception("User not logged in.");
    }

    $uniqueId = $_SESSION['unique_id'];
    echo "Unique ID: " . $uniqueId; // Debugging output

    // Fetch user details from the database
    $detailsQuery = "SELECT * FROM users WHERE unique_id=:unique_id";
    $stmt = $pdo->prepare($detailsQuery);
    $stmt->bindParam(':unique_id', $uniqueId, PDO::PARAM_STR);
    $stmt->execute();
    $detailsResult = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$detailsResult) {
        throw new Exception("User not found.");
    }

    // Extract user details
    $name = $detailsResult['fname'] . " " . $detailsResult['lname'];
    echo "Name: " . $name; // Debugging output
    $email = $detailsResult['email'];

    // Fetch doctor details from the database
    $doctorQuery = "SELECT * FROM doctors WHERE unique_id=:preferred_doctor";
    $stmt_doctor = $pdo->prepare($doctorQuery);
    $stmt_doctor->bindParam(':preferred_doctor', $preferredDoctor, PDO::PARAM_STR);
    $stmt_doctor->execute();
    $doctor_result = $stmt_doctor->fetch(PDO::FETCH_ASSOC);

    if (!$doctor_result) {
        throw new Exception("Doctor not found.");
    }

    $docnamefinal = $doctor_result['fname'] . " " . $doctor_result['lname'];

    // Insert appointment into the database
    $insertQuery = "INSERT INTO appointments (name, pat_id, preferred_doctors, date, time, message, done) 
                    VALUES (:name, :unique_id, :preferred_doctor, :date, :time, :message, :appointment_id)";
    $stmt = $pdo->prepare($insertQuery);
    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
    $stmt->bindParam(':unique_id', $uniqueId, PDO::PARAM_STR);
    $stmt->bindParam(':preferred_doctor', $docnamefinal, PDO::PARAM_STR);  // Ensure correct binding here
    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
    $stmt->bindParam(':time', $time, PDO::PARAM_STR);
    $stmt->bindParam(':message', $message, PDO::PARAM_STR);
    $stmt->bindParam(":appointment_id",$appointment_id);
    $stmt->execute();

    // Return a success response
    $response = [
        "success" => true,
        "message" => "Appointment booked successfully."
    ];
    echo json_encode($response);

} catch (Exception $e) {
    // Handle errors with detailed individual messages
    $response = [
        "success" => false,
        "message" => $e->getMessage()
    ];
    echo json_encode($response);
}
