<?php
declare(strict_types=1);

if (!function_exists('render_origin_page')) {
    function render_origin_page(string $title, callable $content): void
    {
        ?>
        <!doctype html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <title><?= e($title) ?></title>
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <style>
                body {
                    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
                    margin: 0;
                    padding: 40px;
                    background: #0b1020;
                    color: #e5e7eb;
                }
                .card {
                    max-width: 720px;
                    margin: 0 auto;
                    padding: 32px;
                    border-radius: 18px;
                    background: radial-gradient(circle at top left,#1f2937,#020617);
                    border: 1px solid rgba(148,163,184,0.25);
                    box-shadow: 0 18px 60px rgba(0,0,0,0.55);
                }
                a {
                    color: #7dd3fc;
                    text-decoration: none;
                }
                a:hover {
                    text-decoration: underline;
                }
            </style>
        </head>
        <body>
        <div class="card">
            <?php $content(); ?>
        </div>
        </body>
        </html>
        <?php
    }
}
