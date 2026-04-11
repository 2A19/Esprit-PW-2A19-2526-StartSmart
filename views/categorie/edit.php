<div class="bo-header">
    <h2>Modifier la Catégorie #<?php echo htmlspecialchars($this->categorie->id); ?></h2>
    <a href="index.php?controller=categorie&action=index" class="btn-secondary">Retour à la liste</a>
</div>

<?php if(isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>

<div class="bo-form">
    <form id="formCategorieEdit" action="index.php?controller=categorie&action=edit&id=<?php echo htmlspecialchars($this->categorie->id); ?>" method="POST" onsubmit="return validerCategorieEdit(event)">
        <div id="error-msg-cat-edit" style="color:red; display:none; margin-bottom:15px; font-weight:bold;"></div>
        
        <div class="form-group">
            <label>Numéro de Catégorie</label>
            <input type="text" id="num_cat_edit" name="num" value="<?php echo htmlspecialchars($this->categorie->num); ?>" />
        </div>
        <div class="form-group">
            <label>Type de Projet</label>
            <input type="text" id="typeprojet_cat_edit" name="typeprojet" value="<?php echo htmlspecialchars($this->categorie->typeprojet); ?>" />
        </div>
        <div class="form-group">
            <label>Nom Investisseur</label>
            <input type="text" id="nom_investisseur_cat_edit" name="nom_investisseur" value="<?php echo htmlspecialchars($this->categorie->nom_investisseur); ?>" />
        </div>
        <div class="form-group">
            <label>Affecter un Projet</label>
            <select name="projet_id">
                <option value="">-- Aucun --</option>
                <?php foreach ($projetsList as $p): ?>
                    <option value="<?php echo $p['id']; ?>" <?php echo ($this->categorie->projet_id == $p['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($p['nomprojet']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <button type="submit" class="btn-primary">Mettre à jour la Catégorie</button>
    </form>
</div>

<script>
function validerCategorieEdit(e) {
    let errorMsg = document.getElementById('error-msg-cat-edit');
    errorMsg.style.display = 'none';
    errorMsg.innerHTML = '';
    let erreurs = [];

    let num = document.getElementById('num_cat_edit').value.trim();
    let typeprojet = document.getElementById('typeprojet_cat_edit').value.trim();
    let nom_investisseur = document.getElementById('nom_investisseur_cat_edit').value.trim();

    if (num === '' || isNaN(num) || Number(num) <= 0) {
        erreurs.push("Le numéro de catégorie doit être un chiffre positif.");
    }
    if (typeprojet.length < 3) {
        erreurs.push("Le type de projet doit contenir au moins 3 caractères.");
    }
    if (nom_investisseur.length < 3) {
        erreurs.push("Le nom de l'investisseur doit contenir au moins 3 caractères.");
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
