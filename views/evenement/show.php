<section class="container event-detail-shell">
    <?php
    $eventLocation = trim((string) ($evenement['lieu'] ?? ''));
    $showMap = (($_GET['show_map'] ?? '') === '1');
    $mapQuery = rawurlencode($eventLocation !== '' ? $eventLocation : 'Paris');
    $mapEmbedUrl = 'https://www.google.com/maps?q=' . $mapQuery . '&output=embed';
    $mapOpenUrl = 'https://www.google.com/maps/search/?api=1&query=' . $mapQuery;
    ?>
    <article class="event-detail-card">
        <img src="<?= htmlspecialchars((string) ($evenement['image_url'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" alt="Image evenement" class="event-detail-cover">

        <div class="event-detail-body">
            <div class="panel-head">
                <h1><?= htmlspecialchars((string) $evenement['titre'], ENT_QUOTES, 'UTF-8'); ?></h1>
                <span class="event-status"><?= htmlspecialchars((string) $evenement['statut'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>

            <p><?= htmlspecialchars((string) ($evenement['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>

            <div class="event-meta">
                <span><i class="fa-regular fa-calendar"></i> <?= htmlspecialchars((string) $evenement['date_evenement'], ENT_QUOTES, 'UTF-8'); ?></span>
                <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars((string) $evenement['lieu'], ENT_QUOTES, 'UTF-8'); ?></span>
                <span><i class="fa-solid fa-users"></i> Capacite: <?= (int) ($evenement['capacite'] ?? 0); ?></span>
            </div>

            <div class="event-actions">
                <a class="btn event-btn-participer" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?route=participant/create&event_id=<?= (int) $evenement['id']; ?>">Participer</a>
                <a class="btn event-btn-secondary" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?route=evenement/show&id=<?= (int) $evenement['id']; ?>&show_map=1">Map</a>
                <a class="btn event-btn-secondary" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?route=evenement/index">Retour aux evenements</a>
            </div>
        </div>
    </article>
</section>

<?php if ($showMap): ?>
    <section class="container panel-section" style="margin-top: 18px;">
        <div class="panel-head">
            <h2>Localisation de l'evenement</h2>
        </div>

        <?php if ($eventLocation === ''): ?>
            <p class="empty-state">La localisation de cet evenement n'est pas disponible.</p>
        <?php else: ?>
            <p style="margin-top: 0; margin-bottom: 12px;">
                <strong>Adresse:</strong> <?= htmlspecialchars($eventLocation, ENT_QUOTES, 'UTF-8'); ?>
            </p>
            <iframe
                src="<?= htmlspecialchars($mapEmbedUrl, ENT_QUOTES, 'UTF-8'); ?>"
                width="100%"
                height="360"
                style="border:0; border-radius: 12px;"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                allowfullscreen
                title="Carte de localisation de l'evenement"
            ></iframe>
            <p style="margin-top: 12px;">
                <a class="btn event-btn-secondary" href="<?= htmlspecialchars($mapOpenUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                    Ouvrir dans Google Maps
                </a>
                <a class="btn event-btn-secondary" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?route=evenement/show&id=<?= (int) $evenement['id']; ?>">
                    Fermer la carte
                </a>
            </p>
        <?php endif; ?>
    </section>
<?php endif; ?>

<section class="container panel-section">
    <div class="panel-head">
        <h2>Participants inscrits</h2>
        <span class="event-count-pill"><?= count($participants); ?> participant(s)</span>
    </div>

    <?php if ($participants === []): ?>
        <p class="empty-state">Aucune participation pour le moment.</p>
    <?php else: ?>
        <div class="participant-list">
            <?php foreach ($participants as $participant): ?>
                <article class="participant-card">
                    <h4><?= htmlspecialchars((string) $participant['prenom'], ENT_QUOTES, 'UTF-8'); ?> <?= htmlspecialchars((string) $participant['nom'], ENT_QUOTES, 'UTF-8'); ?></h4>
                    <p><i class="fa-solid fa-envelope"></i> <?= htmlspecialchars((string) $participant['email'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><i class="fa-solid fa-phone"></i> <?= htmlspecialchars((string) $participant['telephone'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p><i class="fa-solid fa-briefcase"></i> <?= htmlspecialchars((string) $participant['projet'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <small>Age: <?= (int) $participant['age']; ?> ans</small>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
