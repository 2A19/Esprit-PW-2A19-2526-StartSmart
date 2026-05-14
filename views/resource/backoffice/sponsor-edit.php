<?php
// view/backoffice/sponsor-edit.php - Formulaire de modification de sponsor
?>

<div class="page-header">
    <h1>Modifier le Sponsor</h1>
    <p>Mettez à jour les informations du sponsor</p>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-header">
        <h2>Informations du Sponsor</h2>
    </div>
    
    <div class="card-body" style="padding: 20px;">
        <form method="POST" action="index.php?controller=resource&action=sponsorUpdate&id=<?php echo $sponsor['id_sponsor']; ?>">
            
            <div class="form-group">
                <label for="nom_sponsor">Nom du Sponsor *</label>
                <input 
                    type="text" 
                    id="nom_sponsor" 
                    name="nom_sponsor" 
                    placeholder="Ex: TechCorp Finance"
                    value="<?php echo htmlspecialchars($sponsor['nom_sponsor']); ?>">
                <?php if (isset($errors) && !empty($errors)): ?>
                    <?php foreach ($errors as $error): ?>
                        <?php if (strpos($error, 'Nom du sponsor') !== false): ?>
                            <span class="form-error"><?php echo htmlspecialchars($error); ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email_sponsor">Email *</label>
                <input 
                    type="text" 
                    id="email_sponsor" 
                    name="email_sponsor" 
                    placeholder="contact@sponsor.com"
                    value="<?php echo htmlspecialchars($sponsor['email_sponsor']); ?>">
                <?php if (isset($errors) && !empty($errors)): ?>
                    <?php foreach ($errors as $error): ?>
                        <?php if (strpos($error, 'Email') !== false): ?>
                            <span class="form-error"><?php echo htmlspecialchars($error); ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="telephone">Téléphone</label>
                <input 
                    type="text" 
                    id="telephone" 
                    name="telephone" 
                    placeholder="+33..."
                    value="<?php echo htmlspecialchars($sponsor['telephone'] ?? ''); ?>">
                <?php if (isset($errors) && !empty($errors)): ?>
                    <?php foreach ($errors as $error): ?>
                        <?php if (strpos($error, 'Téléphone') !== false): ?>
                            <span class="form-error"><?php echo htmlspecialchars($error); ?></span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="type_ressources">Type de Ressources</label>
                <input 
                    type="text" 
                    id="type_ressources" 
                    name="type_ressources" 
                    placeholder="Ex: Logiciels, Services"
                    value="<?php echo htmlspecialchars($sponsor['type_ressources'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea 
                    id="description" 
                    name="description" 
                    placeholder="Description du sponsor..."><?php echo htmlspecialchars($sponsor['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="statut">Statut</label>
                <select id="statut" name="statut">
                    <option value="actif" <?php echo $sponsor['statut'] === 'actif' ? 'selected' : ''; ?>>Actif</option>
                    <option value="inactif" <?php echo $sponsor['statut'] === 'inactif' ? 'selected' : ''; ?>>Inactif</option>
                    <option value="suspendu" <?php echo $sponsor['statut'] === 'suspendu' ? 'selected' : ''; ?>>Suspendu</option>
                </select>
            </div>

            <div class="btn-group">
                <button type="submit" class="btn btn-primary">Mettre à Jour</button>
                <a href="index.php?controller=resource&action=sponsorList" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
