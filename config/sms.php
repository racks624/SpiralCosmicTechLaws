<?php
return [
    // Default SMS provider: 'twilio', 'africastalking', 'http'
    'default' => getenv('SMS_PROVIDER') ?: 'twilio',

    'twilio' => [
        'account_sid' => getenv('TWILIO_SID'),
        'auth_token'  => getenv('TWILIO_TOKEN'),
        'from_number' => getenv('TWILIO_FROM'),
    ],

    'africastalking' => [
        'api_key'  => getenv('AFRICASTALKING_API_KEY'),
        'username' => getenv('AFRICASTALKING_USERNAME'),
        'from'     => getenv('AFRICASTALKING_FROM'),
    ],

    'http' => [
        'endpoint' => getenv('SMS_HTTP_ENDPOINT'),
        'api_key'  => getenv('SMS_HTTP_API_KEY'),
        'sender'   => getenv('SMS_HTTP_SENDER'),
    ],
];
