<section class="container form-page event-form-page">
    <div class="panel-head">
        <h1>Participer a l evenement</h1>
        <a class="btn event-btn-secondary" href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?route=evenement/show&id=<?= (int) $evenement['id']; ?>">Retour</a>
    </div>

    <div class="participation-event-head">
        <strong><?= htmlspecialchars((string) $evenement['titre'], ENT_QUOTES, 'UTF-8'); ?></strong>
        <span><i class="fa-regular fa-calendar"></i> <?= htmlspecialchars((string) $evenement['date_evenement'], ENT_QUOTES, 'UTF-8'); ?></span>
        <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars((string) $evenement['lieu'], ENT_QUOTES, 'UTF-8'); ?></span>
    </div>

    <form method="post" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?route=participant/store&event_id=<?= (int) $evenement['id']; ?>" novalidate class="grid-form">
        <div class="field">
            <label for="prenom">Prenom</label>
            <input id="prenom" type="text" name="prenom" value="<?= htmlspecialchars((string) ($formData['prenom'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="Prenom">
            <?php if (!empty($errors['prenom'])): ?><small class="field-error"><?= htmlspecialchars((string) $errors['prenom'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>
        </div>

        <div class="field">
            <label for="nom">Nom</label>
            <input id="nom" type="text" name="nom" value="<?= htmlspecialchars((string) ($formData['nom'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="Nom">
            <?php if (!empty($errors['nom'])): ?><small class="field-error"><?= htmlspecialchars((string) $errors['nom'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>
        </div>

        <div class="field">
            <label for="age">Age</label>
            <input id="age" type="text" name="age" value="<?= htmlspecialchars((string) ($formData['age'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="Ex: 28">
            <?php if (!empty($errors['age'])): ?><small class="field-error"><?= htmlspecialchars((string) $errors['age'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>
        </div>

        <div class="field">
            <label for="telephone">Telephone</label>
            <input id="telephone" type="text" name="telephone" value="<?= htmlspecialchars((string) ($formData['telephone'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="+212 ...">
        </div>

        <div class="field col-2">
            <label for="email">Email</label>
            <input id="email" type="text" name="email" value="<?= htmlspecialchars((string) ($formData['email'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="email@domain.com">
            <?php if (!empty($errors['email'])): ?><small class="field-error"><?= htmlspecialchars((string) $errors['email'], ENT_QUOTES, 'UTF-8'); ?></small><?php endif; ?>
        </div>

        <div class="field col-2">
            <label for="projet">Projet / Societe</label>
            <input id="projet" type="text" name="projet" value="<?= htmlspecialchars((string) ($formData['projet'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="Nom de votre projet">
        </div>

        <div class="form-actions col-2">
            <button type="submit" class="btn event-btn-participer">Valider ma participation</button>
        </div>
    </form>
</section>
