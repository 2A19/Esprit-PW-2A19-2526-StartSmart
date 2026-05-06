<!DOCTYPE html>
<html lang="fr" class="<?php echo (isset($is_admin) && $is_admin === true) ? 'admin-page' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'StartSmart - Gestion des Ressources'; ?></title>
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="<?php echo (isset($is_admin) && $is_admin === true) ? 'admin-page' : ''; ?>">
    <header>
        <div class="container">
            <div class="logo">StartSmart</div>
            <nav>
                <a href="index.php">Accueil</a>
                <a href="index.php?page=ressources">Ressources</a>
                <a href="index.php?page=demandes">Mes Demandes</a>
                <a href="index.php?page=backoffice">Admin</a>
            </nav>
            <?php if (isset($is_admin) && $is_admin === true): ?>
            <!-- Bell Notification Icon -->
            <div class="notif-bell-wrapper" id="notifBellWrapper">
                <button class="notif-bell-btn" id="notifBellBtn" onclick="toggleNotifPanel()" title="Notifications SMS">
                    <svg class="bell-icon" id="bellIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                    <span class="notif-badge" id="notifBadge" style="display:none;">0</span>
                </button>

                <!-- Dropdown Panel -->
                <div class="notif-panel" id="notifPanel">
                    <div class="notif-panel-header">
                        <span>🔔 Notifications SMS</span>
                        <button class="notif-mark-all" onclick="markAllRead()" title="Tout marquer comme lu">✓ Tout lire</button>
                    </div>
                    <div class="notif-list" id="notifList">
                        <div class="notif-loading">Chargement...</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </header>

    <main class="container">
        <?php
        // Afficher les messages d'alerte
        if (isset($_SESSION['success']) && !empty($_SESSION['success'])):
            foreach ($_SESSION['success'] as $msg):
        ?>
                <div class="alert alert-success">
                    <strong>Succès!</strong> <?php echo htmlspecialchars($msg); ?>
                </div>
        <?php
            endforeach;
            unset($_SESSION['success']);
        endif;
        ?>

        <?php
        if (isset($_SESSION['error']) && !empty($_SESSION['error'])):
            foreach ($_SESSION['error'] as $msg):
        ?>
                <div class="alert alert-error">
                    <strong>Erreur!</strong> <?php echo htmlspecialchars($msg); ?>
                </div>
        <?php
            endforeach;
            unset($_SESSION['error']);
        endif;
        ?>

        <?php
        if (isset($_SESSION['warning']) && !empty($_SESSION['warning'])):
            foreach ($_SESSION['warning'] as $msg):
        ?>
                <div class="alert alert-warning">
                    <strong>Attention!</strong> <?php echo htmlspecialchars($msg); ?>
                </div>
        <?php
            endforeach;
            unset($_SESSION['warning']);
        endif;
        ?>

        <?php
        // Afficher le menu admin si on est en mode admin
        if (isset($is_admin) && $is_admin === true):
            include __DIR__ . '/backoffice/admin-menu.php';
        endif;
        ?>

        <!-- Le contenu spécifique à chaque page sera affiché ici -->

<?php if (isset($is_admin) && $is_admin === true): ?>
<script>
const NOTIF_API = 'api/notifications.php';
let panelOpen = false;

function toggleNotifPanel() {
    panelOpen = !panelOpen;
    const panel = document.getElementById('notifPanel');
    panel.classList.toggle('open', panelOpen);
    if (panelOpen) {
        loadNotifications();
        setTimeout(() => document.addEventListener('click', closeOnOutsideClick), 0);
    }
}

function closeOnOutsideClick(e) {
    const wrapper = document.getElementById('notifBellWrapper');
    if (!wrapper.contains(e.target)) {
        document.getElementById('notifPanel').classList.remove('open');
        panelOpen = false;
        document.removeEventListener('click', closeOnOutsideClick);
    }
}

function loadNotifications() {
    fetch(NOTIF_API + '?action=list')
        .then(r => r.json())
        .then(data => {
            renderNotifications(data.notifications);
            updateBadge(data.unread_count);
        })
        .catch(() => {
            document.getElementById('notifList').innerHTML =
                '<div class="notif-empty">Erreur de chargement</div>';
        });
}

function renderNotifications(notifications) {
    const list = document.getElementById('notifList');
    if (!notifications || notifications.length === 0) {
        list.innerHTML = '<div class="notif-empty">\uD83D\uDCEB Aucune notification</div>';
        return;
    }
    list.innerHTML = notifications.map(n => `
        <div class="notif-item ${n.is_read == 0 ? 'unread' : ''}" id="notif-${n.id}">
            <div class="notif-item-icon">${n.type === 'sms' ? '\uD83D\uDCF1' : '\uD83D\uDD14'}</div>
            <div class="notif-item-body">
                <div class="notif-item-title">${escHtml(n.title)}</div>
                <div class="notif-item-msg">${escHtml(n.message)}</div>
                <div class="notif-item-time">${formatTime(n.created_at)}</div>
            </div>
            ${n.is_read == 0 ? `<button class="notif-read-btn" onclick="markRead(${n.id})" title="Marquer comme lu">\u2713</button>` : ''}
        </div>
    `).join('');
}

function markRead(id) {
    fetch(NOTIF_API + '?action=mark_read&id=' + id)
        .then(() => {
            const el = document.getElementById('notif-' + id);
            if (el) {
                el.classList.remove('unread');
                const btn = el.querySelector('.notif-read-btn');
                if (btn) btn.remove();
            }
            refreshBadge();
        });
}

function markAllRead() {
    fetch(NOTIF_API + '?action=mark_read&id=0')
        .then(() => {
            document.querySelectorAll('.notif-item.unread').forEach(el => {
                el.classList.remove('unread');
                const btn = el.querySelector('.notif-read-btn');
                if (btn) btn.remove();
            });
            updateBadge(0);
        });
}

function refreshBadge() {
    fetch(NOTIF_API + '?action=count')
        .then(r => r.json())
        .then(data => updateBadge(data.unread_count));
}

function updateBadge(count) {
    const badge = document.getElementById('notifBadge');
    const bell = document.getElementById('bellIcon');
    if (count > 0) {
        badge.textContent = count > 99 ? '99+' : count;
        badge.style.display = 'flex';
        bell.classList.add('bell-ring');
    } else {
        badge.style.display = 'none';
        bell.classList.remove('bell-ring');
    }
}

function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

function formatTime(dt) {
    const date = new Date(dt);
    const now = new Date();
    const diff = Math.floor((now - date) / 1000);
    if (diff < 60) return 'A l\'instant';
    if (diff < 3600) return Math.floor(diff / 60) + ' min';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h';
    return date.toLocaleDateString('fr-FR', {day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit'});
}

// Auto-refresh badge every 30 seconds
refreshBadge();
setInterval(refreshBadge, 30000);
</script>
<?php endif; ?>
</body>
</html>
