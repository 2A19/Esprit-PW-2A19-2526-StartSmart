<div class="bo-header">
    <h2>Modifier le Commentaire #<?php echo htmlspecialchars($this->commentaire->id_commentaire); ?></h2>
    <a href="index.php?controller=commentaire&action=index" class="btn-secondary">Retour à la liste</a>
</div>

<?php if(isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>

<div class="bo-form">
    <form id="formCommentEdit" action="index.php?controller=commentaire&action=edit&id=<?php echo htmlspecialchars($this->commentaire->id_commentaire); ?>" method="POST" onsubmit="return validerCommentaireEdit(event)">
        <div id="error-msg-com-edit" style="color:red; display:none; margin-bottom:15px; font-weight:bold;"></div>
        
        <div class="form-group">
            <label>Contenu du Commentaire</label>
            <textarea id="contenu_com_edit" name="contenu" rows="4" style="width: 100%; border: 1px solid #ccc; border-radius: 5px; padding: 10px;"><?php echo htmlspecialchars($this->commentaire->contenu); ?></textarea>
        </div>
        <div class="form-group">
            <label>Associer à un Post</label>
            <select id="post_id_com_edit" name="post_id">
                <option value="">-- Sélectionnez un Post --</option>
                <?php foreach ($postsList as $p): ?>
                    <option value="<?php echo $p['id_post']; ?>" <?php echo ($this->commentaire->post_id == $p['id_post']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($p['titre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <button type="submit" class="btn-primary">Mettre à jour le Commentaire</button>
    </form>
</div>

<script>
function validerCommentaireEdit(e) {
    let errorMsg = document.getElementById('error-msg-com-edit');
    errorMsg.style.display = 'none';
    errorMsg.innerHTML = '';
    let erreurs = [];

    let contenu = document.getElementById('contenu_com_edit').value.trim();
    let post_id = document.getElementById('post_id_com_edit').value;

    if (contenu.length < 5) {
        erreurs.push("Le contenu doit contenir au moins 5 caractères.");
    }
    if (post_id === '') {
        erreurs.push("Veuillez sélectionner un Post parent.");
    }

    if (erreurs.length > 0) {
        e.preventDefault();
        errorMsg.innerHTML = erreurs.join('<br>');
        errorMsg.style.display = 'block';
        return false;
    }
    return true;
}
</script>
