<header class="site-header">
    <div class="wrap">
        <h1 class="brand"><a href="?"><?php echo APP_NAME; ?></a></h1>
        <nav class="main-nav" aria-label="Main Navigation">
            <ul>
                <li><a href="?">Home</a></li>
                <li><a href="?action=rooms">Rooms</a></li>
                <li><a href="?action=bookings">My Bookings</a></li>
                <?php if (!empty($_SESSION['role_id']) && $_SESSION['role_id'] == 1): ?>
                    <li><a href="?action=timeslots">Timeslots</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="auth">
            <?php if (!empty($_SESSION['user_id'])): ?>
                <span class="greeting">Hi, <?php echo htmlspecialchars($_SESSION['user_name'] ?? ''); ?></span>
                <form method="post" action="?action=logout" style="display:inline">
                    <button type="submit" class="btn btn--secondary">Logout</button>
                </form>
            <?php else: ?>
                <a href="?action=login" class="btn btn--primary">Login</a>
            <?php endif; ?>
        </div>
    </div>
</header>
