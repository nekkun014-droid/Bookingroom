<?php // login form ?>
<div class="auth-page">
    <div class="auth-card">
        <h2 class="auth-title">Sign in to <?php echo APP_NAME; ?></h2>
        <p class="muted">Enter your credentials to access your dashboard.</p>
        <form method="post" action="?action=login" id="loginForm" novalidate>
            <input type="hidden" name="_csrf" value="<?php echo csrf_token(); ?>">
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" required placeholder="you@example.com" autocomplete="email">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-with-toggle">
                    <input id="password" name="password" type="password" required minlength="6" placeholder="Password" autocomplete="current-password">
                    <button type="button" class="btn-toggle-pw" aria-label="Show password">Show</button>
                </div>
            </div>
            <div class="form-row" style="align-items:center;gap:.5rem">
                <label class="checkbox"><input type="checkbox" name="remember"> Remember me</label>
                <a class="muted" style="margin-left:auto" href="?action=password_request">Forgot password?</a>
            </div>
            <div style="margin-top:1rem">
                <button type="submit" class="btn btn--primary btn--block">Sign in</button>
            </div>
        </form>

        <div class="auth-footer">
            <span class="muted">Don't have an account?</span>
            <a href="?action=register" class="link">Create account</a>
        </div>
    </div>
</div>
