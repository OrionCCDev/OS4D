<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />
    <title>Login (Light) - {{ config('app.name', 'Orion Designers') }}</title>
    <meta name="description" content="Lightweight login for slower devices" />
    <link rel="icon" type="image/x-icon" href="{{ asset('DAssets/logo-blue.webp') }}" />
    <style>
      :root {
        color-scheme: light;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        --bg: #f1f5f9;
        --card-bg: #ffffff;
        --border: #d4d4d8;
        --primary: #2563eb;
        --primary-contrast: #ffffff;
        --text: #1f2937;
        --text-muted: #6b7280;
        --danger: #dc2626;
      }
      * {
        box-sizing: border-box;
      }
      body {
        margin: 0;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--bg);
        color: var(--text);
      }
      .auth-wrapper {
        width: min(480px, 92vw);
        padding: 2.5rem 2rem;
        border-radius: 16px;
        background: var(--card-bg);
        border: 1px solid var(--border);
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.08);
      }
      .auth-logo {
        margin-bottom: 1rem;
        text-align: center;
      }
      .auth-logo img {
        width: 120px;
        height: auto;
      }
      h1 {
        margin: 0 0 0.4rem;
        font-size: clamp(1.5rem, 3vw, 1.9rem);
        text-align: center;
      }
      p {
        margin: 0 0 1.6rem;
        color: var(--text-muted);
        text-align: center;
        font-size: 0.95rem;
      }
      form {
        display: grid;
        gap: 1.2rem;
      }
      label {
        display: block;
        margin-bottom: 0.4rem;
        font-weight: 600;
        font-size: 0.92rem;
      }
      input[type="text"],
      input[type="email"],
      input[type="password"] {
        width: 100%;
        padding: 0.75rem 0.85rem;
        border-radius: 10px;
        border: 1px solid var(--border);
        background: #f8fafc;
        font-size: 0.95rem;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
      }
      input[type="text"]:focus,
      input[type="email"]:focus,
      input[type="password"]:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.2);
        background: #ffffff;
      }
      .remember {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        font-size: 0.9rem;
      }
      .remember input[type="checkbox"] {
        width: 16px;
        height: 16px;
      }
      button[type="submit"] {
        padding: 0.8rem 1rem;
        border: none;
        border-radius: 10px;
        background: var(--primary);
        color: var(--primary-contrast);
        font-weight: 600;
        font-size: 1rem;
        cursor: pointer;
        transition: transform 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
      }
      button[type="submit"]:hover {
        transform: translateY(-1px);
        box-shadow: 0 12px 20px rgba(37, 99, 235, 0.18);
        background: #1d4ed8;
      }
      button[type="submit"]:active {
        transform: translateY(0);
        box-shadow: none;
      }
      .alert {
        margin-bottom: 1rem;
        padding: 0.9rem 1rem;
        border-radius: 10px;
        background: #fee2e2;
        color: var(--danger);
        border: 1px solid rgba(220, 38, 38, 0.25);
      }
      .alert ul {
        margin: 0;
        padding-left: 1rem;
      }
      .footer-links {
        margin-top: 2rem;
        text-align: center;
        font-size: 0.88rem;
        color: var(--text-muted);
      }
      .footer-links a {
        color: var(--primary);
        text-decoration: none;
      }
      .footer-links a:hover {
        text-decoration: underline;
      }
      @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
          animation-duration: 0.01ms !important;
          animation-iteration-count: 1 !important;
          transition-duration: 0.01ms !important;
          scroll-behavior: auto !important;
        }
      }
    </style>
  </head>
  <body>
    <main class="auth-wrapper" role="main">
      <div class="auth-logo">
        <img src="{{ asset('DAssets/logo-blue.webp') }}" alt="{{ config('app.name', 'Orion Designers') }} logo" />
      </div>
      <h1>Welcome Back</h1>
      <p>Use the lightweight login if the main page does not render properly on your device.</p>

      @if ($errors->any())
        <div class="alert" role="alert">
          <ul>
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form action="{{ route('login') }}" method="POST" autocomplete="on">
        @csrf
        <div>
          <label for="email">Email</label>
          <input
            type="text"
            id="email"
            name="email"
            value="{{ old('email') }}"
            placeholder="Enter your email"
            autofocus
            required
          />
        </div>

        <div>
          <label for="password">Password</label>
          <input
            type="password"
            id="password"
            name="password"
            placeholder="Enter your password"
            required
          />
        </div>

        <label class="remember">
          <input type="checkbox" name="remember" />
          <span>Remember me</span>
        </label>

        <button type="submit">Sign in</button>
      </form>

      <div class="footer-links" aria-live="polite">
        <p>
          Trouble with this page?
          <a href="{{ route('login') }}">Try the standard login</a>
        </p>
      </div>
    </main>
  </body>
</html>

