/* aGo Access — Admin JS */
(function () {
    'use strict';
    var $ = document.querySelector.bind(document);
    var $$ = document.querySelectorAll.bind(document);
    var restUrl = (window.agoAccess || {}).restUrl || '';
    var nonce = (window.agoAccess || {}).nonce || '';

    var saveBtn = $('#ago-save-settings');
    if (!saveBtn) return;

    // Save
    saveBtn.addEventListener('click', function () {
        var data = {
            enabled: ($('#ago-enabled') || {}).checked || false,
            position: ($('#ago-position') || {}).value || 'bottom-right',
            color: ($('#ago-color') || {}).value || '#2271b1',
            shape: ($('#ago-shape') || {}).value || 'circle',
            icon: ($('#ago-icon') || {}).value || 'person',
            tools: {},
            auto_fixes: {},
        };
        $$('[data-tool]').forEach(function (cb) { data.tools[cb.getAttribute('data-tool')] = cb.checked; });
        $$('[data-fix]').forEach(function (cb) { data.auto_fixes[cb.getAttribute('data-fix')] = cb.checked; });

        saveBtn.disabled = true;
        saveBtn.textContent = 'Saving...';
        fetch(restUrl + '/settings', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': nonce }, body: JSON.stringify(data) })
        .then(function (r) { return r.json(); })
        .then(function (res) { showStatus(res.saved ? 'success' : 'error', res.saved ? 'Settings saved.' : 'Error.'); })
        .catch(function (e) { showStatus('error', e.message); })
        .finally(function () { saveBtn.disabled = false; saveBtn.textContent = 'Save Settings'; });
    });

    // Scan
    var scanBtn = $('#ago-scan-btn');
    if (scanBtn) {
        scanBtn.addEventListener('click', function () {
            var results = $('#ago-scan-results');
            scanBtn.disabled = true;
            scanBtn.textContent = 'Scanning...';
            results.innerHTML = '';

            fetch(restUrl + '/scan', { headers: { 'X-WP-Nonce': nonce } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var score = data.score || 0;
                var cls = score >= 80 ? 'good' : score >= 50 ? 'ok' : 'bad';
                var html = '<div style="display:flex;align-items:center;margin-bottom:16px"><div class="ago-score ' + cls + '">' + score + '</div><div><strong>Score: ' + score + '/100</strong><br><span style="font-size:12px;color:#666">Scanned: ' + (data.scanned_at || 'now') + '</span></div></div>';

                (data.issues || []).forEach(function (issue) {
                    html += '<div class="ago-issue ' + issue.type + '"><strong>' + esc(issue.msg) + '</strong>';
                    if (issue.fix) html += '<div class="fix">' + esc(issue.fix) + '</div>';
                    html += '</div>';
                });

                results.innerHTML = html;
            })
            .catch(function () { results.innerHTML = '<div class="ago-issue error"><strong>Error scanning</strong></div>'; })
            .finally(function () { scanBtn.disabled = false; scanBtn.textContent = 'Scan Now'; });
        });
    }

    function showStatus(type, msg) {
        var box = $('#ago-status');
        if (!box) return;
        box.style.display = 'block'; box.className = type; box.textContent = msg;
        setTimeout(function () { box.style.display = 'none'; }, 3000);
    }

    function esc(s) { var d = document.createElement('div'); d.appendChild(document.createTextNode(s || '')); return d.innerHTML; }
})();
