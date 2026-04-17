<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - {{ config('app.name') }}</title>
    <style>
        body { font-family: sans-serif; max-width: 800px; margin: 40px auto; padding: 0 20px; color: #333; line-height: 1.6; }
        h1 { font-size: 2rem; margin-bottom: 4px; }
        h2 { font-size: 1.25rem; margin-top: 2rem; }
        .meta { color: #888; font-size: 0.875rem; margin-bottom: 2rem; }
        ul { padding-left: 1.5rem; }
        a { color: #4f46e5; }
        .back { display: inline-block; margin-bottom: 2rem; font-size: 0.875rem; }
    </style>
</head>
<body>
    <a href="{{ url()->previous() }}" class="back">← Back</a>
    <h1>Privacy Policy</h1>
    <p class="meta">Last updated: {{ date('F d, Y') }}</p>

    <h2>1. Information We Collect</h2>
    <p>We collect information you provide directly to us, including but not limited to your name, email address, phone number, and business information when you create an account or contact us.</p>

    <h2>2. How We Use Your Information</h2>
    <p>We use the information we collect to:</p>
    <ul>
        <li>Provide, maintain, and improve our services</li>
        <li>Process your transactions and send related information</li>
        <li>Send you technical notices and support messages</li>
        <li>Respond to your comments and questions</li>
        <li>Comply with legal obligations</li>
    </ul>

    <h2>3. Data Security</h2>
    <p>We implement appropriate technical and organizational measures to protect your personal data against unauthorized access, alteration, disclosure, or destruction.</p>

    <h2>4. Contact Us</h2>
    <p>If you have questions about this Privacy Policy, please contact us at:</p>
    <p>Email: <a href="mailto:info@qalcuity.com">info@qalcuity.com</a><br>Phone: +62 816-5493-2383</p>
</body>
</html>
