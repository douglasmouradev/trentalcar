<?php

declare(strict_types=1);

final class ReportController
{
    public function index(): void
    {
        $range = ReportRepository::normalizeRange(
            isset($_GET['from']) ? (string) $_GET['from'] : null,
            isset($_GET['to']) ? (string) $_GET['to'] : null
        );

        View::render('reports/index', [
            'title' => Lang::get('nav.reports'),
            'monthly' => ReportRepository::monthlyRevenue($range['from'], $range['to']),
            'fleet' => ReportRepository::fleetByStatus(),
            'from' => $range['from'],
            'to' => $range['to'],
        ], 'main');
    }

    public function exportCsv(): void
    {
        $range = ReportRepository::normalizeRange(
            isset($_GET['from']) ? (string) $_GET['from'] : null,
            isset($_GET['to']) ? (string) $_GET['to'] : null
        );
        if (!ReportRepository::validateDateRange($range['from'], $range['to'])) {
            http_response_code(400);
            echo 'Invalid range';
            return;
        }

        $csvRows = [];
        foreach (ReportRepository::monthlyRevenue($range['from'], $range['to']) as $row) {
            $csvRows[] = [
                (string) $row['ym'],
                (string) $row['cnt'],
                number_format((float) $row['total'], 2, ',', ''),
            ];
        }
        CsvResponse::download(
            'relatorio-reservas-' . $range['from'] . '-' . $range['to'] . '.csv',
            ['mes', 'reservas', 'total_brl'],
            $csvRows
        );
    }
}
