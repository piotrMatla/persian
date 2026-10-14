/**
 * eWnioski - obsługa nowego układu: tryb ciemny, wyszukiwanie w menu, ulubione i okna modalne.
 */
(function () {
    'use strict';

    function zapisz(klucz, wartosc) {
        try { localStorage.setItem(klucz, wartosc); } catch (e) {}
    }

    function odczytaj(klucz) {
        try { return localStorage.getItem(klucz); } catch (e) { return null; }
    }

    /* Tryb ciemny */
    function ustawPrzelaczniki() {
        var ciemny = document.documentElement.classList.contains('dark');
        document.querySelectorAll('[data-przelacznik-motywu]').forEach(function (przycisk) {
            przycisk.setAttribute('aria-pressed', ciemny ? 'true' : 'false');
        });
    }

    document.querySelectorAll('[data-przelacznik-motywu]').forEach(function (przycisk) {
        przycisk.addEventListener('click', function () {
            var ciemny = document.documentElement.classList.toggle('dark');
            zapisz('ewnioski-motyw', ciemny ? 'dark' : 'light');
            ustawPrzelaczniki();
        });
    });
    ustawPrzelaczniki();

    /* Menu na wąskich ekranach */
    document.querySelectorAll('[data-menu-toggle]').forEach(function (przycisk) {
        przycisk.addEventListener('click', function () {
            var otwarte = przycisk.closest('.sidebar').classList.toggle('open');
            przycisk.setAttribute('aria-expanded', otwarte ? 'true' : 'false');
        });
    });

    /* Wyszukiwanie w menu bocznym */
    var szukaj = document.querySelector('[data-szukaj-menu]');
    if (szukaj) {
        szukaj.addEventListener('input', function () {
            var fraza = szukaj.value.trim().toLowerCase();
            var nav = document.querySelector('.category-nav');
            var grupa = null;
            var widoczneWGrupie = 0;
            Array.prototype.forEach.call(nav.children, function (element) {
                if (element.hasAttribute('data-grupa-menu')) {
                    if (grupa) grupa.hidden = widoczneWGrupie === 0;
                    grupa = element;
                    widoczneWGrupie = 0;
                    return;
                }
                var pasuje = fraza === '' || element.textContent.toLowerCase().indexOf(fraza) !== -1;
                element.hidden = !pasuje;
                if (pasuje) widoczneWGrupie++;
            });
            if (grupa) grupa.hidden = widoczneWGrupie === 0;
        });
        szukaj.addEventListener('keydown', function (zdarzenie) {
            if (zdarzenie.key !== 'Enter') return;
            var pierwszy = document.querySelector('.category-nav a.tab-btn:not([hidden])');
            if (pierwszy) window.location.href = pierwszy.href;
        });
    }

    /* Ulubione na stronie głównej */
    var KLUCZ_ULUBIONYCH = 'ewnioski-ulubione';

    function ulubione() {
        try { return JSON.parse(odczytaj(KLUCZ_ULUBIONYCH) || '[]'); } catch (e) { return []; }
    }

    function pokazUlubione() {
        var wiersz = document.querySelector('[data-ulubione]');
        var lista = document.querySelector('[data-ulubione-lista]');
        if (!wiersz || !lista) return;
        var wybrane = ulubione();
        lista.innerHTML = '';
        document.querySelectorAll('.app-card[data-skrot]').forEach(function (karta) {
            var jest = wybrane.indexOf(karta.getAttribute('data-skrot')) !== -1;
            var gwiazdka = karta.querySelector('[data-gwiazdka]');
            if (gwiazdka) {
                gwiazdka.classList.toggle('is-fav', jest);
                gwiazdka.setAttribute('aria-pressed', jest ? 'true' : 'false');
            }
        });
        wybrane.forEach(function (skrot) {
            var karta = document.querySelector('.app-card[data-skrot="' + skrot.replace(/"/g, '\\"') + '"]');
            if (!karta) return;
            var chip = document.createElement('a');
            chip.className = 'fav-chip';
            chip.href = karta.getAttribute('href');
            var ikona = karta.querySelector('.app-icon');
            if (ikona) {
                var kopia = ikona.cloneNode(true);
                kopia.style.width = '24px';
                kopia.style.height = '24px';
                kopia.style.borderRadius = '7px';
                kopia.style.background = karta.getAttribute('data-category') === 'admin' ? 'var(--admin-bg)' : 'var(--prod-bg)';
                kopia.style.color = karta.getAttribute('data-category') === 'admin' ? 'var(--admin-accent)' : 'var(--prod-accent)';
                chip.appendChild(kopia);
            }
            chip.appendChild(document.createTextNode(karta.getAttribute('data-nazwa')));
            lista.appendChild(chip);
        });
        wiersz.hidden = lista.children.length === 0;
    }

    document.querySelectorAll('[data-gwiazdka]').forEach(function (gwiazdka) {
        gwiazdka.addEventListener('click', function (zdarzenie) {
            zdarzenie.preventDefault();
            zdarzenie.stopPropagation();
            var skrot = gwiazdka.closest('.app-card').getAttribute('data-skrot');
            var wybrane = ulubione();
            var pozycja = wybrane.indexOf(skrot);
            if (pozycja === -1) wybrane.push(skrot);
            else wybrane.splice(pozycja, 1);
            zapisz(KLUCZ_ULUBIONYCH, JSON.stringify(wybrane));
            pokazUlubione();
        });
    });
    pokazUlubione();

    /* Okna modalne */
    function zamknijModal(okno) {
        okno.hidden = true;
    }

    document.querySelectorAll('[data-otworz-modal]').forEach(function (odnosnik) {
        odnosnik.addEventListener('click', function (zdarzenie) {
            zdarzenie.preventDefault();
            var okno = document.getElementById(odnosnik.getAttribute('data-otworz-modal'));
            if (okno) okno.hidden = false;
        });
    });

    document.querySelectorAll('.modal-overlay').forEach(function (okno) {
        okno.addEventListener('click', function (zdarzenie) {
            if (zdarzenie.target === okno || zdarzenie.target.closest('[data-zamknij-modal]')) zamknijModal(okno);
        });
    });

    document.addEventListener('keydown', function (zdarzenie) {
        if (zdarzenie.key !== 'Escape') return;
        document.querySelectorAll('.modal-overlay:not([hidden])').forEach(zamknijModal);
    });
})();
