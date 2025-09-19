<html>
    <head>
        <meta charset="utf-8" />
        <title>{{ $title }}</title>
        <style>
            body { font-family: DejaVu Sans, sans-serif; }
            h1 { font-size: 20px; margin-bottom: 10px; }
            p { font-size: 12px; }
        </style>
    </head>
    <body>
        <h1>{{ $title }}</h1>
        <p>Generated at: {{ now() }}</p>
    </body>
</html>
