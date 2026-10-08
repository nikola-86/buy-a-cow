<?php

declare(strict_types=1);

use function App\Core\e;

?>
<article>
    <h1>Page A</h1>
    <button
        type="button"
        id="buy-cow-button"
        data-csrf-token="<?= e($csrfToken) ?>"
        data-event-target="<?= e($buyCowTarget) ?>"
    >Buy a cow</button>
    <p id="buy-cow-thank-you" hidden>thankYou</p>
    <p id="buy-cow-error" class="form-error" role="alert" hidden>Something went wrong, please try again</p>
</article>
<script type="module" src="/assets/js/buy-cow.js"></script>
