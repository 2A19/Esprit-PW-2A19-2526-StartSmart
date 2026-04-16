<?php
// view/backoffice/ressource-create.php - Formulaire de création de ressource (BackOffice)
require_once __DIR__ . '/../../model/Sponsor.php';

$sponsorModel = new Sponsor();
$sponsors = $sponsorModel->getActive();
?>

<div class="page-header">
    <h1>Créer une Nouvelle Ressource</h1>
    <p>Ajoutez une nouvelle ressource à la plateforme</p>
</div>

<div class="card">
    <form method="POST" action="index.php?page=ressource-store" id="ressourceForm">
        <div class="form-group">
            <label for="id_sponsor">Sponsor *</label>
            <select id="id_sponsor" name="id_sponsor" required>
                <option value="">-- Sélectionner un sponsor --</option>
                <?php foreach ($sponsors as $sponsor): ?>
                    <option value="<?php echo $sponsor['id_sponsor']; ?>"
                        <?php echo (isset($_POST['id_sponsor']) && $_POST['id_sponsor'] == $sponsor['id_sponsor']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($sponsor['nom_sponsor']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($errors['id_sponsor'])): ?>
                <div class="form-error"><?php echo htmlspecialchars($errors['id_sponsor']); ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="nom_ressource">Nom de la Ressource *</label>
            <input 
                type="text" 
                id="nom_ressource" 
                name="nom_ressource" 
                required
                placeholder="Ex: Audit Financier, Formation ISO, Infrastructure Cloud"
                value="<?php echo isset($_POST['nom_ressource']) ? htmlspecialchars($_POST['nom_ressource']) : ''; ?>"
            >
            <?php if (isset($errors['nom_ressource'])): ?>
                <div class="form-error"><?php echo htmlspecialchars($errors['nom_ressource']); ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="type_ressource">Type de Ressource *</label>
            <select id="type_ressource" name="type_ressource" required>
                <option value="">-- Sélectionner un type --</option>
                <option value="Services" <?php echo (isset($_POST['type_ressource']) && $_POST['type_ressource'] == 'Services') ? 'selected' : ''; ?>>Services</option>
                <option value="Formation" <?php echo (isset($_POST['type_ressource']) && $_POST['type_ressource'] == 'Formation') ? 'selected' : ''; ?>>Formation</option>
                <option value="Infrastructure" <?php echo (isset($_POST['type_ressource']) && $_POST['type_ressource'] == 'Infrastructure') ? 'selected' : ''; ?>>Infrastructure</option>
                <option value="Équipement" <?php echo (isset($_POST['type_ressource']) && $_POST['type_ressource'] == 'Équipement') ? 'selected' : ''; ?>>Équipement</option>
                <option value="Consultation" <?php echo (isset($_POST['type_ressource']) && $_POST['type_ressource'] == 'Consultation') ? 'selected' : ''; ?>>Consultation</option>
                <option value="Financement" <?php echo (isset($_POST['type_ressource']) && $_POST['type_ressource'] == 'Financement') ? 'selected' : ''; ?>>Financement</option>
            </select>
            <?php if (isset($errors['type_ressource'])): ?>
                <div class="form-error"><?php echo htmlspecialchars($errors['type_ressource']); ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea 
                id="description" 
                name="description"
                placeholder="Décrivez la ressource, ses avantages et comment elle sera utilisée"
            ><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
        </div>

        <div class="form-group">
            <label for="quantite_disponible">Quantité Disponible *</label>
            <input 
                type="number" 
                id="quantite_disponible" 
                name="quantite_disponible" 
                min="1"
                value="<?php echo isset($_POST['quantite_disponible']) ? htmlspecialchars($_POST['quantite_disponible']) : ''; ?>"
                required
            >
            <?php if (isset($errors['quantite_disponible'])): ?>
                <div class="form-error"><?php echo htmlspecialchars($errors['quantite_disponible']); ?></div>
            <?php endif; ?>
        </div>

        <div class="btn-group">
            <button type="submit" class="btn btn-primary">Créer la Ressource</button>
            <a href="index.php?page=ressource-list" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<script>
// Validation côté client pour améliorer l'UX (validation serveur obligatoire)
document.getElementById('ressourceForm').addEventListener('submit', function(e) {
    const errors = [];
    
    const sponsor = document.getElementById('id_sponsor').value;
    if (!sponsor) {
        errors.push('Veuillez sélectionner un sponsor');
    }
    
    const nom = document.getElementById('nom_ressource').value.trim();
    if (!nom) {
        errors.push('Le nom de la ressource est obligatoire');
    } else if (nom.length < 3) {
        errors.push('Le nom doit contenir au minimum 3 caractères');
    }
    
    const type = document.getElementById('type_ressource').value;
    if (!type) {
        errors.push('Veuillez sélectionner un type');
    }
    
    const quantite = document.getElementById('quantite_disponible').value;
    if (!quantite || quantite < 1) {
        errors.push('La quantité doit être au minimum 1');
    }
    
    if (errors.length > 0) {
        e.preventDefault();
        alert('Erreurs de saisie:\n\n' + errors.join('\n'));
    }
});
</script>
