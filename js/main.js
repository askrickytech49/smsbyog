/**
 * main.js — Server 1 (TigerSMS)
 * 3-step buy flow: Country → Service → OTP
 */

// ── UTILITIES ─────────────────────────────────────────────────────────────────

function copyToClipboard(text) {
    text = String(text).replace(/^\+/, '').slice(0, 12);
    navigator.clipboard ? navigator.clipboard.writeText(text) : legacyCopy(text);
    Notiflix.Notify.success('Copied: ' + text);
}

function legacyCopy(text) {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed'; ta.style.left = '-9999px';
    document.body.appendChild(ta); ta.select();
    document.execCommand('copy');
    document.body.removeChild(ta);
}

function getCookie(name) {
    const match = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'));
    return match ? decodeURIComponent(match[1]) : '';
}

function setCookie(name, value, minutes) {
    const exp = new Date(Date.now() + minutes * 60000).toUTCString();
    document.cookie = `${name}=${encodeURIComponent(value)}; expires=${exp}; path=/`;
}

function removeCookie(name) {
    document.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
}

function playAudio(file) { try { new Audio(file).play(); } catch(e) {} }

// ── STEP NAVIGATION ────────────────────────────────────────────────────────────

function goStep(n) {
    document.querySelectorAll('.step-panel').forEach((p, i) => {
        p.classList.toggle('active', i + 1 === n);
    });
    document.querySelectorAll('.step-item').forEach((item, i) => {
        item.classList.remove('active', 'done');
        if (i + 1 < n)  item.classList.add('done');
        if (i + 1 === n) item.classList.add('active');
    });
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ── STEP 1 — COUNTRY ──────────────────────────────────────────────────────────

function selectCountry(card) {
    document.querySelectorAll('.country-card').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    const server = card.dataset.server;
    const name   = card.querySelector('.country-name').textContent;
    document.getElementById('server_no').value = server;
    document.getElementById('step2-title').textContent = 'Services for ' + name;
    loadServices(server);
    goStep(2);
}

// ── STEP 2 — SERVICES ─────────────────────────────────────────────────────────

let selectedServiceId    = '';
let selectedServiceName  = '';
let selectedServicePrice = 0;

function loadServices(server) {
    const token = document.getElementById('token').value;
    const list  = document.getElementById('service-list');

    // Show skeletons
    list.innerHTML = '<div class="skeleton-row"></div><div class="skeleton-row"></div><div class="skeleton-row"></div>';
    document.getElementById('buy-btn').disabled = true;
    document.getElementById('selected-name').textContent  = '—';
    document.getElementById('selected-price').textContent = '₦0';
    selectedServiceId = '';

    $.ajax({
        type: 'GET',
        url:  'api/service/getService',
        data: { token, server },
        dataType: 'json',
        success: function(res) {
            list.innerHTML = '';
            const services = res.service || [];

            if (!services.length) {
                list.innerHTML = '<div class="empty-state"><img src="https://cdn-icons-png.flaticon.com/512/5089/5089767.png"><p>No services available for this country.</p></div>';
                return;
            }

            services.forEach(svc => {
                const stock   = parseInt(svc.stock) || 0;
                const stockHtml = stock > 0
                    ? `<span class="service-stock" style="color:${stock < 10 ? '#ef4444' : '#10b981'}">${stock} available</span>`
                    : `<span class="service-stock" style="color:#9ca3af">Limited stock</span>`;

                const row = document.createElement('div');
                row.className = 'service-row';
                row.dataset.id    = svc.id;
                row.dataset.name  = svc.service_name;
                row.dataset.price = svc.service_price;
                row.innerHTML = `
                    <div class="service-info">
                        <div class="service-name">${svc.service_name}</div>
                        ${stockHtml}
                    </div>
                    <div class="service-price">₦${Number(svc.service_price).toLocaleString()}</div>`;
                row.addEventListener('click', () => selectService(row));
                list.appendChild(row);
            });

            // Re-attach search filter
            document.getElementById('service-search').value = '';
            attachServiceSearch();
        },
        error: function() {
            list.innerHTML = '<div class="empty-state"><p>Failed to load services. Please try again.</p></div>';
        }
    });
}

function selectService(row) {
    document.querySelectorAll('.service-row').forEach(r => r.classList.remove('selected'));
    row.classList.add('selected');
    selectedServiceId    = row.dataset.id;
    selectedServiceName  = row.dataset.name;
    selectedServicePrice = row.dataset.price;
    document.getElementById('service_id').value            = selectedServiceId;
    document.getElementById('selected-name').textContent   = selectedServiceName;
    document.getElementById('selected-price').textContent  = '₦' + Number(selectedServicePrice).toLocaleString();
    document.getElementById('buy-btn').disabled = false;
}

function attachServiceSearch() {
    const input = document.getElementById('service-search');
    input.oninput = function() {
        const q = this.value.toLowerCase().trim();
        let found = 0;
        document.querySelectorAll('.service-row').forEach(row => {
            const match = row.dataset.name.toLowerCase().includes(q);
            row.style.display = match ? '' : 'none';
            if (match) found++;
        });
        const existing = document.getElementById('no-result-msg');
        if (!found && !existing) {
            const d = document.createElement('div');
            d.id = 'no-result-msg';
            d.className = 'empty-state';
            d.innerHTML = '<img src="https://cdn-icons-png.flaticon.com/512/6357/6357033.png"><p>No results found.</p>';
            document.getElementById('service-list').appendChild(d);
        } else if (found && existing) {
            existing.remove();
        }
    };
}

// ── STEP 2 → BUY ──────────────────────────────────────────────────────────────

function doBuy() {
    const token   = document.getElementById('token').value;
    const server  = document.getElementById('server_no').value;
    const service = document.getElementById('service_id').value;

    if (!service) { Notiflix.Notify.warning('Please select a service first.'); return; }

    const btn = document.getElementById('buy-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Buying…';

    $.ajax({
        type: 'GET',
        url:  'api/service/buynumber',
        data: { token, server, service },
        dataType: 'json',
        success: function(res) {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-cart-plus-fill"></i> Buy Number';
            if (res.status == 200 || res.status === '200') {
                Notiflix.Notify.success(res.message || 'Number purchased!');
                const h = document.getElementById('step3-title-text');
                const p = document.getElementById('step3-subtitle');
                if (h) h.textContent = 'Number Purchased!';
                if (p) p.textContent = 'Waiting for your OTP code…';
                goStep(3);
                checkOrder();
                user_balance(token);
            } else {
                Notiflix.Notify.failure(res.message || 'Purchase failed. Try again.');
            }
        },
        error: function() {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-cart-plus-fill"></i> Buy Number';
            Notiflix.Notify.failure('Network error. Please try again.');
        }
    });
}

// ── STEP 3 — OTP CARD ─────────────────────────────────────────────────────────

let smsIntervals = {};

function renderOtpCard(item) {
    const card = document.createElement('div');
    card.className = 'sms-card';
    card.innerHTML = `
        <div class="sms-card-inner">
            <div class="sms-top-row">
                <div class="sms-service-badge"><i class="bi bi-phone-fill"></i> ${item.app || 'Virtual Number'}</div>
                <div class="sms-timer-pill" id="t_${item.id}"><i class="bi bi-clock"></i> --:--</div>
            </div>
            <div class="sms-number-row">
                <span class="sms-number-text">+${item.number}</span>
                <button class="sms-copy-btn" onclick="copyToClipboard('+${item.number}')">
                    <i class="bi bi-copy"></i> Copy
                </button>
            </div>
            <div class="sms-divider"></div>
            <div class="sms-body-label"><i class="bi bi-chat-text"></i> OTP / SMS Code</div>
            <div class="sms-code-box" id="sms_${item.id}">
                ${item.sms
                    ? `<span class="sms-code-value">${item.sms}</span>`
                    : `<span class="sms-waiting"><span class="sms-dot"></span><span class="sms-dot"></span><span class="sms-dot"></span>&nbsp;Waiting for SMS…</span>`}
            </div>
            <div class="sms-card-footer">
                <div class="sms-price-tag"><i class="bi bi-wallet2"></i> ₦${Number(item.amount).toLocaleString()}</div>
                <button class="sms-cancel-btn" id="cancel_${item.id}"
                    onclick="cancelNumber('${item.id}', 'cancel_${item.id}', '${item.number}')">
                    <i class="bi bi-x-circle"></i> Cancel & Refund
                </button>
            </div>
        </div>`;
    return card;
}

function countdownTimer(durationMs, elementId) {
    const el = document.getElementById(elementId);
    if (!el) return;
    const interval = setInterval(() => {
        durationMs -= 1000;
        if (durationMs >= 0) {
            const m = String(Math.floor(durationMs / 60000)).padStart(2, '0');
            const s = String(Math.floor((durationMs % 60000) / 1000)).padStart(2, '0');
            el.innerHTML = `<i class="bi bi-clock"></i> ${m}:${s}`;
            el.classList.remove('sms-timer-expired');
        } else {
            clearInterval(interval);
            el.innerHTML = '<i class="bi bi-x-circle"></i> Expired';
            el.classList.add('sms-timer-expired');
        }
    }, 1000);
}

function setSMSInterval(elementId, orderId, token, number) {
    if (!smsIntervals[elementId]) smsIntervals[elementId] = [];
    const interval = setInterval(() => {
        $.ajax({
            type: 'GET',
            url:  'api/service/getMessage',
            data: { order_id: orderId, token },
            dataType: 'json',
            error: function() {},
            success: function(data) {
                const box = document.getElementById(elementId);
                if (!box) { clearInterval(interval); return; }
                if (data.status === '300') {
                    box.innerHTML = '<span class="sms-waiting"><span class="sms-dot"></span><span class="sms-dot"></span><span class="sms-dot"></span>&nbsp;Waiting for SMS…</span>';
                } else if (data.status === '200') {
                    box.innerHTML = `<span class="sms-code-value">${data.sms}</span>`;
                    const old = getCookie(orderId);
                    if (old !== data.sms) {
                        Notiflix.Notify.success('OTP received on +' + number);
                        setCookie(orderId, data.sms, 20);
                        playAudio('bell.mp3');
                    }
                    smsIntervals[elementId].forEach(clearInterval);
                } else {
                    box.innerHTML = '<span class="sms-waiting"><span class="sms-dot"></span><span class="sms-dot"></span><span class="sms-dot"></span>&nbsp;Waiting for SMS…</span>';
                }
            }
        });
    }, 2000);
    smsIntervals[elementId].push(interval);
}

function checkOrder() {
    const token     = document.getElementById('token').value;
    const container = document.getElementById('card-container');

    // Stop all running intervals
    Object.values(smsIntervals).forEach(arr => arr.forEach(clearInterval));
    smsIntervals = {};

    // Show a neutral loading state without jumping to step 1 first
    container.innerHTML = '<div class="skeleton-row"></div><div class="skeleton-row"></div>';

    $.ajax({
        type: 'GET',
        url:  'api/service/ActiveNumber',
        data: { token },
        dataType: 'json',
        success: function(res) {
            user_balance(token);
            container.innerHTML = '';
            const items = res.data || [];

            if (!items.length) {
                // No active numbers — only go to step 1 if we are currently on step 3
                // (i.e. all numbers expired). If on step 1 or 2, stay there.
                if (document.getElementById('step3').classList.contains('active')) {
                    container.innerHTML = '<div class="no-active-numbers"><i class="bi bi-phone"></i><p>No active numbers. Buy a new one below.</p></div>';
                    setTimeout(() => goStep(1), 2000);
                }
                return;
            }

            // Has active numbers — always show step 3
            items.forEach(item => {
                container.appendChild(renderOtpCard(item));
                countdownTimer(item.left_time, 't_' + item.id);
                setSMSInterval('sms_' + item.id, item.id, token, item.number);
            });
            goStep(3);
        },
        error: function() {
            container.innerHTML = '<div class="no-active-numbers"><p>Failed to load numbers.</p></div>';
        }
    });
}

function cancelNumber(orderId, btnId, number) {
    Notiflix.Confirm.show('Confirm Cancel',
        'Cancel number +' + number + '? You will be refunded.',
        'Yes, Cancel', 'No',
        function() {
            const token = document.getElementById('token').value;
            const btn   = document.getElementById(btnId);
            if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>'; }
            $.ajax({
                type: 'GET',
                url:  'api/service/cancelNumber',
                data: { token, order_id: orderId },
                dataType: 'json',
                success: function(res) {
                    if (res.status == 200 || res.status === '200') {
                        Notiflix.Notify.success(res.message || 'Refunded.');
                        removeCookie(orderId);
                    } else {
                        Notiflix.Notify.failure(res.message || 'Error cancelling.');
                    }
                    user_balance(token);
                    checkOrder();
                },
                error: function() {
                    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="bi bi-x-circle"></i> Cancel & Refund'; }
                    Notiflix.Notify.failure('Network error.');
                }
            });
        }
    );
}

function buyAnother() {
    goStep(1);
    document.getElementById('card-container').innerHTML = '';
}

function user_balance(token) {
    $.ajax({
        type: 'POST',
        url:  'api/auth/session',
        data: { token },
        success: function(res) {
            try {
                const d  = JSON.parse(res);
                const el = document.getElementById('current_balance');
                if (el) el.textContent = '₦' + d.balance;
            } catch(e) {}
        }
    });
}

// ── INIT ──────────────────────────────────────────────────────────────────────
// Safe init — works whether jQuery loaded from CDN or fallback
function initPage() {
    // If user already has active numbers on this server, jump to step 3
    checkOrder();
}

if (typeof $ === 'function') {
    $(initPage);
} else {
    document.addEventListener('DOMContentLoaded', initPage);
}
