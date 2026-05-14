<style>
/* ── RESTORED CLASSIC HERO STYLES ── */
.hero {
    min-height: calc(100vh - 72px);
    display: flex; align-items: center; position: relative; overflow: hidden; padding: 60px 5% 80px;
}
.hero-bg { position: absolute; inset: 0; background: linear-gradient(145deg, var(--gray-50) 0%, white 50%, rgba(179,229,252,0.15) 100%); z-index: 0; }
.hero-blob { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.45; z-index: 0; animation: blobFloat 8s ease-in-out infinite alternate; }
.hero-blob-1 { width: 600px; height: 600px; background: rgba(79,195,247,0.25); top: -200px; right: -150px; }
.hero-blob-2 { width: 400px; height: 400px; background: rgba(46,204,113,0.20); bottom: -100px; right: 20%; animation-delay: -3s; }
.hero-blob-3 { width: 300px; height: 300px; background: rgba(2,136,209,0.15); top: 30%; left: -80px; animation-delay: -6s; }

@keyframes blobFloat {
    0% { transform: translate(0,0) scale(1); }
    100% { transform: translate(30px, -20px) scale(1.05); }
}

.hero-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; max-width: 1200px; margin: 0 auto; width: 100%; position: relative; z-index: 1; }
.hero-eyebrow { display: inline-flex; align-items: center; gap: 8px; background: rgba(79,195,247,0.12); border: 1px solid rgba(79,195,247,0.35); border-radius: 99px; padding: 5px 14px 5px 8px; font-size: 12px; font-weight: 600; color: var(--blue-dark); margin-bottom: 24px; width: fit-content; }
.hero-eyebrow .dot { width: 8px; height: 8px; background: var(--green); border-radius: 50%; animation: pulse 2s infinite; }
@keyframes pulse { 0%, 100% { opacity: 1; transform: scale(1); } 50% { opacity: 0.6; transform: scale(1.3); } }

