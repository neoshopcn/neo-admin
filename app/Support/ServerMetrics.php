<?php

namespace App\Support;

/** 主机负载、CPU、内存、磁盘占用快照 */
final class ServerMetrics
{
    /**
     * @return array{
     *     load_1: float|null,
     *     load_5: float|null,
     *     load_15: float|null,
     *     load_ring_percent: float|null,
     *     load_queue_length: float|null,
     *     load_subtitle: string|null,
     *     cpu_count: int|null,
     *     cpu_subtitle: string|null,
     *     cpu_percent: float|null,
     *     memory_percent: float|null,
     *     memory_subtitle: string|null,
     *     disk_percent: float|null,
     *     disk_subtitle: string|null,
     * }
     */
    public static function snapshot(string $diskPath): array
    {
        $loads = self::loadAverage();
        $cpuCount = self::cpuCount();
        $load1 = $loads[0] ?? null;
        $loadRing = null;
        $loadQueueLength = null;

        if ($load1 !== null && $cpuCount !== null && $cpuCount > 0) {
            $loadRing = round(min(100, max(0, ($load1 / $cpuCount) * 100)), 1);
        } elseif (PHP_OS_FAMILY === 'Windows' && function_exists('shell_exec')) {
            $loadQueueLength = self::windowsProcessorQueueLength();
            $n = max(1, $cpuCount ?? 1);
            if ($loadQueueLength !== null && $loadQueueLength >= 0) {
                // Windows：处理器队列长度折算为近似负载占比
                $loadRing = round(min(100, max(0, ($loadQueueLength / ($n * 2)) * 100)), 1);
            }
        }

        $mem = self::memorySnapshot() ?? [];
        $disk = self::diskMetrics($diskPath);

        return [
            'load_1' => $loads[0] ?? null,
            'load_5' => $loads[1] ?? null,
            'load_15' => $loads[2] ?? null,
            'load_ring_percent' => $loadRing,
            'load_queue_length' => $loadQueueLength,
            'load_subtitle' => self::loadSubtitleFromRing($loadRing),
            'cpu_count' => $cpuCount,
            'cpu_subtitle' => $cpuCount !== null ? $cpuCount.' 个核心' : null,
            'cpu_percent' => self::cpuPercent(),
            'memory_percent' => $mem['percent'] ?? null,
            'memory_subtitle' => self::bytesPairSubtitle($mem['used'] ?? null, $mem['total'] ?? null, ''),
            'disk_percent' => $disk['percent'] ?? null,
            'disk_subtitle' => self::bytesPairSubtitle($disk['used'] ?? null, $disk['total'] ?? null, ''),
        ];
    }

    private static function loadSubtitleFromRing(?float $ring): ?string
    {
        if ($ring === null) {
            return null;
        }
        if ($ring < 60) {
            return '运行流畅';
        }
        if ($ring < 85) {
            return '运行正常';
        }

        return '运行异常';
    }

    /**
     * @return array{percent: float, used: int, total: int}|array{}
     */
    private static function diskMetrics(string $path): array
    {
        $total = @disk_total_space($path);
        $free = @disk_free_space($path);
        if ($total === false || $free === false || $total <= 0) {
            return [];
        }
        $used = (int) ($total - $free);

        return [
            'percent' => round(max(0, min(100, ($used / $total) * 100)), 1),
            'used' => $used,
            'total' => (int) $total,
        ];
    }

    /**
     * @return array{percent: float, used: int, total: int}|null
     */
    private static function memorySnapshot(): ?array
    {
        if (PHP_OS_FAMILY === 'Linux' && is_readable('/proc/meminfo')) {
            $mem = @file('/proc/meminfo', FILE_IGNORE_NEW_LINES) ?: [];
            $kb = static function (string $prefix) use ($mem): ?int {
                foreach ($mem as $line) {
                    if (str_starts_with($line, $prefix)) {
                        return (int) filter_var($line, FILTER_SANITIZE_NUMBER_INT);
                    }
                }

                return null;
            };
            $totalKb = $kb('MemTotal:');
            $availKb = $kb('MemAvailable:');
            if ($availKb === null) {
                $memFree = $kb('MemFree:');
                $buffers = $kb('Buffers:') ?? 0;
                $cached = $kb('Cached:') ?? 0;
                $sRec = $kb('SReclaimable:') ?? 0;
                if ($memFree !== null) {
                    $availKb = $memFree + $buffers + $cached + $sRec;
                }
            }
            if ($totalKb && $totalKb > 0 && $availKb !== null) {
                $usedKb = $totalKb - $availKb;
                $pct = round(max(0, min(100, ($usedKb / $totalKb) * 100)), 1);

                return [
                    'percent' => $pct,
                    'used' => (int) round($usedKb * 1024),
                    'total' => (int) round($totalKb * 1024),
                ];
            }
        }

        if (function_exists('shell_exec') && PHP_OS_FAMILY === 'Windows') {
            $win = self::memorySnapshotWindowsWmic();
            if ($win !== null) {
                return $win;
            }

            return self::memorySnapshotWindowsPowerShell();
        }

        return null;
    }

