<?php
/**
 * Mailer – Simple email sender for StartSmart
 * Sends HTML emails via PHP's mail() function (works with XAMPP + sendmail / MailHog)
 * For production, swap the send() body for PHPMailer/SMTP.
 */
class Mailer
{
    private string $fromName  = 'StartSmart';
    private string $fromEmail = 'noreply@startsmart.tn';
    private string $appUrl;

    public function __construct()
    {
        // Detect base URL dynamically (works on localhost & production)
        $scheme        = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host          = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $this->appUrl  = $scheme . '://' . $host . '/startsmart';
    }

    // ── Send verification email ────────────────────────────────
    public function sendVerification(string $toEmail, string $toName, string $token): bool
    {
        $link    = $this->appUrl . '/views/auth/verify-email.php?token=' . urlencode($token);
        $subject = 'Vérifiez votre adresse email – StartSmart';

        $html = $this->wrap("
            <h2 style='color:#3b8cf7;margin:0 0 12px'>Bienvenue sur StartSmart, {$toName} !</h2>
            <p style='color:#ccc;margin:0 0 20px'>Veuillez confirmer votre adresse email en cliquant sur le bouton ci-dessous. Ce lien est valable <strong>24 heures</strong>.</p>
            <a href='{$link}' style='display:inline-block;background:#3b8cf7;color:#fff;text-decoration:none;padding:13px 28px;border-radius:8px;font-weight:700;font-size:1rem'>
              ✉️ Confirmer mon email
            </a>
            <p style='color:#888;margin:24px 0 0;font-size:.82rem'>Si vous n'avez pas créé de compte, ignorez cet email.</p>
            <p style='color:#555;margin:10px 0 0;font-size:.78rem;word-break:break-all'>Lien direct : {$link}</p>
        ");

        return $this->send($toEmail, $subject, $html);
    }

    // ── Private helpers ────────────────────────────────────────
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

    private function send(string $to, string $subject, string $html): bool
    {
        $headers  = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $headers .= "Reply-To: {$this->fromEmail}\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();

        return mail($to, $subject, $html, $headers);
    }
}
