<?php

declare(strict_types=1);

use function App\Core\e;

?>
<article>
    <h1>Login</h1>
    <?php if ($registered): ?>
        <p class="form-success">Registration successful, please log in</p>
    <?php endif; ?>
    <?php foreach ($errors as $error): ?>
        <p class="form-error"><?= e($error) ?></p>
    <?php endforeach; ?>
    <form method="post" action="/login">
        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
        <label>
            Username
            <input type="text" name="username" value="<?= e($username) ?>" autocomplete="username" required>
        </label>
        <label>
            Password
            <input type="password" name="password" autocomplete="current-password" required>
        </label>
        <button type="submit">Login</button>
    </form>
</article>
