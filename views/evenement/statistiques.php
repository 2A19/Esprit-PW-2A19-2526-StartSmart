<?php
$totalEvents = count($evenements);
?>

<div class="panel-section">
    <div class="panel-head" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
        <div>
            <h1 style="margin:0;font-size:1.5rem;font-weight:700;">Statistiques des événements</h1>
            <p style="margin:4px 0 0;color:var(--gray-500);font-size:.9rem;">
                Classement par nombre de participants (ordre décroissant)
            </p>
        </div>
        <a class="btn event-btn-secondary"
           href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?url=evenement/index">
            <i class="fa-solid fa-arrow-left"></i> Retour aux événements
        </a>
    </div>
</div>

<!-- Summary cards -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin:20px 0;">
    <div class="stat-card" style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:12px;padding:20px;text-align:center;">
        <div style="font-size:2rem;font-weight:800;color:#0369a1;"><?= $totalEvents; ?></div>
        <div style="font-size:.85rem;color:#0369a1;margin-top:4px;">Événements</div>
    </div>
    <div class="stat-card" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:20px;text-align:center;">
        <div style="font-size:2rem;font-weight:800;color:#15803d;"><?= $totalParticipants; ?></div>
        <div style="font-size:.85rem;color:#15803d;margin-top:4px;">Participants total</div>
    </div>
    <div class="stat-card" style="background:#fefce8;border:1px solid #fde68a;border-radius:12px;padding:20px;text-align:center;">
        <div style="font-size:2rem;font-weight:800;color:#a16207;">
            <?= $totalEvents > 0 ? round($totalParticipants / $totalEvents, 1) : 0; ?>
        </div>
        <div style="font-size:.85rem;color:#a16207;margin-top:4px;">Moy. par événement</div>
    </div>
    <div class="stat-card" style="background:#fdf4ff;border:1px solid #e9d5ff;border-radius:12px;padding:20px;text-align:center;">
        <div style="font-size:2rem;font-weight:800;color:#7c3aed;"><?= $maxCount; ?></div>
        <div style="font-size:.85rem;color:#7c3aed;margin-top:4px;">Record participants</div>
    </div>
</div>

<!-- Ranking chart -->
<?php if (empty($evenements)): ?>
    <p class="empty-state">Aucun événement enregistré pour le moment.</p>
<?php else: ?>
<div class="panel-section" style="margin-top:8px;">
    <div class="panel-head">
        <h2>Classement des événements</h2>
    </div>

    <div style="display:flex;flex-direction:column;gap:14px;margin-top:16px;">
        <?php foreach ($evenements as $rank => $evt):
            $evtId    = (int) ($evt['id'] ?? 0);
            $count    = (int) ($participantCountByEvent[$evtId] ?? 0);
            $cap      = (int) ($evt['capacite'] ?? 0);
            $pct      = ($maxCount > 0) ? round(($count / $maxCount) * 100) : 0;
            $fillPct  = ($cap > 0) ? min(100, round(($count / $cap) * 100)) : 0;
            $titre    = htmlspecialchars((string) ($evt['titre'] ?? ''), ENT_QUOTES, 'UTF-8');
            $lieu     = htmlspecialchars((string) ($evt['lieu'] ?? ''), ENT_QUOTES, 'UTF-8');
            $date     = htmlspecialchars((string) ($evt['date_evenement'] ?? ''), ENT_QUOTES, 'UTF-8');
            $statut   = htmlspecialchars((string) ($evt['statut'] ?? ''), ENT_QUOTES, 'UTF-8');

            $barColor = match(true) {
                $rank === 0 => '#f59e0b',
                $rank === 1 => '#94a3b8',
                $rank === 2 => '#b45309',
                default     => '#6366f1',
            };
            $medal = match($rank) {
                0 => '🥇',
                1 => '🥈',
                2 => '🥉',
                default => '#' . ($rank + 1),
            };
        ?>
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:18px 20px;display:flex;flex-direction:column;gap:8px;">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                <div style="display:flex;align-items:center;gap:12px;">
                    <span style="font-size:1.4rem;min-width:36px;text-align:center;"><?= $medal; ?></span>
                    <div>
                        <a href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?url=evenement/show&id=<?= $evtId; ?>"
                           style="font-weight:700;font-size:1rem;color:var(--primary);text-decoration:none;">
                            <?= $titre; ?>
                        </a>
                        <div style="font-size:.8rem;color:var(--gray-500);margin-top:2px;">
                            <i class="fa-regular fa-calendar" style="margin-right:4px;"></i><?= $date; ?>
                            &nbsp;·&nbsp;
                            <i class="fa-solid fa-location-dot" style="margin-right:4px;"></i><?= $lieu; ?>
                        </div>
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:10px;">
                    <span style="background:#f3f4f6;border-radius:20px;padding:4px 12px;font-size:.85rem;font-weight:600;">
                        <?= $statut; ?>
                    </span>
                    <span style="background:<?= $barColor; ?>;color:#fff;border-radius:20px;padding:4px 14px;font-size:.9rem;font-weight:700;">
                        <?= $count; ?> participant<?= $count !== 1 ? 's' : ''; ?>
                    </span>
                </div>
            </div>

            <!-- Bar relative to max -->
            <div style="margin-top:4px;">
                <div style="display:flex;justify-content:space-between;font-size:.75rem;color:var(--gray-500);margin-bottom:4px;">
                    <span>Part relative au record</span>
                    <span><?= $pct; ?>%</span>
                </div>
                <div style="background:#f3f4f6;border-radius:99px;height:10px;overflow:hidden;">
                    <div style="width:<?= $pct; ?>%;background:<?= $barColor; ?>;height:100%;border-radius:99px;transition:width .4s ease;"></div>
                </div>
            </div>

            <?php if ($cap > 0): ?>
            <!-- Capacity fill bar -->
            <div>
                <div style="display:flex;justify-content:space-between;font-size:.75rem;color:var(--gray-500);margin-bottom:4px;">
                    <span>Remplissage (<?= $count; ?>/<?= $cap; ?> places)</span>
                    <span><?= $fillPct; ?>%</span>
                </div>
                <div style="background:#f3f4f6;border-radius:99px;height:8px;overflow:hidden;">
                    <div style="width:<?= $fillPct; ?>%;background:<?= $fillPct >= 100 ? '#ef4444' : '#22c55e'; ?>;height:100%;border-radius:99px;"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
