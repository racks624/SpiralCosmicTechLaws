<?php
return [
    // Default email driver: 'smtp', 'sendgrid', 'mailgun'
    'default' => getenv('EMAIL_DRIVER') ?: 'smtp',

    'smtp' => [
        'host'       => getenv('SMTP_HOST'),
        'port'       => getenv('SMTP_PORT'),
        'username'   => getenv('SMTP_USERNAME'),
        'password'   => getenv('SMTP_PASSWORD'),
        'encryption' => getenv('SMTP_ENCRYPTION') ?: 'tls',
        'from_email' => getenv('SMTP_FROM_EMAIL'),
        'from_name'  => getenv('SMTP_FROM_NAME'),
    ],

    'sendgrid' => [
        'api_key'    => getenv('SENDGRID_API_KEY'),
        'from_email' => getenv('SENDGRID_FROM_EMAIL'),
    ],

    'mailgun' => [
        'api_key'    => getenv('MAILGUN_API_KEY'),
        'domain'     => getenv('MAILGUN_DOMAIN'),
        'from_email' => getenv('MAILGUN_FROM_EMAIL'),
    ],
];
