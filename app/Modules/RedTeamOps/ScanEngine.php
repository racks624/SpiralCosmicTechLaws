<?php
namespace App\Modules\RedTeamOps;

class ScanEngine
{
    public static function portScan($target, $ports = null)
    {
        if (!$ports) {
            $ports = [21,22,23,25,53,80,110,135,139,143,443,445,993,995,1433,3306,3389,5432,5900,6379,8080,27017];
        }
        $openPorts = [];
        $host = gethostbyname($target);
        foreach ($ports as $port) {
            $socket = @fsockopen($host, $port, $errno, $errstr, 1);
            if ($socket) {
                $openPorts[] = $port;
                fclose($socket);
            }
        }
        return $openPorts;
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
            // Basic fingerprinting
            if (strpos($banner, "SSH") !== false) $service = "ssh";
            elseif (strpos($banner, "HTTP") !== false) $service = "http";
            elseif ($port == 21) $service = "ftp";
            elseif ($port == 25) $service = "smtp";
            elseif ($port == 3306) $service = "mysql";
            elseif ($port == 5432) $service = "postgresql";
        }
        return ['service' => $service, 'banner' => $banner];
    }

    public static function webScan($url)
    {
        $findings = [];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headers = curl_getinfo($ch);
        curl_close($ch);

        if ($httpCode == 200) {
            // Check for server header
            if (isset($headers['server'])) {
                $findings[] = ['severity' => 'info', 'title' => 'Server header exposed', 'description' => "Server: {$headers['server']}", 'cve_id' => '', 'recommendation' => 'Hide server headers'];
            }
            // Check for X-Powered-By
            if (strpos($response, "X-Powered-By") !== false) {
                $findings[] = ['severity' => 'low', 'title' => 'PHP version disclosed', 'description' => 'X-Powered-By header found', 'cve_id' => '', 'recommendation' => 'Remove PHP version headers'];
            }
            // Directory listing test
            $testUrl = rtrim($url, '/') . '/images/';
            $ch2 = curl_init($testUrl);
            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch2, CURLOPT_TIMEOUT, 5);
            $dirResp = curl_exec($ch2);
            if (strpos($dirResp, "Index of /") !== false) {
                $findings[] = ['severity' => 'medium', 'title' => 'Directory listing enabled', 'description' => "Directory indexing at {$testUrl}", 'cve_id' => '', 'recommendation' => 'Disable directory listing'];
            }
            curl_close($ch2);
        }
        return $findings;
    }
}
