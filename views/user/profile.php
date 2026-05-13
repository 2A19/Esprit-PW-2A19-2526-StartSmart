<?php $userId = isset($_GET['id']) ? (int)$_GET['id'] : 0; ?>

<div class="profile-container">
    <div class="profile-header" id="profileHeader" style="display:none;">
        <div class="profile-avatar">
            <img id="userAvatar" src="" alt="Avatar" class="avatar-img" style="display:none;">
            <div id="userAvatarPlaceholder" class="avatar-placeholder">U</div>
        </div>
        <div class="profile-info">
            <h1 id="userName">User</h1>
            <p class="role-badge" id="userRole">Member</p>
            <p class="city" id="userCity" style="display:none;">📍 </p>
            <p class="email" id="userEmail">✉️ </p>
            <p class="joined" id="userJoined">Membre depuis </p>
        </div>
        <a href="index.php?controller=user&action=edit" class="btn-edit" id="editBtn" style="display:none;">Modifier le profil</a>
    </div>

    <div class="profile-bio" id="bioSection" style="display:none;">
        <h3>À propos</h3>
        <p id="userBio"></p>
    </div>

    <div class="profile-stats">
        <div class="stat">
            <span class="stat-number" id="statPosts">0</span>
            <span class="stat-label">Posts</span>
        </div>
        <div class="stat">
            <span class="stat-number" id="statComments">0</span>
            <span class="stat-label">Commentaires</span>
        </div>
        <div class="stat">
            <span class="stat-number" id="statReactions">0</span>
            <span class="stat-label">Réactions</span>
        </div>
    </div>

    <div class="profile-activity">
        <h3>Activité récente</h3>

        <div class="activity-section" id="recentPostsSection" style="display:none;">
            <h4>Posts récents</h4>
            <ul class="activity-list" id="recentPostsList"></ul>
        </div>

        <div class="activity-section" id="recentCommentsSection" style="display:none;">
            <h4>Commentaires récents</h4>
            <ul class="activity-list" id="recentCommentsList"></ul>
        </div>

        <p class="empty-message" id="emptyMessage" style="display:none;">Aucune activité pour le moment.</p>
    </div>
</div>

<script>
const USER_ID = <?php echo $userId; ?>;

