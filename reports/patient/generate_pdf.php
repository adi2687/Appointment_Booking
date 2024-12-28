<?php
require "../../dbh.inc.php";
require('fpdf.php');

session_start();

// Check for valid session
if (!isset($_SESSION['unique_id']) || empty($_SESSION['unique_id'])) {
    echo "<p>Unauthorized access. Please log in.</p>";
    exit;
}

// Validate appointment ID
if (!isset($_GET['appointment_id']) || empty($_GET['appointment_id'])) {
    echo "<p>No appointment ID provided.</p>";
    exit;
}

$appointmentId = $_GET['appointment_id'];
$userUniqueId = $_SESSION['unique_id'];

try {
    // Fetch appointment details
    $query = "SELECT * FROM appointments WHERE id = :appointment_id AND pat_id = :unique_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':appointment_id', $appointmentId, PDO::PARAM_INT);
    $stmt->bindParam(':unique_id', $userUniqueId, PDO::PARAM_INT);
    $stmt->execute();
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($appointment) {
        // Fetch user details
        $query1 = "SELECT * FROM users WHERE unique_id = :userUniqueId";
        $stmt1 = $pdo->prepare($query1);
        $stmt1->bindParam(":userUniqueId", $userUniqueId, PDO::PARAM_INT);
        $stmt1->execute();
        $result1 = $stmt1->fetch(PDO::FETCH_ASSOC);
        $name = $result1['fname'] . " " . $result1['lname'];
        $email = $result1['email'];
        $phone = $result1['number'];  // Added phone number for contact

        // Initialize PDF
        $pdf = new FPDF();
        $pdf->SetMargins(10, 10, 10); // Set margins
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 14);

        // Header Section
        $pdf->Image('../../image/logo.png', 10, 10, 30); // Logo
        $pdf->SetY(15);
        $pdf->SetFont('Arial', 'B', 20);
        $pdf->Cell(0, 10, 'MedPulse - Appointment Details', 0, 1, 'C');
        $pdf->Ln(10);
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(0, 10, 'Generated on: ' . date('Y-m-d H:i:s'), 0, 1, 'R');
        $pdf->Ln(5);

        // Helper function for table rows
        function addRow($pdf, $label, $value) {
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->Cell(50, 10, $label, 1);
            $pdf->SetFont('Arial', '', 12);
            $pdf->Cell(0, 10, htmlspecialchars($value), 1, 1);
        }

        // Patient Information Section
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->SetFillColor(230, 230, 230); // Light gray background
        $pdf->Cell(0, 10, 'Patient Information', 1, 1, 'L', true);

        addRow($pdf, 'Name:', $name);
        addRow($pdf, 'Email:', $email);  // Include email field
        addRow($pdf, 'Phone:', $phone);  // Include phone field
        addRow($pdf, 'Patient ID:', $appointment['pat_id']);
        $pdf->Ln(5);

        // Appointment Details Section
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'Appointment Information', 1, 1, 'L', true);

        addRow($pdf, 'Appointment ID:', $appointment['id']);
        addRow($pdf, 'Date:', $appointment['date']);
        addRow($pdf, 'Time:', $appointment['time']);
        $pdf->Ln(5);

        // Medication Information Section
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'Medication Information', 1, 1, 'L', true);

        addRow($pdf, 'Dosage:', $appointment['dosage']);
        addRow($pdf, 'Frequency:', $appointment['frequency']);
        addRow($pdf, 'Duration:', $appointment['duration']);
        addRow($pdf, 'Route:', $appointment['route']);
        $pdf->Ln(5);

        // Additional Instructions Section
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, 'Additional Instructions', 1, 1, 'L', true);

        $pdf->SetFont('Arial', '', 12);
        $pdf->MultiCell(0, 10, htmlspecialchars($appointment['instructions']), 1);
        $pdf->Ln(5);

        // Footer Section
        $pdf->SetY(-30);
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->Cell(0, 10, 'MedPulse - Thank you for trusting us with your health!', 0, 1, 'C');
        $pdf->Cell(0, 10, 'Page ' . $pdf->PageNo(), 0, 0, 'C');

        // Output PDF
        $pdf->Output('D', 'appointment_' . $appointment['id'] . '.pdf');
    } else {
        echo "<p>Appointment not found or you do not have permission to access it.</p>";
    }
} catch (PDOException $e) {
    echo "<p>Error retrieving appointment: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
