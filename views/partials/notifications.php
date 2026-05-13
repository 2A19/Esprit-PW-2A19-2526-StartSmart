<?php
// Minimal notifications partial. Expects `isLoggedIn()` helper.
?>
<div id="notification-root" class="notification-root">
    <button id="notification-toggle" class="nav-btn" aria-haspopup="true" aria-expanded="false">
        🔔 <span id="notification-badge" class="badge" style="display:none">0</span>
    </button>
    <div id="notification-dropdown" class="notification-dropdown" style="display:none">
        <div class="notification-header">
            <strong>Notifications</strong>
            <button id="mark-all-read" class="small-link">Marquer tout lu</button>
        </div>
        <ul id="notification-list" class="notification-list">
            <li class="empty">Aucune notification.</li>
        </ul>
    </div>
</div>

<style>
.notification-root { position: relative; display: inline-block; }
.notification-dropdown { position: absolute; right: 0; top: 36px; width: 320px; max-height: 400px; overflow:auto; background:#fff; border:1px solid #ddd; box-shadow:0 6px 18px rgba(0,0,0,.08); z-index:50; }
.notification-list { list-style:none; margin:0; padding:0; }
.notification-list li { padding:8px 12px; border-bottom:1px solid #f1f1f1; }
.notification-list li.unread { background:#f9fbff; }
.badge { background:#e74c3c; color:#fff; border-radius:12px; padding:2px 6px; font-size:12px; margin-left:6px; }
.small-link { background:none; border:none; color:#3498db; cursor:pointer; font-size:12px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function(){
    const toggle = document.getElementById('notification-toggle');
    const dropdown = document.getElementById('notification-dropdown');
    const badge = document.getElementById('notification-badge');
    const list = document.getElementById('notification-list');
    const markAll = document.getElementById('mark-all-read');

    toggle.addEventListener('click', function(e){
        const visible = dropdown.style.display !== 'none';
        dropdown.style.display = visible ? 'none' : 'block';
        toggle.setAttribute('aria-expanded', (!visible).toString());
        if (!visible) {
            // load notifications
            fetchNotifications();
        }
    });

    markAll.addEventListener('click', function(){
        fetch('index.php?controller=notification&action=markAllRead')
            .then(()=>{
                badge.style.display = 'none';
                list.innerHTML = '<li class="empty">Aucune notification.</li>';
            });
    });

    window.fetchNotifications = function() {
        fetch('index.php?controller=notification&action=list')
            .then(r=>r.json())
            .then(data=>{
                if (data.success) {
                    const items = data.items || [];
                    if (items.length === 0) {
                        list.innerHTML = '<li class="empty">Aucune notification.</li>';
                        badge.style.display = 'none';
                    } else {
                        list.innerHTML = '';
                        items.forEach(n=>{
                            const li = document.createElement('li');
                            li.className = n.is_read == 0 ? 'unread' : '';
                            const actor = n.actor_name ? ('<strong>'+escapeHtml(n.actor_name)+'</strong> ') : '';
                            li.innerHTML = '<div>'+actor+escapeHtml(n.message || n.type) +'</div><div class="meta">'+n.created_at+'</div>';
                            li.addEventListener('click', function(){
                                // mark read
                                fetch('index.php?controller=notification&action=markRead', {
                                    method: 'POST', headers: {'Content-Type':'application/json'},
                                    body: JSON.stringify({ id: n.id_notification })
                                }).then(()=>{ li.classList.remove('unread'); updateCount(); });
                            });
                            list.appendChild(li);
                        });
                        updateCount();
                    }
                }
            });
    };

    window.updateCount = function() {
        fetch('index.php?controller=notification&action=count')
            .then(r=>r.json())
            .then(d=>{
                if (d.success && d.count > 0) {
                    badge.style.display = 'inline-block';
                    badge.innerText = d.count;
                } else {
                    badge.style.display = 'none';
                }
            });
    };

    function escapeHtml(s){ if(!s) return ''; return s.replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }

    // initial count
    updateCount();

    // initial count
    updateCount();

    // pure AJAX polling to update badge (safe, no hanging)
    setInterval(updateCount, 30000);
});
</script>
