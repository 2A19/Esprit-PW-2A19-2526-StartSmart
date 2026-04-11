<div class="bo-header">
    <h2>Modifier le Projet #<?php echo htmlspecialchars($this->projet->id); ?></h2>
    <a href="index.php?controller=projet&action=index" class="btn-secondary">Retour à la liste</a>
</div>

<?php if(isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>

<div class="bo-form">
    <form id="formProjetEdit" action="index.php?controller=projet&action=edit&id=<?php echo htmlspecialchars($this->projet->id); ?>" method="POST" onsubmit="return validerProjetEdit(event)">
        <div id="error-msg-edit" style="color:red; display:none; margin-bottom:15px; font-weight:bold;"></div>
        
        <div class="form-group">
            <label>Numéro de Projet</label>
            <input type="text" id="num_edit" name="num" value="<?php echo htmlspecialchars($this->projet->num); ?>" />
        </div>
        <div class="form-group">
            <label>Nom du Projet</label>
            <input type="text" id="nomprojet_edit" name="nomprojet" value="<?php echo htmlspecialchars($this->projet->nomprojet); ?>" />
        </div>
        <div class="form-group">
            <label>Date Début</label>
            <input type="date" id="datedebut_edit" name="datedebut" value="<?php echo htmlspecialchars($this->projet->datedebut); ?>" />
        </div>
        <div class="form-group">
            <label>Date Fin</label>
            <input type="date" id="datefin_edit" name="datefin" value="<?php echo htmlspecialchars($this->projet->datefin); ?>" />
        </div>
        <div class="form-group">
            <label>Budget</label>
            <input type="text" id="budget_edit" name="budget" value="<?php echo htmlspecialchars($this->projet->budget); ?>" />
        </div>
        <div class="form-group">
            <label>Gain</label>
            <input type="text" id="gain_edit" name="gain" value="<?php echo htmlspecialchars($this->projet->gain); ?>" />
        </div>
        
        <button type="submit" class="btn-primary">Mettre à jour le Projet</button>
    </form>
</div>

<script>
function validerProjetEdit(e) {
    let errorMsg = document.getElementById('error-msg-edit');
    errorMsg.style.display = 'none';
    errorMsg.innerHTML = '';
    let erreurs = [];

    let num = document.getElementById('num_edit').value.trim();
    let nomprojet = document.getElementById('nomprojet_edit').value.trim();
    let datedebut = document.getElementById('datedebut_edit').value.trim();
    let datefin = document.getElementById('datefin_edit').value.trim();
    let budget = document.getElementById('budget_edit').value.trim();
    let gain = document.getElementById('gain_edit').value.trim();

    if (num === '' || isNaN(num) || Number(num) <= 0) {
        erreurs.push("Le numéro doit être un chiffre positif.");
    }
    if (nomprojet.length < 3) {
        erreurs.push("Le nom du projet doit contenir au moins 3 caractères.");
    }
    if (datedebut === '') {
        erreurs.push("Veuillez sélectionner une date de début.");
    }
    if (datefin === '') {
        erreurs.push("Veuillez sélectionner une date de fin.");
    }
    if (datedebut !== '' && datefin !== '' && new Date(datefin) <= new Date(datedebut)) {
        erreurs.push("La date de fin doit être strictement supérieure à la date de début.");
    }
    if (budget === '' || isNaN(budget) || Number(budget) < 0) {
        erreurs.push("Le budget doit être un nombre positif.");
    }
    if (gain === '' || isNaN(gain) || Number(gain) < 0) {
        erreurs.push("Le gain doit être un nombre positif.");
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