.hero-headline { font-family: var(--font-display); font-weight: 800; font-size: clamp(40px, 5vw, 66px); line-height: 1.05; letter-spacing: -2px; color: var(--navy); margin-bottom: 20px; }
.hero-headline .line-accent { background: linear-gradient(90deg, var(--blue-dark) 0%, var(--blue) 50%, var(--green) 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.hero-sub { font-size: 17px; font-weight: 300; color: var(--gray-600); line-height: 1.7; margin-bottom: 36px; max-width: 460px; }

.hero-actions { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 40px; }
.hero-actions .btn-primary, .hero-actions .btn-secondary { padding: 13px 26px; font-size: 15px; }
.hero-stats { display: flex; gap: 28px; flex-wrap: wrap; }
.hero-stat { display: flex; flex-direction: column; }
.hero-stat .number { font-family: var(--font-display); font-weight: 700; font-size: 26px; color: var(--navy); letter-spacing: -1px; }
.hero-stat .label { font-size: 12px; color: var(--gray-400); font-weight: 400; }
.hero-stat-divider { width: 1px; background: var(--gray-200); margin: 4px 0; }

.hero-visual { position: relative; height: 480px; }
.hero-card-main { position: absolute; right: 0; top: 40px; width: 320px; background: white; border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-lg); border: 1px solid var(--gray-200); animation: cardFloat 6s ease-in-out infinite; }
@keyframes cardFloat { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

.card-tag { display: inline-flex; align-items: center; gap: 6px; background: rgba(46,204,113,0.12); color: var(--green-dark); font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 99px; margin-bottom: 14px; }
.card-project-title { font-family: var(--font-display); font-size: 16px; font-weight: 700; color: var(--navy); margin-bottom: 6px; }
.card-project-desc { font-size: 13px; color: var(--gray-600); line-height: 1.5; margin-bottom: 16px; }
.card-progress-label { display: flex; justify-content: space-between; font-size: 12px; color: var(--gray-600); margin-bottom: 6px; }
.progress-bar { height: 6px; background: var(--gray-100); border-radius: 99px; overflow: hidden; margin-bottom: 16px; }
.progress-fill { height: 100%; background: linear-gradient(90deg, var(--blue) 0%, var(--green) 100%); border-radius: 99px; transition: width 1s ease; }
.card-avatars { display: flex; align-items: center; gap: 8px; }
.avatar-stack { display: flex; }
.avatar-stack .av { width: 28px; height: 28px; border-radius: 50%; border: 2px solid white; font-size: 11px; font-weight: 600; display: flex; align-items: center; justify-content: center; margin-left: -8px; }
.avatar-stack .av:first-child { margin-left: 0; }
.av-1 { background: #BFDBFE; color: #1D4ED8; } .av-2 { background: #BBF7D0; color: #15803D; } .av-3 { background: #FDE68A; color: #92400E; }
.card-team-label { font-size: 12px; color: var(--gray-600); }

.hero-card-secondary { position: absolute; left: 0; bottom: 40px; width: 230px; background: white; border-radius: var(--radius-md); padding: 18px; box-shadow: var(--shadow-md); border: 1px solid var(--gray-200); animation: cardFloat 6s ease-in-out infinite reverse; animation-delay: -2s; }
.mini-stat-row { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
.mini-stat-row:last-child { margin-bottom: 0; }
.mini-icon { width: 32px; height: 32px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
.mini-icon.blue { background: rgba(79,195,247,0.15); color: var(--blue-dark); }
.mini-icon.green { background: rgba(46,204,113,0.15); color: var(--green-dark); }
.mini-stat-info { display: flex; flex-direction: column; }
.mini-stat-val { font-family: var(--font-display); font-size: 16px; font-weight: 700; color: var(--navy); }
.mini-stat-name { font-size: 11px; color: var(--gray-400); }

.hero-badge-match { position: absolute; right: 90px; bottom: 100px; background: var(--navy); color: white; border-radius: var(--radius-md); padding: 12px 16px; font-size: 13px; font-weight: 600; box-shadow: var(--shadow-md); display: flex; align-items: center; gap: 8px; animation: cardFloat 6s ease-in-out infinite; animation-delay: -4s; }
.match-circle { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--blue), var(--green)); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; }

/* ── RESTORED FEATURES STYLES ── */
section { padding: 100px 5%; }
.section-eyebrow { display: inline-flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: var(--blue-dark); margin-bottom: 14px; }
.section-eyebrow::before { content: ''; display: block; width: 20px; height: 2px; background: linear-gradient(90deg, var(--blue), var(--green)); border-radius: 99px; }
.section-title { font-family: var(--font-display); font-weight: 800; font-size: clamp(28px, 4vw, 46px); line-height: 1.1; letter-spacing: -1.5px; color: var(--navy); margin-bottom: 16px; }
.section-title span { background: linear-gradient(90deg, var(--blue-dark), var(--green)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.section-sub { font-size: 16px; color: var(--gray-600); max-width: 520px; line-height: 1.7; margin: 0 auto; }
.section-head { margin-bottom: 60px; text-align: center; }

.features { background: var(--gray-50); }
.features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 40px; }
.feature-card { background: white; border-radius: var(--radius-lg); padding: 32px 28px; border: 1px solid var(--gray-200); transition: all var(--transition); cursor: default; position: relative; overflow: hidden; }
.feature-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, var(--blue), var(--green)); transform: scaleX(0); transform-origin: left; transition: transform var(--transition); }
.feature-card:hover { transform: translateY(-6px); box-shadow: var(--shadow-lg); border-color: rgba(79,195,247,0.3); }
.feature-card:hover::before { transform: scaleX(1); }
.feature-icon-wrap { width: 52px; height: 52px; border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; font-size: 22px; margin-bottom: 20px; transition: all var(--transition); }
.feature-card:hover .feature-icon-wrap { transform: scale(1.1) rotate(-5deg); }
.fi-blue { background: rgba(79,195,247,0.12); color: var(--blue-dark); }
.fi-green { background: rgba(46,204,113,0.12); color: var(--green-dark); }
.fi-navy { background: rgba(11,28,72,0.08); color: var(--navy); }
.feature-title { font-family: var(--font-display); font-size: 18px; font-weight: 700; color: var(--navy); margin-bottom: 10px; letter-spacing: -0.3px; }
.feature-desc { font-size: 14px; color: var(--gray-600); line-height: 1.65; margin-bottom: 20px; }
.feature-link { font-size: 13px; font-weight: 600; color: var(--blue-dark); text-decoration: none; display: inline-flex; align-items: center; gap: 5px; transition: gap var(--transition); }
.feature-link:hover { gap: 9px; }

/* ── DEDICATED GLOBE SECTION STYLES ── */
.globe-wrapper {
    padding: 20px 5% 80px;
    background: var(--gray-50);
}

.globe-section {
    position: relative;
    width: 100%;
    max-width: 1280px;
    height: 650px;
    margin: 0 auto;
    border-radius: var(--radius-xl);
    background: radial-gradient(circle at center, #1a1a2e 0%, #0B1C48 100%);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 30px 60px rgba(11,28,72,0.15);
}

#heroGlobeViz { width: 100%; height: 100%; position: absolute; inset: 0; z-index: 0; }

.globe-header {
    position: relative;
    z-index: 10;
    text-align: center;
    padding-top: 40px;
    pointer-events: none;
}

.globe-title {
    font-family: var(--font-display);
    font-size: 36px;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 8px;
    color: white; /* Solid white, less 'AI' gradient */
}

.globe-sub {
    font-size: 15px;
    color: rgba(255,255,255,0.7);
    margin-bottom: 0;
    font-weight: 400;
}

/* Floating UI Overlay */
.globe-overlay-ui {
    position: absolute;
    top: 30px;
    left: 30px;
    z-index: 10;
    width: 320px;
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: var(--radius-md);
    padding: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    color: white;
}

.global-search-input {
    width: 100%;
    background: rgba(0,0,0,0.3);
    border: 1px solid rgba(255,255,255,0.2);
    border-radius: var(--radius-sm);
    padding: 12px 14px 12px 36px;
    color: white;
    font-size: 13px;
    transition: all 0.3s;
    margin-bottom: 16px;
}
.global-search-input:focus { outline: none; border-color: var(--blue); background: rgba(0,0,0,0.4); }

.floating-filters { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
.filter-pill {
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.1);
    color: white; padding: 5px 12px; border-radius: 99px;
    font-size: 11px; font-weight: 600; cursor: pointer; transition: all 0.2s;
}
.filter-pill:hover, .filter-pill.active { background: var(--blue); border-color: var(--blue); }

.btn-reset {
    width: 100%; padding: 8px; background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2); color: white;
    border-radius: var(--radius-sm); cursor: pointer; font-size: 12px; font-weight: 600;
    transition: all 0.3s;
}
.btn-reset:hover { background: rgba(255,255,255,0.2); }

/* Instructions Overlay */
.globe-instructions {
    position: absolute;
    bottom: 20px; left: 50%; transform: translateX(-50%);
    z-index: 10;
    display: flex; gap: 20px;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(10px);
    padding: 10px 20px;
    border-radius: 99px;
    border: 1px solid rgba(255,255,255,0.1);
}
.instruction-item {
    color: rgba(255,255,255,0.9); font-size: 12px; font-weight: 500;
    display: flex; align-items: center; gap: 6px;
}

/* Floating Detail Card */
.globe-detail-card {
    position: absolute;
    top: 30px; right: 30px;
    z-index: 20;
    width: 320px;
    background: rgba(11,28,72,0.9);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(79,195,247,0.3);
    border-radius: var(--radius-md);
    padding: 24px;
    box-shadow: 0 20px 50px rgba(0,0,0,0.5);
    color: white;
    transform: translateX(120%);
    opacity: 0;
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.globe-detail-card.active { transform: translateX(0); opacity: 1; }
.btn-close-card {
    position: absolute; top: 12px; right: 12px;
    background: none; border: none; color: rgba(255,255,255,0.6);
    font-size: 18px; cursor: pointer; transition: color 0.2s;
}
.btn-close-card:hover { color: white; }

/* ── RECENT DISCUSSIONS SECTION ── */
.forum-preview { padding: 100px 5%; background: var(--gray-50); }
.forum-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px; border-bottom: 1px solid var(--gray-200); padding-bottom: 20px; }
.forum-header-left h2 { font-family: var(--font-display); font-size: 36px; font-weight: 800; color: var(--navy); margin: 0; }
.forum-header-left h2 span { color: var(--green); }
.forum-grid { display: grid; grid-template-columns: 1fr 320px; gap: 30px; }
.forum-post-card { background: white; border-radius: var(--radius-lg); padding: 24px; border: 1px solid var(--gray-200); margin-bottom: 16px; transition: transform 0.2s, box-shadow 0.2s; cursor: pointer; display: flex; gap: 20px; }
.forum-post-card:hover { transform: translateY(-3px); box-shadow: var(--shadow-md); border-color: var(--blue); }
.fp-votes { display: flex; flex-direction: column; align-items: center; gap: 5px; color: var(--gray-600); }
.fp-votes .vote-btn { background: var(--gray-50); border: none; border-radius: 4px; padding: 4px 8px; cursor: pointer; transition: background 0.2s; }
.fp-votes .vote-btn:hover { background: rgba(52, 152, 219, 0.1); color: var(--blue); }
.fp-content { flex-grow: 1; }
.fp-tags { display: flex; gap: 8px; margin-bottom: 10px; }
.fp-tag { font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 99px; text-transform: uppercase; }
.fp-tag.tag-ia { background: #FCE4EC; color: #C2185B; }
.fp-tag.tag-idee { background: #E1F5FE; color: #0288D1; }
.fp-tag.tag-fin { background: #FFF8E1; color: #F57F17; }
.fp-tag.tag-col { background: #E8F5E9; color: #388E3C; }
.fp-title { font-size: 18px; font-weight: 700; color: var(--navy); margin-bottom: 8px; font-family: var(--font-display); }
.fp-desc { font-size: 14px; color: var(--gray-600); line-height: 1.5; margin-bottom: 16px; }
.fp-meta { display: flex; align-items: center; gap: 16px; font-size: 12px; color: var(--gray-500); }
.fp-meta i { margin-right: 4px; }
.trending-box { background: white; border-radius: var(--radius-lg); padding: 24px; border: 1px solid var(--gray-200); }
.trending-title { font-size: 14px; font-weight: 700; color: var(--navy); margin-bottom: 20px; display: flex; align-items: center; gap: 8px; }
.trending-item { display: flex; gap: 12px; margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px dashed var(--gray-200); cursor: pointer; }
.trending-item:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
.t-rank { font-family: var(--font-display); font-size: 18px; font-weight: 800; color: var(--gray-300); }
.t-info { flex-grow: 1; }
.t-title { font-size: 13px; font-weight: 600; color: var(--navy); margin-bottom: 4px; transition: color 0.2s; }
.trending-item:hover .t-title { color: var(--blue); }
.t-stats { font-size: 11px; color: var(--gray-500); }

/* ── DASHBOARD PREVIEW SECTION ── */
.dash-preview { padding: 100px 5%; background: var(--navy); color: white; position: relative; overflow: hidden; }
.dash-preview-content { position: relative; z-index: 2; max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; align-items: center; }
.dash-eyebrow { font-size: 12px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: var(--blue); margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.dash-eyebrow::before { content: ''; width: 20px; height: 2px; background: var(--blue); }
.dash-title { font-family: var(--font-display); font-size: 42px; font-weight: 800; margin-bottom: 16px; text-align: center; }
.dash-title span { color: var(--blue); }
.dash-sub { font-size: 16px; color: rgba(255,255,255,0.7); max-width: 500px; text-align: center; margin-bottom: 50px; line-height: 1.6; }
.dash-mockup { width: 100%; max-width: 900px; background: #1a2235; border-radius: 16px 16px 0 0; border: 1px solid rgba(255,255,255,0.1); border-bottom: none; box-shadow: 0 30px 60px rgba(0,0,0,0.5); display: flex; overflow: hidden; }
.dm-sidebar { width: 240px; background: #131929; padding: 24px 16px; border-right: 1px solid rgba(255,255,255,0.05); }
.dm-logo { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 16px; margin-bottom: 40px; padding: 0 10px; }
.dm-logo .icon { background: var(--blue); color: white; padding: 4px 6px; border-radius: 6px; font-size: 12px; }
.dm-nav { display: flex; flex-direction: column; gap: 8px; }
.dm-nav-item { padding: 12px 16px; border-radius: 8px; font-size: 13px; font-weight: 500; color: rgba(255,255,255,0.6); display: flex; align-items: center; gap: 12px; cursor: pointer; transition: all 0.2s; }
.dm-nav-item:hover, .dm-nav-item.active { background: rgba(52, 152, 219, 0.15); color: white; }
.dm-nav-item i { width: 16px; }
.dm-main { flex-grow: 1; padding: 32px; }
.dm-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 32px; }
.dm-stat-box { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; padding: 20px; }
.dm-stat-label { font-size: 11px; color: rgba(255,255,255,0.5); text-transform: uppercase; margin-bottom: 12px; font-weight: 600; }
.dm-stat-val { font-size: 28px; font-weight: 700; font-family: var(--font-display); margin-bottom: 8px; }
.dm-stat-trend { font-size: 12px; color: var(--green); display: flex; align-items: center; gap: 4px; }
.dm-list-title { font-size: 12px; color: rgba(255,255,255,0.5); text-transform: uppercase; margin-bottom: 16px; font-weight: 600; }
.dm-list-item { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; padding: 16px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; cursor: pointer; transition: background 0.2s; }
.dm-list-item:hover { background: rgba(255,255,255,0.06); }
.dm-li-left { display: flex; align-items: center; gap: 16px; }
.dm-li-icon { width: 40px; height: 40px; border-radius: 8px; background: rgba(231, 76, 60, 0.1); color: #e74c3c; display: flex; align-items: center; justify-content: center; font-size: 16px; }
.dm-li-icon.blue { background: rgba(52, 152, 219, 0.1); color: var(--blue); }
.dm-li-info h4 { margin: 0 0 4px 0; font-size: 14px; font-weight: 600; }
.dm-li-info p { margin: 0; font-size: 12px; color: rgba(255,255,255,0.5); }
.dm-li-badge { background: rgba(46, 204, 113, 0.15); color: var(--green); padding: 4px 10px; border-radius: 99px; font-size: 10px; font-weight: 700; letter-spacing: 0.5px; }
.dm-li-badge.revue { background: rgba(241, 196, 15, 0.15); color: #f1c40f; }

/* ── CALL TO ACTION SECTION ── */
.cta-section { padding: 100px 5%; background: linear-gradient(135deg, #0B1C48 0%, #17367B 50%, #1e8449 100%); text-align: center; color: white; }
.cta-content { max-width: 800px; margin: 0 auto; }
.cta-eyebrow { font-size: 12px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: rgba(255,255,255,0.8); margin-bottom: 20px; display: inline-flex; align-items: center; gap: 12px; }
.cta-eyebrow::before, .cta-eyebrow::after { content: ''; width: 30px; height: 1px; background: rgba(255,255,255,0.3); }
.cta-title { font-family: var(--font-display); font-size: clamp(32px, 5vw, 56px); font-weight: 800; line-height: 1.1; margin-bottom: 24px; letter-spacing: -1px; }
.cta-sub { font-size: 18px; color: rgba(255,255,255,0.8); margin-bottom: 40px; font-weight: 300; line-height: 1.6; }
.cta-actions { display: flex; justify-content: center; gap: 16px; flex-wrap: wrap; }
.cta-btn-light { background: white; color: var(--navy); padding: 14px 28px; border-radius: var(--radius-md); font-weight: 700; font-size: 15px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: transform 0.2s, box-shadow 0.2s; }
.cta-btn-light:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0,0,0,0.2); color: var(--blue-dark); }
.cta-btn-outline { background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.3); padding: 14px 28px; border-radius: var(--radius-md); font-weight: 600; font-size: 15px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; backdrop-filter: blur(5px); }
.cta-btn-outline:hover { background: rgba(255,255,255,0.2); border-color: rgba(255,255,255,0.5); transform: translateY(-2px); }

/* ── RH SECTION ── */
.rh-home-section {
    padding: 100px 5%;
    background: white;
}
.rh-home-wrap {
    display: grid;
    grid-template-columns: minmax(0, 0.9fr) minmax(360px, 1.1fr);
    gap: 56px;
    align-items: center;
    max-width: 1200px;
    margin: 0 auto;
}
.rh-home-copy {
    max-width: 520px;
}
.rh-home-copy .section-eyebrow {
    margin-bottom: 16px;
}
.rh-home-copy .section-title {
    margin-bottom: 18px;
}
.rh-home-copy p {
    color: var(--gray-600);
    font-size: 16px;
    line-height: 1.75;
    margin-bottom: 28px;
}
.rh-home-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
}
.rh-home-panel {
    background: var(--gray-50);
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-lg);
    padding: 24px;
    box-shadow: var(--shadow-lg);
}
.rh-home-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 22px;
}
.rh-home-toolbar strong {
    font-family: var(--font-display);
    color: var(--navy);
    font-size: 19px;
}
.rh-home-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border-radius: 99px;
    background: var(--green-bg);
    color: var(--green-dark);
    font-size: 12px;
    font-weight: 800;
}
.rh-job-list {
    display: grid;
    gap: 12px;
}
.rh-job-card {
    display: grid;
    grid-template-columns: 44px minmax(0, 1fr) auto;
    align-items: center;
    gap: 14px;
    padding: 16px;
    background: white;
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-md);
}
.rh-job-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: var(--radius-sm);
    background: var(--blue-bg);
    color: var(--blue-dark);
}
.rh-job-main strong {
    display: block;
    color: var(--navy);
    font-family: var(--font-display);
    font-size: 15px;
    margin-bottom: 3px;
}
.rh-job-main span {
    color: var(--gray-500);
    font-size: 13px;
}
.rh-job-meta {
    color: var(--blue-dark);
    font-size: 12px;
    font-weight: 800;
    white-space: nowrap;
}
.rh-home-metrics {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-top: 16px;
}
.rh-home-metric {
    padding: 16px;
    background: white;
    border: 1px solid var(--gray-200);
    border-radius: var(--radius-md);
}
.rh-home-metric strong {
    display: block;
    color: var(--navy);
    font-family: var(--font-display);
    font-size: 22px;
    line-height: 1;
    margin-bottom: 6px;
}
.rh-home-metric span {
    color: var(--gray-500);
    font-size: 12px;
}

@media (max-width: 900px) {
    .rh-home-wrap {
        grid-template-columns: 1fr;
        gap: 36px;
    }
}

@media (max-width: 640px) {
    .rh-home-section {
        padding: 70px 4%;
    }
    .rh-job-card {
        grid-template-columns: 40px minmax(0, 1fr);
    }
    .rh-job-meta {
        grid-column: 2;
    }
    .rh-home-metrics {
        grid-template-columns: 1fr;
    }
}

</style>

<!-- ==============================================
     1. HERO SECTION (Classic Restored)
=============================================== -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-blob hero-blob-1"></div>
    <div class="hero-blob hero-blob-2"></div>
    <div class="hero-blob hero-blob-3"></div>

    <div class="hero-grid">
        <div class="hero-content">
            <div class="hero-eyebrow">
                <div class="dot"></div>
                Plateforme n°1 des étudiants entrepreneurs
            </div>
            <h1 class="hero-headline">
                Transformez vos <br>
                <span class="line-accent">Idées en Startups</span>
            </h1>
            <p class="hero-sub">
                StartSmart connecte les jeunes talents avec des co-fondateurs, des mentors et des financements pour lancer les projets de demain.
            </p>
            <div class="hero-actions">
                <a href="index.php?controller=projet&action=index" class="btn-primary">
                    Découvrir les projets <i class="fa-solid fa-arrow-right"></i>
                </a>
                <a href="index.php?controller=post&action=index" class="btn-secondary">
                    <i class="fa-solid fa-users"></i> Rejoindre la communauté
                </a>
            </div>
            
            <div class="hero-stats">
                <div class="hero-stat">
                    <span class="number">12k+</span>
                    <span class="label">Projets créés</span>
                </div>
                <div class="hero-stat-divider"></div>
                <div class="hero-stat">
                    <span class="number">450+</span>
                    <span class="label">Startups lancées</span>
                </div>
                <div class="hero-stat-divider"></div>
                <div class="hero-stat">
                    <span class="number">2.4M€</span>
                    <span class="label">Fonds levés</span>
                </div>
            </div>
        </div>

        <div class="hero-visual">
            <div class="hero-card-main">
                <div class="card-tag"><i class="fa-solid fa-leaf"></i> CleanTech</div>
                <h3 class="card-project-title">EcoDelivery AI</h3>
                <p class="card-project-desc">Optimisation des tournées de livraison urbaine via machine learning.</p>
                <div class="card-progress-label"><span>Financement</span> <span>85%</span></div>
                <div class="progress-bar"><div class="progress-fill" style="width: 85%;"></div></div>
                <div class="card-avatars">
                    <div class="avatar-stack">
                        <div class="av av-1">AJ</div>
                        <div class="av av-2">MR</div>
                        <div class="av av-3">SK</div>
                    </div>
                    <span class="card-team-label">+12 investisseurs</span>
                </div>
            </div>
            
            <div class="hero-card-secondary">
                <div class="mini-stat-row">
                    <div class="mini-icon blue"><i class="fa-solid fa-chart-line"></i></div>
                    <div class="mini-stat-info">
                        <span class="mini-stat-val">+142%</span>
                        <span class="mini-stat-name">Traction mensuelle</span>
                    </div>
                </div>
                <div class="mini-stat-row">
                    <div class="mini-icon green"><i class="fa-solid fa-users"></i></div>
                    <div class="mini-stat-info">
                        <span class="mini-stat-val">3 nouveaux</span>
                        <span class="mini-stat-name">Membres d'équipe</span>
                    </div>
                </div>
            </div>
            
            <div class="hero-badge-match">
                <div class="match-circle">98%</div>
                Match parfait trouvé
            </div>
        </div>
    </div>
</section>

<!-- ==============================================
     2. FEATURES SECTION (Classic Restored)
=============================================== -->
<section class="features" id="features">
    <div class="container">
        <div class="section-head text-center" style="text-align: center;">
            <div class="section-eyebrow" style="justify-content: center;">Pourquoi StartSmart ?</div>
            <h2 class="section-title">Tout ce qu'il faut pour <span>réussir</span></h2>
            <p class="section-sub" style="margin: 0 auto;">De l'idéation à la levée de fonds, notre plateforme centralise tous les outils nécessaires pour structurer votre startup étudiante.</p>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon-wrap fi-blue"><i class="fa-solid fa-lightbulb"></i></div>
                <h3 class="feature-title">Gestion de Projets</h3>
                <p class="feature-desc">Structurez vos idées, définissez vos jalons et collaborez en temps réel avec votre équipe sur un tableau de bord intuitif.</p>
                <a href="#" class="feature-link">Explorer l'outil <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="feature-card">
                <div class="feature-icon-wrap fi-green"><i class="fa-solid fa-handshake-angle"></i></div>
                <h3 class="feature-title">Matchmaking IA</h3>
                <p class="feature-desc">Trouvez le co-fondateur idéal grâce à notre algorithme analysant vos compétences, valeurs et disponibilités.</p>
                <a href="#" class="feature-link">Trouver un profil <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div class="feature-card">
                <div class="feature-icon-wrap fi-navy"><i class="fa-solid fa-comments"></i></div>
                <h3 class="feature-title">Forum Communautaire</h3>
                <p class="feature-desc">Échangez avec des experts, posez vos questions techniques et obtenez des retours qualifiés sur vos pitchs.</p>
                <a href="#" class="feature-link">Rejoindre la discussion <i class="fa-solid fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>

<!-- ==============================================
     3. RH SECTION
=============================================== -->
<section class="rh-home-section" id="rh-home">
    <div class="rh-home-wrap">
        <div class="rh-home-copy">
            <div class="section-eyebrow">STARTSMART RH</div>
            <h2 class="section-title">Recrutez et postulez <span>dans le meme ecosysteme</span></h2>
            <p>
                L'espace RH relie les startups aux talents de la plateforme. Publiez des offres,
                suivez les candidatures et gardez un profil pret pour les equipes qui recrutent.
            </p>
            <div class="rh-home-actions">
                <a href="rh.php?page=job-offer/index&action=index" class="btn-primary">
                    Voir les offres <i class="fa-solid fa-arrow-right"></i>
                </a>
                <a href="rh.php?page=application/myApplications&action=myApplications" class="btn-secondary">
                    <i class="fa-regular fa-file-lines"></i> Mes candidatures
                </a>
            </div>
        </div>

        <div class="rh-home-panel">
            <div class="rh-home-toolbar">
                <strong>Opportunites actives</strong>
                <span class="rh-home-pill"><i class="fa-solid fa-signal"></i> Live</span>
            </div>
            <div class="rh-job-list">
                <div class="rh-job-card">
                    <div class="rh-job-icon"><i class="fa-solid fa-code"></i></div>
                    <div class="rh-job-main">
                        <strong>Frontend Developer</strong>
                        <span>Startup SaaS · Tunis · Hybride</span>
                    </div>
                    <div class="rh-job-meta">Full-time</div>
                </div>
                <div class="rh-job-card">
                    <div class="rh-job-icon"><i class="fa-solid fa-chart-line"></i></div>
                    <div class="rh-job-main">
                        <strong>Growth & Marketing</strong>
                        <span>Marketplace etudiante · Remote</span>
                    </div>
                    <div class="rh-job-meta">Stage</div>
                </div>
                <div class="rh-job-card">
                    <div class="rh-job-icon"><i class="fa-solid fa-user-gear"></i></div>
                    <div class="rh-job-main">
                        <strong>Product Operator</strong>
                        <span>Fintech early-stage · Sfax</span>
                    </div>
                    <div class="rh-job-meta">Contract</div>
                </div>
            </div>
            <div class="rh-home-metrics">
                <div class="rh-home-metric">
                    <strong>24</strong>
                    <span>offres ouvertes</span>
                </div>
                <div class="rh-home-metric">
                    <strong>8</strong>
                    <span>startups recrutent</span>
                </div>
                <div class="rh-home-metric">
                    <strong>3 min</strong>
                    <span>pour postuler</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==============================================
     4. DEDICATED GLOBE SECTION
=============================================== -->
<section class="globe-section" id="explore-map">
    
    <div class="globe-header">
        <h2 class="globe-title">Explore the Startup World</h2>
        <p class="globe-sub">Navigate and discover startups globally</p>
    </div>

    <!-- The 3D Canvas -->
    <div id="heroGlobeViz"></div>

    <!-- UI Overlay: Filters & Search -->
    <div class="globe-overlay-ui">
        <div style="position: relative;">
            <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:rgba(255,255,255,0.5);"></i>
            <input type="text" class="global-search-input" id="globalSearch" placeholder="Search startups, skills, categories...">
        </div>

        <div class="floating-filters" id="mapFilters">
            <div class="filter-pill active" data-cat="all">🌍 All</div>
            <div class="filter-pill" data-cat="AI">🤖 AI</div>
            <div class="filter-pill" data-cat="Fintech">💰 Fintech</div>
            <div class="filter-pill" data-cat="Green">🌱 Green</div>
            <div class="filter-pill" data-cat="Health">🏥 Health</div>
        </div>
        
        <button class="btn-reset" onclick="resetGlobeView()">
            <i class="fa-solid fa-rotate"></i> Reset View
        </button>
    </div>

    <!-- Floating Detail Card (Hidden by default) -->
    <div class="globe-detail-card" id="startupDetailCard">
        <button class="btn-close-card" onclick="closeDetailCard()"><i class="fa-solid fa-xmark"></i></button>
        <div id="sdc-match" style="display:inline-block; background:rgba(46,204,113,0.2); color:#2ecc71; padding:4px 10px; border-radius:99px; font-size:11px; font-weight:800; margin-bottom:10px;">98% MATCH</div>
        <div id="sdc-cat" style="font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:1px; margin-bottom:4px; color:#4FC3F7;">CATEGORY</div>
        <h3 id="sdc-title" style="font-family:var(--font-display); font-size:24px; margin:0 0 10px 0; line-height:1.2;">Startup Name</h3>
        <p id="sdc-desc" style="font-size:14px; color:rgba(255,255,255,0.7); line-height:1.6; margin-bottom:20px;">Startup description goes here...</p>
        <div style="display:flex; justify-content:space-between; align-items:center; border-top:1px solid rgba(255,255,255,0.1); padding-top:16px; margin-bottom:20px;">
            <span style="font-size:13px; color:rgba(255,255,255,0.6);"><i class="fa-solid fa-location-dot"></i> <span id="sdc-city">City</span></span>
            <span style="font-size:13px; color:rgba(255,255,255,0.6);"><i class="fa-solid fa-fire"></i> <span id="sdc-pop">Score</span></span>
        </div>
        <a id="sdc-link" href="#" class="btn-primary" style="width:100%; text-align:center; display:block;">View Project</a>
    </div>

    <!-- Bottom Instructions -->
    <div class="globe-instructions">
        <div class="instruction-item"><i class="fa-solid fa-hand-pointer"></i> Drag to rotate</div>
        <div class="instruction-item"><i class="fa-solid fa-arrows-up-down"></i> Scroll to zoom</div>
        <div class="instruction-item"><i class="fa-solid fa-location-dot"></i> Click a startup to explore</div>
    </div>

</section>

<!-- ==============================================
     4. RECENT DISCUSSIONS (Forum Preview)
=============================================== -->
<section class="forum-preview" id="forum-preview">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <div class="forum-header">
            <div class="forum-header-left">
                <div class="section-eyebrow">COMMUNAUTÉ</div>
                <h2>Discussions <span>récentes</span></h2>
            </div>
            <a href="index.php?controller=post&action=index" class="btn-primary" style="padding: 10px 20px; font-size: 14px;">Voir tout le forum</a>
        </div>
        
        <div class="forum-grid">
            <!-- Left: Recent Posts List -->
            <div class="forum-posts-list">
                <div class="forum-post-card" onclick="window.location.href='index.php?controller=post&action=index'">
                    <div class="fp-votes">
                        <button class="vote-btn"><i class="fa-solid fa-caret-up"></i></button>
                        <span style="font-weight: 700; color: var(--navy);">48</span>
                        <button class="vote-btn"><i class="fa-solid fa-caret-down"></i></button>
                    </div>
                    <div class="fp-content">
                        <div class="fp-tags">
                            <span class="fp-tag tag-ia">IA</span>
                            <span class="fp-tag tag-idee">IDÉES</span>
                        </div>
                        <h3 class="fp-title">Comment intégrer GPT-4 dans une app de gestion de tâches pour startups ?</h3>
                        <p class="fp-desc">J'explore l'idée de créer un assistant IA capable de prioriser automatiquement les tâches selon l'urgence et le contexte de l'équipe...</p>
                        <div class="fp-meta">
                            <span><i class="fa-regular fa-comment"></i> 23 réponses</span>
                            <span><i class="fa-regular fa-clock"></i> il y a 2h</span>
                            <span>par <strong style="color:var(--navy);">Ahmed M.</strong></span>
                        </div>
                    </div>
                </div>
                
                <div class="forum-post-card" onclick="window.location.href='index.php?controller=post&action=index'">
                    <div class="fp-votes">
                        <button class="vote-btn"><i class="fa-solid fa-caret-up"></i></button>
                        <span style="font-weight: 700; color: var(--navy);">31</span>
                        <button class="vote-btn"><i class="fa-solid fa-caret-down"></i></button>
                    </div>
                    <div class="fp-content">
                        <div class="fp-tags">
                            <span class="fp-tag tag-fin">FINANCEMENT</span>
                            <span class="fp-tag tag-col">COLLABORATION</span>
                        </div>
                        <h3 class="fp-title">Retour d'expérience : lever des fonds en tant qu'étudiant en Tunisie</h3>
                        <p class="fp-desc">Après 6 mois de démarches, j'ai réussi à obtenir un premier financement de 15K€ via un programme européen. Voici ce que j'ai appris...</p>
                        <div class="fp-meta">
                            <span><i class="fa-regular fa-comment"></i> 41 réponses</span>
                            <span><i class="fa-regular fa-clock"></i> il y a 5h</span>
                            <span>par <strong style="color:var(--navy);">Yasmine B.</strong></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right: Trending Sidebar -->
            <div class="trending-sidebar">
                <div class="trending-box">
                    <div class="trending-title"><i class="fa-solid fa-fire" style="color: #e74c3c;"></i> TENDANCES</div>
                    
                    <div class="trending-item" onclick="window.location.href='index.php?controller=post&action=index'">
                        <div class="t-rank">01</div>
                        <div class="t-info">
                            <div class="t-title">Financement seed — les erreurs à éviter</div>
                            <div class="t-stats">127 vues · 34 réponses</div>
                        </div>
                    </div>
                    <div class="trending-item" onclick="window.location.href='index.php?controller=post&action=index'">
                        <div class="t-rank">02</div>
                        <div class="t-info">
                            <div class="t-title">Trouver un co-fondateur technique</div>
                            <div class="t-stats">98 vues · 28 réponses</div>
                        </div>
                    </div>
                    <div class="trending-item" onclick="window.location.href='index.php?controller=post&action=index'">
                        <div class="t-rank">03</div>
                        <div class="t-info">
                            <div class="t-title">Lancer un MVP avec 0€ de budget</div>
                            <div class="t-stats">87 vues · 21 réponses</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==============================================
     5. LATEST PROJECTS PREVIEW
=============================================== -->
<section class="latest-projects-preview" style="padding: 100px 5%; background: var(--navy); color: white; position: relative; overflow: hidden;">
    <div class="dash-preview-content" style="position: relative; z-index: 2; max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; align-items: center;">
        <div class="dash-eyebrow" style="font-size: 12px; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; color: var(--blue); margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
            <div style="width: 20px; height: 2px; background: var(--blue);"></div>
            STARTUPS RÉCENTES
        </div>
        <h2 class="dash-title" style="font-family: var(--font-display); font-size: 42px; font-weight: 800; margin-bottom: 16px; text-align: center;">Découvrez nos <span>derniers projets</span></h2>
        <p class="dash-sub" style="font-size: 16px; color: rgba(255,255,255,0.7); max-width: 500px; text-align: center; margin-bottom: 50px; line-height: 1.6;">
            Explorez les startups nouvellement créées sur la plateforme par nos brillants étudiants.
        </p>

        <div id="latestProjectsContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px; width: 100%; max-width: 1100px; margin-bottom: 40px;">
            <!-- Latest projects will be rendered here by JS -->
        </div>

        <div style="text-align:left; width:100%; max-width:1100px; margin-bottom: 30px;">
            <a id="allProjectsLink" href="index.php?controller=projet&action=index" class="btn btn-primary" style="padding: 14px 28px; font-weight: 600; font-size: 15px; border-radius: 99px; display: inline-flex; align-items: center; gap: 8px;">
                Voir toutes les startups <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>

        <script>
        async function renderLatestProjects() {
            try {
                const res = await fetch('public/api.php?controller=map&action=apiData', { credentials: 'include' });
                const json = await res.json();
                const data = json.success ? json.data : [];
                const container = document.getElementById('latestProjectsContainer');
                container.innerHTML = '';
                const latest = data.slice(0, 6);
                if (latest.length === 0) {
                    container.innerHTML = `<div style="background: rgba(255,255,255,0.05); border: 1px dashed rgba(255,255,255,0.2); border-radius: 12px; padding: 40px; text-align: center; width: 100%; max-width: 600px;"><div style="font-size: 40px; margin-bottom: 15px;">🚀</div><h3 style="margin-bottom: 10px; font-family:var(--font-display); color:white;">Aucun projet pour le moment</h3><p style="color: rgba(255,255,255,0.6); margin-bottom: 20px;">Soyez le premier à lancer votre startup sur StartSmart.</p><a href="index.php?controller=projet&action=create" class="btn btn-primary">Créer un projet</a></div>`;
                    return;
                }
                latest.forEach(row => {
                    const card = document.createElement('div');
                    card.className = 'card card-elevated';
                    card.style = 'text-align: left; cursor: pointer; display: flex; flex-direction: column; transition: transform 0.2s, box-shadow 0.2s;';
                    card.onclick = () => { window.location.href = `index.php?controller=projet&action=show&id=${row.id}`; };
                    const inner = document.createElement('div');
                    inner.style = 'padding: 24px; display:flex; flex-direction:column; flex-grow:1;';
                    inner.innerHTML = `
                        <span style="background: rgba(2, 136, 209, 0.1); color: var(--blue-dark); margin-bottom: 15px; width: fit-content; text-transform: uppercase; font-weight: 800; font-size: 11px; padding: 6px 12px; border-radius: 6px; letter-spacing: 0.5px;">${row.categorie_nom || 'Sans catégorie'}</span>
                        <a href="index.php?controller=projet&action=show&id=${row.id}" style="font-family: var(--font-display); font-size: 20px; font-weight: 700; color: var(--navy); text-decoration: none; margin-bottom: 12px; line-height: 1.3; overflow-wrap: break-word; word-break: break-word;">${escapeHtml(row.nomprojet || row.name || 'Untitled')}</a>
                        <p style="font-size: 14px; color: var(--gray-600); line-height: 1.6; margin-bottom: 20px; flex-grow: 1; overflow-wrap: break-word; word-break: break-word; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical;">${escapeHtml((row.description || '').substring(0, 150))}${(row.description && row.description.length>150)?'...':''}</p>
                    `;
                    // skills
                    if (row.competences && row.competences.length) {
                        const skillsDiv = document.createElement('div');
                        skillsDiv.style = 'display:flex; flex-wrap:wrap; gap:6px; margin-bottom:20px;';
                        (row.competences.slice(0,3)).forEach(comp => {
                            const span = document.createElement('span');
                            span.style = 'background: var(--gray-100); color: var(--gray-600); border: 1px solid var(--gray-200); border-radius: 6px; font-size: 11px; padding: 4px 10px; font-weight: 600;';
                            span.innerText = (typeof comp === 'string') ? comp : (comp.nom || comp.name || '');
                            skillsDiv.appendChild(span);
                        });
                        if (row.competences.length > 3) {
                            const more = document.createElement('span');
                            more.style = 'background: var(--gray-100); color: var(--gray-600); border: 1px solid var(--gray-200); border-radius: 6px; font-size: 11px; padding: 4px 6px; font-weight: 600;';
                            more.innerText = '+' + (row.competences.length - 3);
                            skillsDiv.appendChild(more);
                        }
                        inner.appendChild(skillsDiv);
                    }

                    // footer
                    const footer = document.createElement('div');
                    footer.style = 'display:flex; justify-content:space-between; align-items:center; border-top:1px solid var(--gray-100); padding-top:16px;';
                    footer.innerHTML = `
                        <div style="display:inline-flex; align-items:center; gap:6px; background: rgba(46,204,113,0.1); color: var(--green-dark); padding: 6px 12px; border-radius: 99px; font-weight:700; font-size:12px;">💰 ${escapeHtml(row.budget||'0')} DT</div>
                        <div style="display:flex; align-items:center; gap:8px; font-size:13px; font-weight:600; color:var(--navy);" title="Auteur du projet"><span style="background: var(--blue-light); color: var(--blue-dark); width:26px; height:26px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:11px;">${escapeHtml(((row.auteur_nom||row.author||'U').substring(0,1)).toUpperCase())}</span> ${escapeHtml(row.auteur_nom||row.author||'Utilisateur')}</div>
                    `;
                    inner.appendChild(footer);
                    card.appendChild(inner);
                    container.appendChild(card);
                });
            } catch (e) {
                console.error('Failed to load latest projects', e);
            }
        }

        function escapeHtml(s) { return String(s||'').replace(/[&<>"']/g, function(c){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]); }); }

        document.addEventListener('DOMContentLoaded', () => {
            renderLatestProjects();
        });
        </script>
    </div>
</section>

<!-- ==============================================
     6. CALL TO ACTION
=============================================== -->
<section class="cta-section">
    <div class="cta-content">
        <div class="cta-eyebrow">PRÊT À COMMENCER ?</div>
        <h2 class="cta-title">Rejoignez la prochaine génération de fondateurs</h2>
        <p class="cta-sub">Inscrivez-vous gratuitement et lancez votre startup dès aujourd'hui. La communauté vous attend.</p>
        <div class="cta-actions">
            <a href="login.php?action=register" class="cta-btn-light"><i class="fa-solid fa-rocket"></i> Commencer Gratuitement</a>
            <a href="index.php?controller=projet&action=index" class="cta-btn-outline"><i class="fa-regular fa-compass"></i> Explorer les Projets</a>
        </div>
    </div>
</section>

<!-- Defer heavy 3D libraries so they don't block the initial page load -->
<script src="//unpkg.com/three" defer></script>
<script src="//unpkg.com/globe.gl" defer></script>

<script>
/* ==========================================
   GLOBE SECTION LOGIC
========================================== */
let allProjects = [];
let globeInstance = null;

const colorMap = {
    'AI': '#3498db', 'Fintech': '#2ecc71', 'Green': '#27ae60', 
    'Health': '#e74c3c', 'Tech': '#9b59b6', 'Default': '#4FC3F7'
};

function getCatColor(cat) {
    if(!cat) return colorMap.Default;
    for(let key in colorMap) {
        if(cat.toLowerCase().includes(key.toLowerCase())) return colorMap[key];
    }
    return colorMap.Default;
}

function initGlobe() {
    globeInstance = Globe()
        (document.getElementById('heroGlobeViz'))
        .globeImageUrl('//unpkg.com/three-globe/example/img/earth-night.jpg')
        .bumpImageUrl('//unpkg.com/three-globe/example/img/earth-topology.png')
        .backgroundColor('rgba(0,0,0,0)')
        .pointLat('lat')
        .pointLng('lng')
        .pointColor(d => getCatColor(d.category))
        .pointAltitude(d => 0.02)
        .pointRadius(0.5)
        .pointsMerge(false)
        .ringLat('lat')
        .ringLng('lng')
        .ringColor(d => getCatColor(d.category))
        .ringMaxRadius(d => d.isTrending ? 3 : 1)
        .ringPropagationSpeed(1.5)
        .ringRepeatPeriod(1000)
        .onPointHover(point => {
            document.getElementById('heroGlobeViz').style.cursor = point ? 'pointer' : 'grab';
            globeInstance.controls().autoRotate = !point;
        })
        .onPointClick(d => {
            // Smooth zoom to pin
            globeInstance.pointOfView({ lat: d.lat, lng: d.lng, altitude: 0.6 }, 1000);
            
            // Open Floating Detail Card
            openDetailCard(d);
        });

    globeInstance.controls().autoRotate = true;
    globeInstance.controls().autoRotateSpeed = 0.8;
    
    // Initial Zoom out
    globeInstance.pointOfView({ altitude: 3 }, 0);
    setTimeout(() => {
        globeInstance.pointOfView({ altitude: 1.8 }, 2000);
    }, 500);

    // Handle Resize
    window.addEventListener('resize', () => {
        const container = document.querySelector('.globe-section');
        if(container && globeInstance) {
            globeInstance.width(container.clientWidth).height(container.clientHeight);
        }
    });
}

function resetGlobeView() {
    globeInstance.pointOfView({ altitude: 1.8 }, 1500);
    closeDetailCard();
}

function openDetailCard(d) {
    document.getElementById('sdc-cat').innerText = d.category;
    document.getElementById('sdc-cat').style.color = getCatColor(d.category);
    document.getElementById('sdc-title').innerText = d.name;
    document.getElementById('sdc-desc').innerText = d.description.substring(0, 120) + (d.description.length > 120 ? '...' : '');
    document.getElementById('sdc-city').innerText = d.city;
    document.getElementById('sdc-pop').innerText = d.popularity + ' Activity Score';
    document.getElementById('sdc-match').innerText = Math.floor(Math.random() * 20 + 80) + '% MATCH';
    document.getElementById('sdc-link').href = `index.php?controller=projet&action=show&id=${d.id}`;
    
    document.getElementById('startupDetailCard').classList.add('active');
}

function closeDetailCard() {
    document.getElementById('startupDetailCard').classList.remove('active');
}

function fetchMapData() {
    fetch('public/api.php?controller=map&action=apiData')
        .then(res => res.json())
        .then(response => {
            if(response.success) {
                allProjects = response.data;
                updateGlobeData(allProjects);
            }
        });
}

function updateGlobeData(data) {
    globeInstance.pointsData(data);
    globeInstance.ringData(data.filter(d => d.isTrending));
}

// SEARCH & FILTER LOGIC
document.getElementById('globalSearch').addEventListener('input', (e) => {
    const term = e.target.value.toLowerCase();
    const filtered = allProjects.filter(p => 
        p.name.toLowerCase().includes(term) || 
        p.description.toLowerCase().includes(term) ||
        p.category.toLowerCase().includes(term)
    );
    updateGlobeData(filtered);
    closeDetailCard();
});

document.querySelectorAll('.filter-pill').forEach(btn => {
    btn.addEventListener('click', (e) => {
        document.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
        e.target.classList.add('active');
        
        const cat = e.target.getAttribute('data-cat');
        if(cat === 'all') {
            updateGlobeData(allProjects);
        } else {
            const filtered = allProjects.filter(p => p.category.toLowerCase().includes(cat.toLowerCase()));
            updateGlobeData(filtered);
        }
        closeDetailCard();
    });
});

// Init on Intersection Observer to prevent lag before scrolling
let mapInitialized = false;
const observer = new IntersectionObserver((entries) => {
    if(entries[0].isIntersecting && !mapInitialized) {
        // Ensure the deferred Globe.gl script has finished loading
        if (typeof Globe === 'undefined') {
            setTimeout(() => {
                if(typeof Globe !== 'undefined' && !mapInitialized) {
                    mapInitialized = true;
                    initGlobe();
                    fetchMapData();
                    setTimeout(() => window.dispatchEvent(new Event('resize')), 100);
                }
            }, 500);
            return;
        }
        
        mapInitialized = true;
        initGlobe();
        fetchMapData();
        
        // Ensure dimensions are correct immediately
        setTimeout(() => window.dispatchEvent(new Event('resize')), 100);
    }
}, { threshold: 0.1 });

document.addEventListener('DOMContentLoaded', () => {
    observer.observe(document.querySelector('.globe-section'));
    
    // Reset navbar color behavior since we restored the hero
    document.querySelector('.navbar').style.background = 'rgba(255,255,255,0.82)';
    // Removed document.querySelector('.logo-text-name').style.color as it breaks the footer
});

</script>

