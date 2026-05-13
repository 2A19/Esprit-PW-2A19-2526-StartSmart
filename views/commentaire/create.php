<div class="bo-header">
    <h2>Ajouter un Commentaire</h2>
    <a href="index.php?controller=commentaire&action=index" class="btn-secondary">Retour à la liste</a>
</div>

<?php if(isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>

<div class="bo-form">
    <form id="formComment" action="index.php?controller=commentaire&action=create" method="POST" onsubmit="return validerCommentaire(event)">
        <div id="error-msg-com" style="color:red; display:none; margin-bottom:15px; font-weight:bold;"></div>
        
        <div class="form-group">
            <label>Contenu du Commentaire</label>
            <textarea id="contenu_com" name="contenu" rows="4" style="width: 100%; border: 1px solid #ccc; border-radius: 5px; padding: 10px;"></textarea>
        </div>
        <div class="form-group">
            <label>Associer à un Post</label>
            <select id="post_id_com" name="post_id">
                <option value="">-- Sélectionnez un Post --</option>
                <?php foreach ($postsList as $p): ?>
                    <option value="<?php echo $p['id_post']; ?>"><?php echo htmlspecialchars($p['titre']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <button type="submit" class="btn-primary">Enregistrer le Commentaire</button>
    </form>
</div>

<script>
function validerCommentaire(e) {
    let errorMsg = document.getElementById('error-msg-com');
    errorMsg.style.display = 'none';
    errorMsg.innerHTML = '';
    let erreurs = [];

    let contenu = document.getElementById('contenu_com').value.trim();
    let post_id = document.getElementById('post_id_com').value;

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
