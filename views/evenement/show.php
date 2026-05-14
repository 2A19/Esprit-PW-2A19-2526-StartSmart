<?php
$eventLocation = trim((string) ($evenement['lieu'] ?? ''));
$showMap       = (($_GET['show_map'] ?? '') === '1');
$mapQuery      = rawurlencode($eventLocation !== '' ? $eventLocation : 'Maroc');
$mapEmbedUrl   = 'https://www.google.com/maps?q=' . $mapQuery . '&output=embed';
$mapOpenUrl    = 'https://www.google.com/maps/search/?api=1&query=' . $mapQuery;
?>

<?php if (!empty($flashSuccess)): ?>
<div class="flash-success" style="margin-bottom:16px;">
    <i class="fa-solid fa-circle-check"></i>
    <span><?= htmlspecialchars((string) $flashSuccess, ENT_QUOTES, 'UTF-8'); ?></span>
</div>
<?php endif; ?>

<div class="event-detail-shell">
    <article class="event-detail-card">
        <img src="<?= htmlspecialchars((string) ($evenement['image_url'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
             alt="Image evenement"
             class="event-detail-cover"
             onerror="this.src='https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1200&q=60'">

        <div class="event-detail-body">
            <div class="panel-head">
                <h1><?= htmlspecialchars((string) $evenement['titre'], ENT_QUOTES, 'UTF-8'); ?></h1>
                <span class="event-status"><?= htmlspecialchars((string) $evenement['statut'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>

            <p style="color:var(--gray-600);line-height:1.7;margin-bottom:16px;">
                <?= htmlspecialchars((string) ($evenement['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
            </p>

            <div class="event-meta">
                <span><i class="fa-regular fa-calendar"></i> <?= htmlspecialchars((string) $evenement['date_evenement'], ENT_QUOTES, 'UTF-8'); ?></span>
                <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars((string) $evenement['lieu'], ENT_QUOTES, 'UTF-8'); ?></span>
                <span><i class="fa-solid fa-users"></i> Capacite : <?= (int) ($evenement['capacite'] ?? 0); ?> places</span>
            </div>

            <div class="event-actions" style="margin-top:20px;">
                <a class="btn event-btn-participer"
                   href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?url=participant/create&event_id=<?= (int) $evenement['id']; ?>">
                    <i class="fa-solid fa-user-plus"></i> Participer
                </a>
                <a class="btn event-btn-secondary"
                   href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?url=evenement/show&id=<?= (int) $evenement['id']; ?>&show_map=1">
                    <i class="fa-solid fa-map-location-dot"></i> Voir la carte
                </a>
                <a class="btn event-btn-secondary"
                   href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?url=evenement/index">
                    <i class="fa-solid fa-arrow-left"></i> Retour
                </a>
                <?php if (function_exists('isAdmin') && isAdmin()): ?>
                <a class="btn event-btn-secondary"
                   href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?url=evenement/edit&id=<?= (int) $evenement['id']; ?>">
                    <i class="fa-solid fa-pen"></i> Modifier
                </a>
                <form method="post"
                      action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?url=evenement/delete&id=<?= (int) $evenement['id']; ?>"
                      onsubmit="return confirm('Supprimer cet evenement et tous ses participants ?');"
                      style="margin:0;">
                    <button type="submit" class="btn event-btn-danger" style="border:none;cursor:pointer;">
                        <i class="fa-solid fa-trash"></i> Supprimer
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </article>
</div>

<?php if ($showMap): ?>
<div class="panel-section" style="margin-top:18px;">
    <div class="panel-head">
        <h2>Localisation de l'evenement</h2>
    </div>
    <?php if ($eventLocation === ''): ?>
        <p class="empty-state">La localisation de cet evenement n'est pas disponible.</p>
    <?php else: ?>
        <p style="margin-bottom:12px;"><strong>Adresse :</strong> <?= htmlspecialchars($eventLocation, ENT_QUOTES, 'UTF-8'); ?></p>
        <iframe
            src="<?= htmlspecialchars($mapEmbedUrl, ENT_QUOTES, 'UTF-8'); ?>"
            width="100%" height="360"
            style="border:0;border-radius:12px;"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
            allowfullscreen
            title="Carte de l'evenement">
        </iframe>
        <p style="margin-top:12px;">
            <a class="btn event-btn-secondary"
               href="<?= htmlspecialchars($mapOpenUrl, ENT_QUOTES, 'UTF-8'); ?>"
               target="_blank" rel="noopener noreferrer">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Google Maps
            </a>
            <a class="btn event-btn-secondary"
               href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?url=evenement/show&id=<?= (int) $evenement['id']; ?>"
               style="margin-left:8px;">
                <i class="fa-solid fa-xmark"></i> Fermer la carte
            </a>
        </p>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="panel-section" style="margin-top:18px;">
    <div class="panel-head">
        <h2>Participants inscrits</h2>
        <span class="event-count-pill"><?= count($participants); ?> participant(s)</span>
    </div>

    <?php if (empty($participants)): ?>
        <p class="empty-state">Aucune participation pour le moment. Soyez le premier !</p>
    <?php else: ?>
        <div style="overflow-x:auto;margin-top:8px;">
            <table style="width:100%;border-collapse:collapse;font-size:.9rem;">
                <thead>
                    <tr style="background:#f8fafc;border-bottom:2px solid #e5e7eb;">
                        <th style="text-align:left;padding:10px 14px;font-weight:600;color:var(--gray-600);">#</th>
                        <th style="text-align:left;padding:10px 14px;font-weight:600;color:var(--gray-600);">Nom &amp; Prénom</th>
                        <th style="text-align:left;padding:10px 14px;font-weight:600;color:var(--gray-600);">Âge</th>
                        <th style="text-align:left;padding:10px 14px;font-weight:600;color:var(--gray-600);">Email</th>
                        <th style="text-align:left;padding:10px 14px;font-weight:600;color:var(--gray-600);">Téléphone</th>
                        <th style="text-align:left;padding:10px 14px;font-weight:600;color:var(--gray-600);">Projet</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($participants as $i => $participant): ?>
                    <tr style="border-bottom:1px solid #f1f5f9;<?= $i % 2 === 0 ? '' : 'background:#fafafa;'; ?>">
                        <td style="padding:10px 14px;color:var(--gray-400);"><?= $i + 1; ?></td>
                        <td style="padding:10px 14px;font-weight:600;">
                            <?= htmlspecialchars((string) ($participant['prenom'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                            <?= htmlspecialchars((string) ($participant['nom'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                        </td>
                        <td style="padding:10px 14px;"><?= (int) ($participant['age'] ?? 0); ?> ans</td>
                        <td style="padding:10px 14px;">
                            <a href="mailto:<?= htmlspecialchars((string) ($participant['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                               style="color:var(--primary);text-decoration:none;">
                                <i class="fa-solid fa-envelope" style="margin-right:4px;font-size:.8rem;"></i><?= htmlspecialchars((string) ($participant['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>
                            </a>
                        </td>
                        <td style="padding:10px 14px;">
                            <?php if (!empty($participant['telephone'])): ?>
                                <a href="tel:<?= htmlspecialchars((string) $participant['telephone'], ENT_QUOTES, 'UTF-8'); ?>"
                                   style="color:var(--gray-700);text-decoration:none;">
                                    <i class="fa-solid fa-phone" style="margin-right:4px;font-size:.8rem;"></i><?= htmlspecialchars((string) $participant['telephone'], ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            <?php else: ?>
                                <span style="color:var(--gray-400);">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:10px 14px;">
                            <?php if (!empty($participant['projet'])): ?>
                                <span style="background:#ede9fe;color:#6d28d9;border-radius:20px;padding:3px 10px;font-size:.8rem;">
                                    <i class="fa-solid fa-briefcase" style="margin-right:3px;"></i><?= htmlspecialchars((string) $participant['projet'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            <?php else: ?>
                                <span style="color:var(--gray-400);">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
