<?php
require "../../dbh.inc.php";
require 'fpdf.php';
require '../../vendor/autoload.php'; // Include PHPMailer autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

$appointmentId = intval($_GET['appointment_id']);
$userUniqueId = intval($_SESSION['unique_id']);

try {
    // Fetch appointment details
    $query = "SELECT * FROM appointments WHERE id = :appointment_id AND pat_id = :unique_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':appointment_id', $appointmentId);
    $stmt->bindParam(':unique_id', $userUniqueId);
    $stmt->execute();
    $appointment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$appointment) {
        throw new Exception("Appointment not found or permission denied.");
    }

    // Fetch patient details
    $query1 = "SELECT * FROM users WHERE unique_id = :userUniqueId";
    $stmt1 = $pdo->prepare($query1);
    $stmt1->bindParam(":userUniqueId", $userUniqueId, PDO::PARAM_INT);
    $stmt1->execute();
    $result1 = $stmt1->fetch(PDO::FETCH_ASSOC);
    $name = $result1['fname'] . " " . $result1['lname'];
    $email = $result1['email'];

    // Generate PDF
    $pdf = new FPDF();
    $pdf->AddPage();

    // Header Section
    $pdf->Image('../../image/logo.png', 10, 10, 40); // Logo
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'MedPulse - Appointment Details', 0, 1, 'C');
    $pdf->Ln(5);
    $pdf->SetFont('Arial', 'I', 12);
    $pdf->Cell(0, 10, 'A leading healthcare provider for your well-being', 0, 1, 'C');
    $pdf->Ln(10);

    // Patient Information Table
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Patient Information', 0, 1);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Name: ' . htmlspecialchars($name), 0, 1);
    $pdf->Cell(0, 10, 'Email: ' . htmlspecialchars($email), 0, 1);
    $pdf->Cell(0, 10, 'Patient ID: ' . $appointment['pat_id'], 0, 1);
    $pdf->Ln(10);

    // Appointment Details Table
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Appointment Details', 0, 1);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Appointment ID: ' . $appointment['id'], 0, 1);
    $pdf->Cell(0, 10, 'Date: ' . $appointment['date'], 0, 1);
    $pdf->Cell(0, 10, 'Time: ' . $appointment['time'], 0, 1);
    $pdf->Ln(10);

    // Medication Information
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Medication Information', 0, 1);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Dosage: ' . htmlspecialchars($appointment['dosage']), 0, 1);
    $pdf->Cell(0, 10, 'Frequency: ' . htmlspecialchars($appointment['frequency']), 0, 1);
    $pdf->Cell(0, 10, 'Duration: ' . htmlspecialchars($appointment['duration']), 0, 1);
    $pdf->Cell(0, 10, 'Route: ' . htmlspecialchars($appointment['route']), 0, 1);
    $pdf->Cell(0, 10, 'Refills: ' . htmlspecialchars($appointment['refills']), 0, 1);
    $pdf->Ln(5);

    // Instructions
    $pdf->SetFont('Arial', 'I', 12);
    $pdf->MultiCell(0, 10, 'Instructions: ' . htmlspecialchars($appointment['instructions']));
    $pdf->Ln(5);

    // Additional Information Section
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Additional Information', 0, 1);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, 'Message: ' . htmlspecialchars($appointment['message']), 0, 1);
    $pdf->Cell(0, 10, 'Preferred Doctor: ' . htmlspecialchars($appointment['preferred_doctors']), 0, 1);
    $pdf->Cell(0, 10, 'Drug Interaction Warnings: ' . htmlspecialchars($appointment['drug_interaction_warnings']), 0, 1);
    $pdf->Cell(0, 10, 'Additional Notes: ' . htmlspecialchars($appointment['additional_notes']), 0, 1);
    $pdf->Ln(5);

    // Time of Registration Section
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Time of Registration: ' . htmlspecialchars($appointment['time_of_reg']), 0, 1);
    $pdf->Ln(10);

    // Footer Section
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(0, 10, 'Thank you for trusting MedPulse for your healthcare needs. Visit us at www.medpulse.com', 0, 1, 'C');

    // Save the PDF to a file
    $pdfFilename = 'appointment_' . $appointment['id'] . '.pdf';
    $pdfFilepath = __DIR__ . '/' . $pdfFilename;
    $pdf->Output('F', $pdfFilepath);

    // Send Email with PHPMailer
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username ='adityakuraniyt@gmail.com'; // Fetch from environment
    $mail->Password ='zcbb cull rbut dbke'; // Fetch from environment
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('adityakuraniyt@gmail.com', 'MedPulse'); // Sender email
    $mail->addAddress($email); // Receiver email
    $mail->addAttachment($pdfFilepath); // Attach the PDF

    $mail->isHTML(true);
    $mail->Subject = 'Your Appointment Details';
    $mail->Body = "<p>Dear " . htmlspecialchars($name) . ",</p>
                   <p>Your appointment details are attached in the PDF.</p>
                   <p>Thank you!</p>";

    if ($mail->send()) {
        echo "Email sent successfully with the PDF attached.";
        header("Location: ../../profile");
    } else {
        echo "Failed to send email. Error: " . $mail->ErrorInfo;
    }

    // Clean up: Delete the temporary PDF file
    unlink($pdfFilepath);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
