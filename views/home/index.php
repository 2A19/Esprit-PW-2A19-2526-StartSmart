<section class="hero-section">
    <div class="hero-overlay"></div>
    <div class="container hero-content">
        <span class="hero-kicker"><i class="fa-solid fa-rocket" style="margin-right: 8px;"></i> Plateforme #1 pour entrepreneurs innovants</span>
        <h1>CRÉEZ VOTRE STARTUP <br><span class="gradient-text">EN LIGNE</span></h1>
        <p>Transformez vos idées en projets concrets grâce à notre plateforme d'innovation et de financement collaboratif.</p>
        <div class="hero-actions">
            <a href="#" class="btn btn-primary">Commencer maintenant <i class="fa-solid fa-arrow-right" style="margin-left: 8px;"></i></a>
            <a href="#" class="btn btn-outline">En savoir plus</a>
            <a href="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8'); ?>/index.php?route=backoffice/index&page=events" class="btn btn-outline">Backoffice</a>
        </div>
    </div>
</section>

<section class="container cards-section">
    <article class="feature-card">
        <div class="f-icon f-green"><i class="fa-regular fa-lightbulb"></i></div>
        <h3>Idées</h3>
        <p>Capturez et structurez vos concepts avec un canevas startup intelligent et une analyse de marché prédictive.</p>
    </article>
    <article class="feature-card">
        <div class="f-icon f-blue"><i class="fa-solid fa-handshake-angle"></i></div>
        <h3>Collaboration</h3>
        <p>Travaillez avec votre équipe, vos mentors et des investisseurs dans un espace unique, sécurisé et innovant.</p>
    </article>
    <article class="feature-card">
        <div class="f-icon f-purple"><i class="fa-solid fa-coins"></i></div>
        <h3>Financement</h3>
        <p>Accédez au financement participatif et suivez vos objectifs de levée de fonds en temps réel, avec des métriques claires.</p>
    </article>
</section>

<div class="container dashboard-grid">
    <section class="panel-section">
        <div class="panel-head">
            <h2><i class="fa-solid fa-layer-group" style="color: #1374dd; margin-right: 8px;"></i> Mes projets actifs</h2>
            <a href="#" class="btn-sm">Voir tout</a>
        </div>
        <div class="project-list">
            <article class="project-item">
                <img src="https://images.unsplash.com/photo-1542744173-8e7e53415bb0?auto=format&fit=crop&w=400&q=80" class="p-img" alt="EcoTech">
                <div class="p-body">
                    <div class="p-top">
                        <h4>EcoTech Solutions</h4>
                        <span class="badge badge-green">Environnement</span>
                    </div>
                    <div class="progress-box">
                        <div class="p-stats">
                            <span>Progression</span>
                            <span>75%</span>
                        </div>
                        <div class="progress-bar"><i style="width:75%; background: linear-gradient(90deg, #18d0ae, #1688f1);"></i></div>
                        <div class="p-amounts">
                            <small><strong>45 000 €</strong> levés</small>
                            <small>Objectif: 60 000 €</small>
                        </div>
                    </div>
                </div>
            </article>
            <article class="project-item">
                <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=400&q=80" class="p-img" alt="HealthApp">
                <div class="p-body">
                    <div class="p-top">
                        <h4>HealthApp Mobile</h4>
                        <span class="badge badge-blue">Santé</span>
                    </div>
                    <div class="progress-box">
                        <div class="p-stats">
                            <span>Progression</span>
                            <span>60%</span>
                        </div>
                        <div class="progress-bar"><i style="width:60%; background: linear-gradient(90deg, #1688f1, #8b5cf6);"></i></div>
                        <div class="p-amounts">
                            <small><strong>30 000 €</strong> levés</small>
                            <small>Objectif: 50 000 €</small>
                        </div>
                    </div>
                </div>
            </article>
            <article class="project-item">
                <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=400&q=80" class="p-img" alt="EdTech">
                <div class="p-body">
                    <div class="p-top">
                        <h4>EdTech Platform</h4>
                        <span class="badge badge-purple">Éducation</span>
                    </div>
                    <div class="progress-box">
                        <div class="p-stats">
                            <span>Progression</span>
                            <span>72%</span>
                        </div>
                        <div class="progress-bar"><i style="width:72%; background: linear-gradient(90deg, #8b5cf6, #18d0ae);"></i></div>
                        <div class="p-amounts">
                            <small><strong>52 000 €</strong> levés</small>
                            <small>Objectif: 72 000 €</small>
                        </div>
                    </div>
                </div>
            </article>
        </div>
    </section>

    <section class="panel-section activity-panel">
        <div class="panel-head">
            <h2><i class="fa-solid fa-chart-line" style="color: #1374dd; margin-right: 8px;"></i> Activité récente</h2>
        </div>
        <ul class="activity-list">
            <li>
                <div class="act-icon act-green"><i class="fa-solid fa-circle-check"></i></div>
                <div class="act-body">
                    <strong>Milestone atteint</strong>
                    <p>EdTech Platform a complété 90% de son objectif initial.</p>
                    <time><i class="fa-regular fa-clock"></i> Il y a 1 jour</time>
                </div>
            </li>
            <li>
                <div class="act-icon act-blue"><i class="fa-regular fa-comment-dots"></i></div>
                <div class="act-body">
                    <strong>Nouveau commentaire</strong>
                    <p>EcoTech Solutions a reçu 12 nouveaux commentaires des investisseurs.</p>
                    <time><i class="fa-regular fa-clock"></i> Il y a 2 jours</time>
                </div>
            </li>
            <li>
                <div class="act-icon act-purple"><i class="fa-solid fa-user-plus"></i></div>
                <div class="act-body">
                    <strong>Nouveau contributeur</strong>
                    <p>HealthApp Mobile accueille un nouvel investisseur stratégique.</p>
                    <time><i class="fa-regular fa-clock"></i> Il y a 3 jours</time>
                </div>
            </li>
        </ul>
    </section>
</div>
