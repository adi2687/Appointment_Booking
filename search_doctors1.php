<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure the output is JSON
header('Content-Type: application/json');

// Database connection settings
$host = 'localhost'; // Database host
$username = 'root'; // Database username
$password = ''; // Database password
$database = 'database'; // Database name

// Create the database connection
$mysqli = new mysqli($host, $username, $password, $database);

// Check if connection was successful
if ($mysqli->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $mysqli->connect_error]));
}

if (isset($_GET['query'])) {
    $query = strtolower(trim($_GET['query']));

    // Prepare the SQL query to search for doctors based on the name
    $sql = "SELECT * FROM doctors WHERE LOWER(fname) LIKE ?";
    
    // Use prepared statements to prevent SQL injection
    if ($stmt = $mysqli->prepare($sql)) {
        $searchTerm = "%{$query}%";
        $stmt->bind_param("s", $searchTerm); // Bind the query parameter
        $stmt->execute(); // Execute the query

        // Get the result
        $result = $stmt->get_result();
        $results = [];

        while ($row = $result->fetch_assoc()) {
            $results[] = [
                'id' => $row['id'],
                'fname' => $row['fname'],
                'lname' => $row['lname'],
                'specialty' => $row['specialization1'],
                'unique_id'=>$row['unique_id']
            ];
        }

        // Output the results as JSON
        echo json_encode($results);

        $stmt->close();
    } else {
        echo json_encode(['error' => 'Failed to prepare SQL query.']);
    }
} else {
    echo json_encode(['error' => 'No query parameter provided.']);
}

// Close the database connection
$mysqli->close();
?>