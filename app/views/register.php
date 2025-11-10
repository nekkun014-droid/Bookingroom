<?php // registration form - minimal frontend + server-side registration will handle validation ?>
<div class="auth-page">
    <div class="auth-card">
        <h2 class="auth-title">Create an account</h2>
        <p class="muted">Register to book rooms and manage your bookings.</p>
        <form method="post" action="?action=register" id="registerForm" novalidate>
            <input type="hidden" name="_csrf" value="<?php echo csrf_token(); ?>">
            <div class="form-group">
                <label for="name">Full name</label>
                <input id="name" name="name" type="text" required placeholder="Your name" autocomplete="name">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" required placeholder="you@example.com" autocomplete="email">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-with-toggle">
                    <input id="password" name="password" type="password" required minlength="6" placeholder="Create a password" autocomplete="new-password">
                    <button type="button" class="btn-toggle-pw" aria-label="Show password">Show</button>
                </div>
            </div>
            <div class="form-group">
                <label for="password_confirm">Confirm password</label>
                <input id="password_confirm" name="password_confirm" type="password" required minlength="6" placeholder="Confirm password" autocomplete="new-password">
            </div>
            <div style="margin-top:1rem">
                <button type="submit" class="btn btn--primary btn--block">Create account</button>
            </div>
        </form>
        <div class="auth-footer">
            <span class="muted">Already have an account?</span>
            <a href="?action=login" class="link">Sign in</a>
        </div>
    </div>
</div>
