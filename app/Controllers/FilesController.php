<?php
namespace App\Controllers;

use App\Core\Request;
use App\Models\Campaign;
use App\Models\EmailTemplate;
use App\Models\SmsLog;
use App\Models\MaskedUrl;
use App\Models\Agent;
use App\Models\Task;
use App\Models\VirtualMachine;
use App\Models\Payload;
use App\Models\Target;
use App\Models\Finding;

class FilesController extends Controller
{
    // ---- Dashboard ----
    public function index()
    {
        $this->view('files/index');
    }

    // ---- List items per category ----
    public function list(Request $request)
    {
        $category = $request->input('category');
        $data = [];
        switch ($category) {
            case 'payloads':
                $data = Payload::all();
                break;
            case 'templates':
                $data = EmailTemplate::all();
                break;
            case 'sms':
                $data = SmsLog::all();
                break;
            case 'urls':
                $data = MaskedUrl::all();
                break;
            case 'c2':
                $data = Agent::all();
                break;
            case 'virtuallab':
                $data = VirtualMachine::all();
                break;
            case 'targets':
                $data = Target::all();
                break;
            case 'findings':
                $data = Finding::all();
                break;
            default:
                $this->json(['error' => 'Invalid category'], 400);
        }
        $this->json(['items' => $data]);
    }

    // ---- Show item ----
    public function show(Request $request)
    {
        $category = $request->input('category');
        $id = $request->input('id');
        $item = $this->getItem($category, $id);
        if (!$item) {
            $this->json(['error' => 'Item not found'], 404);
        }
        $this->json(['item' => $item]);
    }

    // ---- Edit item ----
    public function edit(Request $request)
    {
        $category = $request->input('category');
        $id = $request->input('id');
        $data = $request->only($this->getFields($category));
        $item = $this->getItem($category, $id);
        if (!$item) {
            $this->json(['error' => 'Item not found'], 404);
        }
        $model = $this->getModel($category);
        $model::update($id, $data);
        $this->json(['success' => true]);
    }

    // ---- Delete item ----
    public function delete(Request $request)
    {
        $category = $request->input('category');
        $id = $request->input('id');
        $item = $this->getItem($category, $id);
        if (!$item) {
            $this->json(['error' => 'Item not found'], 404);
        }
        $model = $this->getModel($category);
        $model::delete($id);
        $this->json(['success' => true]);
    }

    // ---- Share item (generate link) ----
    public function share(Request $request)
    {
        $category = $request->input('category');
        $id = $request->input('id');
        $item = $this->getItem($category, $id);
        if (!$item) {
            $this->json(['error' => 'Item not found'], 404);
        }
        $token = 'share_' . bin2hex(random_bytes(8));
        $_SESSION['share_' . $category . '_' . $id] = $token;
        $url = getenv('APP_URL') . '/files/share/' . $category . '/' . $id . '?token=' . $token;
        $this->json(['share_url' => $url]);
    }

    // ---- Download item ----
    public function download(Request $request)
    {
        $category = $request->input('category');
        $id = $request->input('id');
        $item = $this->getItem($category, $id);
        if (!$item) {
            http_response_code(404);
            echo 'Not found';
            exit;
        }
        if ($category === 'payloads' && isset($item['filepath']) && file_exists($item['filepath'])) {
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($item['filepath']) . '"');
            readfile($item['filepath']);
            exit;
        }
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $category . '_' . $id . '.json"');
        echo json_encode($item, JSON_PRETTY_PRINT);
        exit;
    }

    // ---- Helpers ----
    private function getModel($category)
    {
        $map = [
            'payloads' => 'App\Models\Payload',
            'templates' => 'App\Models\EmailTemplate',
            'sms' => 'App\Models\SmsLog',
            'urls' => 'App\Models\MaskedUrl',
            'c2' => 'App\Models\Agent',
            'virtuallab' => 'App\Models\VirtualMachine',
            'targets' => 'App\Models\Target',
            'findings' => 'App\Models\Finding'
        ];
        return $map[$category] ?? null;
    }

    private function getItem($category, $id)
    {
        $model = $this->getModel($category);
        if (!$model) return null;
        return $model::find($id);
    }

    private function getFields($category)
    {
        switch ($category) {
            case 'payloads':
                return ['notes', 'status'];
            case 'templates':
                return ['name', 'subject', 'body', 'ab_group'];
            case 'sms':
                return ['status'];
            case 'urls':
                return ['original_url'];
            case 'c2':
                return ['hostname', 'os', 'ip_address', 'status', 'description', 'tags'];
            case 'virtuallab':
                return ['os', 'status', 'ip', 'config'];
            case 'targets':
                return ['name', 'target_value', 'status', 'description'];
            case 'findings':
                return ['severity', 'title', 'description', 'recommendation'];
            default:
                return [];
        }
    }
}
