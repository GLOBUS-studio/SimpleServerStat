<?php

declare(strict_types=1);

/**
 * SimpleServerStat - JSON API endpoint.
 *
 * Endpoints:
 *   loader.php?action=system_info  -> live CPU load and memory usage
 *   loader.php?action=general      -> static host information
 *
 * All metrics are collected without blocking the request (no `top -bn2`,
 * `mpstat 2 1` or `iostat` style sampling). CPU usage is derived from a
 * cached /proc/stat snapshot, memory from /proc/meminfo.
 */

function sss_config(): array
{
    static $config = null;
    if ($config !== null) {
        return $config;
    }

    $defaults = ['access_token' => ''];
    $file = __DIR__ . '/config.php';

    if (is_file($file)) {
        $loaded = require $file;
        if (is_array($loaded)) {
            $config = array_merge($defaults, $loaded);
            return $config;
        }
    }

    $config = $defaults;
    return $config;
}

function sss_send_json($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($data, JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Enforce the optional shared-secret token. Disabled when the configured
 * token is empty. NOTE: this is a basic safeguard only - protect index.php
 * at the web-server level (HTTP auth / IP allowlist) for real privacy.
 */
function sss_authorize(): void
{
    $token = (string) sss_config()['access_token'];
    if ($token === '') {
        return;
    }

    $provided = '';
    if (isset($_GET['token'])) {
        $provided = (string) $_GET['token'];
    } elseif (isset($_SERVER['HTTP_X_AUTH_TOKEN'])) {
        $provided = (string) $_SERVER['HTTP_X_AUTH_TOKEN'];
    }

    if (!hash_equals($token, $provided)) {
        sss_send_json(['error' => 'Unauthorized'], 401);
    }
}

final class SystemInfo
{
    private string $cacheDir;

    public function __construct()
    {
        $this->cacheDir = sys_get_temp_dir();
    }

    public function getCpuCount(): int
    {
        $count = 0;

        if (is_readable('/proc/cpuinfo')) {
            $cpuinfo = @file_get_contents('/proc/cpuinfo');
            if ($cpuinfo !== false) {
                $count = preg_match_all('/^processor\s*:/m', $cpuinfo);
            }
        }

        if ($count < 1 && $this->isLinux()) {
            $out = @shell_exec('nproc 2>/dev/null');
            if (is_string($out)) {
                $count = (int) trim($out);
            }
        }

        return $count > 0 ? $count : 1;
    }

    /**
     * CPU usage in percent (0-100). Uses a cached /proc/stat snapshot so the
     * request never blocks. Falls back to load average when unavailable.
     */
    public function getCpuUsage(): ?float
    {
        $line = $this->firstLine('/proc/stat');
        if ($line === null || strncmp($line, 'cpu ', 4) !== 0) {
            return $this->loadAverageUsage();
        }

        $parts = preg_split('/\s+/', trim($line));
        $values = array_map('intval', array_slice($parts, 1));
        if (count($values) < 4) {
            return $this->loadAverageUsage();
        }

        $idle = ($values[3] ?? 0) + ($values[4] ?? 0); // idle + iowait
        $total = array_sum($values);

        $cacheFile = $this->cacheDir . '/sss_cpu.json';
        $previous = $this->readSnapshot($cacheFile);
        $this->writeSnapshot($cacheFile, ['total' => $total, 'idle' => $idle, 'time' => time()]);

        if ($previous === null) {
            return $this->loadAverageUsage();
        }

        $totalDelta = $total - $previous['total'];
        $idleDelta = $idle - $previous['idle'];
        if ($totalDelta <= 0) {
            return $this->loadAverageUsage();
        }

        $usage = (1 - $idleDelta / $totalDelta) * 100;
        return round($this->clamp($usage), 2);
    }

    /**
     * Memory usage parsed from /proc/meminfo. Values are reported in GiB,
     * usage as an integer percentage. Returns null when unavailable.
     */
    public function getMemoryUsage(): ?array
    {
        if (!is_readable('/proc/meminfo')) {
            return null;
        }

        $raw = @file_get_contents('/proc/meminfo');
        if ($raw === false) {
            return null;
        }

        $values = [];
        foreach (explode("\n", $raw) as $entry) {
            if (preg_match('/^(\w+):\s+(\d+)/', $entry, $match)) {
                $values[$match[1]] = (int) $match[2]; // kB
            }
        }

        $total = $values['MemTotal'] ?? 0;
        if ($total <= 0) {
            return null;
        }

        $available = $values['MemAvailable']
            ?? (($values['MemFree'] ?? 0) + ($values['Buffers'] ?? 0) + ($values['Cached'] ?? 0));
        $used = max(0, $total - $available);

        return [
            'total' => $this->toGiB($total),
            'used' => $this->toGiB($used),
            'free' => $this->toGiB($values['MemFree'] ?? 0),
            'available' => $this->toGiB($available),
            'cached' => $this->toGiB($values['Cached'] ?? 0),
            'usage' => (int) round($used / $total * 100),
        ];
    }

    public function getPhpVersion(): string
    {
        return 'PHP version: ' . PHP_VERSION;
    }

    public function getOsData(): string
    {
        return trim(php_uname('s') . ' ' . php_uname('r'));
    }

    private function loadAverageUsage(): ?float
    {
        if (!function_exists('sys_getloadavg')) {
            return null;
        }

        $load = sys_getloadavg();
        if (!is_array($load) || !isset($load[0])) {
            return null;
        }

        $usage = ($load[0] / max(1, $this->getCpuCount())) * 100;
        return round($this->clamp($usage), 2);
    }

    private function readSnapshot(string $file): ?array
    {
        if (!is_readable($file)) {
            return null;
        }

        $raw = @file_get_contents($file);
        if ($raw === false) {
            return null;
        }

        $data = json_decode($raw, true);
        if (!is_array($data) || !isset($data['total'], $data['idle'], $data['time'])) {
            return null;
        }

        if ((time() - (int) $data['time']) > 60) {
            return null; // stale snapshot, delta would be meaningless
        }

        return ['total' => (int) $data['total'], 'idle' => (int) $data['idle']];
    }

    private function writeSnapshot(string $file, array $data): void
    {
        @file_put_contents($file, json_encode($data), LOCK_EX);
    }

    private function firstLine(string $file): ?string
    {
        if (!is_readable($file)) {
            return null;
        }

        $handle = @fopen($file, 'r');
        if ($handle === false) {
            return null;
        }

        $line = fgets($handle);
        fclose($handle);

        return $line === false ? null : $line;
    }

    private function toGiB(int $kb): float
    {
        return round($kb / 1048576, 2);
    }

    private function clamp(float $value): float
    {
        return max(0.0, min(100.0, $value));
    }

    private function isLinux(): bool
    {
        return stripos(PHP_OS, 'linux') === 0;
    }
}

$action = isset($_GET['action']) ? (string) $_GET['action'] : '';

sss_authorize();

$info = new SystemInfo();

switch ($action) {
    case 'system_info':
        sss_send_json([
            'load' => $info->getCpuUsage(),
            'memory_usage' => $info->getMemoryUsage(),
        ]);
        break;

    case 'general':
        sss_send_json([
            'cpu_count' => 'Cores: ' . $info->getCpuCount(),
            'php_ver' => $info->getPhpVersion(),
            'os_data' => $info->getOsData(),
        ]);
        break;

    default:
        sss_send_json(['error' => 'Unknown action'], 404);
}
