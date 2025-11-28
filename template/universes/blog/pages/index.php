<?php declare(strict_types=1); ?>

<?php
render_origin_page('Blog Universe', function () use ($universe, $page) {
?>
    <h1>Blog Universe</h1>
    <p>This universe was added without touching any core file.</p>

    <p>
        Universe: <code><?= e($universe) ?></code><br>
        Page: <code><?= e($page) ?></code>
    </p>
<?php
});
?>
