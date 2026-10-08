<?php

declare(strict_types=1);

use function App\Core\e;

$seriesLabels = [
    'page_a_views' => 'Page view A',
    'page_b_views' => 'Page view B',
    'buy_cow_clicks' => 'Click “Buy a cow”',
    'download_clicks' => 'Click “Download”',
];

?>
<article>
    <h1>Reports</h1>
    <?php if ($isAdmin): ?>
        <form method="get" action="/reports">
            <div class="grid">
                <label>
                    User
                    <select name="user_id">
                        <option value="">All users</option>
                        <?php foreach ($users as $user): ?>
                            <option
                                value="<?= e($user['id']) ?>"
                                <?= $selectedUserId === $user['id'] ? 'selected' : '' ?>
                            ><?= e($user['username']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <button type="submit">Filter</button>
        </form>
    <?php endif; ?>

    <?php if ($report === []): ?>
        <p>No activity yet</p>
    <?php else: ?>
        <canvas id="report-chart" aria-label="Daily activity chart" role="img"></canvas>
        <script id="report-data" type="application/json"><?= json_encode(
            ['series' => $seriesLabels, 'rows' => $report],
            JSON_HEX_TAG | JSON_THROW_ON_ERROR
        ) ?></script>

        <div class="overflow-auto">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <?php foreach ($seriesLabels as $label): ?>
                            <th><?= e($label) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($report as $row): ?>
                        <tr>
                            <td><?= e($row['day']) ?></td>
                            <?php foreach (array_keys($seriesLabels) as $seriesKey): ?>
                                <td><?= e($row[$seriesKey]) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
        <script type="module" src="/assets/js/reports.js"></script>
    <?php endif; ?>
</article>
