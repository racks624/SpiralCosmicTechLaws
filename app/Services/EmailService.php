<?php
namespace App\Services;

use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

class EmailService
{
    protected $config;

    public function __construct()
    {
        $this->config = require ROOT_PATH . '/config/email.php';
    }

    /**
     * Send an email using the configured driver.
     *
     * @param string $to      Recipient email
     * @param string $subject Subject
     * @param string $body    HTML body
     * @param string $from    Sender email (optional, uses default if empty)
     * @param string $fromName Sender name (optional)
     * @return bool
     * @throws Exception
     */
    public function send($to, $subject, $body, $from = '', $fromName = '')
    {
        $driver = $this->config['default'];
        $method = 'sendVia' . ucfirst($driver);
        if (!method_exists($this, $method)) {
            throw new Exception("Email driver '{$driver}' not supported.");
        }
        return $this->$method($to, $subject, $body, $from, $fromName);
    }

    /**
     * Send via SMTP using PHPMailer.
     */
    protected function sendViaSmtp($to, $subject, $body, $from, $fromName)
    {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $this->config['smtp']['host'] ?? '';
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['smtp']['username'] ?? '';
            $mail->Password = $this->config['smtp']['password'] ?? '';
            $mail->SMTPSecure = $this->config['smtp']['encryption'] ?? 'tls';
            $mail->Port = $this->config['smtp']['port'] ?? 587;

            $mail->setFrom(
                $from ?: ($this->config['smtp']['from_email'] ?? $mail->Username),
                $fromName ?: ($this->config['smtp']['from_name'] ?? '')
            );
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();
            return true;
        } catch (PHPMailerException $e) {
            throw new Exception('SMTP error: ' . $mail->ErrorInfo);
        }
    }

    /**
     * Send via SendGrid API.
     */
    protected function sendViaSendgrid($to, $subject, $body, $from, $fromName)
    {
        $apiKey = $this->config['sendgrid']['api_key'] ?? '';
        $defaultFrom = $this->config['sendgrid']['from_email'] ?? '';
        if (empty($apiKey) || empty($defaultFrom)) {
            throw new Exception('SendGrid credentials not configured.');
        }

        $data = [
            'personalizations' => [[
                'to' => [['email' => $to]]
            ]],
            'from' => ['email' => $from ?: $defaultFrom, 'name' => $fromName ?: ''],
            'subject' => $subject,
            'content' => [['type' => 'text/html', 'value' => $body]]
        ];

        $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 202) {
            throw new Exception('SendGrid error: ' . substr($response, 0, 200));
        }
        return true;
    }

    /**
     * Send via Mailgun API.
     */
    protected function sendViaMailgun($to, $subject, $body, $from, $fromName)
    {
        $apiKey = $this->config['mailgun']['api_key'] ?? '';
        $domain = $this->config['mailgun']['domain'] ?? '';
        $defaultFrom = $this->config['mailgun']['from_email'] ?? '';
        if (empty($apiKey) || empty($domain) || empty($defaultFrom)) {
            throw new Exception('Mailgun credentials not configured.');
        }

        $data = [
            'from' => ($fromName ? "$fromName <" : '') . ($from ?: $defaultFrom) . ($fromName ? '>' : ''),
            'to' => $to,
            'subject' => $subject,
            'html' => $body,
        ];

        $ch = curl_init("https://api.mailgun.net/v3/{$domain}/messages");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "api:{$apiKey}");
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new Exception('Mailgun error: ' . substr($response, 0, 200));
        }
        return true;
    }
}
