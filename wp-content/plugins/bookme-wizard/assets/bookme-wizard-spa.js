/**
 * bookme-wizard-spa.js
 * Minimal SPA to handle select-master -> select-service -> select-time
 * Uses REST endpoints: /wp-json/bookme/v1/staff/{vendor}, /services?vendor=&staff=, /booking-form?service_id=&staff_id=&vendor_id=
 */

(function(){
    if (typeof BOOKME_WIZARD_DATA === 'undefined') {
        console.error('BOOKME_WIZARD_DATA missing');
        return;
    }

    const restRoot = BOOKME_WIZARD_DATA.rest_root.replace(/\/+$/, '/') ;
    const containerId = 'bookme-wizard-root';

    // Create container if not present
    let root = document.getElementById(containerId);
    if (!root) {
        root = document.createElement('div');
        root.id = containerId;
        // You can append to body or to a specific element. If you have a page, add a placeholder there.
        document.body.prepend(root);
    }

    // Utility
    function el(str, cls) {
        const d = document.createElement('div');
        d.innerHTML = str;
        if (cls) d.classList.add(cls);
        return d.firstElementChild || d;
    }
    function fetchJSON(path) {
        return fetch(path, { credentials: 'same-origin' }).then(r => r.json());
    }

    // Router: read URL to decide step
    // expected path: /company/{vendor}/personal/select-master  (or select-service/select-time)
    function parseUrl() {
        const path = location.pathname.replace(/\/+$/, '');
        const parts = path.split('/').filter(Boolean);
        // find 'company' in parts
        const idx = parts.indexOf('company');
        if (idx === -1) return null;
        const vendor = parts[idx + 1];
        const personal = parts[idx + 2]; // should be 'personal'
        const step = parts[idx + 3]; // 'select-master' etc
        return {
            vendor_id: vendor ? parseInt(vendor, 10) : null,
            step: step || 'select-master'
        };
    }

    // Push URL for SPA navigation
    function pushUrl(vendorId, step, query) {
        const path = `/company/${vendorId}/personal/${step}`;
        const q = query ? ('?' + new URLSearchParams(query).toString()) : '';
        history.pushState({}, '', path + q);
    }

    // Render functions
    async function renderMaster(vendorId) {
        root.innerHTML = `<div class="bm-step"><h2>Choose a staff</h2><div class="bm-list" id="bm-staff-list">Loading staff…</div></div>`;
        try {
            const data = await fetchJSON(`${restRoot}staff/${vendorId}`);
            const list = document.getElementById('bm-staff-list');
            if (!data || data.length === 0) {
                list.innerHTML = '<p>No staff found for this vendor.</p>';
                return;
            }
            list.innerHTML = '';
            data.forEach( s => {
                const card = document.createElement('a');
                card.className = 'bm-card';
                card.href = '#';
                card.dataset.staffId = s.id;
                card.innerHTML = `<div class="bm-card-title">${escapeHTML(s.name)}</div><div class="bm-card-sub">ID #${s.id}</div>`;
                card.addEventListener('click', (e) => {
                    e.preventDefault();
                    // navigate to services
                    pushUrl(vendorId, 'select-service', { staff_id: s.id });
                    renderService(vendorId, s.id);
                });
                list.appendChild(card);
            });
        } catch (err) {
            document.getElementById('bm-staff-list').innerText = 'Failed to load staff';
            console.error(err);
        }
    }

    async function renderService(vendorId, staffId) {
        root.innerHTML = `<div class="bm-step"><h2>Choose a service</h2><a class="bm-back" href="#" id="bm-back">← Back</a><div id="bm-services">Loading services…</div></div>`;
        document.getElementById('bm-back').addEventListener('click', (e) => {
            e.preventDefault();
            pushUrl(vendorId, 'select-master');
            renderMaster(vendorId);
        });

        try {
            const data = await fetchJSON(`${restRoot}services?vendor=${vendorId}&staff=${staffId}`);
            const box = document.getElementById('bm-services');
            if (!data || data.length === 0) {
                box.innerHTML = '<p>No services assigned to this staff.</p>';
                return;
            }
            box.innerHTML = '';
            data.forEach( svc => {
                const card = document.createElement('a');
                card.className = 'bm-card';
                card.href = '#';
                card.dataset.serviceId = svc.id;
                card.innerHTML = `<div class="bm-card-title">${escapeHTML(svc.name)}</div><div class="bm-card-sub">Duration: ${escapeHTML(svc.duration || '—')} • Price: ${escapeHTML(svc.price || '—')}</div>`;
                card.addEventListener('click', (e) => {
                    e.preventDefault();
                    pushUrl(vendorId, 'select-time', { staff_id: staffId, service_id: svc.id });
                    renderTime(vendorId, staffId, svc.id);
                });
                box.appendChild(card);
            });
        } catch (err) {
            document.getElementById('bm-services').innerText = 'Failed to load services';
            console.error(err);
        }
    }

    async function renderTime(vendorId, staffId, serviceId) {
        root.innerHTML = `<div class="bm-step"><h2>Choose time</h2><a class="bm-back" href="#" id="bm-back">← Back</a><div id="bm-time">Loading booking form…</div></div>`;
        document.getElementById('bm-back').addEventListener('click', (e) => {
            e.preventDefault();
            pushUrl(vendorId, 'select-service', { staff_id: staffId });
            renderService(vendorId, staffId);
        });

        try {
            console.log(`${restRoot}booking-form?service_id=${serviceId}&staff_id=${staffId}&vendor_id=${vendorId}`)
            const resp = await fetchJSON(`${restRoot}booking-form?service_id=${serviceId}&staff_id=${staffId}&vendor_id=${vendorId}`);
            const html = resp && resp.html ? resp.html : '<p>Booking form not available.</p>';
            console.log(html);
            const timeBox = document.getElementById('bm-time');
            // insert HTML and ensure the form will submit via AJAX and include our hidden fields
            timeBox.innerHTML = html;

            // Find the booking form (WooCommerce booking add-to-cart form usually has class 'cart' or 'form.cart')
            const form = timeBox.querySelector('form.cart') || timeBox.querySelector('form');
            if (!form) {
                // if no form, maybe product page markup; show fallback
                const add = document.createElement('div');
                add.innerHTML = '<p>Unable to find booking form on server response.</p>';
                timeBox.appendChild(add);
                return;
            }

            // Ensure our hidden inputs exist in the form (some templates already added them)
            if (!form.querySelector('input[name="bookme_staff_id"]')) {
                const h1 = document.createElement('input');
                h1.type = 'hidden'; h1.name = 'bookme_staff_id'; h1.value = staffId;
                form.appendChild(h1);
            }
            if (!form.querySelector('input[name="bookme_vendor_id"]')) {
                const h2 = document.createElement('input');
                h2.type = 'hidden'; h2.name = 'bookme_vendor_id'; h2.value = vendorId;
                form.appendChild(h2);
            }

            // Hook submit: submit to WooCommerce AJAX add-to-cart endpoint
            form.addEventListener('submit', async function (ev) {
                ev.preventDefault();

                // Build FormData
                const fd = new FormData(form);
                // WooCommerce add-to-cart via wc-ajax
                // If booking product uses special fields, they are included by the template
                // We need to send action=woocommerce_add_to_cart and product_id via wc-ajax endpoint
                // The simplest: post to /?wc-ajax=add_to_cart with body form data + add-to-cart param
                // Ensure add-to-cart is present
                if (!fd.has('add-to-cart')) {
                    // try to get product id from data or the page
                    const productId = form.querySelector('input[name="product_id"]') ? form.querySelector('input[name="product_id"]').value : serviceId;
                    fd.set('add-to-cart', productId);
                }

                // perform fetch
                try {
                    // wc-ajax endpoint is at /?wc-ajax=add_to_cart
                    const ajaxUrl = (BOOKME_WIZARD_DATA && BOOKME_WIZARD_DATA.home_url ? BOOKME_WIZARD_DATA.home_url : '/') + '?wc-ajax=add_to_cart';
                    const res = await fetch(ajaxUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        body: fd
                    });
                    const text = await res.text();

                    // wc-ajax response can be HTML or JSON depending on the site; a safe approach is to redirect to checkout/cart
                    // If the add-to-cart succeeded, redirect to cart or checkout
                    // We'll try to detect success by status code or text
                    // Simpler: navigate to checkout
                    window.location.href = (BOOKME_WIZARD_DATA && BOOKME_WIZARD_DATA.home_url ? BOOKME_WIZARD_DATA.home_url : '/') + 'checkout/';
                } catch (err) {
                    alert('Failed to add to cart. Try again.');
                    console.error(err);
                }
            });

        } catch (err) {
            document.getElementById('bm-time').innerText = 'Failed to load booking form';
            console.error(err);
        }
    }

    // helper
    function escapeHTML(s) {
        if (typeof s !== 'string') return s;
        return s.replace(/[&<>"']/g, function(m) { return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[m]; });
    }

    // SPA entry: read URL and show step
    function boot() {
        const parsed = parseUrl();
        if (!parsed || !parsed.vendor_id) {
            root.innerHTML = '<div class="bm-start"><p>Invalid booking link. Use /company/{vendor_id}/personal/select-master</p></div>';
            return;
        }
        const vendor = parsed.vendor_id;
        const step = parsed.step || 'select-master';
        // read query params
        const qs = Object.fromEntries(new URLSearchParams(location.search).entries());
        if (step === 'select-master') {
            renderMaster(vendor);
        } else if (step === 'select-service') {
            const staff = parseInt(qs.staff_id || qs.staff || '', 10) || null;
            if (!staff) {
                pushUrl(vendor, 'select-master');
                renderMaster(vendor);
                return;
            }
            renderService(vendor, staff);
        } else if (step === 'select-time') {
            const staff = parseInt(qs.staff_id || qs.staff || '', 10) || null;
            const service = parseInt(qs.service_id || qs.service || '', 10) || null;
            if (!staff || !service) {
                pushUrl(vendor, 'select-master');
                renderMaster(vendor);
                return;
            }
            renderTime(vendor, staff, service);
        } else {
            renderMaster(vendor);
        }
    }

    // Support back/forward
    window.addEventListener('popstate', boot);

    // Auto-boot
    document.addEventListener('DOMContentLoaded', boot);

})();
