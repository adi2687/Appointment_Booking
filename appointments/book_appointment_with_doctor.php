<?php
require "../dbh.inc.php";
session_start();

try {
    // Validate doctor ID
    $doctor_id = filter_input(INPUT_GET, 'doctor_id', FILTER_SANITIZE_STRING);
    if (!$doctor_id) {
        throw new Exception("Invalid doctor ID.");
    }

    // Fetch doctor details
    $query = "SELECT * FROM doctors WHERE unique_id = :doctor_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':doctor_id', $doctor_id, PDO::PARAM_STR);
    $stmt->execute();
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doctor) {
        throw new Exception("Doctor not found.");
    }
    $doctor_name = htmlspecialchars($doctor['fname'] . " " . $doctor['lname']);

    // Fetch user details
    $unique_id = $_SESSION['unique_id'] ?? null;
    if (!$unique_id) {
        throw new Exception("User not logged in.");
    }

    $query = "SELECT * FROM users WHERE unique_id = :unique_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':unique_id', $unique_id, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("User not found.");
    }
    $user_name = htmlspecialchars($user['fname'] . " " . $user['lname']);

    // Process form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $date = $_POST['date'] ?? date("Y-m-d");
        $time = $_POST['time'] ?? null;
        $message = htmlspecialchars($_POST['message'] ?? '');

        $query = "INSERT INTO appointments (name, pat_id, date, message, preferred_doctors, time, done) 
                  VALUES (:name, :unique_id, :date, :message, :doctor_name, :time, 0)";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':name', $user_name, PDO::PARAM_STR);
        $stmt->bindParam(':unique_id', $unique_id, PDO::PARAM_STR);
        $stmt->bindParam(':date', $date, PDO::PARAM_STR);
        $stmt->bindParam(':message', $message, PDO::PARAM_STR);
        $stmt->bindParam(':doctor_name', $doctor_name, PDO::PARAM_STR);
        $stmt->bindParam(':time', $time, PDO::PARAM_STR);

        if ($stmt->execute()) {
            echo "Appointment successfully booked.";
            header("Location: ../profile");
        } else {
            throw new Exception("Failed to book the appointment. Please try again.");
        }
    }
} catch (Exception $e) {
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}

$rating = "SELECT COUNT(*) as count FROM rating WHERE doctor=:doctor_name";
$stmt3 = $pdo->prepare($rating);
$stmt3->bindParam(':doctor_name', $doctor_name, PDO::PARAM_STR);
$stmt3->execute();
$rate = $stmt3->fetch(PDO::FETCH_ASSOC);

// Now you can access both the rate and the count
$doctor_rating = $doctor['rating'];
$rating_info = $rate['count'];
if ($rating_info==1){
    $rating_info.=" patient";
}
else{
    $rating_info.=" unique patients";
}
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="appointment.css">
    <style>
        .preferred_doctors input {
            display: none;
        }

        .container {
            padding: 20px;
            max-width: 36%;
            min-height: 100%;

        }

        .container img {
            width: 90px;
            height: 80px
        }

        nav a {
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            gap: 20px;
            cursor: pointer;
            margin-top: 20px
        }

        nav a h2 {
            font-size: 40px;
        }

        .container {
            margin-top: 40px
        }

        a {
            text-decoration: none;
        }
        .docname {
            width:130px;
            margin-left:-2%;
            padding:px
        }
        .doctorname{
            width:390px;
            margin-right:120%;
            text-align: left;
            font-weight: 700;
        }
    </style>
</head>

<body>
    <div class="container">
        <nav><a href="../">
                <img src="../image/logo.png" alt="">

                <h2>MedPulse</h2>
            </a>
        </nav>
        <br>
        <h3>Book an Appointment</h3>

        <!-- Doctor Search Form -->
        <form class="doctor-search-form" id="doctor-search-form" method="post" action="">


            <div class="preferred_doctors">
                <label for="pre_doc">Preferred Doctors</label>
             <div class="doctorname">
             <?php  echo $doctor_name."<br> Rated ".round($doctor_rating,1)." by ".$rating_info?>

             </div>
            </div>
            <br>
            <div class="form-group">
                <label for="date">Preferred Date</label>
                <input type="date" id="date" name="date">
            </div>

            <div class="form-group">
                <label for="time">Preferred Time</label>
                <input type="time" id="time" name="time">
            </div>


            <div class="form-group">
                <label for="message">Additional Information (Optional)</label>
                <textarea id="message" name="message" rows="4" placeholder="Any special request or message"></textarea>
            </div>

            <button class="submitbut">Book Appointment</button>


        </form>


        




    </div>
</body>

</html>