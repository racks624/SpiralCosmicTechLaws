<?php
namespace App\Modules\RedTeamOps;

use App\Models\Finding;
use App\Models\Scan;

class ScanEngine
{
    // Parallel port scan using pcntl_fork if available
    public static function portScan($target, $ports = null)
    {
        if (!$ports) {
            $ports = [21,22,23,25,53,80,110,135,139,143,443,445,993,995,1433,3306,3389,5432,5900,6379,8080,27017];
        }
        shuffle($ports);
        $openPorts = [];
        $host = gethostbyname($target);
        if (function_exists('pcntl_fork') && count($ports) > 5) {
            $children = [];
            $chunkSize = ceil(count($ports) / 4);
            $chunks = array_chunk($ports, $chunkSize);
            foreach ($chunks as $chunk) {
                $pid = pcntl_fork();
                if ($pid == -1) {
                    return self::sequentialScan($host, $ports);
                } elseif ($pid == 0) {
                    $result = self::sequentialScan($host, $chunk);
                    file_put_contents("/tmp/scan_{$host}_{$pid}.json", json_encode($result));
                    exit(0);
                } else {
                    $children[] = $pid;
                }
            }
            foreach ($children as $pid) {
                pcntl_waitpid($pid, $status);
                $file = "/tmp/scan_{$host}_{$pid}.json";
                if (file_exists($file)) {
                    $data = json_decode(file_get_contents($file), true);
                    $openPorts = array_merge($openPorts, $data);
                    unlink($file);
                }
            }
            sort($openPorts);
            return $openPorts;
        } else {
            return self::sequentialScan($host, $ports);
        }
    }

    private static function sequentialScan($host, $ports)
    {
        $open = [];
        foreach ($ports as $port) {
            usleep(rand(100000, 1000000));
            $socket = @fsockopen($host, $port, $errno, $errstr, 1);
            if ($socket) {
                $open[] = $port;
                fclose($socket);
            }
        }
        return $open;
    }

    public static function serviceFingerprint($target, $port)
    {
        $service = "unknown";
        $banner = "";
        $fp = @fsockopen($target, $port, $errno, $errstr, 2);
        if ($fp) {
            stream_set_timeout($fp, 2);
            $banner = fgets($fp, 1024);
            fclose($fp);
            if (strpos($banner, "SSH") !== false) $service = "ssh";
            elseif (strpos($banner, "HTTP") !== false) $service = "http";
            elseif ($port == 21) $service = "ftp";
            elseif ($port == 25) $service = "smtp";
            elseif ($port == 3306) $service = "mysql";
            elseif ($port == 5432) $service = "postgresql";
            else $service = "unknown";
        }
        return ['service' => $service, 'banner' => $banner];
    }

    public static function multiWebScan($urls)
    {
        $results = [];
        $mh = curl_multi_init();
        $handles = [];
        foreach ($urls as $i => $url) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_multi_add_handle($mh, $ch);
            $handles[$i] = $ch;
        }
        do {
            curl_multi_exec($mh, $running);
        } while ($running);
        foreach ($handles as $i => $ch) {
            $response = curl_multi_getcontent($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $headers = curl_getinfo($ch);
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
            $results[] = ['url' => $urls[$i], 'response' => $response, 'code' => $httpCode, 'headers' => $headers];
        }
        curl_multi_close($mh);
        return $results;
    }

    public static function webScan($url)
    {
        $findings = [];
        $results = self::multiWebScan([$url]);
        $data = $results[0];
        $response = $data['response'];
        $httpCode = $data['code'];
        $headers = $data['headers'];

        if ($httpCode == 200) {
            if (isset($headers['server'])) {
                $findings[] = [
                    'severity' => 'info',
                    'title' => 'Server header exposed',
                    'description' => "Server: {$headers['server']}",
                    'cve_id' => '',
                    'recommendation' => 'Hide server headers'
                ];
            }
            if (strpos($response, "X-Powered-By") !== false) {
                $findings[] = [
                    'severity' => 'low',
                    'title' => 'PHP version disclosed',
                    'description' => 'X-Powered-By header found',
                    'cve_id' => '',
                    'recommendation' => 'Remove PHP version headers'
                ];
            }
            $testUrl = rtrim($url, '/') . '/images/';
            $ch2 = curl_init($testUrl);
            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch2, CURLOPT_TIMEOUT, 5);
            $dirResp = curl_exec($ch2);
            if (strpos($dirResp, "Index of /") !== false) {
                $findings[] = [
                    'severity' => 'medium',
                    'title' => 'Directory listing enabled',
                    'description' => "Directory indexing at {$testUrl}",
                    'cve_id' => '',
                    'recommendation' => 'Disable directory listing'
                ];
            }
            curl_close($ch2);
        }
        return $findings;
    }

    public static function mapToMitre($cveId)
    {
        $mapFile = __DIR__ . '/data/mitre_map.json';
        if (!file_exists($mapFile)) {
            return null;
        }
        $map = json_decode(file_get_contents($mapFile), true);
        return $map[$cveId] ?? null;
    }

    public static function fullScanWithMitre($targetId, $targetValue, $targetType)
    {
        $scanId = Scan::startScan($targetId, 'full');
        $target = \App\Models\Target::find($targetId);
        $findings = [];

        $openPorts = self::portScan($targetValue);
        foreach ($openPorts as $port) {
            $fingerprint = self::serviceFingerprint($targetValue, $port);
            $cveId = '';
            $mitre = null;
            if ($port == 80 || $port == 443) {
                $cveId = 'CVE-2021-44228';
                $mitre = self::mapToMitre($cveId);
            }
            $riskScore = $mitre ? $mitre['cvss'] * 0.5 : 1.0;
            Finding::addFinding(
                $scanId,
                'info',
                "Open port: {$port}",
                "Service: {$fingerprint['service']} - Banner: {$fingerprint['banner']}",
                $cveId,
                'Consider closing unnecessary ports',
                null,
                $mitre['tactic'] ?? null,
                $mitre['technique'] ?? null,
                $mitre['cvss'] ?? 0,
                $riskScore
            );
        }

        if (in_array(80, $openPorts) || in_array(443, $openPorts)) {
            $protocol = in_array(443, $openPorts) ? "https" : "http";
            $url = "{$protocol}://{$targetValue}";
            $webFindings = self::webScan($url);
            foreach ($webFindings as $wf) {
                $cveId = $wf['cve_id'] ?? '';
                $mitre = $cveId ? self::mapToMitre($cveId) : null;
                $riskScore = $mitre ? $mitre['cvss'] * 0.6 : 2.0;
                Finding::addFinding(
                    $scanId,
                    $wf['severity'],
                    $wf['title'],
                    $wf['description'],
                    $cveId,
                    $wf['recommendation'] ?? '',
                    null,
                    $mitre['tactic'] ?? null,
                    $mitre['technique'] ?? null,
                    $mitre['cvss'] ?? 0,
                    $riskScore
                );
            }
        }

        Scan::completeScan($scanId, 'completed', "Found " . count($findings) . " issues");
        return $scanId;
    }
}
