<?php
/**
 * Mailer – StartSmart email sender
 *
 * Uses Brevo (formerly Sendinblue) FREE tier: 300 emails/day, no credit card.
 * Sign up at https://app.brevo.com → API Keys → create key → paste below.
 *
 * Falls back to PHP mail() if no API key is configured (XAMPP dev mode).
 */
class Mailer
{
    // ── Brevo config ───────────────────────────────────────────
    // Get your FREE key at https://app.brevo.com/settings/keys/api
    private string $brevoApiKey  = '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';  // ← replace
    private string $fromName     = 'StartSmart';
    private string $fromEmail    = 'mohamedgahgouh8@gmail.com'; // ← must match a verified Brevo sender
    private string $appUrl;

    public function __construct()
    {
        $scheme       = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host         = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $this->appUrl = $scheme . '://' . $host . '/startsmart';
    }

    // ── Send email verification link ───────────────────────────
    public function sendVerification(string $toEmail, string $toName, string $token): bool
    {
        $link    = $this->appUrl . '/views/auth/verify-email.php?token=' . urlencode($token);
        $subject = 'Vérifiez votre adresse email – StartSmart';
        $html    = $this->wrap("
            <h2 style='color:#3b8cf7;margin:0 0 12px'>Bienvenue sur StartSmart, {$toName} !</h2>
            <p style='color:#ccc;margin:0 0 20px'>Veuillez confirmer votre adresse email. Ce lien est valable <strong>24 heures</strong>.</p>
            <a href='{$link}' style='display:inline-block;background:#3b8cf7;color:#fff;text-decoration:none;padding:13px 28px;border-radius:8px;font-weight:700;font-size:1rem'>
              Confirmer mon email
            </a>
            <p style='color:#888;margin:24px 0 0;font-size:.82rem'>Si vous n'avez pas cree de compte, ignorez cet email.</p>
        ");
        return $this->send($toEmail, $toName, $subject, $html);
    }

    // ── Send password reset OTP ────────────────────────────────
    public function sendPasswordResetOtp(string $toEmail, string $toName, string $otp): bool
    {
        $subject = 'Reinitialisation de mot de passe - StartSmart';
        $html    = $this->wrap("
            <h2 style='color:#00e5a0;margin:0 0 12px'>Reinitialisation de mot de passe</h2>
            <p style='color:#ccc;margin:0 0 20px'>Bonjour <strong>{$toName}</strong>, voici votre code de verification :</p>
            <div style='background:#0d2137;border:2px solid #3b8cf7;border-radius:16px;padding:28px;text-align:center;margin:0 0 24px'>
              <div style='font-size:2.8rem;font-weight:900;letter-spacing:18px;color:#fff;font-family:monospace'>{$otp}</div>
              <div style='color:#888;font-size:.82rem;margin-top:12px'>Ce code expire dans <strong style='color:#ffaa00'>10 minutes</strong></div>
            </div>
            <p style='color:#888;font-size:.82rem;margin:0'>Si vous n'avez pas demande cette reinitialisation, ignorez cet email.</p>
        ");
        return $this->send($toEmail, $toName, $subject, $html);
    }

    // ── Private: send via Brevo API or fallback to mail() ─────
    private function send(string $toEmail, string $toName, string $subject, string $html): bool
    {
        if ($this->brevoApiKey !== 'YOUR_BREVO_API_KEY') {
            return $this->sendViaBrevo($toEmail, $toName, $subject, $html);
        }
        return $this->sendViaMail($toEmail, $subject, $html);
    }

    private function sendViaBrevo(string $toEmail, string $toName, string $subject, string $html): bool
    {
        $payload = json_encode([
            'sender'      => ['name' => $this->fromName, 'email' => $this->fromEmail],
            'to'          => [['email' => $toEmail, 'name' => $toName]],
            'subject'     => $subject,
            'htmlContent' => $html,
        ]);

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Accept: application/json',
                'Content-Type: application/json',
                'api-key: ' . $this->brevoApiKey,
            ],
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $result   = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $httpCode >= 200 && $httpCode < 300;
    }

    private function sendViaMail(string $toEmail, string $subject, string $html): bool
    {
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $headers .= "Reply-To: {$this->fromEmail}\r\n";
        return mail($toEmail, $subject, $html, $headers);
    }

    private function wrap(string $body): string
    {
        return "<!DOCTYPE html><html><body style='margin:0;padding:0;background:#0d1b2a;font-family:sans-serif'>
            <table width='100%' cellpadding='0' cellspacing='0'>
              <tr><td align='center' style='padding:40px 20px'>
                <table width='480' cellpadding='0' cellspacing='0' style='background:#112036;border-radius:16px;padding:36px 32px;border:1px solid rgba(255,255,255,.08)'>
                  <tr><td>
                    <div style='text-align:center;margin-bottom:28px'>
                      <span style='font-size:2rem;font-weight:900;color:#fff'>Start<span style='color:#3b8cf7'>Smart</span><span style='color:#2ddc78'>:</span></span>
                    </div>
                    {$body}
                  </td></tr>
                </table>
              </td></tr>
            </table>
        </body></html>";
    }
}
