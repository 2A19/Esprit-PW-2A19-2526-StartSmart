<?php
// view/backoffice/ressource-edit.php - Formulaire d'édition de ressource (BackOffice)
require_once __DIR__ . '/../../model/Sponsor.php';

$sponsorModel = new Sponsor();
$sponsors = $sponsorModel->getActive();
?>

<div class="page-header">
    <h1>Modifier la Ressource</h1>
    <p><?php echo htmlspecialchars($ressource['nom_ressource']); ?></p>
</div>

<div class="card">
    <form method="POST" action="index.php?page=ressource-update&id=<?php echo $ressource['id_ressource']; ?>" id="ressourceForm">
        <div class="form-group">
            <label for="id_sponsor">Sponsor *</label>
            <select id="id_sponsor" name="id_sponsor" required disabled>
                <?php foreach ($sponsors as $sponsor): ?>
                    <option value="<?php echo $sponsor['id_sponsor']; ?>"
                        <?php echo ($sponsor['id_sponsor'] == $ressource['id_sponsor']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($sponsor['nom_sponsor']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <small style="color: var(--text-light);">Le sponsor ne peut pas être changé</small>
        </div>

        <div class="form-group">
            <label for="nom_ressource">Nom de la Ressource *</label>
            <input 
                type="text" 
                id="nom_ressource" 
                name="nom_ressource" 
                required
                value="<?php echo htmlspecialchars($ressource['nom_ressource']); ?>"
            >
            <?php if (isset($errors['nom_ressource'])): ?>
                <div class="form-error"><?php echo htmlspecialchars($errors['nom_ressource']); ?></div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="type_ressource">Type de Ressource *</label>
            <select id="type_ressource" name="type_ressource" required>
                <option value="Services" <?php echo ($ressource['type_ressource'] == 'Services') ? 'selected' : ''; ?>>Services</option>
                <option value="Formation" <?php echo ($ressource['type_ressource'] == 'Formation') ? 'selected' : ''; ?>>Formation</option>
                <option value="Infrastructure" <?php echo ($ressource['type_ressource'] == 'Infrastructure') ? 'selected' : ''; ?>>Infrastructure</option>
                <option value="Équipement" <?php echo ($ressource['type_ressource'] == 'Équipement') ? 'selected' : ''; ?>>Équipement</option>
                <option value="Consultation" <?php echo ($ressource['type_ressource'] == 'Consultation') ? 'selected' : ''; ?>>Consultation</option>
                <option value="Financement" <?php echo ($ressource['type_ressource'] == 'Financement') ? 'selected' : ''; ?>>Financement</option>
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
            ><?php echo htmlspecialchars($ressource['description']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="quantite_disponible">Quantité Disponible *</label>
            <input 
                type="number" 
                id="quantite_disponible" 
                name="quantite_disponible" 
                min="1"
                value="<?php echo $ressource['quantite_disponible']; ?>"
                required
            >
            <?php if (isset($errors['quantite_disponible'])): ?>
                <div class="form-error"><?php echo htmlspecialchars($errors['quantite_disponible']); ?></div>
            <?php endif; ?>
            <small style="color: var(--text-light);">Utilisée: <?php echo $ressource['quantite_utilisee']; ?></small>
        </div>

        <div class="form-group">
            <label for="statut">Statut *</label>
            <select id="statut" name="statut" required>
                <option value="disponible" <?php echo ($ressource['statut'] == 'disponible') ? 'selected' : ''; ?>>Disponible</option>
                <option value="indisponible" <?php echo ($ressource['statut'] == 'indisponible') ? 'selected' : ''; ?>>Indisponible</option>
                <option value="archive" <?php echo ($ressource['statut'] == 'archive') ? 'selected' : ''; ?>>Archivée</option>
            </select>
        </div>

        <div class="btn-group">
            <button type="submit" class="btn btn-primary">Mettre à Jour</button>
            <a href="index.php?page=ressource-list" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<script>
// Validation côté client pour améliorer l'UX (validation serveur obligatoire)
document.getElementById('ressourceForm').addEventListener('submit', function(e) {
    const errors = [];
    
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
