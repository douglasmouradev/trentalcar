<?php

declare(strict_types=1);

/** Exportação e anonimização LGPD de clientes. */
final class CustomerService
{
    /** @return array<string, mixed> */
    public static function exportPayload(int $customerId): ?array
    {
        $c = Customer::find($customerId);
        if ($c === null) {
            return null;
        }
        $payload = [
            'exported_at' => gmdate('c'),
            'customer' => $c,
            'reservations' => Reservation::forCustomer($customerId),
        ];
        unset($payload['customer']['attachment_path']);
        return $payload;
    }

    /**
     * @return array{ok: bool, error: string|null}
     */
    public static function anonymize(int $customerId): array
    {
        $c = Customer::find($customerId);
        if ($c === null) {
            return ['ok' => false, 'error' => 'not_found'];
        }
        if (Customer::isAnonymized($c)) {
            return ['ok' => false, 'error' => 'already_anonymized'];
        }
        if (Customer::hasActiveReservations($customerId)) {
            return ['ok' => false, 'error' => 'active_reservations'];
        }

        if (!empty($c['attachment_path'])) {
            $path = CustomerAttachment::resolvePath((string) $c['attachment_path'])
                ?? CustomerAttachment::filesystemPath((string) $c['attachment_path']);
            if ($path !== null && is_file($path)) {
                @unlink($path);
            }
        }

        Customer::anonymize($customerId);
        return ['ok' => true, 'error' => null];
    }
}
