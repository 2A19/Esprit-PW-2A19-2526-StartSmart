<?php
// view/backoffice/demande-refuser.php - Formulaire de refus de demande (BackOffice)
?>

<div class="page-header">
    <h1>Refuser une Demande d'Accès</h1>
    <p>Indiquez la raison du refus</p>
</div>

<div class="card">
    <div style="background: #f9f9f9; padding: 20px; margin-bottom: 20px; border-radius: 4px;">
        <h3 style="margin-top: 0; color: var(--primary-dark);">Détails de la Demande</h3>
        <p><strong>Utilisateur:</strong> <?php echo htmlspecialchars($demande['nom_utilisateur']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($demande['email_utilisateur']); ?></p>
        <p><strong>Entreprise:</strong> <?php echo htmlspecialchars($demande['entreprise']); ?></p>
        <p><strong>Ressource:</strong> <?php echo htmlspecialchars($demande['nom_ressource']); ?></p>
        <p><strong>Sponsor:</strong> <?php echo htmlspecialchars($demande['nom_sponsor']); ?></p>
        <p><strong>Quantité Demandée:</strong> <?php echo $demande['quantite_demandee']; ?></p>
        <p><strong>Motif de la Demande:</strong></p>
        <p><?php echo htmlspecialchars($demande['description_demande']); ?></p>
    </div>

    <form method="POST" action="index.php?page=demande-refuser-store&id=<?php echo $demande['id_demande']; ?>" id="refusForm">
        <div class="form-group">
            <label for="raison_refus">Raison du Refus *</label>
            <textarea 
                id="raison_refus" 
                name="raison_refus"
                required
                placeholder="Expliquez pourquoi vous refusez cette demande"
            ><?php echo isset($_POST['raison_refus']) ? htmlspecialchars($_POST['raison_refus']) : ''; ?></textarea>
            <?php if (isset($errors['raison_refus'])): ?>
                <div class="form-error"><?php echo htmlspecialchars($errors['raison_refus']); ?></div>
            <?php endif; ?>
        </div>

        <div class="btn-group">
            <button type="submit" class="btn btn-danger">Confirmer le Refus</button>
            <a href="index.php?page=demande-list" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</div>

<script>
document.getElementById('refusForm').addEventListener('submit', function(e) {
    const raison = document.getElementById('raison_refus').value.trim();
    if (!raison) {
        e.preventDefault();
        alert('Veuillez indiquer une raison pour le refus');
    } else if (raison.length < 10) {
        e.preventDefault();
        alert('La raison du refus doit contenir au minimum 10 caractères');
    }
});
</script>
