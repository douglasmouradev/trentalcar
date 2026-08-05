<?php declare(strict_types=1);
/** @var array<int,array<string,mixed>> $cars */
/** @var int $selectedCarId */
/** @var array<string,mixed>|null $selectedCar */
/** @var string $inicio */
/** @var string $fim */
/** @var string $local */
/** @var string $hotelNome */
/** @var string|null $lead_banner */
/** @var array<string,mixed> $leadOld */
/** @var array<int,string> $leadErrors */
$asset = static fn (string $path): string => htmlspecialchars(Router::url($path), ENT_QUOTES, 'UTF-8');
$appRoot = rtrim(Router::url('/'), '/');
$locale = Lang::locale();
$htmlLang = str_replace('_', '-', $locale);
$today = date('Y-m-d');
$local = $local ?? (string) ($leadOld['local'] ?? '');
$hotelNome = $hotelNome ?? (string) ($leadOld['hotel_nome'] ?? '');
$hotelFilterSelected = LeadPickupOptions::isHotel($local);
?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($htmlLang, ENT_QUOTES, 'UTF-8') ?>" data-app-origin="<?= htmlspecialchars($appRoot, ENT_QUOTES, 'UTF-8') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#152238">
    <meta name="csrf-token" content="<?= htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8') ?>">
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?> — <?= Lang::e('app.name') ?></title>
    <?php View::partial('partials/favicon'); ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= htmlspecialchars(Asset::url('/landing/css/site.css'), ENT_QUOTES, 'UTF-8') ?>">
</head>
<body class="lp-body lp-body--booking" data-usd-brl-rate="<?= htmlspecialchars((string) ExchangeRate::rate(), ENT_QUOTES, 'UTF-8') ?>">
<a class="skip-link" href="#conteudo"><?= Lang::e('a11y.skip_content') ?></a>
<?php View::partial('partials/public_header', ['asset' => $asset, 'locale' => $locale, 'activeNav' => 'booking']); ?>

