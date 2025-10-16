<?php

return [
    'driver' => 'imap',
    'imap' => [
        'host' => env('IMAP_HOST', 'mail.orion-contracting.com'),
        'port' => env('IMAP_PORT', 993),
        'username' => env('IMAP_USERNAME', 'engineering@orion-contracting.com'),
        'password' => env('IMAP_PASSWORD', ''),
        'folder' => env('IMAP_FOLDER', 'INBOX'),
        'encryption' => 'ssl',
    ],
    'storage' => [
        'driver' => 'database',
        'table' => 'mailbox_messages',
    ],
    'webhook' => [
        'enabled' => false,
        'url' => env('MAILBOX_WEBHOOK_URL'),
        'secret' => env('MAILBOX_WEBHOOK_SECRET'),
    ],
];
