<?php

declare(strict_types=1);

use function App\Core\e;

?>
<article>
    <h1>Register</h1>
    <?php foreach ($errors as $error): ?>
        <p class="form-error"><?= e($error) ?></p>
    <?php endforeach; ?>
    <form method="post" action="/register">
        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
        <label>
            Username
            <input type="text" name="username" value="<?= e($username) ?>" autocomplete="username" required>
        </label>
        <label>
            Password
            <input type="password" name="password" autocomplete="new-password" required>
        </label>
        <button type="submit">Register</button>
    </form>
</article>
