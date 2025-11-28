<?php declare(strict_types=1); ?>

<?php
render_origin_page('Origin-Core · Home Universe', function () use ($universe, $page) {
?>
    <h1>Home Universe</h1>
    <p>This is the minimal Origin-Core template. All routing is handled by
        <code>core/router.php</code>.</p>

    <h2>Try routing:</h2>
    <ul>
        <li><code>?u=home&amp;p=index</code></li>
        <li>Create <code>/universes/home/pages/auth/login.php</code> → visit
            <code>?u=home&amp;p=auth/login</code></li>
        <li>Visit a missing page to see <code>404</code></li>
    </ul>

    <p style="margin-top:24px;font-size:12px;color:#9ca3af;">
        Powered by <strong>Origin-Core Architecture</strong>.
        Universe: <code><?= e($universe) ?></code>,
        Page: <code><?= e($page) ?></code>
    </p>
<?php
});
?>
