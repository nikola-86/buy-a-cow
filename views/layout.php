<?php

declare(strict_types=1);

use App\Enums\Role;

use function App\Core\e;

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Buy a Cow') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <header class="container">
        <nav>
            <ul>
                <li><strong>Buy a Cow</strong></li>
            </ul>
            <ul>
                <?php if ($currentRole === null): ?>
                    <li><a href="/login">Login</a></li>
                    <li><a href="/register">Register</a></li>
                <?php else: ?>
                    <li><a href="/page-a">Page A</a></li>
                    <li><a href="/page-b">Page B</a></li>
                    <li><a href="/reports">Reports</a></li>
                    <?php if ($currentRole === Role::Admin): ?>
                        <li><a href="/statistics">Statistics</a></li>
                    <?php endif; ?>
                    <li><span><?= e($currentUsername) ?></span></li>
                    <li>
                        <form class="inline-form" method="post" action="/logout">
                            <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
                            <button class="secondary outline" type="submit">Logout</button>
                        </form>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main class="container">
        <?php require $viewPath; ?>
    </main>
</body>
</html>
