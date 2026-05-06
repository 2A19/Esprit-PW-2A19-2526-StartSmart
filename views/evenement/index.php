<section class="container event-page-head">
    <div>
        <h1>Evenements SmartSmart</h1>
        <p>Decouvrez les evenements startup avec une experience premium.</p>
    </div>
</section>

<?php if (!empty($flashSuccess)): ?>
    <section class="container">
        <div class="flash-success">
            <i class="fa-solid fa-circle-check"></i>
            <span><?= htmlspecialchars((string) $flashSuccess, ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    </section>
<?php endif; ?>

<section class="container event-grid event-grid-pro">
    <?php foreach ($evenements as $evenement): ?>
        <article class="event-card event-card-pro">
            <div class="event-card-media">
                <img src="<?= htmlspecialchars((string) ($evenement['image_url'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" alt="Image evenement">
                <span class="event-status"><?= htmlspecialchars((string) $evenement['statut'], ENT_QUOTES, 'UTF-8'); ?></span>
            </div>

            <div class="event-card-body">
                <h3><?= htmlspecialchars((string) $evenement['titre'], ENT_QUOTES, 'UTF-8'); ?></h3>
                <p><?= htmlspecialchars((string) ($evenement['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></p>

                <div class="event-meta">
                    <span><i class="fa-regular fa-calendar"></i> <?= htmlspecialchars((string) $evenement['date_evenement'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars((string) $evenement['lieu'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <span><i class="fa-solid fa-users"></i> <?= (int) ($participantCountByEvent[(int) $evenement['id']] ?? 0); ?> participant(s)</span>
                </div>

                <div class="event-actions">
                    <a class="btn event-btn-participer" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?route=participant/create&event_id=<?= (int) $evenement['id']; ?>">Participer</a>
                    <a class="btn event-btn-secondary" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?route=evenement/show&id=<?= (int) $evenement['id']; ?>">Details</a>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</section>
