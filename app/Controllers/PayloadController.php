<?php
namespace App\Controllers;

use App\Core\Request;
use App\Models\Payload;
use App\Modules\PayloadGenerator\WindowsBuilder;
use App\Modules\PayloadGenerator\LinuxBuilder;
use App\Modules\PayloadGenerator\AndroidBuilder;

class PayloadController extends Controller
{
    public function index()
    {
        $payloads = Payload::all();
        $this->view('payload/index', ['payloads' => $payloads]);
    }

    public function generate(Request $request)
    {
        $type = $request->input('type');
        $lhost = $request->input('lhost');
        $lport = (int)$request->input('lport');
        $notes = $request->input('notes', '');
        if (!$type || !$lhost || !$lport) {
            $this->json(['error' => 'Missing parameters'], 400);
        }

        $builder = null;
        $os = '';
        switch ($type) {
            case 'windows':
                $builder = new WindowsBuilder($lhost, $lport);
                $os = 'windows';
                break;
            case 'linux':
                $builder = new LinuxBuilder($lhost, $lport);
                $os = 'linux';
                break;
            case 'android':
                $builder = new AndroidBuilder($lhost, $lport);
                $os = 'android';
                break;
            default:
                $this->json(['error' => 'Unsupported payload type'], 400);
        }

        $payloadContent = $builder->generate();
        $filename = 'payload_' . uniqid() . '.' . $builder->getFileExtension();
        $fullPath = $builder->save($payloadContent, $filename);

        $payloadId = Payload::create([
            'os' => $os,
            'lhost' => $lhost,
            'lport' => $lport,
            'filename' => $filename,
            'filepath' => $fullPath,
            'content' => base64_encode($payloadContent),
            'notes' => $notes,
            'status' => 'generated'
        ]);

        $this->json([
            'status' => 'generated',
            'id' => $payloadId,
            'filename' => $filename,
            'payload' => base64_encode($payloadContent),
            'download_url' => '/payload/download/' . $filename
        ]);
    }

    public function editPayload(Request $request)
    {
        $id = $request->input('id');
        $data = $request->only(['notes', 'status']);
        if (!$id) $this->json(['error' => 'ID required'], 400);
        $payload = Payload::find($id);
        if (!$payload) $this->json(['error' => 'Payload not found'], 404);
        Payload::update($id, $data);
        $this->json(['success' => true]);
    }

    public function viewPayload(Request $request)
    {
        $id = $request->input('id');
        if (!$id) $this->json(['error' => 'ID required'], 400);
        $payload = Payload::find($id);
        if (!$payload) $this->json(['error' => 'Payload not found'], 404);
        $this->json(['payload' => $payload]);
    }

    public function deletePayload(Request $request)
    {
        $id = $request->input('id');
        if (!$id) $this->json(['error' => 'ID required'], 400);
        $payload = Payload::find($id);
        if ($payload && file_exists($payload['filepath'])) {
            unlink($payload['filepath']);
        }
        Payload::delete($id);
        $this->json(['success' => true]);
    }

    public function download(Request $request)
    {
        $filename = $request->input('filename');
        if (!$filename) {
            $this->json(['error' => 'Filename required'], 400);
        }
        $payload = Payload::where('filename', $filename);
        if (!$payload) {
            http_response_code(404);
            echo "Payload not found";
            exit;
        }
        $payload = $payload[0];
        $filePath = $payload['filepath'];
        if (!file_exists($filePath)) {
            http_response_code(404);
            echo "File not found";
            exit;
        }
        Payload::update($payload['id'], ['downloads' => ($payload['downloads'] ?? 0) + 1]);

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        readfile($filePath);
        exit;
    }

    public function list()
    {
        $payloads = Payload::all();
        $this->json(['payloads' => $payloads]);
    }
}
