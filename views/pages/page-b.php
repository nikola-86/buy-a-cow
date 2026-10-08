<?php

declare(strict_types=1);

use function App\Core\e;

?>
<article>
    <h1>Page B</h1>
    <form method="post" action="/download">
        <input type="hidden" name="csrf_token" value="<?= e($csrfToken) ?>">
        <button type="submit">Download</button>
    </form>
</article>