    /**
     * @return array{percent: float, used: int, total: int}|null
     */
    private static function memorySnapshotWindowsWmic(): ?array
    {
        $o = @shell_exec('wmic OS get FreePhysicalMemory,TotalVisibleMemorySize /value 2>NUL');
        if (is_string($o)
            && preg_match('/FreePhysicalMemory=(\d+)/', $o, $f)
            && preg_match('/TotalVisibleMemorySize=(\d+)/', $o, $t)) {
            $freeKb = (float) $f[1];
            $totalKb = (float) $t[1];
            if ($totalKb > 0) {
                $usedKb = $totalKb - $freeKb;
                $pct = round(max(0, min(100, ($usedKb / $totalKb) * 100)), 1);

                return [
                    'percent' => $pct,
                    'used' => (int) round($usedKb * 1024),
                    'total' => (int) round($totalKb * 1024),
                ];
            }
        }

        return null;
    }

    /**
     * @return array{percent: float, used: int, total: int}|null
     */
    private static function memorySnapshotWindowsPowerShell(): ?array
    {
        $cmd = "powershell -NoProfile -NonInteractive -Command \"\$o=Get-CimInstance Win32_OperatingSystem -ErrorAction SilentlyContinue; if(\$o -and \$o.TotalVisibleMemorySize -gt 0){\$t=[double]\$o.TotalVisibleMemorySize;\$f=[double]\$o.FreePhysicalMemory;\$u=\$t-\$f; Write-Output (([string][math]::Round(\$u/\$t*100,1))+','+\$u+','+\$t)}else{Write-Output '-1'}\" 2>NUL";
        $o = @shell_exec($cmd);
        if (! is_string($o)) {
            return null;
        }
        $o = trim($o);
        if ($o === '' || $o === '-1') {
            return null;
        }
        $parts = explode(',', $o);
        if (count($parts) !== 3) {
            return null;
        }
        $pct = (float) $parts[0];
        $usedKb = (float) $parts[1];
        $totalKb = (float) $parts[2];
        if ($pct < 0 || $pct > 100 || $totalKb <= 0) {
            return null;
        }

        return [
            'percent' => $pct,
            'used' => (int) round($usedKb * 1024),
            'total' => (int) round($totalKb * 1024),
        ];
    }

    private static function bytesPairSubtitle(?int $used, ?int $total, string $kind): ?string
    {
        if ($used === null || $total === null || $total <= 0) {
            return null;
        }

        return ''.self::formatBytes($used).' / '.$kind.' '.self::formatBytes($total);
    }

    private static function formatBytes(float|int $bytes): string
    {
        $b = max(0, (float) $bytes);
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        if ($b < 1) {
            return '0 B';
        }
        $pow = (int) min(floor(log($b, 1024)), count($units) - 1);
        $b /= 1024 ** $pow;
        $dec = $pow >= 2 ? 2 : 0;

        return round($b, $dec).' '.$units[$pow];
    }

    /**
     * @return array{0?: float, 1?: float, 2?: float}
     */
    private static function loadAverage(): array
    {
        if (! function_exists('sys_getloadavg')) {
            return [];
        }
        $la = @sys_getloadavg();
        if ($la === false || ! is_array($la) || count($la) < 3) {
            return [];
        }

        return [
            round((float) $la[0], 2),
            round((float) $la[1], 2),
            round((float) $la[2], 2),
        ];
    }

    /**
     * Windows：性能计数器「处理器队列长度」，用于在无 loadavg 时近似负载压力。
     */
    private static function windowsProcessorQueueLength(): ?float
    {
        $cmd = 'powershell -NoProfile -NonInteractive -Command "$s=Get-CimInstance Win32_PerfFormattedData_PerfOS_System -ErrorAction SilentlyContinue; if($s -and $null -ne $s.ProcessorQueueLength){[double]$s.ProcessorQueueLength}else{-1}" 2>NUL';
        $o = @shell_exec($cmd);
        if (! is_string($o)) {
            return null;
        }
        $v = (float) trim($o);
        if ($v >= 0 && $v < 1_000_000) {
            return round($v, 2);
        }

        return null;
    }

