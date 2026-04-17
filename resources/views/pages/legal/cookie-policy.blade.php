<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cookie Policy - {{ config('app.name') }}</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 40px auto; padding: 0 20px; color: #333; line-height: 1.6; }
        h1 { font-size: 2rem; margin-bottom: 4px; }
        h2 { font-size: 1.25rem; margin-top: 2rem; }
        .meta { color: #888; font-size: 0.875rem; margin-bottom: 2rem; }
        a { color: #4f46e5; }
        .back { display: inline-block; margin-bottom: 2rem; font-size: 0.875rem; }
    </style>
</head>
<body>
    <a href="{{ url()->previous() }}" class="back">← Back</a>
    <h1>Cookie Policy</h1>
    <p class="meta">Last updated: {{ date('F d, Y') }}</p>

    <p>This site uses cookies to enhance your experience. Learn more about how we use cookies and your choices.</p>

    <h2>What Are Cookies</h2>
    <p>Cookies are small text files stored on your device when you visit a website. They help us remember your preferences and improve your experience.</p>

    <h2>How We Use Cookies</h2>
    <p>We use cookies for authentication, session management, and to understand how you use our application.</p>

    <h2>Contact</h2>
    <p>Questions? Email us at <a href="mailto:info@qalcuity.com">info@qalcuity.com</a></p>
</body>
</html>
