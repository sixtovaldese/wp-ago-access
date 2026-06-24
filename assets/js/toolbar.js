(function () {
    'use strict';
    var cfg = window.agoaccessCfg || {};

    function init() {
        var container = document.getElementById('ago-access-toolbar');
        if (!container) return;
        boot(container);
    }

    if (document.getElementById('ago-access-toolbar')) {
        init();
    } else if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        setTimeout(init, 50);
    }

    function boot(container) {

    var i18n = cfg.i18n || {};
    var tools = cfg.tools || {};
    var color = cfg.color || '#2271b1';
    var icons = {
        person: '<svg viewBox="0 0 24 24"><circle cx="12" cy="4" r="2"/><path d="M12 7c-3 0-7 1-7 1v2s3-1 5-1v4l-3 7h2l2-5 2 5h2l-3-7v-4c2 0 5 1 5 1v-2s-4-1-7-1z"/></svg>',
        wheelchair: '<svg viewBox="0 0 24 24"><circle cx="12" cy="4" r="2"/><path d="M19 13v-2c-1.54.02-3.09-.75-4.07-1.83l-1.29-1.43c-.17-.19-.38-.34-.61-.45-.01 0-.01-.01-.02-.01H13c-.35-.2-.75-.3-1.19-.26C10.76 7.11 10 8.04 10 9.09V15c0 1.1.9 2 2 2h5v5h2v-5.5c0-1.1-.9-2-2-2h-3v-3.45c1.29 1.07 3.25 1.94 5 1.95zm-6.17 5c-.41 1.16-1.52 2-2.83 2-1.66 0-3-1.34-3-3 0-1.31.84-2.42 2-2.83V12.1a5 5 0 105.9 5.9h-2.07z"/></svg>',
        eye: '<svg viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>',
        universal: '<svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/><circle cx="12" cy="8.5" r="1.5"/><path d="M15 11H9v1h2v4H9v1h6v-1h-2v-5z"/></svg>',
    };
    var icon = icons[cfg.icon] || icons.person;
    var shapeClass = 'shape-' + (cfg.shape || 'circle');
    var posClass = cfg.position || 'bottom-right';
    var STORAGE_KEY = 'ago_access_prefs';
    var prefs = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
    var isOpen = false;
    var fontSize = prefs.fontSize || 100;
    var contrastMode = prefs.contrast || 'normal';
    var readingGuideEl = null;
    var speechSynth = window.speechSynthesis || null;

    applyAll();
    render();

    function render() {
        container.innerHTML =
            '<button class="ago-a11y-toggle ' + posClass + ' ' + shapeClass + '" style="background:' + color + '" aria-label="' + esc(i18n.title) + '" title="' + esc(i18n.title) + '">' + icon + (cfg.shape === 'rounded' ? ' <span style="font-size:12px;font-weight:600">' + esc(i18n.title) + '</span>' : '') + '</button>' +
            '<div class="ago-a11y-panel ' + posClass + '" role="dialog" aria-label="' + esc(i18n.title) + '">' +
                '<div class="ago-a11y-header" style="background:' + color + '">' +
                    '<h3>' + icon + ' ' + esc(i18n.title) + '</h3>' +
                    '<div class="ago-a11y-header-btns">' +
                        '<button data-action="reset">' + esc(i18n.reset) + '</button>' +
                        '<button data-action="close">&times;</button>' +
                    '</div>' +
                '</div>' +
                '<div class="ago-a11y-body">' + renderTools() + '</div>' +
            '</div>';

        container.querySelector('.ago-a11y-toggle').addEventListener('click', toggle);
        container.querySelector('[data-action="close"]').addEventListener('click', toggle);
        container.querySelector('[data-action="reset"]').addEventListener('click', resetAll);

        container.querySelectorAll('[data-tool-action]').forEach(function (el) {
            el.addEventListener('click', function () { handleTool(el.getAttribute('data-tool-action')); });
        });

        var fsDec = container.querySelector('[data-fs="dec"]');
        var fsInc = container.querySelector('[data-fs="inc"]');
        if (fsDec) fsDec.addEventListener('click', function (e) { e.stopPropagation(); changeFontSize(-10); });
        if (fsInc) fsInc.addEventListener('click', function (e) { e.stopPropagation(); changeFontSize(10); });

        container.querySelectorAll('[data-contrast]').forEach(function (btn) {
            btn.addEventListener('click', function (e) { e.stopPropagation(); setContrast(btn.getAttribute('data-contrast')); });
        });

        document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && isOpen) toggle(); });
    }

    function renderTools() {
        var html = '';

        if (tools.fontSize) {
            html += '<div class="ago-a11y-tool" data-tool-action="fontSize" tabindex="0" role="button">' +
                '<span class="ago-a11y-tool-label"><svg viewBox="0 0 24 24"><path d="M2 4v3h5v12h3V7h5V4H2zm19 5h-9v3h3v7h3v-7h3V9z"/></svg> ' + esc(i18n.fontSize) + '</span>' +
                '<div class="ago-a11y-fontsize"><button data-fs="dec" aria-label="Decrease">A-</button><span>' + fontSize + '%</span><button data-fs="inc" aria-label="Increase">A+</button></div>' +
            '</div>';
        }

        if (tools.contrast) {
            html += '<div class="ago-a11y-tool" tabindex="0">' +
                '<span class="ago-a11y-tool-label"><svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18V4c4.41 0 8 3.59 8 8s-3.59 8-8 8z"/></svg> ' + esc(i18n.contrast) + '</span>' +
                '<div class="ago-a11y-contrast">' +
                    '<button data-contrast="normal" class="' + (contrastMode === 'normal' ? 'active' : '') + '">' + esc(i18n.contrastNormal) + '</button>' +
                    '<button data-contrast="high" class="' + (contrastMode === 'high' ? 'active' : '') + '">' + esc(i18n.contrastHigh) + '</button>' +
                    '<button data-contrast="inverted" class="' + (contrastMode === 'inverted' ? 'active' : '') + '">' + esc(i18n.contrastInverted) + '</button>' +
                    '<button data-contrast="dark" class="' + (contrastMode === 'dark' ? 'active' : '') + '">' + esc(i18n.contrastDark) + '</button>' +
                '</div>' +
            '</div>';
        }

        var simpleTools = [
            { key: 'dyslexiaFont', icon: '<svg viewBox="0 0 24 24"><path d="M9.93 13.5h4.14L12 7.98 9.93 13.5zM20 2H4c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-4.05 16.5l-1.14-3H9.17l-1.12 3H5.96l5.11-13h1.86l5.11 13h-2.09z"/></svg>' },
            { key: 'spacing', icon: '<svg viewBox="0 0 24 24"><path d="M3 21h18v-2H3v2zm0-4h18v-2H3v2zm0-4h18v-2H3v2zm0-4h18V7H3v2zm0-6v2h18V3H3z"/></svg>' },
            { key: 'bigCursor', icon: '<svg viewBox="0 0 24 24"><path d="M13.64 21.97C13.14 22.21 12.54 22 12.31 21.5l-2.8-6.45-4.18 4.17c-.39.39-1.02.39-1.41 0-.2-.2-.29-.45-.29-.71V3.41c0-.89 1.08-1.34 1.71-.71l14.59 14.59c.63.63.18 1.71-.71 1.71h-4.7l-.88 2.97z"/></svg>' },
            { key: 'readingGuide', icon: '<svg viewBox="0 0 24 24"><path d="M3 13h18v-2H3v2z"/></svg>' },
            { key: 'highlightLinks', icon: '<svg viewBox="0 0 24 24"><path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z"/></svg>' },
            { key: 'stopAnimations', icon: '<svg viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>' },
            { key: 'grayscale', icon: '<svg viewBox="0 0 24 24"><path d="M17.66 7.93L12 2.27 6.34 7.93c-3.12 3.12-3.12 8.19 0 11.31A7.98 7.98 0 0012 21.58a7.98 7.98 0 005.66-2.34c3.12-3.12 3.12-8.19 0-11.31zM12 19.59c-1.6 0-3.11-.62-4.24-1.76C6.62 16.69 6 15.19 6 13.59s.62-3.11 1.76-4.24L12 5.1v14.49z"/></svg>' },
            { key: 'hideImages', icon: '<svg viewBox="0 0 24 24"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>' },
            { key: 'textToSpeech', icon: '<svg viewBox="0 0 24 24"><path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/></svg>' },
        ];

        simpleTools.forEach(function (t) {
            if (!tools[t.key]) return;
            var isActive = prefs[t.key] || false;
            html += '<div class="ago-a11y-tool' + (isActive ? ' active' : '') + '" data-tool-action="' + t.key + '" tabindex="0" role="button" aria-pressed="' + isActive + '">' +
                '<span class="ago-a11y-tool-label">' + t.icon + ' ' + esc(i18n[t.key] || t.key) + '</span>' +
                '<div class="ago-a11y-tool-indicator"></div>' +
            '</div>';
        });

        return html;
    }

    function toggle() {
        isOpen = !isOpen;
        var panel = container.querySelector('.ago-a11y-panel');
        var toggleBtn = container.querySelector('.ago-a11y-toggle');
        panel.classList.toggle('open', isOpen);
        toggleBtn.style.display = isOpen ? 'none' : '';
        if (isOpen) panel.querySelector('[data-action="close"]').focus();
    }

    function handleTool(key) {
        if (key === 'fontSize') return;
        if (key === 'textToSpeech') { handleTTS(); return; }
        prefs[key] = !prefs[key];
        save();
        applyAll();
        var el = container.querySelector('[data-tool-action="' + key + '"]');
        if (el) { el.classList.toggle('active', prefs[key]); el.setAttribute('aria-pressed', prefs[key]); }
    }

    function changeFontSize(delta) {
        fontSize = Math.max(80, Math.min(200, fontSize + delta));
        prefs.fontSize = fontSize;
        save();
        document.documentElement.style.fontSize = fontSize + '%';
        var label = container.querySelector('[data-tool-action="fontSize"] .ago-a11y-fontsize span');
        if (label) label.textContent = fontSize + '%';
    }

    function setContrast(mode) {
        contrastMode = mode;
        prefs.contrast = mode;
        save();
        document.body.classList.remove('ago-high-contrast', 'ago-inverted', 'ago-dark-mode');
        if (mode === 'high') document.body.classList.add('ago-high-contrast');
        else if (mode === 'inverted') document.body.classList.add('ago-inverted');
        else if (mode === 'dark') document.body.classList.add('ago-dark-mode');
        container.querySelectorAll('[data-contrast]').forEach(function (b) { b.classList.toggle('active', b.getAttribute('data-contrast') === mode); });
    }

    function handleTTS() {
        if (!speechSynth) return;
        if (speechSynth.speaking) { speechSynth.cancel(); return; }
        var text = window.getSelection().toString();
        if (!text) { alert(i18n.ttsPlay); return; }
        var utter = new SpeechSynthesisUtterance(text);
        utter.lang = document.documentElement.lang || 'en';
        speechSynth.speak(utter);
    }

    function applyAll() {
        var b = document.body;
        if (prefs.fontSize && prefs.fontSize !== 100) document.documentElement.style.fontSize = prefs.fontSize + '%';
        b.classList.remove('ago-high-contrast', 'ago-inverted', 'ago-dark-mode');
        if (prefs.contrast === 'high') b.classList.add('ago-high-contrast');
        else if (prefs.contrast === 'inverted') b.classList.add('ago-inverted');
        else if (prefs.contrast === 'dark') b.classList.add('ago-dark-mode');
        b.classList.toggle('ago-dyslexia-font', !!prefs.dyslexiaFont);
        b.classList.toggle('ago-spacing', !!prefs.spacing);
        b.classList.toggle('ago-big-cursor', !!prefs.bigCursor);
        b.classList.toggle('ago-highlight-links', !!prefs.highlightLinks);
        b.classList.toggle('ago-stop-animations', !!prefs.stopAnimations);
        b.classList.toggle('ago-grayscale', !!prefs.grayscale);
        b.classList.toggle('ago-hide-images', !!prefs.hideImages);
        if (prefs.readingGuide) {
            if (!readingGuideEl) {
                readingGuideEl = document.createElement('div');
                readingGuideEl.className = 'ago-reading-guide';
                readingGuideEl.style.background = color;
                document.body.appendChild(readingGuideEl);
                document.addEventListener('mousemove', moveGuide);
            }
        } else if (readingGuideEl) {
            readingGuideEl.remove();
            readingGuideEl = null;
            document.removeEventListener('mousemove', moveGuide);
        }
    }

    function moveGuide(e) { if (readingGuideEl) readingGuideEl.style.top = e.clientY + 'px'; }

    function resetAll() {
        prefs = {};
        fontSize = 100;
        contrastMode = 'normal';
        localStorage.removeItem(STORAGE_KEY);
        document.documentElement.style.fontSize = '';
        var b = document.body;
        b.classList.remove('ago-high-contrast', 'ago-inverted', 'ago-dark-mode', 'ago-dyslexia-font', 'ago-spacing', 'ago-big-cursor', 'ago-highlight-links', 'ago-stop-animations', 'ago-grayscale', 'ago-hide-images');
        if (readingGuideEl) { readingGuideEl.remove(); readingGuideEl = null; }
        render();
    }

    function save() { localStorage.setItem(STORAGE_KEY, JSON.stringify(prefs)); }
    function esc(s) { var d = document.createElement('div'); d.appendChild(document.createTextNode(s || '')); return d.innerHTML; }
    }
})();
