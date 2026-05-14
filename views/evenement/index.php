<?php
$flashSuccess = $flashSuccess ?? null;
$flashError   = $_SESSION['flash']['error'] ?? null;
if (isset($_SESSION['flash']['error'])) unset($_SESSION['flash']['error']);
$currentSort  = $currentSort ?? 'date_desc';
$eventsJson   = json_encode(array_map(function($e) use ($participantCountByEvent) {
    return [
        'id'    => (int)$e['id'],
        'titre' => $e['titre'] ?? '',
        'date'  => $e['date_evenement'] ?? '',
        'lieu'  => $e['lieu'] ?? '',
        'statut'=> $e['statut'] ?? '',
        'capacite' => (int)($e['capacite'] ?? 0),
        'participants' => (int)($participantCountByEvent[(int)$e['id']] ?? 0),
    ];
}, $evenements ?? []), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT);
?>

<div class="event-page-head">
    <div>
        <h1><i class="fa-solid fa-calendar-days" style="color:var(--blue-dark);margin-right:8px;"></i>Evenements StartSmart</h1>
        <p style="color:var(--gray-600);margin-top:6px;">Decouvrez et rejoignez les evenements de la communaute startup.</p>
    </div>
    <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
        <?php if (function_exists('isAdmin') && isAdmin()): ?>
        <a class="btn event-create-btn" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?url=evenement/create">
            <i class="fa-solid fa-plus"></i> Creer un evenement
        </a>
        <?php endif; ?>
        <a class="btn event-btn-secondary" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?url=evenement/statistiques"
           style="display:inline-flex;align-items:center;gap:6px;padding:10px 16px;font-weight:700;font-size:0.9rem;">
            <i class="fa-solid fa-chart-bar"></i> Statistiques
        </a>
    </div>
</div>

<?php if (!empty($flashSuccess)): ?>
<div class="flash-success"><i class="fa-solid fa-circle-check"></i> <span><?= htmlspecialchars((string) $flashSuccess, ENT_QUOTES, 'UTF-8'); ?></span></div>
<?php endif; ?>
<?php if (!empty($flashError)): ?>
<div class="alert-error"><i class="fa-solid fa-circle-xmark"></i> <span><?= htmlspecialchars((string) $flashError, ENT_QUOTES, 'UTF-8'); ?></span></div>
<?php endif; ?>

<!-- Barre de tri -->
<div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
    <span style="color:var(--gray-600);font-size:0.9rem;font-weight:600;">Trier par :</span>
    <a href="?url=evenement/index&sort=date_desc"
       style="padding:7px 16px;border-radius:20px;font-size:0.85rem;font-weight:600;text-decoration:none;border:1.5px solid <?= $currentSort==='date_desc' ? 'var(--blue-dark)' : 'var(--gray-200)' ?>;background:<?= $currentSort==='date_desc' ? 'var(--blue-dark)' : '#fff' ?>;color:<?= $currentSort==='date_desc' ? '#fff' : 'var(--gray-600)' ?>;">
        <i class="fa-solid fa-arrow-down-wide-short"></i> Date ↓
    </a>
    <a href="?url=evenement/index&sort=date_asc"
       style="padding:7px 16px;border-radius:20px;font-size:0.85rem;font-weight:600;text-decoration:none;border:1.5px solid <?= $currentSort==='date_asc' ? 'var(--blue-dark)' : 'var(--gray-200)' ?>;background:<?= $currentSort==='date_asc' ? 'var(--blue-dark)' : '#fff' ?>;color:<?= $currentSort==='date_asc' ? '#fff' : 'var(--gray-600)' ?>;">
        <i class="fa-solid fa-arrow-up-wide-short"></i> Date ↑
    </a>
    <a href="?url=evenement/index&sort=participants_desc"
       style="padding:7px 16px;border-radius:20px;font-size:0.85rem;font-weight:600;text-decoration:none;border:1.5px solid <?= $currentSort==='participants_desc' ? 'var(--blue-dark)' : 'var(--gray-200)' ?>;background:<?= $currentSort==='participants_desc' ? 'var(--blue-dark)' : '#fff' ?>;color:<?= $currentSort==='participants_desc' ? '#fff' : 'var(--gray-600)' ?>;">
        <i class="fa-solid fa-users"></i> Participants
    </a>
    <span style="margin-left:auto;color:var(--gray-600);font-size:0.85rem;"><?= count($evenements ?? []); ?> evenement(s)</span>
