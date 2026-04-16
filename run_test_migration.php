<?php
// Set environment variables for testing
putenv('APP_ENV=testing');
putenv('DB_DATABASE=qalcuity_erp_test');

// Run migration via artisan
$output = shell_exec('php artisan migrate --env=testing 2>&1');
echo $output;
