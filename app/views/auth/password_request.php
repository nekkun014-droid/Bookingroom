<?php
// form to request password reset
?>
<section>
    <h2>Reset kata sandi</h2>
    <p>Masukkan email akun Anda. Kami akan mengirimkan tautan satu-kali untuk mereset kata sandi.</p>
    <form method="post" action="?action=password_send" style="max-width:480px">
        <input type="hidden" name="_csrf" value="<?php echo csrf_token(); ?>">
        <div class="form-group">
            <label for="pr_email">Email</label>
            <input id="pr_email" name="email" type="email" required>
        </div>
    <button type="submit" class="btn btn--primary">Kirim tautan reset</button>
    </form>

    <?php if (defined('TESTING') && TESTING && !empty($_SESSION['reset_link'])): ?>
        <p><strong>Tautan reset (pengembangan):</strong></p>
        <p><a href="<?php echo htmlspecialchars($_SESSION['reset_link']); ?>"><?php echo htmlspecialchars($_SESSION['reset_link']); ?></a></p>
        <?php unset($_SESSION['reset_link']); ?>
    <?php endif; ?>
</section>
