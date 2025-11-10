// Minimal JS: modal, toast auto-hide, client-side validation and event delegation
document.addEventListener('DOMContentLoaded', function(){
    // small UX: toggle show/hide password for auth forms
    document.querySelectorAll('.btn-toggle-pw').forEach(btn=>{
        btn.addEventListener('click', function(e){
            const container = btn.closest('.auth-card');
            if (!container) return;
            // find nearby password input (first one)
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
    // close
    document.querySelectorAll('.modal .close').forEach(b=>b.addEventListener('click', ()=>{
        b.closest('.modal').setAttribute('aria-hidden','true');
    }));

    // toast auto hide
    document.querySelectorAll('.toast').forEach(t=>{
        setTimeout(()=>{ t.style.display='none'; }, 3500);
    });

    // booking form simple client-side validation
    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm) bookingForm.addEventListener('submit', function(e){
        const start = document.getElementById('start_time').value;
        const end = document.getElementById('end_time').value;
        if (!start || !end || start >= end) {
            e.preventDefault();
            alert('Please provide a valid start and end time.');
        }
    });

    // timeslot select -> autofill datetime-local inputs
    const timeslotSelect = document.getElementById('timeslot_select');
    if (timeslotSelect) {
        timeslotSelect.addEventListener('change', function(e){
            const opt = timeslotSelect.options[timeslotSelect.selectedIndex];
            const startTime = opt.getAttribute('data-start'); // HH:MM:SS
            const endTime = opt.getAttribute('data-end');
            const startInput = document.getElementById('start_time');
            const endInput = document.getElementById('end_time');
            if (!startTime || !endTime) return;
            // determine date portion: use existing start_input date if filled else today
            function getDatePartFromInput(inp) {
                if (!inp || !inp.value) return null;
                const v = inp.value; // format YYYY-MM-DDTHH:MM
                const parts = v.split('T');
                return parts[0] || null;
            }
            let datePart = getDatePartFromInput(startInput);
            if (!datePart) {
                const d = new Date();
                // local date in yyyy-mm-dd
                const yyyy = d.getFullYear();
                const mm = String(d.getMonth()+1).padStart(2,'0');
                const dd = String(d.getDate()).padStart(2,'0');
                datePart = `${yyyy}-${mm}-${dd}`;
            }
            // format times (drop seconds)
            function hhmm(t) {
                if (!t) return '';
                return t.slice(0,5);
            }
            startInput.value = datePart + 'T' + hhmm(startTime);
            endInput.value = datePart + 'T' + hhmm(endTime);
        });
    }
    
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
});
