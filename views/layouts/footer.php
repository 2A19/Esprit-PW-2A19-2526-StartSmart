</main>
<footer class="site-footer">
    <div class="container footer-grid">
        <div>
            <h4>StartSmart</h4>
            <p>Plateforme d'innovation collaborative pour creer, developper et financer des startups.</p>
        </div>
        <div>
            <h5>Navigation</h5>
            <a href="#">Accueil</a>
            <a href="#">Evenements</a>
            <a href="#">Mon compte</a>
        </div>
        <div>
            <h5>Ressources</h5>
            <a href="#">Guide de demarrage</a>
            <a href="#">Blog</a>
            <a href="#">FAQ</a>
        </div>
        <div>
            <h5>Contact</h5>
            <a href="mailto:contact@startsmart.local">contact@startsmart.local</a>
            <span>+33 1 00 00 00 00</span>
            <span>Paris, France</span>
        </div>
    </div>
    <p class="copyright">© 2026 StartSmart. Tous droits reserves.</p>
</footer>
<script src="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/js/app.js"></script>
<link rel="stylesheet" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/css/chatbot.css">
<?php require BASE_PATH . '/views/partials/ai_chat_widget.php'; ?>
<script>
window.StartSmartAiChat = {
    endpoint: "<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/chatbot.php"
};
</script>
<script src="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/js/chatbot.js"></script>
<?php
// Reusable Tawk.to integration for frontend pages.
$chatConfig = require BASE_PATH . '/config/Chat.php';
$chatEnabled = (bool) ($chatConfig['enabled'] ?? false);
$chatPropertyId = trim((string) ($chatConfig['property_id'] ?? ''));
$chatWidgetId = trim((string) ($chatConfig['widget_id'] ?? '1'));
?>
<?php if ($chatEnabled && $chatPropertyId !== ''): ?>
    <script type="text/javascript">
        // Async Tawk.to widget load (non-blocking).
        window.Tawk_API = window.Tawk_API || {};
        window.Tawk_API.customStyle = {
            visibility: {
                desktop: { position: 'bl', xOffset: '20px', yOffset: 20 },
                mobile: { position: 'bl', xOffset: '10px', yOffset: 10 }
            }
        };
        window.Tawk_LoadStart = new Date();
        (function () {
            var s1 = document.createElement("script");
            var s0 = document.getElementsByTagName("script")[0];
            s1.async = true;
            s1.src = "https://embed.tawk.to/<?= htmlspecialchars($chatPropertyId, ENT_QUOTES, 'UTF-8'); ?>/<?= htmlspecialchars($chatWidgetId, ENT_QUOTES, 'UTF-8'); ?>";
            s1.charset = "UTF-8";
            s1.setAttribute("crossorigin", "*");
            s0.parentNode.insertBefore(s1, s0);
        })();
    </script>
<?php endif; ?>
</body>
</html>
