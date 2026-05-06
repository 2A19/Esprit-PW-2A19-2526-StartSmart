<section class="container form-page event-form-page">
    <div class="panel-head">
        <h1>Modifier un evenement</h1>
        <a class="btn event-btn-secondary" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?route=evenement/index">Retour</a>
    </div>

    <form method="post" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?route=evenement/update&id=<?= (int) ($eventId ?? 0); ?>" novalidate class="grid-form">
        <div class="field col-2">
            <label for="titre">Titre</label>
            <input id="titre" type="text" name="titre" value="<?= htmlspecialchars((string) ($formData['titre'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="Nom de l evenement">
            <?php if (!empty($errors['titre'])): ?><small class="field-error"><?= htmlspecialchars((string) $errors['titre'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>
        </div>

        <div class="field">
            <label for="date_evenement">Date</label>
            <input id="date_evenement" type="text" name="date_evenement" value="<?= htmlspecialchars((string) ($formData['date_evenement'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="YYYY-MM-DD">
            <?php if (!empty($errors['date_evenement'])): ?><small class="field-error"><?= htmlspecialchars((string) $errors['date_evenement'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>
        </div>

        <div class="field">
            <label for="capacite">Capacite</label>
            <input id="capacite" type="text" name="capacite" value="<?= htmlspecialchars((string) ($formData['capacite'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ex: 120">
            <?php if (!empty($errors['capacite'])): ?><small class="field-error"><?= htmlspecialchars((string) $errors['capacite'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>
        </div>

        <div class="field col-2">
            <label for="lieu">Lieu</label>
            <input id="lieu" type="text" name="lieu" value="<?= htmlspecialchars((string) ($formData['lieu'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ville ou En ligne">
            <?php if (!empty($errors['lieu'])): ?><small class="field-error"><?= htmlspecialchars((string) $errors['lieu'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>
        </div>

        <div class="field">
            <label for="statut">Statut</label>
            <select id="statut" name="statut">
                <?php $currentStatut = (string) ($formData['statut'] ?? 'Ouvert'); ?>
                <option value="Ouvert" <?= $currentStatut === 'Ouvert' ? 'selected' : ''; ?>>Ouvert</option>
                <option value="Complet" <?= $currentStatut === 'Complet' ? 'selected' : ''; ?>>Complet</option>
            </select>
        </div>

        <div class="field">
            <label for="image_url">Image URL (optionnel)</label>
            <input id="image_url" type="text" name="image_url" value="<?= htmlspecialchars((string) ($formData['image_url'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="https://...">
        </div>

        <div class="field col-2">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4" placeholder="Description de l evenement"><?= htmlspecialchars((string) ($formData['description'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
            <?php if (!empty($errors['description'])): ?><small class="field-error"><?= htmlspecialchars((string) $errors['description'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>
        </div>

        <div class="form-actions col-2">
            <button type="submit" class="btn event-btn-participer">Mettre a jour</button>
        </div>
    </form>
</section>
