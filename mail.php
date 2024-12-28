<?php
$receiver = "neelamkurani@gmail.com";
$subject = "fees submission";
$body = "have an appointment at domins 7pm";
$sender = "adityakuraniyt@gmail.com";

if(mail($receiver, $subject, $body, $sender)){
    echo "Email sent successfully to $receiver";
}else{
    echo "Sorry, failed while sending mail!";
}
?>