    private static function cpuCount(): ?int
    {
        if (PHP_OS_FAMILY === 'Linux' && is_readable('/proc/cpuinfo')) {
            $c = substr_count((string) file_get_contents('/proc/cpuinfo'), 'processor');

            return max(1, $c);
        }
        if (function_exists('shell_exec') && PHP_OS_FAMILY === 'Windows') {
            $o = @shell_exec('wmic cpu get NumberOfCores /value 2>NUL');
            if (is_string($o) && preg_match('/NumberOfCores=(\d+)/', $o, $m)) {
                return max(1, (int) $m[1]);
            }

            return self::cpuCountWindowsPowerShell();
        }

        return null;
    }

    private static function cpuCountWindowsPowerShell(): ?int
    {
        $cmd = 'powershell -NoProfile -NonInteractive -Command "$s=(Get-CimInstance Win32_Processor | Measure-Object -Property NumberOfLogicalProcessors -Sum).Sum; if($s -gt 0){$s}else{-1}" 2>NUL';
        $o = @shell_exec($cmd);
        if (is_string($o) && preg_match('/^-?\d+$/', trim($o))) {
            $n = (int) trim($o);

            return $n > 0 ? $n : null;
        }

        return null;
    }

    private static function cpuPercent(): ?float
    {
        if (PHP_OS_FAMILY === 'Linux' && is_readable('/proc/stat')) {
            $pct = self::cpuPercentLinuxProcStat();
            if ($pct !== null) {
                return $pct;
            }
        }

        if (function_exists('shell_exec') && PHP_OS_FAMILY === 'Windows') {
            $pct = self::cpuPercentWindowsWmic();
            if ($pct !== null) {
                return $pct;
            }
            $pct = self::cpuPercentWindowsPowerShellFormatted();
            if ($pct !== null) {
                return $pct;
            }

            return self::cpuPercentWindowsPerformanceCounter();
        }

        return null;
    }

    /** Linux：/proc/stat CPU 占比 */
    private static function cpuPercentLinuxProcStat(): ?float
    {
        $read = static function (): ?array {
            $line = @file('/proc/stat', FILE_IGNORE_NEW_LINES)[0] ?? '';
            if ($line === '' || ! preg_match('/^cpu\s+(.+)/', $line, $m)) {
                return null;
            }
            $p = array_map('intval', preg_split('/\s+/', trim($m[1])) ?: []);
            if (count($p) < 4) {
                return null;
            }
            $idle = $p[3] + ($p[4] ?? 0);
            $total = array_sum($p);

            return ['idle' => $idle, 'total' => $total];
        };

        foreach ([150_000, 300_000] as $delay) {
            $a = $read();
            usleep($delay);
            $b = $read();
            if ($a && $b) {
                $di = $b['idle'] - $a['idle'];
                $dt = $b['total'] - $a['total'];
                if ($dt > 0) {
                    return round(max(0, min(100, (1 - $di / $dt) * 100)), 1);
                }
            }
        }

        return null;
    }

    private static function cpuPercentWindowsWmic(): ?float
    {
        $o = @shell_exec('wmic cpu get loadpercentage /value 2>NUL');
        if (is_string($o) && preg_match('/LoadPercentage=(\d+)/', $o, $m)) {
            return round((float) $m[1], 1);
        }

        return null;
    }

    /** Windows：WMI 格式化 CPU 占用 */
    private static function cpuPercentWindowsPowerShellFormatted(): ?float
    {
        $cmd = 'powershell -NoProfile -NonInteractive -Command "$p=Get-CimInstance Win32_PerfFormattedData_PerfOS_Processor | Where-Object { $_.Name -eq \'_Total\' }; if($p -and $null -ne $p.PercentProcessorTime){[math]::Round([double]$p.PercentProcessorTime,1)}else{-1}" 2>NUL';
        $o = @shell_exec($cmd);
        if (is_string($o)) {
            $v = (float) trim($o);
            if ($v >= 0 && $v <= 100) {
                return round(max(0, min(100, $v)), 1);
            }
        }

        return null;
    }

    /** Windows：性能计数器采样 */
    private static function cpuPercentWindowsPerformanceCounter(): ?float
    {
        $cmd = "powershell -NoProfile -NonInteractive -Command \"\$c=Get-Counter '\\Processor(_Total)\\% Processor Time' -SampleInterval 1 -MaxSamples 1 -ErrorAction SilentlyContinue; if(\$c -and \$c.CounterSamples.Count -gt 0){[math]::Round([double]\$c.CounterSamples[0].CookedValue,1)}else{-1}\" 2>NUL";
        $o = @shell_exec($cmd);
        if (is_string($o)) {
            $v = (float) trim($o);
            if ($v >= 0 && $v <= 100) {
                return round(max(0, min(100, $v)), 1);
            }
        }

        return null;
    }
}
