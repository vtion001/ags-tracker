<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use PragmaRX\Google2FA\Google2FA;

class SetupTotp extends Command
{
    protected $signature = 'totp:setup {email : User email}';
    protected $description = 'Set up TOTP for a user';

    public function handle(): int
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User not found: {$email}");
            return 1;
        }

        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        $user->totp_secret = $secret;
        $user->totp_enabled = true;
        $user->totp_setup_at = now();
        $user->save();

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        $this->info("TOTP enabled for: {$user->email}");
        $this->info("Secret Key: {$secret}");
        $this->info("QR Code URL: {$qrCodeUrl}");
        $this->info("");
        $this->info("Users with TOTP enabled will be prompted for verification code on dev-login.");

        return 0;
    }
}
