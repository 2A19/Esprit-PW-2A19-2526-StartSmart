<div class="event-form-wrapper">
    <div class="panel-head" style="margin-bottom:20px;">
        <h1><i class="fa-solid fa-user-plus" style="color:var(--blue-dark);margin-right:8px;"></i>Participer a l'evenement</h1>
        <a class="btn event-btn-secondary"
           href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?url=evenement/show&id=<?= (int) ($evenement['id'] ?? 0); ?>">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
    </div>

    <div class="participation-event-head">
        <strong><?= htmlspecialchars((string) ($evenement['titre'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></strong>
        <span><i class="fa-regular fa-calendar"></i> <?= htmlspecialchars((string) ($evenement['date_evenement'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
        <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars((string) ($evenement['lieu'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></span>
    </div>

    <?php if (!empty($errors['general'])): ?>
    <div class="alert-error">
        <i class="fa-solid fa-circle-xmark"></i>
        <?= htmlspecialchars((string) $errors['general'], ENT_QUOTES, 'UTF-8'); ?>
    </div>
    <?php endif; ?>

    <form method="post"
          action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?url=participant/store&event_id=<?= (int) ($evenement['id'] ?? 0); ?>"
          novalidate
          class="grid-form"
          style="margin-top:20px;">
        <input type="hidden" name="from_admin" value="<?= (!empty($fromAdmin)) ? '1' : '0'; ?>">

        <div class="field">
            <label for="prenom">Prenom <span style="color:#c33653;">*</span></label>
            <input id="prenom" type="text" name="prenom"
                   value="<?= htmlspecialchars((string) ($formData['prenom'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                   placeholder="Votre prenom">
            <?php if (!empty($errors['prenom'])): ?>
                <small class="field-error"><?= htmlspecialchars((string) $errors['prenom'], ENT_QUOTES, 'UTF-8'); ?></small>
            <?php endif; ?>
        </div>

        <div class="field">
            <label for="nom">Nom <span style="color:#c33653;">*</span></label>
            <input id="nom" type="text" name="nom"
                   value="<?= htmlspecialchars((string) ($formData['nom'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                   placeholder="Votre nom">
            <?php if (!empty($errors['nom'])): ?>
                <small class="field-error"><?= htmlspecialchars((string) $errors['nom'], ENT_QUOTES, 'UTF-8'); ?></small>
            <?php endif; ?>
        </div>

        <div class="field">
            <label for="age">Age <span style="color:#c33653;">*</span></label>
            <input id="age" type="number" name="age" min="1" max="120"
                   value="<?= htmlspecialchars((string) ($formData['age'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                   placeholder="Ex: 28">
            <?php if (!empty($errors['age'])): ?>
                <small class="field-error"><?= htmlspecialchars((string) $errors['age'], ENT_QUOTES, 'UTF-8'); ?></small>
            <?php endif; ?>
        </div>

        <div class="field">
            <label for="telephone">Telephone</label>
            <input id="telephone" type="tel" name="telephone"
                   value="<?= htmlspecialchars((string) ($formData['telephone'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                   placeholder="+212 6 00 00 00 00">
        </div>

        <div class="field col-2">
            <label for="email">Email <span style="color:#c33653;">*</span></label>
            <input id="email" type="email" name="email"
                   value="<?= htmlspecialchars((string) ($formData['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                   placeholder="email@domaine.com">
            <?php if (!empty($errors['email'])): ?>
                <small class="field-error"><?= htmlspecialchars((string) $errors['email'], ENT_QUOTES, 'UTF-8'); ?></small>
            <?php endif; ?>
        </div>

        <div class="field col-2">
            <label for="projet">Projet / Societe</label>
            <input id="projet" type="text" name="projet"
                   value="<?= htmlspecialchars((string) ($formData['projet'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                   placeholder="Nom de votre projet ou societe (optionnel)">
        </div>

        <div class="form-actions col-2">
            <button type="submit" class="btn event-btn-participer" style="padding:12px 28px;font-size:15px;">
                <i class="fa-solid fa-check"></i> Valider ma participation
            </button>
        </div>
    </form>
</div>
