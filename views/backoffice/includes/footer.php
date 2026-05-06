<link rel="stylesheet" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/css/chatbot.css">
<?php require BASE_PATH . '/views/partials/ai_chat_widget.php'; ?>
<script>
window.StartSmartAiChat = {
    endpoint: "<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/chatbot.php"
};
</script>
<script src="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/js/chatbot.js"></script>
<?php
// Reusable Tawk.to integration for backoffice pages.
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
