<?php
namespace App\Core;

trait LoggerTrait {
    /**
     * Write a log entry to logs/app.log
     */
    public function logActivity(string $message): void {
        $logDir  = __DIR__ . '/../../logs';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $entry = '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL;
        file_put_contents($logDir . '/app.log', $entry, FILE_APPEND);
    }
}
