<?php
// password reset form (via token)
$token = $_GET['token'] ?? '';
?>
<section>
    <h2>Atur kata sandi baru</h2>
    <form method="post" action="?action=password_update" style="max-width:480px">
        <input type="hidden" name="_csrf" value="<?php echo csrf_token(); ?>">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <div class="form-group">
            <label for="pw_new">Kata sandi baru</label>
            <input id="pw_new" name="password" type="password" minlength="6" required>
        </div>
        <div class="form-group">
            <label for="pw_confirm">Konfirmasi kata sandi</label>
            <input id="pw_confirm" name="password_confirm" type="password" minlength="6" required>
        </div>
        <button type="submit" class="btn btn--primary">Simpan kata sandi</button>
    </form>
</section>
