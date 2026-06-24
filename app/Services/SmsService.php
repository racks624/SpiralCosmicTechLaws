<?php
namespace App\Services;

use Exception;

class SmsService
{
    protected $provider;
    protected $config;

    public function __construct()
    {
        $this->config = require ROOT_PATH . '/config/sms.php';
        $this->provider = $this->config['default'];
    }

    /**
     * Send an SMS via the configured provider.
     *
     * @param string $phone   Recipient phone number (E.164 format recommended)
     * @param string $message SMS message
     * @return bool
     * @throws Exception
     */
    public function send($phone, $message)
    {
        $method = 'sendVia' . ucfirst($this->provider);
        if (!method_exists($this, $method)) {
            throw new Exception("SMS provider '{$this->provider}' not supported.");
        }
        return $this->$method($phone, $message);
    }

    /**
     * Send via Twilio.
     */
    protected function sendViaTwilio($phone, $message)
    {
        $sid = $this->config['twilio']['account_sid'] ?? '';
        $token = $this->config['twilio']['auth_token'] ?? '';
        $from = $this->config['twilio']['from_number'] ?? '';
        if (empty($sid) || empty($token) || empty($from)) {
            throw new Exception('Twilio credentials not configured.');
        }

        $url = "https://api.twilio.com/2010-04-01/Accounts/{$sid}/Messages.json";
        $data = [
            'To' => $phone,
            'From' => $from,
            'Body' => $message,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, "{$sid}:{$token}");
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 201) {
            $error = json_decode($response, true);
            throw new Exception('Twilio error: ' . ($error['message'] ?? 'Unknown error'));
        }
        return true;
    }

    /**
     * Send via Africa's Talking.
     */
    protected function sendViaAfricastalking($phone, $message)
    {
        $apiKey = $this->config['africastalking']['api_key'] ?? '';
        $username = $this->config['africastalking']['username'] ?? '';
        $from = $this->config['africastalking']['from'] ?? '';
        if (empty($apiKey) || empty($username) || empty($from)) {
            throw new Exception('Africa\'s Talking credentials not configured.');
        }

        $url = "https://api.africastalking.com/version1/messaging";
        $data = [
            'username' => $username,
            'to' => $phone,
            'from' => $from,
            'message' => $message,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'apiKey: ' . $apiKey,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 201 && $httpCode !== 200) {
            $error = json_decode($response, true);
            throw new Exception('Africa\'s Talking error: ' . ($error['errorMessage'] ?? 'Unknown error'));
        }
        return true;
    }

    /**
     * Send via generic HTTP (e.g., custom gateway).
     */
    protected function sendViaHttp($phone, $message)
    {
        $endpoint = $this->config['http']['endpoint'] ?? '';
        $apiKey = $this->config['http']['api_key'] ?? '';
        $sender = $this->config['http']['sender'] ?? '';
        if (empty($endpoint) || empty($apiKey)) {
            throw new Exception('HTTP SMS gateway not configured.');
        }

        $data = [
            'api_key' => $apiKey,
            'sender' => $sender,
            'to' => $phone,
            'message' => $message,
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 && $httpCode !== 201) {
            throw new Exception('HTTP SMS gateway error: ' . substr($response, 0, 200));
        }
        return true;
    }
}
