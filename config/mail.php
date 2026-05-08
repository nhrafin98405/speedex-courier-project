<?php
/**
 * nhr Email Notification System
 *
 * Uses PHPMailer if available (composer require phpmailer/phpmailer),
 * otherwise gracefully falls back to PHP mail() while still logging the
 * attempt to email_logs.
 *
 * Configure SMTP credentials below.
 */
require_once __DIR__ . '/database.php';

// ---------- SMTP CONFIG ----------
define('MAIL_DRIVER',   'smtp');                 // smtp | mail
define('MAIL_HOST',     'smtp.gmail.com');
define('MAIL_PORT',     587);
define('MAIL_USERNAME', 'your-email@gmail.com');
define('MAIL_PASSWORD', 'your-app-password');
define('MAIL_ENCRYPTION','tls');                 // tls | ssl
define('MAIL_FROM',     'no-reply@speedex.com');
define('MAIL_FROM_NAME','nhr Courier Service');

class Mailer {
    /**
     * Send branded email and log result to email_logs.
     */
    public static function send(string $toEmail, string $toName, string $subject, string $template, array $data = [], ?int $parcelId = null): bool {
        $body = self::renderTemplate($template, $data);
        $ok = false; $err = null;

        if (MAIL_DRIVER === 'smtp' && class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            try {
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = MAIL_HOST;
                $mail->SMTPAuth   = true;
                $mail->Username   = MAIL_USERNAME;
                $mail->Password   = MAIL_PASSWORD;
                $mail->SMTPSecure = MAIL_ENCRYPTION;
                $mail->Port       = MAIL_PORT;
                $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
                $mail->addAddress($toEmail, $toName);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $body;
                $mail->AltBody = strip_tags($body);
                $ok = $mail->send();
            } catch (Throwable $e) {
                $err = $e->getMessage();
            }
        } else {
            $headers  = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM . ">\r\n";
            $ok = @mail($toEmail, $subject, $body, $headers);
            if (!$ok) $err = 'mail() failed';
        }

        try {
            $db = Database::getConnection();
            $db->prepare("INSERT INTO email_logs (to_email,to_name,subject,template,parcel_id,status,error) VALUES (?,?,?,?,?,?,?)")
               ->execute([$toEmail,$toName,$subject,$template,$parcelId,$ok?'sent':'failed',$err]);
        } catch (Exception $e) {}

        return $ok;
    }

    public static function renderTemplate(string $template, array $data): string {
        $file = __DIR__ . '/../emails/' . $template . '.php';
        if (!file_exists($file)) $file = __DIR__ . '/../emails/generic.php';
        extract($data);
        ob_start();
        include $file;
        return ob_get_clean();
    }

    /* ---------- Convenience helpers ---------- */
    public static function registrationSuccess(string $email, string $name): bool {
        return self::send($email, $name, 'Welcome to nhr Courier Service', 'registration', ['name' => $name]);
    }
    public static function parcelBooked(array $parcel): bool {
        if (empty($parcel['sender_email'])) return false;
        return self::send($parcel['sender_email'], $parcel['sender_name'], 'Your nhr Parcel Has Been Booked', 'parcel_booked', ['parcel' => $parcel], (int)$parcel['id']);
    }
    public static function parcelInTransit(array $parcel): bool {
        if (empty($parcel['receiver_email'])) return false;
        return self::send($parcel['receiver_email'], $parcel['receiver_name'], 'A nhr Parcel Is On Its Way to You', 'parcel_in_transit', ['parcel' => $parcel], (int)$parcel['id']);
    }
    public static function outForDelivery(array $parcel): bool {
        if (empty($parcel['receiver_email'])) return false;
        return self::send($parcel['receiver_email'], $parcel['receiver_name'], 'Your nhr Parcel Is Out for Delivery', 'out_for_delivery', ['parcel' => $parcel], (int)$parcel['id']);
    }
    public static function delivered(array $parcel): bool {
        if (empty($parcel['receiver_email'])) return false;
        return self::send($parcel['receiver_email'], $parcel['receiver_name'], 'Your nhr Parcel Has Been Delivered', 'delivered', ['parcel' => $parcel], (int)$parcel['id']);
    }
}
