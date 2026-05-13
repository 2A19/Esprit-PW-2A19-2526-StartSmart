<style>
    body { margin: 0; padding: 0; overflow: hidden; background-color: #000; }
    
    .map-container {
        position: relative;
        width: 100vw;
        height: calc(100vh - 60px);
        margin-left: calc(-50vw + 50%);
        background: radial-gradient(circle at center, #1a1a2e 0%, #0B1C48 100%);
    }

    #globeViz {
        width: 100%;
        height: 100%;
    }

    /* Map UI Overlay */
    .map-overlay {
        position: absolute;
        top: 20px;
        left: 20px;
        z-index: 10;
        background: rgba(11, 28, 72, 0.85);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 20px;
        color: white;
        width: 320px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        font-family: 'Roboto', sans-serif;
        max-height: 80vh;
        overflow-y: auto;
    }

    /* Custom Scrollbar for overlay */
    .map-overlay::-webkit-scrollbar { width: 6px; }
    .map-overlay::-webkit-scrollbar-track { background: rgba(0,0,0,0.2); border-radius: 10px; }
    .map-overlay::-webkit-scrollbar-thumb { background: rgba(52, 152, 219, 0.5); border-radius: 10px; }

    /* Overlay Minimizer */
    .map-overlay.minimized {
        height: 60px;
        overflow: hidden;
    }
    .minimize-btn {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(255,255,255,0.1);
        border: none;
        color: white;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }
    .minimize-btn:hover { background: rgba(255,255,255,0.2); }

    .map-overlay h2 {
        margin: 0 0 15px 0;
        font-size: 1.5em;
        background: linear-gradient(90deg, #3498db, #2ecc71);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        padding-right: 30px;
    }

    /* Mode Toggle */
    .mode-toggle {
        display: flex;
        background: rgba(0,0,0,0.3);
        border-radius: 8px;
        padding: 4px;
        margin-bottom: 20px;
    }
    .mode-btn {
        flex: 1;
        text-align: center;
        padding: 8px;
        color: #bdc3c7;
        font-weight: bold;
        cursor: pointer;
        border-radius: 6px;
        transition: all 0.3s;
        font-size: 0.9em;
    }
    .mode-btn.active {
        background: #3498db;
        color: white;
        box-shadow: 0 2px 8px rgba(52,152,219,0.4);
    }

    .filter-section {
        margin-bottom: 20px;
    }
    .filter-section h3 {
        font-size: 0.9em;
        text-transform: uppercase;
        color: #95a5a6;
        margin-bottom: 10px;
        letter-spacing: 1px;
    }
    .category-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .cat-btn {
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8em;
        cursor: pointer;
        transition: all 0.3s;
    }
    .cat-btn.active, .cat-btn:hover {
        background: var(--cat-color, #3498db);
        border-color: var(--cat-color, #3498db);
    }

    /* Side Panel Elements for Forum Mode */
    #forumSidePanel {
        display: none;
        margin-top: 20px;
        border-top: 1px solid rgba(255,255,255,0.1);
        padding-top: 20px;
    }
    
    .location-group { margin-bottom: 15px; }
    .location-group-title {
        font-weight: bold;
        color: #3498db;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        padding: 8px 10px;
        background: rgba(255,255,255,0.05);
        border-radius: 6px;
        transition: background 0.2s;
    }
    .location-group-title:hover { background: rgba(255,255,255,0.1); }
    .location-group-cities {
        margin-left: 15px;
        font-size: 0.9em;
        color: #bdc3c7;
        margin-top: 5px;
        display: none;
    }
    .location-group.expanded .location-group-cities { display: block; }
    .city-item {
        display: flex;
        justify-content: space-between;
        padding: 5px 0;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }

    .trending-post {
        background: rgba(255,255,255,0.05);
        border-left: 4px solid #e74c3c;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 12px;
        cursor: pointer;
        transition: background 0.2s, transform 0.2s;
    }
    .trending-post:hover { background: rgba(255,255,255,0.1); transform: translateX(5px); }
    .trending-post-title { font-weight: bold; font-size: 0.95em; margin-bottom: 8px; color: #fff; }
    .trending-post-meta { font-size: 0.8em; color: #bdc3c7; display: flex; justify-content: space-between; align-items: center; }
    .trending-post-score { background: rgba(231, 76, 60, 0.2); color: #ff7675; padding: 2px 6px; border-radius: 4px; font-weight: bold; }

    /* Custom tooltip for Globe.gl */
    .globe-tooltip {
        background: rgba(11, 28, 72, 0.95);
        border: 1px solid rgba(52, 152, 219, 0.5);
        backdrop-filter: blur(5px);
        color: white;
        padding: 15px;
        border-radius: 8px;
        font-family: 'Roboto', sans-serif;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        pointer-events: none;
        max-width: 280px;
    }
    .globe-tooltip .cat-badge {
        display: inline-block;
        font-size: 0.7em;
        text-transform: uppercase;
        padding: 3px 8px;
        border-radius: 12px;
        margin-bottom: 8px;
        font-weight: bold;
    }
    .globe-tooltip h4 { margin: 0 0 5px 0; font-size: 1.1em; }
    .globe-tooltip p { margin: 0 0 10px 0; font-size: 0.85em; color: #bdc3c7; line-height: 1.4; }
    .globe-tooltip .stats { font-size: 0.8em; color: #3498db; font-weight: bold; }
    .trending-badge {
        background: linear-gradient(90deg, #ff9a9e, #fecfef);
        color: #d63031;
        font-size: 0.7em;
        padding: 2px 6px;
        border-radius: 4px;
        margin-left: 5px;
        font-weight: bold;
    }
</style>

<script src="//unpkg.com/three"></script>
<script src="//unpkg.com/globe.gl"></script>

<div class="map-container">
    <div id="map-loading-overlay" style="position:absolute; inset:0; background:rgba(11,28,72,0.7); z-index:50; display:flex; flex-direction:column; align-items:center; justify-content:center; backdrop-filter:blur(5px); color:white;">
        <div class="skeleton" style="width:120px; height:120px; border-radius:50%; margin-bottom:20px; animation: pulse 1.5s infinite;"></div>
        <h3 style="margin:0; font-family:'Syne', sans-serif;">Chargement du Globe 3D...</h3>
    </div>

    <div class="map-overlay" id="mapOverlay">
        <button class="minimize-btn" onclick="document.getElementById('mapOverlay').classList.toggle('minimized')">−</button>
        <h2>Ecosystème Mondial</h2>
        
        <div class="mode-toggle">
            <div class="mode-btn active" id="btn-mode-startups" onclick="switchMode('startups')" style="display:flex; align-items:center; justify-content:center; gap:5px;"><i data-lucide="rocket" style="width:16px;height:16px;"></i> Startups</div>
            <div class="mode-btn" id="btn-mode-forum" onclick="switchMode('forum')" style="display:flex; align-items:center; justify-content:center; gap:5px;"><i data-lucide="message-square" style="width:16px;height:16px;"></i> Discussions</div>
        </div>

        <p style="font-size: 0.9em; color: #bdc3c7; margin-bottom: 20px;" id="mode-desc">Découvrez les startups et tendances à travers le monde.</p>
        
        <div class="filter-section">
            <h3>Filtrer par Secteur</h3>
            <div class="category-filters" id="categoryFilters">
                <button class="cat-btn active" data-cat="all" style="--cat-color: #3498db;">Tous</button>
                <button class="cat-btn" data-cat="AI" style="--cat-color: #3498db;">AI</button>
                <button class="cat-btn" data-cat="Fintech" style="--cat-color: #2ecc71;">Fintech</button>
                <button class="cat-btn" data-cat="Gaming" style="--cat-color: #9b59b6;">Gaming</button>
                <button class="cat-btn" data-cat="HealthTech" style="--cat-color: #e74c3c;">Health</button>
            </div>
        </div>

        <!-- Forum Specific Panel -->
        <div id="forumSidePanel">
            <div class="filter-section">
                <h3 style="display:flex; align-items:center; gap:5px;">Top Tendances <i data-lucide="flame" style="width:16px;height:16px;color:#e74c3c;"></i></h3>
                <div id="trendingPostsList">
                    <!-- Populated via JS -->
                </div>
            </div>

            <div class="filter-section">
                <h3 style="display:flex; align-items:center; gap:5px;">Discussions par Lieu <i data-lucide="map-pin" style="width:16px;height:16px;color:#3498db;"></i></h3>
                <div id="locationsList">
                    <!-- Populated via JS -->
                </div>
            </div>
        </div>

        <div style="margin-top: 20px; font-size: 0.8em; color: #7f8c8d;">
            <p>💡 Astuce: Cliquez et glissez pour tourner la planète. Molette pour zoomer.</p>
        </div>
    </div>

    <div id="globeViz"></div>
</div>

<script>
    let currentMode = 'startups'; // 'startups' or 'forum'
    let allData = [];

    const colorMap = {
        'AI': '#3498db',
        'Fintech': '#2ecc71',
        'Gaming': '#9b59b6',
        'HealthTech': '#e74c3c',
        'Health': '#e74c3c',
        'SaaS': '#f1c40f',
        'E-commerce': '#e67e22'
    };

    function getCategoryColor(category) {
        if (!category) return '#7f8c8d';
        for (let key in colorMap) {
            if (category.toLowerCase().includes(key.toLowerCase())) {
                return colorMap[key];
            }
        }
        return '#7f8c8d'; 
    }

    // Initialize Globe
    const globe = Globe()
        (document.getElementById('globeViz'))
        .globeImageUrl('//unpkg.com/three-globe/example/img/earth-night.jpg')
        .bumpImageUrl('//unpkg.com/three-globe/example/img/earth-topology.png')
        .backgroundImageUrl('//unpkg.com/three-globe/example/img/night-sky.png')
        .pointLat('lat')
        .pointLng('lng')
        .pointColor(d => getCategoryColor(d.category))
        .pointsMerge(false)
        .pointResolution(32)
        .onPointHover(point => {
            if (point) {
                globe.controls().autoRotate = false;
                document.body.style.cursor = 'pointer';
            } else {
                globe.controls().autoRotate = true;
                document.body.style.cursor = 'default';
            }
        })
        .onPointClick(d => {
            if (currentMode === 'startups') {
                window.location.href = `index.php?controller=projet&action=show&id=${d.id}`;
            } else {
                window.location.href = `index.php?controller=post&action=show&id=${d.id}`;
            }
        });

    // Ring configuration for pulsing animations (trending items)
    globe.ringLat('lat')
         .ringLng('lng')
         .ringColor(d => t => {
             const color = getCategoryColor(d.category);
             return `${color}${Math.round((1-t)*255).toString(16).padStart(2, '0')}`;
         })
         .ringPropagationSpeed(1)
         .ringRepeatPeriod(1000);

    function setGlobeConfigForMode() {
        const flameSvg = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:2px"><path d="M8.5 14.5A2.5 2.5 0 0011 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 11-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 002.5 2.5z"/></svg>';
        const pinSvg = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-right:2px"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>';
        const thumbsUpSvg = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-left:2px"><path d="M14 9V5a3 3 0 00-3-3l-4 9v11h11.28a2 2 0 002-1.7l1.38-9a2 2 0 00-2-2.3zM7 22H4a2 2 0 01-2-2v-7a2 2 0 012-2h3"/></svg>';
        const messageSvg = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle;margin-left:2px"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>';

        if (currentMode === 'startups') {
            globe.pointAltitude(d => (d.popularity || 0) * 0.01 + 0.01)
                 .pointRadius(d => d.isTrending ? 0.8 : 0.4)
                 .ringMaxRadius(d => (d.popularity || 0) * 0.5 + 2)
                 .pointLabel(d => `
                    <div class="globe-tooltip">
                        <span class="cat-badge" style="background: ${getCategoryColor(d.category)}; color: #fff;">${d.category}</span>
                        ${d.isTrending ? '<span class="trending-badge">' + flameSvg + ' TRENDING</span>' : ''}
                        <h4>${d.name}</h4>
                        <p>${d.city}, ${d.country}</p>
                        <div class="stats">Popularité: ${d.popularity || 0}</div>
                    </div>
                 `);
        } else {
            // Forum mode config
            globe.pointAltitude(d => (d.score || 0) * 0.01 + 0.02)
                 .pointRadius(d => (d.score && d.score > 10) ? 0.8 : 0.4)
                 .ringMaxRadius(d => (d.score || 0) * 0.3 + 1.5)
                 .pointLabel(d => `
                    <div class="globe-tooltip">
                        <span class="cat-badge" style="background: ${getCategoryColor(d.category)}; color: #fff;">Forum: ${d.category}</span>
                        ${(d.score && d.score > 10) ? '<span class="trending-badge">' + flameSvg + ' HOT</span>' : ''}
                        <h4>${d.title}</h4>
                        <p>${d.content}</p>
                        <p style="color:#bdc3c7; font-size: 0.8em; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 5px; display:flex; align-items:center;">${pinSvg} ${d.city}, ${d.country}</p>
                        <div class="stats" style="display:flex; align-items:center;">Score: ${d.score || 0} (${d.likes} ${thumbsUpSvg} | ${d.comments} ${messageSvg})</div>
                    </div>
                 `);
        }
    }

    function loadData() {
        // Show loading state
        const overlay = document.getElementById('map-loading-overlay');
        if (overlay) overlay.style.display = 'flex';

        const url = currentMode === 'startups' 
            ? 'index.php?controller=map&action=apiData' 
            : 'index.php?controller=forumApi&action=feed';
            
        fetch(url)
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    allData = response.data;
                    updateGlobe(allData);
                }
            })
            .catch(err => console.error('Error fetching map data:', err))
            .finally(() => {
                // Hide loading state after brief simulated delay for effect
                setTimeout(() => {
                    if (overlay) overlay.style.display = 'none';
                }, 500);
            });

        if (currentMode === 'forum') {
            loadForumSidePanel();
        }
    }

    function updateGlobe(data) {
        globe.pointsData(data);
        if (currentMode === 'startups') {
            globe.ringData(data.filter(d => d.isTrending));
        } else {
            // In forum mode, high score posts pulse
            globe.ringData(data.filter(d => d.score > 10));
        }
    }

    function switchMode(mode) {
        currentMode = mode;
        
        document.getElementById('btn-mode-startups').classList.remove('active');
        document.getElementById('btn-mode-forum').classList.remove('active');
        document.getElementById(`btn-mode-${mode}`).classList.add('active');

        if (mode === 'startups') {
            document.getElementById('mode-desc').innerText = "Découvrez les startups et tendances à travers le monde.";
            document.getElementById('forumSidePanel').style.display = 'none';
        } else {
            document.getElementById('mode-desc').innerText = "Explorez les discussions et requêtes par zone géographique.";
            document.getElementById('forumSidePanel').style.display = 'block';
        }

        // Reset filters
        document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
        document.querySelector('.cat-btn[data-cat="all"]').classList.add('active');

        setGlobeConfigForMode();
        loadData();
    }

    // --- Forum Side Panel Logic ---
    function loadForumSidePanel() {
        // Load Trending
        fetch('index.php?controller=forumApi&action=trending')
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    const container = document.getElementById('trendingPostsList');
                    container.innerHTML = '';
                    response.data.slice(0, 5).forEach(post => { // Show top 5
                        container.innerHTML += `
                            <div class="trending-post" onclick="globe.controls().autoRotate = false; globe.pointOfView({lat: ${post.lat}, lng: ${post.lng}, altitude: 0.5}, 1000)">
                                <div class="trending-post-title">${post.title}</div>
                                <div class="trending-post-meta">
                                    <span style="display:flex; align-items:center; gap:4px;"><i data-lucide="map-pin" style="width:12px;height:12px;"></i> ${post.city}</span>
                                    <span class="trending-post-score" style="display:flex; align-items:center; gap:4px;"><i data-lucide="flame" style="width:12px;height:12px;"></i> ${post.score} pts</span>
                                </div>
                            </div>
                        `;
                    });
                    if (window.lucide) lucide.createIcons();
                }
            });

        // Load Locations Grouping
        fetch('index.php?controller=forumApi&action=location')
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    const container = document.getElementById('locationsList');
                    container.innerHTML = '';
                    
                    const data = response.data;
                    for (const country in data) {
                        const countryData = data[country];
                        let citiesHtml = '';
                        for (const city in countryData.cities) {
                            citiesHtml += `
                                <div class="city-item">
                                    <span>${city}</span>
                                    <span style="background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 10px;">${countryData.cities[city]}</span>
                                </div>
                            `;
                        }

                        container.innerHTML += `
                            <div class="location-group">
                                <div class="location-group-title" onclick="this.parentElement.classList.toggle('expanded')">
                                    <span>${country}</span>
                                    <span>${countryData.count} posts ▾</span>
                                </div>
                                <div class="location-group-cities">
                                    ${citiesHtml}
                                </div>
                            </div>
                        `;
                    }
                    if (window.lucide) lucide.createIcons();
                }
            });
    }

    // Filtering
    const filterBtns = document.querySelectorAll('.cat-btn');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            filterBtns.forEach(b => b.classList.remove('active'));
            e.target.classList.add('active');

            const filterCat = e.target.getAttribute('data-cat');
            if (filterCat === 'all') {
                updateGlobe(allData);
            } else {
                const filtered = allData.filter(d => (d.category || '').toLowerCase().includes(filterCat.toLowerCase()));
                updateGlobe(filtered);
            }
        });
    });

    window.addEventListener('resize', () => {
        globe.width(window.innerWidth).height(window.innerHeight - 60);
    });
    
    globe.controls().autoRotate = true;
    globe.controls().autoRotateSpeed = 0.15;

    // Initial setup
    setGlobeConfigForMode();
    loadData();

    setTimeout(() => {
        globe.pointOfView({ altitude: 2 }, 2000);
    }, 1000);

</script>
