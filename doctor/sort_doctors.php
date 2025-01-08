<?php
// Include the database connection
require "../dbh.inc.php";
session_start();

// Get the sorting option from the GET request
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'default';

// Initialize the base query
$query = "SELECT * FROM doctors";

// Modify the query based on the sorting option
if ($sort === 'rating') {
    $query .= " ORDER BY rating DESC"; // Sorting by rating in descending order
} else {
    $query .= " ORDER BY id ASC"; // Default sorting (by ID or another column)
}

// Debugging output
echo "<div>Sorting by: " . htmlspecialchars($sort) . "</div>";
echo "<div>Query: " . htmlspecialchars($query) . "</div>";

try {
    // Prepare and execute the query
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    // Fetch all doctors from the result
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Render the doctors as HTML
    if ($doctors) {
        foreach ($doctors as $doctor) {
            $doctorName = $doctor['fname'] . " " . $doctor['lname'];
            echo "<div class='doctor-info'>";
            echo "<div class='doctor-name'>" . htmlspecialchars($doctorName) . "</div>";
            echo "<div class='rating'>Rating: " . htmlspecialchars($doctor['rating']) . " stars</div>";
            echo "</div>";
        }
    } else {
        echo "<div>No doctors found.</div>";
    }
} catch (PDOException $e) {
    // Handle any errors and output a friendly message
    echo "<div>Error fetching doctors: " . htmlspecialchars($e->getMessage()) . "</div>";
}
?>