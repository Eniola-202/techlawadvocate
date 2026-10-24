<?php

require_once('phpmailer/class.phpmailer.php');
require_once('phpmailer/class.smtp.php');

$message = "";
$status = "false";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim(isset($_POST['form_name']) ? $_POST['form_name'] : '');
    $email = trim(isset($_POST['form_email']) ? $_POST['form_email'] : '');
    $subject = trim(isset($_POST['form_subject']) ? $_POST['form_subject'] : '');
    $phone = trim(isset($_POST['form_phone']) ? $_POST['form_phone'] : '');
    $formMessage = trim(isset($_POST['form_message']) ? $_POST['form_message'] : '');
    $botcheck = trim(isset($_POST['form_botcheck']) ? $_POST['form_botcheck'] : '');

    if ($botcheck !== '') {
        $message = 'Bot <strong>detected</strong>. Please try again.';
    } elseif ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $subject === '' || $formMessage === '') {
        $message = 'Please <strong>fill in</strong> your name, email, subject and message.';
    }
    else {
        $toemail = 'thetechlawadvocate@gmail.com';
        $toname = 'Tech Law Advocates';
        $smtpHost = getenv('SMTP_HOST');
        $smtpUsername = getenv('SMTP_USERNAME');
        $smtpPassword = getenv('SMTP_PASSWORD');
        $smtpPort = getenv('SMTP_PORT');
        $smtpEncryption = getenv('SMTP_ENCRYPTION');

        if ($smtpHost === false || $smtpUsername === false || $smtpPassword === false) {
            $message = 'Email delivery is not configured on the server. Please contact the site administrator.';
        } else {
            $mail = new PHPMailer();
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $smtpUsername;
            $mail->Password = $smtpPassword;
            $mail->SMTPSecure = $smtpEncryption !== false && $smtpEncryption !== '' ? $smtpEncryption : 'tls';
            $mail->Port = $smtpPort !== false && $smtpPort !== '' ? (int) $smtpPort : 587;
            $mail->CharSet = 'UTF-8';
            $mail->SetFrom($smtpUsername, $toname);
            $mail->AddReplyTo($email, $name);
            $mail->AddAddress($toemail, $toname);
            $mail->Subject = $subject;

            $body = '<strong>Name:</strong> ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '<br><br>';
            $body .= '<strong>Email:</strong> ' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '<br><br>';
            $body .= '<strong>Phone:</strong> ' . htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') . '<br><br>';
            $body .= '<strong>Message:</strong><br>' . nl2br(htmlspecialchars($formMessage, ENT_QUOTES, 'UTF-8'));

            $mail->MsgHTML($body);

            if ($mail->Send()) {
                $message = 'We have <strong>successfully</strong> received your message and will get back to you as soon as possible.';
                $status = 'true';
            } else {
                $message = 'Email <strong>could not</strong> be sent. Please try again later.';
            }
        }
    }
} else {
    $message = 'An <strong>unexpected error</strong> occurred. Please try again later.';
}

header('Content-Type: application/json; charset=UTF-8');
$status_array = array('message' => $message, 'status' => $status);
echo json_encode($status_array);
?>