<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function send_mail($to, $toName, $subject, $body)
{
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'sandbox.smtp.mailtrap.io';
        $mail->SMTPAuth   = true;
        $mail->Username   = '6d50f90d35b843';
        $mail->Password   = '6e910dc0756cfb';
        $mail->Port       = 2525;

        $mail->setFrom('hello@demomailtrap.co', 'Sturdy Supplies');
        $mail->addAddress($to, $toName);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);

        $mail->send();

        return true;
    } catch (Exception $e) {
        return false;
    }
}