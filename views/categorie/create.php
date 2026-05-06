<div class="bo-header">
    <h2>Ajouter une Nouvelle Catégorie</h2>
    <a href="index.php?controller=categorie&action=index" class="btn-secondary">Retour à la liste</a>
</div>

<?php if(isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>

<div class="bo-form">
    <form id="formCategorie" action="index.php?controller=categorie&action=create" method="POST" onsubmit="return validerCategorie(event)">
        <div id="error-msg-cat" style="color:red; display:none; margin-bottom:15px; font-weight:bold;"></div>
        
        <div class="form-group">
            <label>Numéro de Catégorie</label>
            <input type="text" id="num_cat" name="num" />
        </div>
        <div class="form-group">
            <label>Type de Projet</label>
            <input type="text" id="typeprojet_cat" name="typeprojet" />
        </div>
        <div class="form-group">
            <label>Nom Investisseur</label>
            <input type="text" id="nom_investisseur_cat" name="nom_investisseur" />
        </div>

        <p style="margin: 0 0 18px; color: #555;">La catégorie sera créée sans projet lié. Les projets se rattachent désormais à la catégorie.</p>
        
        <button type="submit" class="btn-primary">Enregistrer la Catégorie</button>
    </form>
</div>

<script>
function validerCategorie(e) {
    let errorMsg = document.getElementById('error-msg-cat');
    errorMsg.style.display = 'none';
    errorMsg.innerHTML = '';
    let erreurs = [];

    let num = document.getElementById('num_cat').value.trim();
    let typeprojet = document.getElementById('typeprojet_cat').value.trim();
    let nom_investisseur = document.getElementById('nom_investisseur_cat').value.trim();

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
