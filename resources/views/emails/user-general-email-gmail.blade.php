<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(to right, #0056b3, #003d80);
            padding: 20px;
            text-align: center;
            color: #ffffff;
        }
        .header img {
            max-width: 150px;
            height: auto;
        }
        .content {
            padding: 20px 30px;
        }
        .content p {
            margin-bottom: 15px;
        }
        .button {
            display: inline-block;
            background-color: #0056b3;
            color: #ffffff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            @if(isset($message) && $message->embed(public_path('uploads/logo-blue.webp')))
                <img src="{{ $message->embed(public_path('uploads/logo-blue.webp')) }}" alt="Orion Contracting Logo" width="150">
            @else
                <img src="{{ asset('uploads/logo-blue.webp') }}" alt="Orion Contracting Logo" width="150">
            @endif
            <h1>Orion Contracting</h1>
        </div>
        <div class="content">
            {!! $bodyContent !!}
        </div>
    </div>
</body>
</html>
