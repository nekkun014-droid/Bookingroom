<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php require __DIR__ . '/header.php'; ?>

<main id="main" class="container">
    <?php
    // flash
    foreach (flash() as $k => $m) {
        if (!$m) continue;
        echo '<div class="toast ' . ($k==='error' ? 'error' : 'success') . '">' . htmlspecialchars($m) . '</div>';
    }
    ?>

    <?php echo $content; ?>
</main>

<?php require __DIR__ . '/footer.php'; ?>
<script src="assets/js/app.js"></script>
</body>
</html>
