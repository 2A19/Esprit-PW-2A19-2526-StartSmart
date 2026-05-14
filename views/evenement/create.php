<div class="event-form-wrapper">
    <div class="panel-head" style="margin-bottom:24px;">
        <h1><i class="fa-solid fa-calendar-plus" style="color:var(--blue-dark);margin-right:8px;"></i>Creer un evenement</h1>
        <a class="btn event-btn-secondary"
           href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?url=evenement/index">
            <i class="fa-solid fa-arrow-left"></i> Retour
        </a>
    </div>

    <form method="post"
          action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?url=evenement/store"
          novalidate
          class="grid-form">
        <input type="hidden" name="from_admin" value="<?= (!empty($fromAdmin)) ? '1' : '0'; ?>">

        <div class="field col-2">
            <label for="titre">Titre <span style="color:#c33653;">*</span></label>
            <input id="titre" type="text" name="titre"
                   value="<?= htmlspecialchars((string) ($formData['titre'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                   placeholder="Nom de l'evenement">
            <?php if (!empty($errors['titre'])): ?>
                <small class="field-error"><?= htmlspecialchars((string) $errors['titre'], ENT_QUOTES, 'UTF-8'); ?></small>
            <?php endif; ?>
        </div>

        <div class="field">
            <label for="date_evenement">Date <span style="color:#c33653;">*</span></label>
            <input id="date_evenement" type="date" name="date_evenement"
                   value="<?= htmlspecialchars((string) ($formData['date_evenement'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
            <?php if (!empty($errors['date_evenement'])): ?>
                <small class="field-error"><?= htmlspecialchars((string) $errors['date_evenement'], ENT_QUOTES, 'UTF-8'); ?></small>
            <?php endif; ?>
        </div>

        <div class="field">
            <label for="capacite">Capacite (places)</label>
            <input id="capacite" type="number" name="capacite" min="1"
                   value="<?= htmlspecialchars((string) ($formData['capacite'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                   placeholder="Ex: 100">
            <?php if (!empty($errors['capacite'])): ?>
                <small class="field-error"><?= htmlspecialchars((string) $errors['capacite'], ENT_QUOTES, 'UTF-8'); ?></small>
            <?php endif; ?>
        </div>

        <div class="field col-2">
            <label for="lieu">Lieu <span style="color:#c33653;">*</span></label>
            <input id="lieu" type="text" name="lieu"
                   value="<?= htmlspecialchars((string) ($formData['lieu'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                   placeholder="Ville ou En ligne">
            <?php if (!empty($errors['lieu'])): ?>
                <small class="field-error"><?= htmlspecialchars((string) $errors['lieu'], ENT_QUOTES, 'UTF-8'); ?></small>
            <?php endif; ?>
        </div>

        <div class="field">
            <label for="statut">Statut</label>
            <select id="statut" name="statut">
                <?php $currentStatut = (string) ($formData['statut'] ?? 'Ouvert'); ?>
                <option value="Ouvert"  <?= $currentStatut === 'Ouvert'  ? 'selected' : ''; ?>>Ouvert</option>
                <option value="Complet" <?= $currentStatut === 'Complet' ? 'selected' : ''; ?>>Complet</option>
                <option value="Ferme"   <?= $currentStatut === 'Ferme'   ? 'selected' : ''; ?>>Ferme</option>
            </select>
        </div>

        <div class="field">
            <label for="image_url">URL de l'image (optionnel)</label>
            <input id="image_url" type="url" name="image_url"
                   value="<?= htmlspecialchars((string) ($formData['image_url'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                   placeholder="https://...">
        </div>

        <div class="field col-2">
            <label for="description">Description <span style="color:#c33653;">*</span></label>
            <textarea id="description" name="description" rows="5"
                      placeholder="Decrivez l'evenement..."><?= htmlspecialchars((string) ($formData['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
            <?php if (!empty($errors['description'])): ?>
                <small class="field-error"><?= htmlspecialchars((string) $errors['description'], ENT_QUOTES, 'UTF-8'); ?></small>
            <?php endif; ?>
        </div>

        <div class="form-actions col-2">
            <button type="submit" class="btn event-btn-participer" style="padding:12px 28px;font-size:15px;">
                <i class="fa-solid fa-floppy-disk"></i> Enregistrer l'evenement
            </button>
        </div>
    </form>
</div>
