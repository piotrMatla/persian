<?php

use EWnioski\View\Icons;

/**
 * @var callable                              $e        helper escapujący
 * @var \EWnioski\Domain\User\User            $user
 * @var list<\EWnioski\Domain\Navigation\NavItem> $modules  już przefiltrowane po roli
 * @var bool                                  $hasAdmin
 * @var list<string>                          $profiles
 */
?>
<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>eWnioski — pulpit</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
    --bg: #f4f5f7;
    --card-bg: #ffffff;
    --border: #e7e9ec;
    --text: #1a1a1a;
    --sub-text: #8a8f96;
    --input-bg: #f8f9fa;
    --admin-accent: #3B5B76;
    --admin-bg: #eaf0f4;
    --prod-accent: #C2410C;
    --prod-bg: #fbe9e0;
    --red: #E4032E;
}

html.dark {
    --bg: #101114;
    --card-bg: #1a1b1f;
    --border: #2c2e34;
    --text: #f2f3f5;
    --sub-text: #8b909a;
    --input-bg: #1a1b1f;
    --admin-bg: rgba(59, 91, 118, .28);
    --prod-bg: rgba(194, 65, 12, .22);
}

* { box-sizing: border-box; }

body {
    margin: 0;
    font-family: 'Inter', system-ui, sans-serif;
    background: var(--bg);
    color: var(--text);
}

.app-shell { display: flex; min-height: 100vh; }

.sidebar {
    width: 240px; flex: none; background: var(--card-bg);
    border-right: 1px solid var(--border);
    display: flex; flex-direction: column; padding: 24px 16px;
    position: fixed; top: 0; left: 0; height: 100vh; overflow-y: auto; z-index: 10;
}

.brand { display: flex; align-items: center; gap: 8px; padding: 0 4px 24px; }
.brand-name { font-weight: 800; font-size: 16px; color: var(--text); letter-spacing: -.2px; }

.search-box { position: relative; margin-bottom: 18px; }
.search-box svg { position: absolute; left: 11px; top: 10px; color: var(--sub-text); pointer-events: none; }
.search-box input {
    width: 100%; padding: 9px 10px 9px 32px; border-radius: 10px;
    border: 1px solid var(--border); font-size: 12.5px; color: var(--text);
    background: var(--input-bg); outline: none;
}

.field-label {
    font-size: 10.5px; font-weight: 700; color: var(--sub-text);
    letter-spacing: .06em; text-transform: uppercase; padding: 0 6px 8px; margin-top: 6px;
}

.site-select {
    width: 100%; margin-bottom: 8px; padding: 9px 10px; border-radius: 10px;
    border: 1px solid var(--border); background: var(--input-bg); color: var(--text);
    font-size: 12.5px; font-family: inherit;
}

.category-nav { display: flex; flex-direction: column; gap: 4px; }
.tab-btn {
    all: unset; cursor: pointer; padding: 10px 12px; border-radius: 10px;
    font-size: 13.5px; font-weight: 600; color: var(--sub-text);
}
.tab-btn:hover { opacity: .85; }
.tab-btn.active[data-category="admin"] { background: var(--admin-bg); color: var(--admin-accent); }
.tab-btn.active[data-category="prod"]  { background: var(--prod-bg);  color: var(--prod-accent); }
.tab-btn.active[data-category="all"]   { background: var(--border);   color: var(--text); }

.sidebar-footer {
    margin-top: auto; padding-top: 16px; border-top: 1px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
    font-size: 11.5px; color: var(--sub-text);
}

.user-box { padding: 10px 6px 14px; font-size: 12px; }
.user-box .u-name { font-weight: 700; color: var(--text); }
.user-box .u-role { color: var(--sub-text); font-size: 11px; }

.toggle {
    all: unset; cursor: pointer; width: 36px; height: 20px; border-radius: 10px;
    background: #d6d9dd; position: relative;
}
.toggle[aria-pressed="true"] { background: var(--red); }
.toggle-knob {
    position: absolute; top: 2px; left: 2px; width: 16px; height: 16px; border-radius: 50%;
    background: #fff; box-shadow: 0 1px 2px rgba(0,0,0,.2); transition: left .15s;
}
.toggle[aria-pressed="true"] .toggle-knob { left: 18px; }

.admin-link {
    display: block; text-align: center; margin-top: 12px; padding: 8px;
    font-size: 11.5px; font-weight: 600; color: var(--sub-text); text-decoration: none;
}
.admin-link:hover { color: var(--text); }

.content { flex: 1; margin-left: 240px; padding: 24px 32px 60px; min-width: 0; }

