<?php
// Candidatures view
// Expects: $project_candidatures array
?>

<div class="candidatures-header" style="background: linear-gradient(135deg, var(--navy) 0%, var(--blue-dark) 100%); color: white; padding: 40px; border-radius: 16px; margin-bottom: 30px; box-shadow: var(--shadow-md);">
    <h1 style="font-size: 32px; font-weight: 800; margin: 0 0 10px 0;">
        <i class="fa-solid fa-users" style="color: var(--blue); margin-right: 10px;"></i> Candidatures
    </h1>
    <p style="font-size: 16px; opacity: 0.9; margin: 0;">Gérez les utilisateurs intéressés par vos projets</p>
</div>

<div class="candidatures-container">
    <?php if (empty($project_candidatures)): ?>
        <div class="empty-state card card-elevated" style="text-align: center; padding: 60px 30px; background: white; border-radius: 16px;">
            <i class="fa-regular fa-folder-open" style="font-size: 48px; color: var(--gray-400); margin-bottom: 20px;"></i>
            <h2 style="color: var(--navy); margin-bottom: 10px;">Aucune candidature pour le moment</h2>
            <p style="color: var(--gray-600);">Vous n'avez reçu aucune demande pour vos projets actifs.</p>
        </div>
    <?php else: ?>
        <?php foreach ($project_candidatures as $pc): ?>
            <?php $p = $pc['project']; $candidates = $pc['candidates']; ?>
            <div class="project-candidature-block card card-elevated" style="background: white; border-radius: 16px; margin-bottom: 30px; overflow: hidden;">
                
                <div class="pc-header" style="background: var(--gray-50); padding: 20px 30px; border-bottom: 1px solid var(--gray-200); display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span style="font-size: 12px; font-weight: 700; color: var(--gray-500); text-transform: uppercase; letter-spacing: 1px;">Projet</span>
                        <h3 style="margin: 5px 0 0 0; color: var(--navy); font-size: 20px; font-weight: 800;">
                            <?php echo htmlspecialchars($p['nomprojet']); ?>
                        </h3>
                    </div>
                    <div style="background: var(--blue-light); color: var(--blue-dark); padding: 8px 16px; border-radius: 99px; font-weight: 700; font-size: 14px;">
                        <?php echo count($candidates); ?> Candidat(s)
                    </div>
                </div>

                <div class="pc-body" style="padding: 20px 30px;">
                    <div class="candidates-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px;">
                        <?php foreach ($candidates as $c): ?>
                            <div class="candidate-card" style="border: 1px solid var(--gray-200); border-radius: 12px; padding: 20px; transition: all 0.2s;" id="candidate-<?php echo $c['id']; ?>-<?php echo $p['id']; ?>">
                                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                                    <div class="avatar-placeholder" style="width: 50px; height: 50px; background: var(--grad-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 800; font-size: 20px;">
                                        <?php echo strtoupper(substr($c['nom'], 0, 1) . substr($c['prenom'] ?? '', 0, 1)); ?>
                                    </div>
                                    <div>
                                        <h4 style="margin: 0 0 5px 0; color: var(--navy); font-size: 16px; font-weight: 700;">
                                            <?php echo htmlspecialchars($c['nom'] . ' ' . ($c['prenom'] ?? '')); ?>
                                        </h4>
                                        <span style="font-size: 12px; color: var(--gray-500);"><i class="fa-regular fa-clock"></i> <?php echo date('d M Y', strtotime($c['created_at'])); ?></span>
                                    </div>
                                </div>

                                <?php if ($c['action'] === 'accepted'): ?>
                                    <div style="background: rgba(46, 204, 113, 0.1); color: var(--green-dark); padding: 12px; border-radius: 8px; text-align: center; font-weight: 700; font-size: 14px;">
                                        <i class="fa-solid fa-check"></i> Candidat Accepté
                                        <a href="index.php?controller=message&action=index" style="display: block; margin-top: 8px; color: var(--navy); text-decoration: underline;">Ouvrir la messagerie</a>
                                    </div>
                                <?php else: ?>
                                    <div class="action-buttons" style="display: grid; grid-template-columns: 1fr; gap: 10px;">
                                        <button onclick="acceptCandidate(<?php echo $c['id']; ?>, <?php echo $p['id']; ?>)" class="btn" style="background: var(--grad-primary); color: white; border: none; padding: 10px; border-radius: 8px; font-weight: 700; cursor: pointer;">
                                            <i class="fa-solid fa-check"></i> Accepter
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function acceptCandidate(candidateId, projetId) {
    if (!confirm("Êtes-vous sûr de vouloir accepter ce candidat ? Vous pourrez discuter avec lui.")) return;

    fetch('index.php?controller=matching&action=acceptCandidate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            candidate_id: candidateId,
            projet_id: projetId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI dynamically
            const card = document.getElementById(`candidate-${candidateId}-${projetId}`);
            if (card) {
                const actionDiv = card.querySelector('.action-buttons');
                if (actionDiv) {
                    actionDiv.outerHTML = `
                        <div style="background: rgba(46, 204, 113, 0.1); color: var(--green-dark); padding: 12px; border-radius: 8px; text-align: center; font-weight: 700; font-size: 14px;">
                            <i class="fa-solid fa-check"></i> Candidat Accepté
                            <a href="index.php?controller=message&action=index" style="display: block; margin-top: 8px; color: var(--navy); text-decoration: underline;">Ouvrir la messagerie</a>
                        </div>
                    `;
                }
            }
        } else {
            alert("Erreur: " + data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert("Erreur réseau");
    });
}
</script>
