<?php

declare(strict_types=1);

/** Orquestra validação e persistência de reservas. */
final class ReservationService
{
    /**
     * @param array<string, mixed> $post
     * @return array{ok: bool, error: string|null, data: array<string, mixed>|null}
     */
    public static function validateInput(array $post, bool $isOwner, ?array $old = null): array
    {
        return ReservationValidator::validate($post, $isOwner, $old);
    }

    public static function create(array $data): int
    {
        return Reservation::createSafely($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function update(int $id, array $data): void
    {
        Reservation::updateSafely($id, $data);
    }
}