<main id="conteudo" class="lp-booking-page">
    <?php if (($lead_banner ?? null) === 'limite'): ?>
        <p class="lp-lead-banner lp-lead-banner--warn" role="alert"><?= Lang::e('landing.lead_limite', ['phone' => Contact::phoneDisplay()]) ?></p>
    <?php elseif (($lead_banner ?? null) === 'erro'): ?>
        <p class="lp-lead-banner lp-lead-banner--warn" role="alert"><?= Lang::e('landing.lead_erro') ?></p>
    <?php endif; ?>

    <?php if (($lead_banner ?? null) === 'ok'): ?>
    <section class="lp-section lp-section--wide lp-booking-form-section" id="reserva" aria-labelledby="lead-success-title">
        <div class="lp-booking-wrap lp-booking-wrap--page">
            <?php
            View::partial('partials/landing_lead_success', [
                'asset' => $asset,
                'leadWhatsappUrl' => $leadWhatsappUrl ?? null,
            ]);
            ?>
        </div>
    </section>
    <?php else: ?>

    <section class="lp-section lp-section--wide">
        <header class="lp-section-head">
            <p class="lp-hero-kicker"><?= Lang::e('landing.nav_reservar') ?></p>
            <h1><?= Lang::e('booking.title') ?></h1>
            <p class="lp-section-lead"><?= Lang::e('booking.lead') ?></p>
        </header>

        <form class="lp-date-filter card" method="get" action="<?= $asset('/reservar') ?>" id="booking-date-filter">
            <div class="lp-booking-grid lp-booking-grid--filter">
                <div class="lp-field lp-field--grow lp-pickup-wrap">
                    <label class="lp-field-inner" for="filter-local">
                        <span class="lp-label"><?= Lang::e('landing.form_local_label') ?></span>
                        <select class="lp-input" name="local" id="filter-local" required>
                            <option value="" disabled <?= $local === '' ? 'selected' : '' ?>><?= Lang::e('landing.form_local_ph') ?></option>
                            <?php foreach (LeadPickupOptions::choices() as $opt): ?>
                                <option value="<?= htmlspecialchars($opt['value'], ENT_QUOTES, 'UTF-8') ?>" <?= $local === $opt['value'] ? 'selected' : '' ?>><?= htmlspecialchars($opt['label'], ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <div class="lp-hotel-name<?= $hotelFilterSelected ? ' lp-hotel-name--visible' : '' ?>" id="lp-filter-hotel-name">
                        <label class="lp-field-inner" for="filter-hotel-nome">
                            <span class="lp-label"><?= Lang::e('landing.form_hotel_label') ?></span>
                            <input class="lp-input" type="text" name="hotel_nome" id="filter-hotel-nome" maxlength="120"
                                   placeholder="<?= Lang::e('landing.form_hotel_ph') ?>"
                                   <?= $hotelFilterSelected ? '' : 'disabled' ?>
                                   value="<?= htmlspecialchars($hotelNome, ENT_QUOTES, 'UTF-8') ?>">
                        </label>
                    </div>
                </div>
                <label class="lp-field">
                    <span class="lp-label"><?= Lang::e('landing.form_pickup') ?></span>
                    <input class="lp-input" type="date" name="inicio" value="<?= htmlspecialchars($inicio, ENT_QUOTES, 'UTF-8') ?>" min="<?= $today ?>" required>
                </label>
                <label class="lp-field">
                    <span class="lp-label"><?= Lang::e('landing.form_return') ?></span>
                    <input class="lp-input" type="date" name="fim" value="<?= htmlspecialchars($fim, ENT_QUOTES, 'UTF-8') ?>" min="<?= htmlspecialchars($inicio !== '' ? $inicio : $today, ENT_QUOTES, 'UTF-8') ?>" required>
                </label>
                <?php if ($selectedCarId > 0): ?>
                    <input type="hidden" name="car" value="<?= $selectedCarId ?>">
                <?php endif; ?>
                <div class="lp-field lp-field--btn">
                    <span class="lp-label lp-label--ghost" aria-hidden="true">&nbsp;</span>
                    <button class="btn btn-secondary" type="submit"><?= Lang::e('booking.filter_dates') ?></button>
                </div>
            </div>
        </form>
    </section>

    <section class="lp-section lp-section--wide" id="frota" aria-labelledby="booking-fleet-title">
        <header class="lp-section-head">
            <h2 id="booking-fleet-title"><?= Lang::e('landing.fleet_title') ?></h2>
            <?php if ($inicio !== '' && $fim !== ''): ?>
                <p class="lp-section-lead"><?= Lang::e('booking.dates_filtered', ['from' => $inicio, 'to' => $fim]) ?></p>
            <?php endif; ?>
        </header>

        <?php if ($cars === []): ?>
            <div class="lp-empty-state lp-empty-state--icon">
                <p><?= Lang::e('booking.empty') ?></p>
                <a class="btn btn-primary" href="<?= $asset('/reservar') ?>"><?= Lang::e('booking.clear_filters') ?></a>
            </div>
        <?php else: ?>
            <div class="lp-fleet">
                <?php foreach ($cars as $car): ?>
                    <?php
                    $filterKey = Car::landingFilterKey((string) $car['category']);
                    $img = !empty($car['image_url'])
                        ? Car::publicImageUrl((string) $car['image_url'])
                        : Car::publicImageUrl(null);
                    $alt = htmlspecialchars((string) $car['brand'] . ' ' . (string) $car['model'], ENT_QUOTES, 'UTF-8');
                    $isSelected = (int) $car['id'] === $selectedCarId;
                    ?>
                    <article class="lp-car<?= $isSelected ? ' lp-car--selected' : '' ?>" data-category="<?= htmlspecialchars($filterKey, ENT_QUOTES, 'UTF-8') ?>">
                        <div class="lp-car-img">
                            <?php if (($car['status'] ?? '') === 'available'): ?>
                                <span class="lp-car-badge"><?= Lang::e('car.available') ?></span>
                            <?php endif; ?>
                            <img src="<?= htmlspecialchars($img, ENT_QUOTES, 'UTF-8') ?>" alt="<?= $alt ?>" width="360" height="230" loading="lazy">
                        </div>
                        <div class="lp-car-body">
                            <p class="lp-car-group"><?= Lang::e('category.' . ($car['category'] ?? 'standard')) ?></p>
                            <h3><?= $alt ?></h3>
                            <p class="lp-car-price"><?= Lang::e('landing.car_price_from') ?> <strong><?= Formatter::moneyWithBrl((float) $car['daily_rate']) ?></strong> <span><?= Lang::e('landing.car_per_day') ?></span></p>
                            <a class="btn btn-block btn-primary" href="#reserva" data-car-id="<?= (int) $car['id'] ?>" data-car-label="<?= $alt ?>" data-daily-rate="<?= htmlspecialchars((string) (float) ($car['daily_rate'] ?? 0), ENT_QUOTES, 'UTF-8') ?>"><?= Lang::e('booking.cta') ?></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="lp-section lp-section--wide lp-booking-form-section" id="reserva" aria-labelledby="booking-form-title">
        <header class="lp-section-head">
            <h2 id="booking-form-title"><?= Lang::e('landing.form_title') ?></h2>
            <p class="lp-section-lead"><?= Lang::e('landing.lead_hint') ?></p>
        </header>
        <div class="lp-booking-wrap lp-booking-wrap--page">
            <?php
            View::partial('partials/landing_lead_form', [
                'asset' => $asset,
                'leadOld' => $leadOld,
                'leadErrors' => $leadErrors,
                'selectedCar' => $selectedCar,
                'formAction' => '/lead',
                'returnPath' => '/reservar',
            ]);
            ?>
        </div>
    </section>
    <?php endif; ?>
</main>

<?php View::partial('partials/public_footer', ['asset' => $asset]); ?>
<script src="<?= htmlspecialchars(Asset::url('/js/lang-switcher.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
<script src="<?= htmlspecialchars(Asset::url('/js/cookie-notice.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
<script src="<?= htmlspecialchars(Asset::url('/landing/js/phone-country.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
<script src="<?= htmlspecialchars(Asset::url('/landing/js/site.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
<script src="<?= htmlspecialchars(Asset::url('/landing/js/lead-form.js'), ENT_QUOTES, 'UTF-8') ?>" defer></script>
</body>
</html>
