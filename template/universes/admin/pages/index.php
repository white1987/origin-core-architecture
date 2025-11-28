<?php declare(strict_types=1); ?>

<?php
render_origin_page('Origin-Core · Admin Universe', function () use ($universe, $page) {
?>
    <h1>Admin Universe</h1>
    <p>You have passed the gate and entered the <code>admin</code> universe.</p>

    <p>
        Universe: <code><?= e($universe) ?></code><br>
        Page: <code><?= e($page) ?></code>
    </p>

    <p style="margin-top:16px;font-size:13px;color:#9ca3af;">
        This is only a demo gate: it checks for <code>?key=demo</code> in the query string.<br>
        In real projects, you would connect this to sessions, JWT, roles, etc.
    </p>
<?php
});
?>
