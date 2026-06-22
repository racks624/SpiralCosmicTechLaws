<?php
namespace App\Controllers;

use App\Core\Request;
use App\Models\Target;
use App\Models\Finding;
use Dompdf\Dompdf;
use Dompdf\Options;

class ReportController extends Controller
{
    public function generate(Request $request)
    {
        $targetId = $request->input('target_id');
        $format = $request->input('format', 'html');
        $campaignName = $request->input('campaign_name', 'RedTeam Assessment');

        if ($format === 'pdf') {
            $this->generatePDF($targetId, $campaignName);
        } elseif ($format === 'json') {
            $this->generateJSON($targetId);
        } else {
            $this->generateHTML($targetId, $campaignName);
        }
    }

    private function generateHTML($targetId, $campaignName)
    {
        $targets = $targetId ? [Target::find($targetId)] : Target::all();
        $html = $this->buildReportHTML($targets, $campaignName);
        echo $html;
    }

    private function generatePDF($targetId, $campaignName)
    {
        $targets = $targetId ? [Target::find($targetId)] : Target::all();
        $html = $this->buildReportHTML($targets, $campaignName);
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream("report_{$campaignName}.pdf", ['Attachment' => 0]);
    }

    private function generateJSON($targetId)
    {
        $targets = $targetId ? [Target::find($targetId)] : Target::all();
        $data = [];
        foreach ($targets as $t) {
            $findings = Finding::where('scan_id', $t['id']); // simplified
            $data[] = ['target' => $t, 'findings' => $findings];
        }
        $this->json($data);
    }

    private function buildReportHTML($targets, $campaignName)
    {
        ob_start(); ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>RedTeam Report - <?= $campaignName ?></title>
            <style>
                body { font-family: Arial, sans-serif; background: #0a0a0a; color: #ccc; }
                table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                th, td { border: 1px solid #444; padding: 8px; text-align: left; }
                th { background: #1a1a2e; }
                .high { color: #ff6b6b; }
                .critical { color: #ff0000; font-weight: bold; }
            </style>
        </head>
        <body>
            <h1>RedTeam Assessment: <?= $campaignName ?></h1>
            <?php foreach ($targets as $t): ?>
                <h2>Target: <?= htmlspecialchars($t['name']) ?></h2>
                <?php
                $findings = Finding::where('scan_id', $t['id']); // adjust if needed
                if (empty($findings)): ?>
                    <p>No findings.</p>
                <?php else: ?>
                    <table>
                        <tr><th>Severity</th><th>Title</th><th>Description</th><th>MITRE Tactic</th><th>Risk Score</th></tr>
                        <?php foreach ($findings as $f): ?>
                            <tr>
                                <td class="<?= $f['severity'] ?>"><?= strtoupper($f['severity']) ?></td>
                                <td><?= htmlspecialchars($f['title']) ?></td>
                                <td><?= htmlspecialchars($f['description']) ?></td>
                                <td><?= htmlspecialchars($f['mitre_tactic'] ?? '') ?></td>
                                <td><?= number_format($f['risk_score'] ?? 0, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            <?php endforeach; ?>
            <p>Generated: <?= date('Y-m-d H:i:s') ?></p>
        </body>
        </html>
        <?php return ob_get_clean();
    }
}
