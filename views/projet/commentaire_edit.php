<div class="bo-header">
    <h2>Modifier le Commentaire</h2>
    <a href="javascript:history.back();" class="btn-secondary">Retour</a>
</div>

<?php if(isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>

<div class="bo-form" style="max-width: 700px; margin: 0 auto;">
    <form action="index.php?controller=projet_commentaire&action=edit&id=<?php echo $this->commentaire->id; ?>" method="POST">
        <div class="form-group">
            <label for="contenu">Commentaire</label>
            <textarea id="contenu" name="contenu" rows="6" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: inherit; font-size: 14px;"><?php echo htmlspecialchars($this->commentaire->contenu); ?></textarea>
        </div>
        
        <div style="display: flex; gap: 10px; margin-top: 25px;">
            <button type="submit" class="btn-primary" style="flex: 1; padding: 12px; background: #0B1C48; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 16px;">✓ Mettre à jour</button>
            <a href="javascript:history.back();" style="flex: 1; padding: 12px; background: #e9ecef; color: #495057; border: 1px solid #ddd; border-radius: 6px; text-align: center; text-decoration: none; font-weight: 600;">Annuler</a>
        </div>
    </form>
</div>
