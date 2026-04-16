<?php
// view/frontoffice/demande-create.php - Formulaire de création de demande d'accès
?>

<div class="page-header">
    <h1>Déposer une Demande d'Accès</h1>
    <p>Remplissez le formulaire ci-dessous pour demander l'accès à une ressource</p>
</div>

<div class="card">
    <form method="POST" action="index.php?page=demande-store" id="demandeForm">
        <div class="form-group">
            <label for="id_utilisateur">Votre Nom *</label>
            <input 
                type="text" 
                id="id_utilisateur" 
                name="id_utilisateur" 
                required
                placeholder="Entrez votre nom complet"
                value="<?php echo isset($_POST['id_utilisateur']) ? htmlspecialchars($_POST['id_utilisateur']) : ''; ?>"
            >
            <?php if (isset($errors['id_utilisateur'])): ?>
                <div class="form-error"><?php echo htmlspecialchars($errors['id_utilisateur']); ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="id_ressource">Ressource Demandée *</label>
            <select id="id_ressource" name="id_ressource" required>
                <option value="">-- Sélectionner une ressource --</option>
                <?php if (isset($ressources) && !empty($ressources)): ?>
                    <?php foreach ($ressources as $res): ?>
                        <option value="<?php echo $res['id_ressource']; ?>"
                            <?php echo (isset($_POST['id_ressource']) && $_POST['id_ressource'] == $res['id_ressource']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($res['nom_ressource']); ?> 
                            (<?php echo $res['nom_sponsor']; ?>)
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <?php if (isset($errors['id_ressource'])): ?>
                <div class="form-error"><?php echo htmlspecialchars($errors['id_ressource']); ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="quantite_demandee">Quantité Demandée *</label>
            <input 
                type="number" 
                id="quantite_demandee" 
                name="quantite_demandee" 
                min="1" 
                value="<?php echo isset($_POST['quantite_demandee']) ? htmlspecialchars($_POST['quantite_demandee']) : '1'; ?>"
                required
            >
            <?php if (isset($errors['quantite_demandee'])): ?>
                <div class="form-error"><?php echo htmlspecialchars($errors['quantite_demandee']); ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="duree_acces_jours">Durée d'Accès (en jours) *</label>
            <input 
                type="number" 
                id="duree_acces_jours" 
                name="duree_acces_jours" 
                min="1"
                value="<?php echo isset($_POST['duree_acces_jours']) ? htmlspecialchars($_POST['duree_acces_jours']) : '30'; ?>"
                required
            >
            <small style="color: var(--text-light);">Durée de votre accès à la ressource</small>
        </div>

        <div class="form-group">
            <label for="description_demande">Description de Votre Besoin</label>
            <textarea 
                id="description_demande" 
                name="description_demande"
                placeholder="Expliquez pourquoi vous avez besoin de cette ressource et comment vous l'utiliserez"
            ><?php echo isset($_POST['description_demande']) ? htmlspecialchars($_POST['description_demande']) : ''; ?></textarea>
        </div>

        <div class="btn-group">
            <button type="submit" class="btn btn-primary">Soumettre la Demande</button>
            <a href="index.php?page=ressources" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<script>
// Validation côté client pour améliorer l'UX (validation serveur obligatoire)
document.getElementById('demandeForm').addEventListener('submit', function(e) {
    const errors = [];
    
    const idUtilisateur = document.getElementById('id_utilisateur').value.trim();
    if (!idUtilisateur) {
        errors.push('Votre nom est obligatoire');
    } else if (idUtilisateur.length < 3) {
        errors.push('Votre nom doit contenir au minimum 3 caractères');
    }
    
    const idRessource = document.getElementById('id_ressource').value;
    if (!idRessource) {
        errors.push('Veuillez sélectionner une ressource');
    }
    
    const quantite = document.getElementById('quantite_demandee').value;
    if (!quantite || quantite < 1) {
        errors.push('La quantité doit être au minimum 1');
    }
    
    const duree = document.getElementById('duree_acces_jours').value;
    if (!duree || duree < 1) {
        errors.push('La durée doit être au minimum 1 jour');
    }
    
    if (errors.length > 0) {
        e.preventDefault();
        alert('Erreurs de saisie:\n\n' + errors.join('\n'));
    }
});
</script>
