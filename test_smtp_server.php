<?php

echo "=== SMTP Server Test ===\n\n";

// Test SMTP server connectivity
$host = 'mail.orion-contracting.com';
$port = 587;

echo "Testing SMTP server: {$host}:{$port}\n\n";

// 1. Test basic connection
echo "1. Testing basic connection...\n";
$connection = @fsockopen($host, $port, $errno, $errstr, 30);
if (!$connection) {
    echo "   ❌ Connection failed: {$errstr} ({$errno})\n";
    echo "   This means the SMTP server is not accessible!\n";
} else {
    echo "   ✅ Connection successful\n";
    fclose($connection);
}

// 2. Test with different ports
echo "\n2. Testing different ports...\n";
$ports = [25, 465, 587, 2525];
foreach ($ports as $testPort) {
    $conn = @fsockopen($host, $testPort, $errno, $errstr, 10);
    if ($conn) {
        echo "   ✅ Port {$testPort}: Open\n";
        fclose($conn);
    } else {
        echo "   ❌ Port {$testPort}: Closed ({$errstr})\n";
    }
}

// 3. Test DNS resolution
echo "\n3. Testing DNS resolution...\n";
$ip = gethostbyname($host);
if ($ip === $host) {
    echo "   ❌ DNS resolution failed\n";
} else {
    echo "   ✅ DNS resolved to: {$ip}\n";
}

// 4. Test with telnet simulation
echo "\n4. Testing SMTP handshake...\n";
$context = stream_context_create([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    ]
]);

$smtp = @stream_socket_client("tcp://{$host}:{$port}", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
if ($smtp) {
    echo "   ✅ SMTP socket connection successful\n";

    // Read initial response
    $response = fgets($smtp, 1024);
    echo "   Server greeting: " . trim($response) . "\n";

    // Send EHLO command
    fwrite($smtp, "EHLO orion-contracting.com\r\n");
    $response = fgets($smtp, 1024);
    echo "   EHLO response: " . trim($response) . "\n";

    // Send QUIT
    fwrite($smtp, "QUIT\r\n");
    fclose($smtp);
    echo "   ✅ SMTP handshake successful\n";
} else {
    echo "   ❌ SMTP handshake failed: {$errstr} ({$errno})\n";
}

echo "\n=== DIAGNOSIS ===\n";
if (!$connection) {
    echo "❌ SMTP SERVER IS NOT ACCESSIBLE\n";
    echo "   - Check if mail.orion-contracting.com is correct\n";
    echo "   - Verify the server is running\n";
    echo "   - Check firewall settings\n";
    echo "   - Contact your hosting provider\n";
} else {
    echo "✅ SMTP SERVER IS ACCESSIBLE\n";
    echo "   - The issue is likely with authentication\n";
    echo "   - Check username/password\n";
    echo "   - Verify account permissions\n";
    echo "   - Check email limits\n";
}

echo "\nTest completed!\n";
