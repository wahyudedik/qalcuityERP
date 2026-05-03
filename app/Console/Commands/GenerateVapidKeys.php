<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateVapidKeys extends Command
{
    protected $signature = 'vapid:generate {--force : Backward-compatible no-op option}';
    protected $description = 'Generate VAPID key pair for Web Push notifications';

    public function handle(): int
    {
        // Generate ECDSA P-256 key pair
        $key = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);

        if (!$key) {
            $this->error('Failed to generate key pair with OpenSSL.');
            $this->newLine();
            $this->info('Alternative: run `npx web-push generate-vapid-keys` and copy the keys to .env');
            return self::FAILURE;
        }

        $details = openssl_pkey_get_details($key);
        openssl_pkey_export($key, $privateKeyPem);

        // Extract raw public key (uncompressed point, 65 bytes)
        $x = str_pad($details['ec']['x'], 32, "\0", STR_PAD_LEFT);
        $y = str_pad($details['ec']['y'], 32, "\0", STR_PAD_LEFT);
        $publicKey = "\x04" . $x . $y;

        // Extract raw private key (32 bytes)
        $d = str_pad($details['ec']['d'], 32, "\0", STR_PAD_LEFT);

        // URL-safe base64 encode
        $publicKeyB64  = rtrim(strtr(base64_encode($publicKey), '+/', '-_'), '=');
        $privateKeyB64 = rtrim(strtr(base64_encode($d), '+/', '-_'), '=');

        $this->info('VAPID keys generated successfully!');
        $this->newLine();
        $this->line("VAPID_PUBLIC_KEY={$publicKeyB64}");
        $this->line("VAPID_PRIVATE_KEY={$privateKeyB64}");
        $this->newLine();
        $this->info('Add these to your .env file.');

        return self::SUCCESS;
    }
}