.favorites-row { margin-bottom: 20px; }
.section-label {
    font-size: 11px; font-weight: 700; color: var(--sub-text);
    letter-spacing: .06em; text-transform: uppercase; margin-bottom: 10px;
}
.favorites-track {
    display: flex; gap: 10px; overflow-x: auto; padding-bottom: 16px;
    border-bottom: 1px solid var(--border);
}
.fav-chip {
    all: unset; cursor: pointer; flex: none; display: flex; align-items: center; gap: 8px;
    background: var(--card-bg); border: 1px solid var(--border); border-radius: 12px;
    padding: 9px 12px; min-width: 150px; font-size: 12px; font-weight: 600; color: var(--text);
}

.content-header { display: flex; align-items: baseline; justify-content: space-between; margin-bottom: 16px; }
.content-header h1 { font-size: 20px; font-weight: 800; margin: 0; }
.results-count { font-size: 12.5px; color: var(--sub-text); }

.app-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 14px; }

.app-card {
    position: relative; z-index: 1; cursor: pointer; background: var(--card-bg);
    border: 1px solid var(--border); border-radius: 14px; padding: 16px 14px;
    display: flex; flex-direction: column; gap: 8px;
    transition: transform .15s, box-shadow .15s, border-color .15s;
    text-decoration: none; color: inherit;
}
.app-card:hover { transform: translateY(-2px); border-color: var(--red); box-shadow: 0 10px 24px rgba(228, 3, 46, .16); z-index: 20; }

.app-card.is-disabled { cursor: default; opacity: .62; }
.app-card.is-disabled:hover { transform: none; border-color: var(--border); box-shadow: none; }

