#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

echo '[' . date('Y-m-d H:i:s') . "] Domain Hunter cron starting...\n";

$report = $service->refreshAll();

foreach ($report as $domain => $changes) {
    if ($changes === []) {
        echo "  $domain — no changes\n";
    } else {
        echo "  $domain — " . count($changes) . " change(s):\n";
        foreach ($changes as $change) {
            echo "    • $change\n";
        }
    }
}

echo '[' . date('Y-m-d H:i:s') . "] Done.\n";
