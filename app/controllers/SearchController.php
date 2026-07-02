<?php

declare(strict_types=1);

final class SearchController
{
    public function globalSearch(): void
    {
        ApiRateLimiter::guardJson();
        header('Content-Type: application/json; charset=utf-8');
        $q = trim((string) ($_GET['q'] ?? ''));
        if (strlen($q) < 2) {
            echo json_encode(['ok' => true, 'data' => []], JSON_THROW_ON_ERROR);
            return;
        }
        $like = '%' . $q . '%';
        $pdo = Database::pdo();
        $results = [];

        if (!Auth::isPartner()) {
            $customerSql = 'SELECT id, full_name, document, phone FROM customers WHERE full_name LIKE ? OR document LIKE ? OR phone LIKE ?';
            $params = [$like, $like, $like];
            if (!Auth::isOwner()) {
                $customerSql .= ' AND created_by = ?';
                $params[] = Auth::id();
            }
            $customerSql .= ' ORDER BY full_name LIMIT 5';
            $stmt = Database::prepare($customerSql);
            $stmt->execute($params);
            foreach ($stmt->fetchAll() as $row) {
                $results[] = [
                    'type' => Lang::get('search.type.customer'),
                    'label' => $row['full_name'] . ' — ' . Formatter::document((string) $row['document']),
                    'url' => Router::url('/customers/' . (int) $row['id'] . '/edit'),
                ];
            }
        }

        $carSql = 'SELECT id, brand, model, license_plate FROM cars WHERE brand LIKE ? OR model LIKE ? OR license_plate LIKE ?';
        $carParams = [$like, $like, $like];
        if (Auth::isPartner()) {
            $ids = Auth::partnerCarIds();
            if ($ids === []) {
                $carSql .= ' AND 1=0';
            } else {
                $ph = implode(',', array_fill(0, count($ids), '?'));
                $carSql .= " AND id IN ($ph)";
                $carParams = array_merge($carParams, $ids);
            }
        }
        $carSql .= ' ORDER BY brand LIMIT 5';
        $stmt = Database::prepare($carSql);
        $stmt->execute($carParams);
        foreach ($stmt->fetchAll() as $row) {
            $results[] = [
                'type' => Lang::get('search.type.car'),
                'label' => $row['brand'] . ' ' . $row['model'] . ' — ' . $row['license_plate'],
                'url' => Router::url('/cars/' . (int) $row['id']),
            ];
        }

        if (Auth::isStaff()) {
            $leadStmt = Database::prepare(
                'SELECT id, full_name, email FROM leads
                 WHERE full_name LIKE ? OR email LIKE ? OR phone LIKE ? OR local LIKE ?
                 ORDER BY created_at DESC LIMIT 5'
            );
            $leadStmt->execute([$like, $like, $like, $like]);
            foreach ($leadStmt->fetchAll() as $row) {
                $results[] = [
                    'type' => Lang::get('search.type.lead'),
                    'label' => (string) $row['full_name'] . ' · ' . (string) $row['email'],
                    'url' => Router::url('/leads/' . (int) $row['id']),
                ];
            }
        }

        if (!Auth::isPartner()) {
            $resSql = 'SELECT r.id, r.code, c.full_name FROM reservations r JOIN customers c ON c.id = r.customer_id WHERE r.code LIKE ? OR c.full_name LIKE ? OR c.phone LIKE ?';
            $resParams = [$like, $like, $like];
            if (!Auth::isOwner()) {
                $resSql .= ' AND r.operator_id = ?';
                $resParams[] = Auth::id();
            }
            $resSql .= ' ORDER BY r.id DESC LIMIT 5';
            $stmt = Database::prepare($resSql);
            $stmt->execute($resParams);
            foreach ($stmt->fetchAll() as $row) {
                $results[] = [
                    'type' => Lang::get('search.type.reservation'),
                    'label' => $row['code'] . ' — ' . $row['full_name'],
                    'url' => Router::url('/reservations/' . (int) $row['id']),
                ];
            }
        }

        echo json_encode(['ok' => true, 'data' => $results], JSON_THROW_ON_ERROR);
    }
}
