<section>
    <section class="hero wrap">
        <div style="display:flex;flex-direction:column;gap:.8rem;max-width:860px;margin:0 auto;padding:2rem 0;text-align:center">
            <h1 style="margin:0;font-size:2rem;color:var(--accent-600)">Welcome to <?php echo APP_NAME; ?></h1>
            <p class="muted" style="font-size:1.05rem;max-width:680px;margin:0 auto">A simple, secure room booking app — designed for clarity and speed. Book rooms, manage your reservations, and view available timeslots with a clean interface.</p>

            <div style="display:flex;gap:0.75rem;justify-content:center;align-items:center;margin-top:1rem;flex-wrap:wrap">
                <?php if (empty($_SESSION['user_id'])): ?>
                    <a href="?action=login" class="btn btn--primary">Sign in</a>
                    <a href="?action=register" class="btn btn--secondary">Create account</a>
                <?php else: ?>
                    <a href="?action=dashboard" class="btn btn--primary">Go to dashboard</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="wrap container" style="margin-top:1.5rem">
        <h3 style="margin-top:0">Why use <?php echo APP_NAME; ?>?</h3>
        <div class="room-grid" style="margin-top:1rem">
            <div class="room-card">
                <h4 style="margin:0 .25rem .45rem 0">Fast booking flow</h4>
                <p class="muted">Intuitive booking form and timeslot picker so users can reserve a room in seconds.</p>
            </div>
            <div class="room-card">
                <h4 style="margin:0 .25rem .45rem 0">Simple admin tools</h4>
                <p class="muted">Admins can manage timeslots and export data easily from the dashboard.</p>
            </div>
            <div class="room-card">
                <h4 style="margin:0 .25rem .45rem 0">Lightweight & secure</h4>
                <p class="muted">Minimal dependencies, secure session handling, CSRF protection and remember-me token rotation.</p>
            </div>
        </div>

        <!-- Quick preview and demo panels removed to simplify landing experience -->
    </section>
</section>
