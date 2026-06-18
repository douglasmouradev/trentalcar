<?php

declare(strict_types=1);

final class Ui
{
    /** Badge HTML escapado para status de reserva. */
    public static function statusBadge(string $status): string
    {
        $allowed = ['pending', 'confirmed', 'active', 'completed', 'cancelled'];
        if (!in_array($status, $allowed, true)) {
            $status = 'pending';
        }
        return self::badgeHtml('status.' . $status, 'st-' . $status);
    }

    public static function carStatusBadge(string $status): string
    {
        $allowed = ['available', 'rented', 'maintenance', 'inactive'];
        if (!in_array($status, $allowed, true)) {
            $status = 'inactive';
        }
        return self::badgeHtml('car.' . $status, 'car-' . $status);
    }

    public static function paymentBadge(string $status): string
    {
        $allowed = ['unpaid', 'partial', 'paid'];
        if (!in_array($status, $allowed, true)) {
            $status = 'unpaid';
        }
        return self::badgeHtml('payment.' . $status, 'pay-' . $status);
    }

    public static function categoryLabel(string $category): string
    {
        $key = 'category.' . $category;
        $label = Lang::get($key);
        return htmlspecialchars($label === $key ? $category : $label, ENT_QUOTES, 'UTF-8');
    }

    public static function activeBadge(bool $active): string
    {
        $key = $active ? 'common.active' : 'common.inactive';
        $class = $active ? 'badge-active' : 'badge-inactive';
        return self::badgeHtml($key, $class);
    }

    public static function enumLabel(string $prefix, string $value): string
    {
        $key = $prefix . '.' . $value;
        $label = Lang::get($key);
        return htmlspecialchars($label === $key ? $value : $label, ENT_QUOTES, 'UTF-8');
    }

    private static function badgeHtml(string $langKey, string $cssClass): string
    {
        $label = htmlspecialchars(Lang::get($langKey), ENT_QUOTES, 'UTF-8');
        $class = htmlspecialchars($cssClass, ENT_QUOTES, 'UTF-8');
        return '<span class="badge ' . $class . '">' . $label . '</span>';
    }
}
