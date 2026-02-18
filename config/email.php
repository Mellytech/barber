<?php

return [
    'smtp' => [
        'host' => getenv('SMTP_HOST') ?: 'smtp.gmail.com',
        'username' => getenv('SMTP_USERNAME'),
        'password' => getenv('SMTP_PASSWORD'),
        'port' => getenv('SMTP_PORT') ?: 587,
        'encryption' => getenv('SMTP_ENCRYPTION') ?: 'tls',
        'from_email' => getenv('EMAIL_FROM') ?: 'noreply@yourbarbershop.com',
        'from_name' => getenv('EMAIL_FROM_NAME') ?: 'Your Barber Shop',
    ],
    'debug' => 2,
];
