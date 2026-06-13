<?php
namespace App\Controllers;

class PayloadController extends Controller
{
    public function index() { $this->view('payload/index'); }
    public function generate() { $this->json(['payload' => base64_encode("PAYLOAD_".$_POST['type']."_".$_POST['lhost']."_".$_POST['lport']), 'filename' => 'payload.bin']); }
}
