<?php

declare(strict_types=1);

final class ReportController
{
    public function index(): void
    {
        $from = (string) ($_GET['from'] ?? date('Y-m-01'));
        $to = (string) ($_GET['to'] ?? date('Y-m-t'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            $from = date('Y-m-01');
            $to = date('Y-m-t');
        }
        $monthly = self::monthlyStats($from, $to);

        $fleet = Database::query(
            "SELECT status, COUNT(*) AS c FROM cars GROUP BY status"
        )->fetchAll();

        View::render('reports/index', [
            'title' => Lang::get('nav.reports'),
            'monthly' => $monthly,
            'fleet' => $fleet,
            'from' => $from,
            'to' => $to,
        ], 'main');
    }

    public function exportCsv(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            return;
        }
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            Redirect::to('/reports');
        }
        $from = (string) ($_POST['from'] ?? date('Y-m-01'));
        $to = (string) ($_POST['to'] ?? date('Y-m-t'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            Flash::error(Lang::get('flash.error'));
            Redirect::to('/reports');
        }
        $rows = self::monthlyStats($from, $to);

        $filename = 'relatorio-reservas-' . $from . '-' . $to . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        if ($out === false) {
            return;
        }
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['mes', 'reservas', 'total_brl'], ';');
        foreach ($rows as $row) {
            fputcsv($out, [
                (string) $row['ym'],
                (string) $row['cnt'],
                number_format((float) $row['total'], 2, ',', ''),
            ], ';');
        }
        fclose($out);
    }

    /** @return list<array<string, mixed>> */
    private static function monthlyStats(string $from, string $to): array
    {
        $stmt = Database::prepare(
            "SELECT DATE_FORMAT(r.pickup_date, '%Y-%m') AS ym, SUM(r.final_amount) AS total, COUNT(*) AS cnt
             FROM reservations r
             WHERE r.status IN ('confirmed','active','completed') AND r.pickup_date BETWEEN ? AND ?
             GROUP BY ym ORDER BY ym"
        );
        $stmt->execute([$from, $to]);
        $rows = $stmt->fetchAll();

        return array_values($rows);
    }
}
