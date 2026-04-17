<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security - {{ config('app.name') }}</title>
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
    <h1>Security</h1>
    <p class="meta">Last updated: {{ date('F d, Y') }}</p>

    <p>Learn about our security practices and how we protect your data.</p>

    <h2>Data Encryption</h2>
    <p>All data is encrypted in transit using TLS and at rest using industry-standard encryption algorithms.</p>

    <h2>Access Control</h2>
    <p>We enforce strict access controls and role-based permissions to ensure only authorized users can access sensitive data.</p>

    <h2>Report a Vulnerability</h2>
    <p>If you discover a security vulnerability, please report it responsibly to <a href="mailto:info@qalcuity.com">info@qalcuity.com</a></p>
</body>
</html>
