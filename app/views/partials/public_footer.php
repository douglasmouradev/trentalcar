<?php declare(strict_types=1);
/** @var callable(string): string $asset */
View::partial('partials/landing_footer', ['asset' => $asset, 'compact' => true]);
View::partial('partials/cookie_notice');
