<?php ?>

<div class="edit-profile-container">
    <h2>Modifier votre profil</h2>

    <?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" class="profile-form">
        <div class="form-group">
            <label for="bio">Biographie</label>
            <textarea id="bio" name="bio" rows="4" placeholder="Parlez de vous..." class="form-control"><?php echo htmlspecialchars($this->user->bio ?? ''); ?></textarea>
        </div>

        <div class="form-group">
            <label for="city">Ville</label>
            <input type="text" id="city" name="city" placeholder="Votre ville" class="form-control" value="<?php echo htmlspecialchars($this->user->city ?? ''); ?>">
        </div>

        <div class="form-group">
            <label for="avatar_url">URL Avatar</label>
            <input type="url" id="avatar_url" name="avatar_url" placeholder="https://example.com/avatar.jpg" class="form-control" value="<?php echo htmlspecialchars($this->user->avatar_url ?? ''); ?>">
            <small>Lien complet vers une image (JPG, PNG)</small>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary">Enregistrer les modifications</button>
            <a href="index.php?controller=user&action=profile" class="btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<style>
.edit-profile-container { max-width: 600px; margin: 2rem auto; padding: 2rem; background: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
.edit-profile-container h2 { margin-top: 0; }
.alert { padding: 1rem; border-radius: 4px; margin-bottom: 1rem; }
.alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
.alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
.profile-form { display: flex; flex-direction: column; gap: 1rem; }
.form-group { display: flex; flex-direction: column; }
.form-group label { font-weight: bold; margin-bottom: 0.5rem; }
.form-control { padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem; }
.form-control:focus { outline: none; border-color: #3498db; box-shadow: 0 0 5px rgba(52, 152, 219, 0.3); }
.form-group small { color: #666; font-size: 0.9rem; margin-top: 0.25rem; }
.form-actions { display: flex; gap: 1rem; }
.btn-primary, .btn-secondary { padding: 0.75rem 1.5rem; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; text-decoration: none; display: inline-block; text-align: center; }
.btn-primary { background: #27ae60; color: #fff; }
.btn-primary:hover { background: #229954; }
.btn-secondary { background: #95a5a6; color: #fff; }
.btn-secondary:hover { background: #7f8c8d; }
</style>
