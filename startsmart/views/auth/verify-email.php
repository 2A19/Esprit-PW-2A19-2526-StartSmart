<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>StartSmart – Vérification Email</title>
<link rel="stylesheet" href="../../public/css/style.css">
<style>
  body{background:var(--navy);display:flex;align-items:center;justify-content:center;min-height:100vh;}
  body::before{content:'';position:fixed;inset:0;
    background:radial-gradient(ellipse 60% 50% at 15% 20%,rgba(59,140,247,.12) 0%,transparent 60%),
               radial-gradient(ellipse 50% 55% at 85% 80%,rgba(45,220,120,.07) 0%,transparent 60%);
    pointer-events:none;}
  .wrap{width:min(480px,96vw);position:relative;z-index:1;padding:2rem 0;text-align:center;}
  .card{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:20px;
        padding:2.5rem 2rem;backdrop-filter:blur(18px);}
  .icon{font-size:4rem;margin-bottom:1rem;}
  h1{color:#fff;font-size:1.5rem;margin:0 0 .8rem;}
  p{color:rgba(255,255,255,.55);font-size:.9rem;line-height:1.6;margin:0 0 1.5rem;}
  .alert-success{background:rgba(45,220,120,.1);border:1px solid rgba(45,220,120,.3);color:#2ddc78;
                  border-radius:10px;padding:14px;margin-bottom:1.5rem;}
  .alert-error{background:rgba(232,68,90,.12);border:1px solid rgba(232,68,90,.35);color:#f87089;
                border-radius:10px;padding:14px;margin-bottom:1.5rem;}
  .btn{display:inline-block;background:var(--blue);color:#fff;text-decoration:none;padding:12px 28px;
       border-radius:8px;font-weight:700;font-size:.9rem;border:none;cursor:pointer;transition:opacity .18s;}
  .btn:hover{opacity:.85;}
  .btn-outline{background:transparent;border:1.5px solid rgba(255,255,255,.2);color:rgba(255,255,255,.6);}
  .divider{height:1px;background:rgba(255,255,255,.07);margin:1.5rem 0;}
</style>
</head>
<body>
<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Handle token verification via GET
if (!empty($_GET['token'])) {
    require_once __DIR__ . '/../../controllers/AuthController.php';
    $ctrl = new AuthController();
    $ctrl->verifyEmailToken(); // redirects back here with session message
}

$success = $_SESSION['verify_success'] ?? '';
$error   = $_SESSION['verify_error']   ?? '';
unset($_SESSION['verify_success'], $_SESSION['verify_error']);
?>

<div class="wrap">
  <div style="text-align:center;margin-bottom:2rem;">
    <img src="../../public/img/logo.png" alt="StartSmart Logo" style="max-width:160px;height:auto;margin-bottom:1rem;">
    <div style="font-size:2.8rem;font-weight:900;color:#fff">Start<span style="color:#3b8cf7">Smart</span><span style="color:#2ddc78">:</span></div>
  </div>

  <div class="card">
    <?php if ($success): ?>
      <div class="icon">✅</div>
      <h1>Email vérifié !</h1>
      <div class="alert-success"><?= htmlspecialchars($success) ?></div>
      <a href="/startsmart/views/auth/login.php" class="btn">Se connecter →</a>

    <?php elseif ($error): ?>
      <div class="icon">❌</div>
      <h1>Lien invalide</h1>
      <div class="alert-error"><?= htmlspecialchars($error) ?></div>
      <a href="/startsmart/views/auth/login.php?tab=register" class="btn">Créer un nouveau compte</a>

    <?php else: ?>
      <div class="icon">✉️</div>
      <h1>Vérifiez votre email</h1>
      <p>
        Un lien de vérification a été envoyé à votre adresse email.<br>
        Cliquez sur le lien dans l'email pour activer votre compte.<br><br>
        <strong style="color:rgba(255,255,255,.7)">Le lien est valable 24 heures.</strong>
      </p>
      <p style="font-size:.8rem;color:rgba(255,255,255,.35)">
        Vous n'avez pas reçu l'email ? Vérifiez votre dossier spam ou courrier indésirable.
      </p>
      <div class="divider"></div>
      <a href="/startsmart/views/auth/login.php" class="btn btn-outline">← Retour à la connexion</a>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