async function loadProfile() {
    if (!USER_ID) return;
    try {
        const res = await fetch('public/api.php?controller=profile&action=index&id=' + encodeURIComponent(USER_ID) + '&format=json', { headers: { 'Accept': 'application/json' }, credentials: 'include' });
        const json = await res.json();
        const u = json.success ? json.data : json.user || {};
        
        // Show header
        document.getElementById('profileHeader').style.display = 'flex';
        
        // Avatar
        if (u.avatar_url) {
            document.getElementById('userAvatar').src = u.avatar_url;
            document.getElementById('userAvatar').style.display = 'block';
            document.getElementById('userAvatarPlaceholder').style.display = 'none';
        } else {
            document.getElementById('userAvatarPlaceholder').innerText = (u.nom||'U').substring(0,1).toUpperCase();
        }
        
        document.getElementById('userName').innerText = u.nom || 'User';
        document.getElementById('userRole').innerText = u.role || 'Member';
        if (u.city) {
            document.getElementById('userCity').innerText = '📍 ' + u.city;
            document.getElementById('userCity').style.display = 'block';
        }
        document.getElementById('userEmail').innerText = '✉️ ' + (u.email || 'N/A');
        if (u.created_at) {
            document.getElementById('userJoined').innerText = 'Membre depuis le ' + new Date(u.created_at).toLocaleDateString('fr-FR');
        }
        
        // Bio
        if (u.bio) {
            document.getElementById('bioSection').style.display = 'block';
            document.getElementById('userBio').innerHTML = (u.bio||'').replace(/\n/g, '<br>');
        }
        
        // Stats
        document.getElementById('statPosts').innerText = u.stats?.posts || 0;
        document.getElementById('statComments').innerText = u.stats?.comments || 0;
        document.getElementById('statReactions').innerText = u.stats?.reactions || 0;
        
        // Check if user is viewing own profile
        try {
            const authRes = await fetch('public/api.php?path=auth/me&format=json', { credentials: 'include' });
            const authJson = await authRes.json();
            const currentUser = authJson.success ? authJson.data : authJson.user || {};
            if (currentUser.id_utilisateur === USER_ID) {
                document.getElementById('editBtn').style.display = 'block';
            }
        } catch (err) { console.error('auth check', err); }
        
        // Recent activity
        let hasActivity = false;
        if (u.recentPosts && u.recentPosts.length) {
            document.getElementById('recentPostsSection').style.display = 'block';
            const list = document.getElementById('recentPostsList'); list.innerHTML = '';
            u.recentPosts.forEach(p => {
                const li = document.createElement('li');
                li.innerHTML = '<a href="index.php?controller=post&action=show&id=' + encodeURIComponent(p.id_post) + '">' + escapeHtml((p.titre||'Post').substring(0, 50)) + '...</a><span class="date">' + new Date(p.date_creation).toLocaleString('fr-FR') + '</span>';
                list.appendChild(li);
            });
            hasActivity = true;
        }
        
        if (u.recentComments && u.recentComments.length) {
            document.getElementById('recentCommentsSection').style.display = 'block';
            const list = document.getElementById('recentCommentsList'); list.innerHTML = '';
            u.recentComments.forEach(c => {
                const li = document.createElement('li');
                li.innerHTML = '<a href="index.php?controller=post&action=show&id=' + encodeURIComponent(c.post_id) + '">Sur: ' + escapeHtml((c.post_titre || 'Sujet inconnu').substring(0, 40)) + '</a><span class="date">' + new Date(c.date_creation).toLocaleString('fr-FR') + '</span>';
                list.appendChild(li);
            });
            hasActivity = true;
        }
        
        if (!hasActivity) {
            document.getElementById('emptyMessage').style.display = 'block';
        }
        
    } catch (err) { console.error('loadProfile error', err); }
}

function escapeHtml(s){ return String(s||'').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

document.addEventListener('DOMContentLoaded', () => loadProfile());
</script>

<style>
.profile-container { max-width: 800px; margin: 2rem auto; padding: 2rem; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
.profile-header { display: flex; gap: 2rem; margin-bottom: 2rem; align-items: flex-start; }
.profile-avatar { flex-shrink: 0; }
.avatar-img { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; }
.avatar-placeholder { width: 120px; height: 120px; border-radius: 50%; background: #3498db; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 48px; font-weight: bold; }
.profile-info { flex: 1; }
.profile-info h1 { margin: 0 0 0.5rem 0; }
.role-badge { display: inline-block; background: #2ecc71; color: #fff; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 12px; font-weight: bold; margin: 0 0 0.5rem 0; }
.city, .email, .joined { margin: 0.25rem 0; color: #666; }
.btn-edit { display: inline-block; background: #3498db; color: #fff; padding: 0.5rem 1rem; border-radius: 4px; text-decoration: none; margin-top: 1rem; }
.btn-edit:hover { background: #2980b9; }
.profile-bio { background: #f9f9f9; padding: 1.5rem; border-radius: 4px; margin-bottom: 2rem; }
.profile-bio h3 { margin-top: 0; }
.profile-stats { display: flex; gap: 2rem; margin-bottom: 2rem; padding: 1rem; background: #f0f0f0; border-radius: 4px; }
.stat { text-align: center; }
.stat-number { display: block; font-size: 24px; font-weight: bold; color: #2c3e50; }
.stat-label { display: block; color: #7f8c8d; font-size: 12px; }
.profile-activity h3 { margin-top: 2rem; }
.activity-section { margin-bottom: 1.5rem; }
.activity-list { list-style: none; padding: 0; }
.activity-list li { padding: 0.75rem; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
.activity-list li a { color: #3498db; text-decoration: none; }
.activity-list li a:hover { text-decoration: underline; }
.date { color: #999; font-size: 12px; }
.empty-message { text-align: center; color: #999; padding: 2rem; }
</style>