</div>

<?php if (empty($evenements)): ?>
<p class="empty-state" style="margin-top:2rem;">Aucun evenement disponible pour le moment.</p>
<?php else: ?>
<div class="event-grid event-grid-pro">
    <?php foreach ($evenements as $evenement):
        $nbPart  = (int) ($participantCountByEvent[(int) $evenement['id']] ?? 0);
        $capacite = ($evenement['capacite'] !== null && $evenement['capacite'] !== '') ? (int) $evenement['capacite'] : null;
        $complet = $capacite !== null && $nbPart >= $capacite;
    ?>
    <article class="event-card event-card-pro">
        <div class="event-card-media">
            <img src="<?= htmlspecialchars((string) ($evenement['image_url'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                 alt="Image evenement"
                 onerror="this.src='https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=800&q=60'">
            <span class="event-status"><?= htmlspecialchars((string) $evenement['statut'], ENT_QUOTES, 'UTF-8'); ?></span>
            <?php if ($complet): ?>
            <span class="event-status" style="left:10px;right:auto;background:rgba(195,54,83,0.85);">Complet</span>
            <?php endif; ?>
        </div>

        <div class="event-card-body">
            <h3><?= htmlspecialchars((string) $evenement['titre'], ENT_QUOTES, 'UTF-8'); ?></h3>
            <p><?= htmlspecialchars((string) ($evenement['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>

            <div class="event-meta">
                <span><i class="fa-regular fa-calendar"></i> <?= htmlspecialchars((string) $evenement['date_evenement'], ENT_QUOTES, 'UTF-8'); ?></span>
                <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars((string) $evenement['lieu'], ENT_QUOTES, 'UTF-8'); ?></span>
                <span style="color:<?= $complet ? '#c33653' : 'var(--gray-600)' ?>;">
                    <i class="fa-solid fa-users"></i>
                    <?= $nbPart; ?><?= $capacite !== null ? ' / ' . $capacite : ''; ?> participant(s)
                </span>
            </div>

            <?php if ($capacite !== null): ?>
            <div style="margin:8px 0;background:var(--gray-200,#E2E8F0);border-radius:999px;height:6px;overflow:hidden;">
                <div style="height:100%;border-radius:999px;width:<?= min(100, $capacite > 0 ? round($nbPart/$capacite*100) : 0) ?>%;background:<?= $complet ? '#c33653' : 'var(--blue-dark,#0288D1)' ?>;transition:width .4s;"></div>
            </div>
            <?php endif; ?>

            <div class="event-actions">
                <?php if (!$complet): ?>
                <a class="btn event-btn-participer"
                   href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?url=participant/create&event_id=<?= (int) $evenement['id']; ?>">
                    <i class="fa-solid fa-user-plus"></i> Participer
                </a>
                <?php else: ?>
                <span class="btn event-btn-danger" style="opacity:.7;cursor:not-allowed;">
                    <i class="fa-solid fa-ban"></i> Complet
                </span>
                <?php endif; ?>
                <a class="btn event-btn-secondary"
                   href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?url=evenement/show&id=<?= (int) $evenement['id']; ?>">
                    <i class="fa-solid fa-circle-info"></i> Details
                </a>
            </div>

            <?php if (function_exists('isAdmin') && isAdmin()): ?>
            <div class="event-actions" style="margin-top:8px;">
                <a class="btn event-btn-secondary"
                   href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?url=evenement/edit&id=<?= (int) $evenement['id']; ?>">
                    <i class="fa-solid fa-pen"></i> Modifier
                </a>
                <form method="post"
                      action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?url=evenement/delete&id=<?= (int) $evenement['id']; ?>"
                      onsubmit="return confirm('Supprimer cet evenement et tous ses participants ?');">
                    <button type="submit" class="btn event-btn-danger" style="width:100%;border:none;cursor:pointer;">
                        <i class="fa-solid fa-trash"></i> Supprimer
                    </button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </article>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ═══════════════ CHATBOT ═══════════════ -->
<style>
.chatbot-btn{position:fixed;bottom:28px;right:28px;width:58px;height:58px;border-radius:50%;background:linear-gradient(135deg,var(--blue-dark,#0288D1),#4FC3F7);border:none;cursor:pointer;box-shadow:0 6px 24px rgba(2,136,209,.45);display:flex;align-items:center;justify-content:center;font-size:1.5rem;z-index:9999;transition:transform .2s,box-shadow .2s;}
.chatbot-btn:hover{transform:scale(1.1);box-shadow:0 10px 32px rgba(2,136,209,.6);}
.chatbot-win{position:fixed;bottom:100px;right:28px;width:360px;max-height:520px;background:#fff;border-radius:18px;box-shadow:0 20px 60px rgba(11,28,72,.18);display:flex;flex-direction:column;z-index:9998;overflow:hidden;transform:scale(.9) translateY(20px);opacity:0;pointer-events:none;transition:all .25s cubic-bezier(.4,0,.2,1);}
.chatbot-win.open{transform:scale(1) translateY(0);opacity:1;pointer-events:all;}
.chatbot-head{background:linear-gradient(135deg,var(--blue-dark,#0288D1),#026fab);color:#fff;padding:16px 20px;display:flex;align-items:center;gap:12px;}
.chatbot-head-avatar{width:38px;height:38px;border-radius:50%;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:1.2rem;}
.chatbot-head-info strong{display:block;font-size:.95rem;font-weight:700;}
.chatbot-head-info small{font-size:.75rem;opacity:.8;}
.chatbot-close{margin-left:auto;background:none;border:none;color:#fff;font-size:1.3rem;cursor:pointer;opacity:.8;}
.chatbot-close:hover{opacity:1;}
.chatbot-msgs{flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:10px;}
.msg{max-width:82%;padding:10px 14px;border-radius:14px;font-size:.88rem;line-height:1.5;}
.msg.bot{background:var(--gray-50,#F8FAFC);border:1px solid var(--gray-200,#E2E8F0);color:var(--gray-800,#1E293B);align-self:flex-start;border-bottom-left-radius:4px;}
.msg.user{background:var(--blue-dark,#0288D1);color:#fff;align-self:flex-end;border-bottom-right-radius:4px;}
.chatbot-suggestions{padding:6px 12px 10px;display:flex;flex-wrap:wrap;gap:6px;}
.chip{padding:5px 12px;border-radius:20px;border:1.5px solid var(--blue-dark,#0288D1);color:var(--blue-dark,#0288D1);background:#fff;font-size:.78rem;font-weight:600;cursor:pointer;transition:background .15s,color .15s;}
.chip:hover{background:var(--blue-dark,#0288D1);color:#fff;}
.chatbot-form{display:flex;gap:8px;padding:10px 14px;border-top:1px solid var(--gray-200,#E2E8F0);}
.chatbot-input{flex:1;border:1.5px solid var(--gray-200,#E2E8F0);border-radius:10px;padding:9px 12px;font-size:.88rem;outline:none;transition:border-color .2s;}
.chatbot-input:focus{border-color:var(--blue-dark,#0288D1);}
.chatbot-send{background:var(--blue-dark,#0288D1);color:#fff;border:none;border-radius:10px;padding:9px 14px;cursor:pointer;font-size:1rem;transition:background .2s;}
.chatbot-send:hover{background:#026fab;}
</style>

<button class="chatbot-btn" onclick="toggleChat()" title="Discuter avec l'assistant">
    <i class="fa-solid fa-robot" style="color:#fff;"></i>
</button>

<div class="chatbot-win" id="chatbotWin">
    <div class="chatbot-head">
        <div class="chatbot-head-avatar"><i class="fa-solid fa-robot"></i></div>
        <div class="chatbot-head-info">
            <strong>Assistant Evenements</strong>
            <small>Posez-moi vos questions</small>
        </div>
        <button class="chatbot-close" onclick="toggleChat()">✕</button>
    </div>
    <div class="chatbot-msgs" id="chatMsgs">
        <div class="msg bot">Bonjour ! Je suis votre assistant evenements. Comment puis-je vous aider ?</div>
    </div>
    <div class="chatbot-suggestions" id="chatSuggestions">
        <button class="chip" onclick="askBot('Combien y a-t-il d\'evenements ?')">Combien d'evenements ?</button>
        <button class="chip" onclick="askBot('Quel est le prochain evenement ?')">Prochain evenement</button>
        <button class="chip" onclick="askBot('Quels evenements ont de la place ?')">Places disponibles</button>
        <button class="chip" onclick="askBot('Quel evenement a le plus de participants ?')">Plus populaire</button>
    </div>
    <form class="chatbot-form" onsubmit="sendChat(event)">
        <input class="chatbot-input" id="chatInput" type="text" placeholder="Posez une question..." autocomplete="off">
        <button type="submit" class="chatbot-send"><i class="fa-solid fa-paper-plane"></i></button>
    </form>
</div>

<script>
const EVENTS_DATA = <?= $eventsJson; ?>;

function toggleChat() {
    document.getElementById('chatbotWin').classList.toggle('open');
}

function addMsg(text, who) {
    const box = document.getElementById('chatMsgs');
    const d = document.createElement('div');
    d.className = 'msg ' + who;
    d.innerHTML = text;
    box.appendChild(d);
    box.scrollTop = box.scrollHeight;
}

function sendChat(e) {
    e.preventDefault();
    const inp = document.getElementById('chatInput');
    const q = inp.value.trim();
    if (!q) return;
    inp.value = '';
    askBot(q);
}

function askBot(question) {
    addMsg(question, 'user');
    document.getElementById('chatSuggestions').style.display = 'none';
    setTimeout(function() { addMsg(botReply(question), 'bot'); }, 350);
}

function normalize(str) {
    return str.toLowerCase().replace(/[àâä]/g,'a').replace(/[éèêë]/g,'e')
              .replace(/[îï]/g,'i').replace(/[ôö]/g,'o').replace(/[ùûü]/g,'u')
              .replace(/ç/g,'c');
}

function botReply(q) {
    const lq = normalize(q);
    const total = EVENTS_DATA.length;

    // Salutation
    if (/^(bonjour|salut|hello|hi|bonsoir|coucou|hey)/.test(lq)) {
        return 'Bonjour ! Je suis votre assistant evenements 👋 Posez-moi une question sur les evenements ou cliquez sur une suggestion ci-dessous.';
    }

    // Aide
    if (/aide|help|quoi|que (peux|peut)|comment/.test(lq)) {
        return 'Je peux vous aider avec :<br>• Nombre d\'evenements<br>• Prochain evenement<br>• Places disponibles<br>• Evenement le plus populaire<br>• Lieux des evenements<br>• Recherche par titre';
    }

    if (!total) return 'Aucun evenement n\'est disponible pour le moment.';

    // Nombre d'evenements
    if (/combien|nombre|total/.test(lq)) {
        return 'Il y a actuellement <strong>' + total + '</strong> evenement(s) sur la plateforme.';
    }

    // Prochain evenement
    if (/prochain|bientot|prochainement|avenir|futur/.test(lq)) {
        const now = new Date().toISOString().slice(0,10);
        const upcoming = EVENTS_DATA.filter(function(e) { return e.date >= now; });
        upcoming.sort(function(a,b) { return a.date.localeCompare(b.date); });
        const next = upcoming[0];
        if (!next) return 'Aucun evenement a venir pour le moment.';
        return 'Le prochain evenement est <strong>' + next.titre + '</strong> le <strong>' + next.date + '</strong>' +
               (next.lieu ? ' a <strong>' + next.lieu + '</strong>' : '') + '.';
    }

    // Places disponibles
    if (/place|disponible|libre|ouvert/.test(lq)) {
        const dispo = EVENTS_DATA.filter(function(e) { return !e.capacite || e.participants < e.capacite; });
        if (!dispo.length) return 'Tous les evenements sont complets en ce moment.';
        return '<strong>' + dispo.length + '</strong> evenement(s) avec des places disponibles :<br>' +
            dispo.slice(0,3).map(function(e) {
                return '• <strong>' + e.titre + '</strong> — ' + e.participants + (e.capacite ? '/' + e.capacite : '') + ' participants';
            }).join('<br>');
    }

    // Plus populaire
    if (/populaire|plus de participant|maximum|max|top/.test(lq)) {
        const sorted = EVENTS_DATA.slice().sort(function(a,b) { return b.participants - a.participants; });
        const top = sorted[0];
        return 'L\'evenement le plus populaire est <strong>' + top.titre + '</strong> avec <strong>' + top.participants + '</strong> participant(s)' +
               (top.capacite ? ' (capacite : ' + top.capacite + ')' : '') + '.';
    }

    // Participants
    if (/participant/.test(lq)) {
        const sorted = EVENTS_DATA.slice().sort(function(a,b) { return b.participants - a.participants; });
        return 'Classement par participants :<br>' +
            sorted.slice(0,5).map(function(e,i) {
                return (i===0?'🥇':i===1?'🥈':i===2?'🥉':'•') + ' <strong>' + e.titre + '</strong> — ' + e.participants + ' participant(s)';
            }).join('<br>');
    }

    // Lieu / ville
    if (/lieu|ville|ou se|endroit|localisation/.test(lq)) {
        const lieux = [];
        EVENTS_DATA.forEach(function(e) { if (e.lieu && lieux.indexOf(e.lieu) === -1) lieux.push(e.lieu); });
        if (!lieux.length) return 'Les lieux ne sont pas renseignes pour le moment.';
        return 'Les evenements se deroulent dans : <strong>' + lieux.slice(0,5).join(', ') + '</strong>.';
    }

    // Complet / plein
    if (/complet|plein|full/.test(lq)) {
        const full = EVENTS_DATA.filter(function(e) { return e.capacite && e.participants >= e.capacite; });
        if (!full.length) return 'Aucun evenement n\'est complet pour le moment.';
        return '<strong>' + full.length + '</strong> evenement(s) complet(s) : ' +
               full.map(function(e) { return '<strong>' + e.titre + '</strong>'; }).join(', ') + '.';
    }

    // Liste tous
    if (/liste|tous|all|montre|voir/.test(lq)) {
        return 'Evenements disponibles :<br>' +
            EVENTS_DATA.slice(0,5).map(function(e) {
                return '• <strong>' + e.titre + '</strong> (' + e.date + ') — ' + e.participants + ' participant(s)';
            }).join('<br>') +
            (EVENTS_DATA.length > 5 ? '<br><em>... et ' + (EVENTS_DATA.length-5) + ' autre(s).</em>' : '');
    }

    // Recherche par titre
    const lqOrig = q.toLowerCase();
    const found = EVENTS_DATA.filter(function(e) { return normalize(e.titre).includes(lq) || e.titre.toLowerCase().includes(lqOrig); });
    if (found.length) {
        const e = found[0];
        return '<strong>' + e.titre + '</strong><br>📅 ' + e.date + (e.lieu ? ' &nbsp; 📍 ' + e.lieu : '') +
               '<br>👥 ' + e.participants + (e.capacite ? '/' + e.capacite : '') + ' participant(s) &nbsp; Statut : ' + e.statut;
    }

    return 'Je n\'ai pas compris votre question 🤔<br>Essayez : <em>prochain evenement</em>, <em>places disponibles</em>, <em>le plus populaire</em>, ou tapez le nom d\'un evenement.';
}
</script>
