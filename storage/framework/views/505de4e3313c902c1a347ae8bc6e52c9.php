<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GDPR Compliance - <?php echo e(config('app.name')); ?></title>
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
    <a href="<?php echo e(url()->previous()); ?>" class="back">← Back</a>
    <h1>GDPR Compliance</h1>
    <p class="meta">Last updated: <?php echo e(date('F d, Y')); ?></p>

    <p>How <?php echo e(config('app.name')); ?> complies with the General Data Protection Regulation.</p>

    <h2>Your Rights</h2>
    <p>Under GDPR, you have the right to access, rectify, erase, and port your personal data. You may also object to or restrict processing of your data.</p>

    <h2>Data Controller</h2>
    <p><?php echo e(config('app.name')); ?> acts as the data controller for personal data collected through our platform.</p>

    <h2>Contact</h2>
    <p>For GDPR-related requests, contact us at <a href="mailto:info@qalcuity.com">info@qalcuity.com</a></p>
</body>
</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\pages\legal\gdpr.blade.php ENDPATH**/ ?>