<?php
// Simple webhook test script
// Save this as test_webhook.php in your project root

$url = 'https://odc.com/email/webhook/incoming';
$data = [
    'from' => 'test@example.com',
    'to' => 'designers@orion-contracting.com',
    'subject' => 'Re: Test Email Subject',
    'body' => 'This is a test reply to check if the webhook is working.',
    'message_id' => 'test-message-' . time()
];

$options = [
    'http' => [
        'header' => "Content-Type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode($data)
    ]
];

$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo "Webhook Test Result:\n";
echo "Response: " . $result . "\n";
echo "HTTP Response Code: " . $http_response_header[0] . "\n";

if ($result === false) {
    echo "Error: Failed to send webhook request\n";
} else {
    echo "Webhook request sent successfully!\n";
}
