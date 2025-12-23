// Minimal JS: modal, toast auto-hide, client-side validation and event delegation
document.addEventListener('DOMContentLoaded', function(){
    // small UX: toggle show/hide password for auth forms
    document.querySelectorAll('.btn-toggle-pw').forEach(btn=>{
        btn.addEventListener('click', function(e){
            const container = btn.closest('.auth-card');
            if (!container) return;
            const pw = container.querySelector('input[type="password"]');
            if (!pw) return;
            if (pw.type === 'password') {
                pw.type = 'text';
                btn.textContent = 'Hide';
            } else {
                pw.type = 'password';
                btn.textContent = 'Show';
            }
        });
    });

    // open booking modal
    document.querySelectorAll('.open-booking').forEach(btn=>{
        btn.addEventListener('click', e=>{
            const id = btn.dataset.roomId;
            const modal = document.getElementById('bookingModal');
            document.getElementById('room_id').value = id;
            modal.setAttribute('aria-hidden','false');
        });
    });

    // helper to attach open-booking listeners for dynamically rendered room cards
    function attachBookingListeners(container) {
        const scope = container || document;
        scope.querySelectorAll('.open-booking').forEach(btn=>{
            btn.removeEventListener && btn.removeEventListener('click', null);
            btn.addEventListener('click', e=>{
                const id = btn.dataset.roomId;
                const modal = document.getElementById('bookingModal');
                const roomInput = document.getElementById('room_id');
                if (roomInput) roomInput.value = id;
                if (modal) modal.setAttribute('aria-hidden','false');
            });
        });
    }

    // close modal
    document.querySelectorAll('.modal .close').forEach(b=>b.addEventListener('click', ()=>{
        b.closest('.modal').setAttribute('aria-hidden','true');
    }));

    // toast auto hide
    document.querySelectorAll('.toast').forEach(t=>{
        setTimeout(()=>{ t.style.display='none'; }, 3500);
    });

    // booking form: client-side validation + API submit (if API token present)
    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm) bookingForm.addEventListener('submit', async function(e){
        const start = document.getElementById('start_time').value;
        const end = document.getElementById('end_time').value;
        if (!start || !end || start >= end) {
            e.preventDefault();
            alert('Please provide a valid start and end time.');
            return;
        }

        // If no API token stored, fallback to normal form POST to server-side handler
        const token = localStorage.getItem('api_token');
        if (!token) {
            return; // allow regular submit
        }

        e.preventDefault();

        const roomId = document.getElementById('room_id').value;

        function toSQL(dt) {
            if (!dt) return dt;
            let s = dt.replace('T', ' ');
            if (s.length === 16) s = s + ':00'; // add seconds if missing
            return s;
        }

        const payload = {
            room_id: roomId,
            start_time: toSQL(start),
            end_time: toSQL(end)
        };

        try {
            const resp = await fetch('/api.php/bookings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + token
                },
                body: JSON.stringify(payload)
            });

            const data = await resp.json().catch(()=>({ success: false, message: resp.statusText }));

            if (resp.ok && data.success) {
                // close modal if present and reload to reflect booking
                const modal = document.getElementById('bookingModal');
                if (modal) modal.setAttribute('aria-hidden','true');
                alert(data.message || 'Booking request sent');
                location.reload();
            } else {
                alert(data.message || 'Failed to create booking');
            }
        } catch (err) {
            console.error('Booking API error', err);
            alert('Network error while creating booking');
        }
    });

    // timeslot select -> autofill datetime-local inputs
    const timeslotSelect = document.getElementById('timeslot_select');
    if (timeslotSelect) {
        timeslotSelect.addEventListener('change', function(e){
            const opt = timeslotSelect.options[timeslotSelect.selectedIndex];
            const startTime = opt.getAttribute('data-start');
            const endTime = opt.getAttribute('data-end');
            const startInput = document.getElementById('start_time');
            const endInput = document.getElementById('end_time');
            if (!startTime || !endTime) return;

            function getDatePartFromInput(inp) {
                if (!inp || !inp.value) return null;
                const v = inp.value;
                const parts = v.split('T');
                return parts[0] || null;
            }
            let datePart = getDatePartFromInput(startInput);
            if (!datePart) {
                const d = new Date();
                const yyyy = d.getFullYear();
                const mm = String(d.getMonth()+1).padStart(2,'0');
                const dd = String(d.getDate()).padStart(2,'0');
                datePart = `${yyyy}-${mm}-${dd}`;
            }
            function hhmm(t) { return t ? t.slice(0,5) : ''; }
            startInput.value = datePart + 'T' + hhmm(startTime);
            endInput.value = datePart + 'T' + hhmm(endTime);
        });
    }

    // If api_token present, fetch rooms and timeslots via API and render client-side
    async function hydrateFromApi() {
        const token = localStorage.getItem('api_token');
        if (!token) return;

        // Fetch rooms
        try {
            const r = await fetch('/api.php/rooms', { headers: { 'Authorization': 'Bearer ' + token } });
            const jr = await r.json().catch(()=>null);
            if (r.ok && jr && jr.success && jr.data && Array.isArray(jr.data.rooms)) {
                const rooms = jr.data.rooms;
                const grid = document.querySelector('.room-grid');
                if (grid) {
                    grid.innerHTML = rooms.map(rm => `\n                        <article class="room-card">\n                            <h3>${escapeHtml(rm.name)}</h3>\n                            <p>Location: ${escapeHtml(rm.location)}</p>\n                            <p>Capacity: ${escapeHtml(String(rm.capacity))}</p>\n                            <button class="btn open-booking" data-room-id="${rm.id}">Book</button>\n                        </article>`).join('');
                    attachBookingListeners(grid);
                }
            }
        } catch (err) {
            console.error('Failed to fetch rooms from API', err);
        }

        // Fetch timeslots and populate select
        try {
            const t = await fetch('/api.php/timeslots', { headers: { 'Authorization': 'Bearer ' + token } });
            const jt = await t.json().catch(()=>null);
            if (t.ok && jt && jt.success && jt.data && Array.isArray(jt.data.timeslots)) {
                const ts = jt.data.timeslots;
                const sel = document.getElementById('timeslot_select');
                if (sel) {
                    // keep the empty option
                    const emptyOpt = sel.querySelector('option[value=""]') ? sel.querySelector('option[value=""]').outerHTML : '<option value="">-- choose a timeslot --</option>';
                    sel.innerHTML = emptyOpt + ts.map(s => `\n                        <option value="${s.id}" data-start="${s.start_time}" data-end="${s.end_time}">${escapeHtml(s.name + ' (' + s.start_time.slice(0,5) + ' - ' + s.end_time.slice(0,5) + ')')}</option>`).join('');
                }
            }
        } catch (err) {
            console.error('Failed to fetch timeslots from API', err);
        }
    }

    // small HTML escape helper
    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/[&<>"']/g, function(m) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[m]; });
    }

    // run hydration after DOM ready
    hydrateFromApi();

    // API-based login/register: store token in localStorage when available
    const loginForm = document.getElementById('loginForm');
    if (loginForm) loginForm.addEventListener('submit', async function(e){
        // If no API path is desired, allow normal submit
        const useApi = true; // flip to false to disable API login
        if (!useApi) return;

        e.preventDefault();
        const form = e.currentTarget;
        const email = form.querySelector('input[name="email"]').value;
        const password = form.querySelector('input[name="password"]').value;
        try {
            const resp = await fetch('/api.php/auth/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: email, password: password })
            });
            const data = await resp.json().catch(()=>({ success:false, message:resp.statusText }));
            if (resp.ok && data.success) {
                const token = data.data && data.data.token ? data.data.token : null;
                if (token) {
                    localStorage.setItem('api_token', token);
                    localStorage.setItem('api_user', JSON.stringify(data.data.user || null));
                }
                // Also perform server-side login POST so PHP session is created (role-based views)
                try {
                    const csrfInput = form.querySelector('input[name="_csrf"]');
                    const csrf = csrfInput ? csrfInput.value : '';
                    const body = new URLSearchParams();
                    body.append('_csrf', csrf);
                    body.append('email', email);
                    body.append('password', password);

                    // send credentials to server login endpoint to set PHP session cookie
                    const serverResp = await fetch('?action=login', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: body.toString(),
                        credentials: 'same-origin'
                    });
                    // ignore body; redirect to dashboard which will now reflect server session
                } catch (err) {
                    console.warn('Server-side login attempt failed', err);
                }

                alert(data.message || 'Login successful');
                window.location = '?action=dashboard';
            } else {
                alert(data.message || 'Login failed');
            }
        } catch (err) {
            console.error('API login error', err);
            alert('Network error during login');
        }
    });

    const registerForm = document.getElementById('registerForm');
    if (registerForm) registerForm.addEventListener('submit', async function(e){
        e.preventDefault();
        const form = e.currentTarget;
        const name = form.querySelector('input[name="name"]').value;
        const email = form.querySelector('input[name="email"]').value;
        const password = form.querySelector('input[name="password"]').value;
        try {
            const resp = await fetch('/api.php/auth/register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name: name, email: email, password: password })
            });
            const data = await resp.json().catch(()=>({ success:false, message:resp.statusText }));
            if (resp.ok && data.success) {
                const token = data.data && data.data.token ? data.data.token : null;
                if (token) {
                    localStorage.setItem('api_token', token);
                    localStorage.setItem('api_user', JSON.stringify(data.data.user || null));
                }
                alert(data.message || 'Registration successful');
                window.location = '?action=dashboard';
            } else {
                alert(data.message || 'Registration failed');
            }
        } catch (err) {
            console.error('API register error', err);
            alert('Network error during registration');
        }
    });

    // client-side logout for API token (if you have a logout link with id apiLogout)
    const apiLogout = document.getElementById('apiLogout');
    if (apiLogout) apiLogout.addEventListener('click', function(e){
        localStorage.removeItem('api_token');
        localStorage.removeItem('api_user');
        // allow server-side logout to continue if it's a link
    });

    // edit timeslot -> open edit modal and populate fields
    document.querySelectorAll('.edit-timeslot').forEach(btn=>{
        btn.addEventListener('click', function(e){
            const id = btn.getAttribute('data-id');
            const name = btn.getAttribute('data-name');
            const start = btn.getAttribute('data-start');
            const end = btn.getAttribute('data-end');
            const modal = document.getElementById('timeslotModal');
            if (!modal) return;
            document.getElementById('ts_edit_id').value = id;
            document.getElementById('ts_edit_name').value = name;
            document.getElementById('ts_edit_start').value = start;
            document.getElementById('ts_edit_end').value = end;
            modal.setAttribute('aria-hidden','false');
        });
    });

    // ==== Fancy Button Interactions ====
    const fancyButtons = document.querySelectorAll('.btn');
    fancyButtons.forEach(btn => {
        // efek klik ripple
        btn.addEventListener('click', function(e){
            const circle = document.createElement('span');
            const diameter = Math.max(btn.clientWidth, btn.clientHeight);
            const radius = diameter / 2;
            circle.style.width = circle.style.height = `${diameter}px`;
            circle.style.left = `${e.clientX - btn.offsetLeft - radius}px`;
            circle.style.top = `${e.clientY - btn.offsetTop - radius}px`;
            circle.classList.add('ripple');
            const ripple = btn.getElementsByClassName('ripple')[0];
            if (ripple) ripple.remove(); // hapus efek lama
            btn.appendChild(circle);
        });

        // efek hover glow dinamis
        btn.addEventListener('mouseenter', () => {
            btn.style.boxShadow = '0 0 25px rgba(255,255,255,0.4), 0 0 45px rgba(79,70,229,0.5)';
        });
        btn.addEventListener('mouseleave', () => {
            btn.style.boxShadow = '';
        });
    });
});
