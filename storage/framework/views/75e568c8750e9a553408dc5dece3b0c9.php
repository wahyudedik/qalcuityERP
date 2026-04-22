<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - <?php echo e(config('app.name')); ?></title>
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
    <h1>Terms of Service</h1>
    <p class="meta">Last updated: <?php echo e(date('F d, Y')); ?></p>

    <h2>1. Acceptance of Terms</h2>
    <p>By accessing or using <?php echo e(config('app.name')); ?>, you agree to be bound by these Terms of Service and all applicable laws and regulations.</p>

    <h2>2. Use License</h2>
    <p>Permission is granted to temporarily use <?php echo e(config('app.name')); ?> for personal or business purposes. This is the grant of a license, not a transfer of title.</p>

    <h2>3. Subscription and Payment</h2>
    <p>Access to <?php echo e(config('app.name')); ?> requires a subscription. All fees are non-refundable unless otherwise stated in your subscription agreement.</p>

    <h2>4. Contact</h2>
    <p>For questions about these terms, contact us at <a href="mailto:info@qalcuity.com">info@qalcuity.com</a></p>
</body>
</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\pages\legal\terms-of-service.blade.php ENDPATH**/ ?>