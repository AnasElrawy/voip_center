<?php


namespace App\Services;

use App\Models\EmailVerification;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyCustomerEmail;

class CustomerAuthService
{
    /**
     * Generate a unique email verification token
     */
    public function generateToken(): string
    {
        do {
            $token = Str::random(64);
        } while (EmailVerification::where('token', $token)->exists());

        return $token;
    }

    /**
     * Store the token in the email_verifications table
     */
    public function storeToken(string $email, string $token): void
    {
        EmailVerification::where('email', $email)->delete();

        EmailVerification::create([
            'email' => $email,
            'token' => $token,
            'expires_at' => Carbon::now()->addMinutes(30),
        ]);
    }

    /**
     * Send the verification email
     */
    public function sendVerificationEmail(string $email, string $token): void
    {
        Mail::to($email)->send(new VerifyCustomerEmail($token, $email));
    }

    /**
     * Full process to generate token and send email
     */
    public function generateAndSendVerification(string $email): void
    {
        $token = $this->generateToken();
        $this->storeToken($email, $token);
        $this->sendVerificationEmail($email, $token);
    }
}
