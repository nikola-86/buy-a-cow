<?php

declare(strict_types=1);

use App\Enums\EventAction;

use function App\Core\e;

?>
<article>
    <h1>Statistics</h1>
    <form method="get" action="/statistics">
        <div class="grid">
            <label>
                Date
                <input type="date" name="date" value="<?= e($filter->date?->format('Y-m-d')) ?>">
            </label>
            <label>
                User
                <select name="user_id">
                    <option value="">All users</option>
                    <?php foreach ($users as $user): ?>
                        <option
                            value="<?= e($user['id']) ?>"
                            <?= $filter->userId === $user['id'] ? 'selected' : '' ?>
                        ><?= e($user['username']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>
                Action
                <select name="action">
                    <option value="">All actions</option>
                    <?php foreach (EventAction::cases() as $action): ?>
                        <option
                            value="<?= e($action->value) ?>"
                            <?= $filter->action === $action ? 'selected' : '' ?>
                        ><?= e($action->value) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <div class="form-actions">
            <button type="submit">Filter</button>
            <a href="/statistics" role="button" class="secondary outline">Reset</a>
        </div>
    </form>

    <?php if ($events === []): ?>
        <p>No events found.</p>
    <?php else: ?>
        <div class="overflow-auto">
            <table>
                <thead>
                    <tr>
                        <th>Date/time (UTC)</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Target</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?= e($event['created_at']) ?></td>
                            <td><?= e($event['username']) ?></td>
                            <td><?= e($event['action']) ?></td>
                            <td><?= e($event['target'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <nav class="pagination">
        <?php if ($previousPageUrl !== null): ?>
            <a href="<?= e($previousPageUrl) ?>">Previous</a>
        <?php endif; ?>
        <span>Page <?= e($filter->page) ?></span>
        <?php if ($nextPageUrl !== null): ?>
            <a href="<?= e($nextPageUrl) ?>">Next</a>
        <?php endif; ?>
    </nav>
</article>