.app-hover-info {
    position: absolute; top: 100%; left: -8px; right: -8px; margin-top: 10px;
    display: flex; flex-direction: column; gap: 6px; padding: 12px 14px;
    background: var(--red); border-radius: 12px; box-shadow: 0 14px 30px rgba(228, 3, 46, .35);
    opacity: 0; transform: translateY(-6px); transition: opacity .15s ease, transform .15s ease; pointer-events: none;
}
.app-card:hover .app-hover-info { opacity: 1; transform: translateY(0); }
.hover-info-title { font-size: 13px; font-weight: 700; color: #fff; margin-bottom: 2px; }
.hover-info-row { font-size: 11.5px; line-height: 1.4; color: rgba(255, 255, 255, .95); }
.hover-info-row .hover-info-label {
    display: block; font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .04em; color: rgba(255, 255, 255, .7);
}

.fav-star { all: unset; cursor: pointer; position: absolute; top: 10px; right: 10px; z-index: 2; color: #c7cbd1; display: flex; }
html.dark .fav-star { color: #565a63; }
.fav-star.is-fav { color: var(--red); }
.fav-star.is-fav svg { fill: var(--red); }
.fav-star:hover { transform: scale(1.15); }

.app-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
.app-card[data-category="admin"] .app-icon { background: var(--admin-bg); color: var(--admin-accent); }
.app-card[data-category="prod"]  .app-icon { background: var(--prod-bg);  color: var(--prod-accent); }

.app-name { font-size: 13px; font-weight: 600; line-height: 1.3; }
.app-purpose { font-size: 11.5px; color: var(--sub-text); line-height: 1.4; }
.app-meta { margin-top: auto; display: flex; flex-wrap: wrap; gap: 4px; }
.badge {
    font-size: 10px; font-weight: 600; padding: 2px 7px; border-radius: 6px;
    background: var(--input-bg); color: var(--sub-text); border: 1px solid var(--border);
}
.badge.soon { background: var(--red); color: #fff; border-color: var(--red); }

.empty-state { text-align: center; color: var(--sub-text); padding: 60px 0; font-size: 13px; }

.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.4); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal-overlay[hidden] { display: none; }
.modal { background: var(--card-bg); border-radius: 14px; width: min(480px, 90vw); max-height: 80vh; overflow-y: auto; padding: 20px; }
.modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
.modal-header h2 { font-size: 16px; margin: 0; }
.modal-header button { all: unset; cursor: pointer; font-size: 20px; line-height: 1; color: var(--sub-text); }
.modal-links { display: flex; flex-direction: column; gap: 6px; }
.modal-links a { padding: 9px 12px; border-radius: 8px; background: var(--input-bg); color: var(--text); text-decoration: none; font-size: 13px; }
.modal-links a:hover { background: var(--border); }

@media (max-width: 720px) {
    .app-shell { flex-direction: column; }
    .sidebar { width: 100%; height: auto; position: static; }
    .content { padding: 20px; margin-left: 0; }
}
</style>
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand"><?= Icons::get('logo') ?><span class="brand-name">eWnioski</span></div>

        <div class="search-box">
            <?= Icons::get('search') ?>
            <input id="search" type="search" placeholder="Szukaj modułu…" autocomplete="off">
        </div>

        <div class="field-label">Zakład</div>
        <select class="site-select" id="site-select">
            <?php foreach ($profiles as $p): ?>
                <option><?= $e($p) ?></option>
            <?php endforeach; ?>
        </select>

        <div class="field-label" style="margin-top:14px;">Kategorie</div>
        <nav class="category-nav" id="category-nav">
            <button class="tab-btn active" data-category="all">Wszystkie moduły</button>
            <button class="tab-btn" data-category="prod">Produkcja / pracownik</button>
            <?php if ($hasAdmin): ?>
                <button class="tab-btn" data-category="admin">Administracja / HR</button>
            <?php endif; ?>
        </nav>

        <div class="sidebar-footer">
            <span>Tryb ciemny</span>
            <button class="toggle" id="theme-toggle" aria-pressed="false" aria-label="Przełącz tryb ciemny">
                <span class="toggle-knob"></span>
            </button>
        </div>
        <div class="user-box">
            <div class="u-name"><?= $e($user->fullName()) ?></div>
            <div class="u-role"><?= $e($user->role->label()) ?></div>
        </div>
        <a class="admin-link" href="logout">Wyloguj</a>
    </aside>

    <main class="content">
        <section class="favorites-row" id="favorites-row" hidden>
            <div class="section-label">Ulubione</div>
            <div class="favorites-track" id="favorites-track"></div>
        </section>

        <div class="content-header">
            <h1 id="heading">Wszystkie moduły</h1>
            <span class="results-count" id="results-count"></span>
        </div>

        <div class="app-grid" id="app-grid">
            <?php foreach ($modules as $m): ?>
                <?php
                $isGroup = $m->isGroup();
                $isLink = !$isGroup && $m->available && $m->href !== null;
                $tag = $isLink ? 'a' : 'div';
                $classes = 'app-card' . (!$isGroup && !$m->available ? ' is-disabled' : '');
                ?>
                <<?= $tag ?> class="<?= $classes ?>"
                    data-category="<?= $e($m->category) ?>"
                    data-id="<?= $e($m->id) ?>"
                    data-name="<?= $e($m->name) ?>"
                    data-purpose="<?= $e($m->purpose) ?>"
                    <?= $isLink ? 'href="' . $e($m->href) . '"' : '' ?>
                    <?= $isGroup ? "data-links='" . $e(json_encode($m->links, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS)) . "'" : '' ?>>
                    <button class="fav-star" data-fav="<?= $e($m->id) ?>" aria-label="Ulubione" type="button"><?= Icons::get('star') ?></button>
                    <div class="app-icon"><?= Icons::get($m->icon) ?></div>
                    <div class="app-name"><?= $e($m->name) ?></div>
                    <div class="app-purpose"><?= $e($m->purpose) ?></div>
                    <div class="app-meta">
                        <?php foreach ($m->badges as $b): ?><span class="badge"><?= $e($b) ?></span><?php endforeach; ?>
                        <?php if (!$isGroup && !$m->available): ?><span class="badge soon">Wkrótce</span><?php endif; ?>
                    </div>
                    <div class="app-hover-info">
                        <div class="hover-info-title"><?= $e($m->name) ?></div>
                        <?php foreach ($m->info as $label => $value): ?>
                            <div class="hover-info-row"><span class="hover-info-label"><?= $e($label) ?></span><?= $e($value) ?></div>
                        <?php endforeach; ?>
                        <?php if ($isGroup): ?><div class="hover-info-row"><?= count($m->links) ?> raportów — kliknij, by wybrać</div><?php endif; ?>
                    </div>
                </<?= $tag ?>>
            <?php endforeach; ?>
        </div>
        <div class="empty-state" id="empty-state" hidden>Brak modułów pasujących do wyszukiwania.</div>
    </main>
</div>

<div class="modal-overlay" id="modal" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
        <div class="modal-header">
            <h2 id="modal-title"></h2>
            <button id="modal-close" aria-label="Zamknij" type="button">&times;</button>
        </div>
        <div class="modal-links" id="modal-links"></div>
    </div>
</div>

<script>
(function () {
    'use strict';
    const grid = document.getElementById('app-grid');
    const cards = [...grid.querySelectorAll('.app-card')];
    const emptyState = document.getElementById('empty-state');
    const resultsCount = document.getElementById('results-count');
    const heading = document.getElementById('heading');
    const favRow = document.getElementById('favorites-row');
    const favTrack = document.getElementById('favorites-track');
    const FAV_KEY = 'ewnioski_favorites';
    const THEME_KEY = 'ewnioski_theme';
    let activeCategory = 'all';
    let searchTerm = '';

    const CATEGORY_TITLE = { all: 'Wszystkie moduły', prod: 'Produkcja / pracownik', admin: 'Administracja / HR' };

    function loadFav() { try { return new Set(JSON.parse(localStorage.getItem(FAV_KEY) || '[]')); } catch { return new Set(); } }
    function saveFav(s) { try { localStorage.setItem(FAV_KEY, JSON.stringify([...s])); } catch {} }
    let favorites = loadFav();

    function plural(n) {
        if (n === 1) return 'moduł';
        if (n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 10 || n % 100 >= 20)) return 'moduły';
        return 'modułów';
    }

    function apply() {
        const term = searchTerm.trim().toLowerCase();
        let visible = 0;
        cards.forEach(card => {
            const catOk = activeCategory === 'all' || card.dataset.category === activeCategory;
            const searchOk = term === '' ||
                card.dataset.name.toLowerCase().includes(term) ||
                card.dataset.purpose.toLowerCase().includes(term);
            const show = catOk && searchOk;
            card.style.display = show ? '' : 'none';
            if (show) visible++;
            card.querySelector('.fav-star').classList.toggle('is-fav', favorites.has(card.dataset.id));
        });
        emptyState.hidden = visible > 0;
        resultsCount.textContent = visible + ' ' + plural(visible);
        heading.textContent = term ? 'Wyniki wyszukiwania' : CATEGORY_TITLE[activeCategory];
        renderFav();
    }

    function renderFav() {
        const favCards = cards.filter(c => favorites.has(c.dataset.id));
        favRow.hidden = favCards.length === 0;
        favTrack.innerHTML = '';
        favCards.forEach(c => {
            const chip = document.createElement('button');
            chip.className = 'fav-chip';
            chip.dataset.id = c.dataset.id;
            chip.innerHTML = c.querySelector('.app-icon').innerHTML + '<span>' + c.dataset.name.replace(/[&<>"]/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m])) + '</span>';
            favTrack.appendChild(chip);
        });
    }

    document.getElementById('search').addEventListener('input', e => { searchTerm = e.target.value; apply(); });

    document.getElementById('category-nav').addEventListener('click', e => {
        const btn = e.target.closest('.tab-btn');
        if (!btn) return;
        activeCategory = btn.dataset.category;
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.toggle('active', b === btn));
        apply();
    });

    grid.addEventListener('click', e => {
        const favBtn = e.target.closest('[data-fav]');
        if (favBtn) {
            e.preventDefault();
            const id = favBtn.dataset.fav;
            favorites.has(id) ? favorites.delete(id) : favorites.add(id);
            saveFav(favorites);
            apply();
            return;
        }
        const card = e.target.closest('.app-card');
        if (card && card.dataset.links) { e.preventDefault(); openModal(card); }
    });

    favTrack.addEventListener('click', e => {
        const chip = e.target.closest('.fav-chip');
        if (!chip) return;
        const card = cards.find(c => c.dataset.id === chip.dataset.id);
        if (!card) return;
        if (card.dataset.links) openModal(card);
        else if (card.getAttribute('href')) window.location.href = card.getAttribute('href');
    });

    const modal = document.getElementById('modal');
    function openModal(card) {
        let links = [];
        try { links = JSON.parse(card.dataset.links); } catch {}
        document.getElementById('modal-title').textContent = card.dataset.name;
        const box = document.getElementById('modal-links');
        box.innerHTML = '';
        links.forEach(l => {
            const a = document.createElement('a');
            a.href = l.href;
            a.textContent = l.label;
            box.appendChild(a);
        });
        modal.hidden = false;
    }
    function closeModal() { modal.hidden = true; }
    document.getElementById('modal-close').addEventListener('click', closeModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

    const themeToggle = document.getElementById('theme-toggle');
    function applyTheme(dark) {
        document.documentElement.classList.toggle('dark', dark);
        themeToggle.setAttribute('aria-pressed', dark ? 'true' : 'false');
    }
    try {
        const stored = localStorage.getItem(THEME_KEY);
        applyTheme(stored ? stored === 'dark' : matchMedia('(prefers-color-scheme: dark)').matches);
    } catch { applyTheme(false); }
    themeToggle.addEventListener('click', () => {
        const dark = !document.documentElement.classList.contains('dark');
        applyTheme(dark);
        try { localStorage.setItem(THEME_KEY, dark ? 'dark' : 'light'); } catch {}
    });

    apply();
})();
</script>
</body>
</html>
