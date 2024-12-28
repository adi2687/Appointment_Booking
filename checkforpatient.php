<?php

session_start();
require "dbh.inc.php";
$unique_id=$_SESSION['unique_id'];
$query="SELECT * FROM users WHERE unique_id=:unique_id";
$stmt=$pdo->prepare($query);
$stmt->bindParam(':unique_id',$unique_id);
$stmt->execute();
$result=$stmt->fetch();
if ($result){echo "success";}
else{echo "failed";}