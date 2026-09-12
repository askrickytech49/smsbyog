/**
 * main.js — Server 1 (TigerSMS)
 * SERVICE-FIRST buy flow: Service → Country → OTP
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

// ── STEP 1 — SERVICE (first step now) ─────────────────────────────────────────

let selectedServiceId   = '';
let selectedServiceName = '';
let selectedCountryCode = '';
let selectedCountryName = '';
let selectedPrice       = 0;

function loadAllServices() {
    const token = document.getElementById('token').value;
    const list  = document.getElementById('service-list');

    list.innerHTML = '<div class="skeleton-row"></div><div class="skeleton-row"></div><div class="skeleton-row"></div><div class="skeleton-row"></div><div class="skeleton-row"></div>';

    $.ajax({
        type: 'GET',
        url:  'api/service/getServices1',
        data: { token },
        dataType: 'json',
        success: function(res) {
            list.innerHTML = '';
            const services = res.service || [];

            if (!services.length) {
                list.innerHTML = '<div class="empty-state"><img src="https://cdn-icons-png.flaticon.com/512/5089/5089767.png"><p>No services available right now.</p></div>';
                return;
            }

            services.forEach(svc => {
                const row = document.createElement('div');
                row.className = 'service-row';
                row.dataset.id    = svc.id;
                row.dataset.name  = svc.service_name;
                row.innerHTML = `
                    <div class="service-info" style="display:flex; align-items:center; gap:12px;">
                        <img src="${svc.logo_url}" onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 24 24%22 fill=%22%23cbd5e1%22%3E%3Cpath d=%22M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm2.07-7.75l-.9.92C13.45 12.9 13 13.5 13 15h-2v-.5c0-1.1.45-2.1 1.17-2.83l1.24-1.26c.37-.36.59-.86.59-1.41 0-1.1-.9-2-2-2s-2 .9-2 2H8c0-2.21 1.79-4 4-4s4 1.79 4 4c0 .88-.36 1.68-.93 2.25z%22/%3E%3C/svg%3E';" style="width:30px; height:30px; border-radius:6px; object-fit:contain;" alt="${svc.service_name}">
                        <div>
                            <div class="service-name">${svc.service_name}</div>
                            <span class="service-stock" style="color:#10b981">${svc.country_count} countries</span>
                        </div>
                    </div>
                    <div class="service-price" style="color:#6b7280; font-size:0.85rem;">
                        <i class="bi bi-arrow-right-circle"></i>
                    </div>`;
                row.addEventListener('click', () => selectServiceStep1(svc.id, svc.service_name));
                list.appendChild(row);
            });

            // Attach search filter
            document.getElementById('service-search').value = '';
            attachServiceSearch();
        },
        error: function() {
            list.innerHTML = '<div class="empty-state"><p>Failed to load services. Please try again.</p></div>';
        }
    });
}

function selectServiceStep1(serviceId, serviceName) {
    selectedServiceId   = serviceId;
    selectedServiceName = serviceName;
    document.getElementById('service_id').value = serviceId;
    document.getElementById('step2-title').textContent = 'Countries for ' + serviceName;

    // Reset country selection
    selectedCountryCode = '';
    selectedCountryName = '';
    selectedPrice       = 0;
    document.getElementById('buy-btn').disabled = true;
    document.getElementById('selected-name').textContent  = '—';
    document.getElementById('selected-price').textContent = '₦0';

    loadCountriesForService(serviceId);
    goStep(2);
}

function attachServiceSearch() {
    const input = document.getElementById('service-search');
    input.oninput = function() {
        const q = this.value.toLowerCase().trim();
        let found = 0;
        document.querySelectorAll('#service-list .service-row').forEach(row => {
            const match = row.dataset.name.toLowerCase().includes(q);
            row.style.display = match ? '' : 'none';
            if (match) found++;
        });
        const existing = document.getElementById('no-result-msg');
        if (!found && !existing) {
            const d = document.createElement('div');
            d.id = 'no-result-msg';
            d.className = 'empty-state';
            d.innerHTML = '<img src="https://cdn-icons-png.flaticon.com/512/6357/6357033.png"><p>No services found.</p>';
            document.getElementById('service-list').appendChild(d);
        } else if (found && existing) {
            existing.remove();
        }
    };
}

// ── STEP 2 — COUNTRY (second step now) ────────────────────────────────────────

// ISO country code → flag-icons class map
const countryFlagMap = {
    'afghanistan':'af','albania':'al','algeria':'dz','angola':'ao','antigua and barbuda':'ag',
    'argentinas':'ar','armenia':'am','aruba':'aw','australia':'au','austria':'at','azerbaijan':'az',
    'bahamas':'bs','bahrain':'bh','bangladesh':'bd','barbados':'bb','belarus':'by','belgium':'be',
    'belize':'bz','benin':'bj','bhutane':'bt','bih':'ba','bolivia':'bo','botswana':'bw','brazil':'br',
    'brunei':'bn','bulgaria':'bg','burkina faso':'bf','burundi':'bi','cambodia':'kh','cameroon':'cm',
    'canada':'ca','cape verde':'cv','cayman islands':'ky','chad':'td','chile':'cl','china':'cn',
    'colombia':'co','comoros':'km','congo':'cg','costa rica':'cr','croatia':'hr','cyprus':'cy',
    'czech republic':'cz','denmark':'dk','djibouti':'dj','dominican republic':'do','ecuador':'ec',
    'egypt':'eg','el salvador':'sv','england':'gb','equatorial guinea':'gq','eritrea':'er','estonia':'ee',
    'ethiopia':'et','finland':'fi','france':'fr','french guiana':'gf','gabon':'ga','gambia':'gm',
    'georgia':'ge','germany':'de','ghana':'gh','greece':'gr','guadeloupe':'gp','guatemala':'gt',
    'guinea':'gn','guinea-bissau':'gw','guyana':'gy','haiti':'ht','honduras':'hn','hong kong':'hk',
    'hungary':'hu','iceland':'is','india':'in','indonesia':'id','iraq':'iq','ireland':'ie','israel':'il',
    'italy':'it','ivory coast':'ci','jamaica':'jm','japan':'jp','jordan':'jo','kazakhstan':'kz',
    'kenya':'ke','kuwait':'kw','kyrgyzstan':'kg','laos':'la','latvia':'lv','lebanon':'lb','lesotho':'ls',
    'liberia':'lr','lithuania':'lt','luxembourg':'lu','macau':'mo','madagascar':'mg','malawi':'mw',
    'malaysia':'my','maldives':'mv','mali':'ml','mauritania':'mr','mauritius':'mu','mexico':'mx',
    'moldova':'md','monaco':'mc','mongolia':'mn','montenegro':'me','montserrat':'ms','morocco':'ma',
    'mozambique':'mz','myanmar':'mm','namibia':'na','nepal':'np','netherlands':'nl','new caledonia':'nc',
    'new zealand':'nz','nicaragua':'ni','niger':'ne','nigeria':'ng','north macedonia':'mk','norway':'no',
    'oman':'om','pakistan':'pk','palestine':'ps','panama':'pa','papua new guinea':'pg','paraguay':'py',
    'peru':'pe','philippines':'ph','poland':'pl','portugal':'pt','puerto rico':'pr','qatar':'qa',
    'reunion':'re','romania':'ro','russia':'ru','rwanda':'rw','samoa':'ws','saudi arabia':'sa',
    'senegal':'sn','serbia':'rs','seychelles':'sc','sierra leone':'sl','singapore':'sg','slovakia':'sk',
    'slovenia':'si','somalia':'so','south africa':'za','south korea':'kr','spain':'es','sri lanka':'lk',
    'sudan':'sd','suriname':'sr','sweden':'se','switzerland':'ch','syrian arab republic':'sy',
    'taiwan':'tw','tajikistan':'tj','tanzania':'tz','thailand':'th','timor-leste':'tl','togo':'tg',
    'tonga':'to','trinidad and tobago':'tt','tunisia':'tn','turkey':'tr','turkmenistan':'tm',
    'turks and caicos islands':'tc','uganda':'ug','ukraine':'ua','united arab emirates':'ae',
    'uruguay':'uy','usa':'us','uzbekistan':'uz','venezuela':'ve','vietnam':'vn','yemen':'ye',
    'zambia':'zm','zimbabwe':'zw'
};

function getFlag(countryName) {
    const n = countryName.toLowerCase();
    return countryFlagMap[n] || 'un';
}

function loadCountriesForService(serviceId) {
    const token = document.getElementById('token').value;
    const list  = document.getElementById('country-list');

    list.innerHTML = '<div class="skeleton-row"></div><div class="skeleton-row"></div><div class="skeleton-row"></div>';

    $.ajax({
        type: 'GET',
        url:  'api/service/getCountriesForService',
        data: { token, service: serviceId },
        dataType: 'json',
        success: function(res) {
            list.innerHTML = '';
            const countries = res.countries || [];

            if (!countries.length) {
                list.innerHTML = '<div class="empty-state"><img src="https://cdn-icons-png.flaticon.com/512/5089/5089767.png"><p>No countries available for this service.</p></div>';
                return;
            }

            countries.forEach(c => {
                const iso = getFlag(c.country_name);
                const stock = c.stock;
                const stockColor = stock < 10 ? '#ef4444' : '#10b981';
                
                const row = document.createElement('div');
                row.className = 'service-row';
                row.dataset.code  = c.country_code;
                row.dataset.name  = c.country_name;
                row.dataset.price = c.price;
                row.innerHTML = `
                    <div class="service-info" style="display:flex; align-items:center; gap:10px;">
                        <span class="fi fi-${iso}" style="font-size:1.3rem;"></span>
                        <div>
                            <div class="service-name">${c.country_name}</div>
                            <span class="service-stock" style="color:${stockColor}">${stock} available</span>
                        </div>
                    </div>
                    <div class="service-price">₦${Number(c.price).toLocaleString()}</div>`;
                row.addEventListener('click', () => selectCountryStep2(row));
                list.appendChild(row);
            });

            // Reset country search
            document.getElementById('country-search').value = '';
        },
        error: function() {
            list.innerHTML = '<div class="empty-state"><p>Failed to load countries. Please try again.</p></div>';
        }
    });
}

function selectCountryStep2(row) {
    document.querySelectorAll('#country-list .service-row').forEach(r => r.classList.remove('selected'));
    row.classList.add('selected');
    
    selectedCountryCode = row.dataset.code;
    selectedCountryName = row.dataset.name;
    selectedPrice       = row.dataset.price;

    document.getElementById('server_no').value            = selectedCountryCode;
    document.getElementById('selected-name').textContent  = selectedServiceName + ' — ' + selectedCountryName;
    document.getElementById('selected-price').textContent = '₦' + Number(selectedPrice).toLocaleString();
    document.getElementById('buy-btn').disabled = false;
}

function filterCountryRows() {
    const q = document.getElementById('country-search').value.toLowerCase().trim();
    document.querySelectorAll('#country-list .service-row').forEach(row => {
        const match = row.dataset.name.toLowerCase().includes(q);
        row.style.display = match ? '' : 'none';
    });
}

// ── BUY ───────────────────────────────────────────────────────────────────────

function doBuy() {
    const token   = document.getElementById('token').value;
    const server  = document.getElementById('server_no').value;
    const service = document.getElementById('service_id').value;

    if (!service) { Notiflix.Notify.warning('Please select a service first.'); return; }
    if (!server)  { Notiflix.Notify.warning('Please select a country first.'); return; }

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
                <span class="sms-number-text">+${String(item.number).replace(/^\+/, '')}</span>
                <button class="sms-copy-btn" onclick="copyToClipboard('+${String(item.number).replace(/^\+/, '')}')">
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
        'Cancel number +' + String(number).replace(/^\+/, '') + '? You will be refunded.',
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
                if (el) el.textContent = '₦' + Number(d.balance).toLocaleString('en-NG', {minimumFractionDigits:2, maximumFractionDigits:2});
            } catch(e) {}
        }
    });
}

// ── INIT ──────────────────────────────────────────────────────────────────────
function initPage() {
    // Load all services for step 1
    loadAllServices();
    // If user already has active numbers on this server, jump to step 3
    checkOrder();
}

if (typeof $ === 'function') {
    $(initPage);
} else {
    document.addEventListener('DOMContentLoaded', initPage);
}
