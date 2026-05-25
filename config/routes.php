<?php

declare(strict_types=1);

return [
    'GET:/' => ['HomeController', 'index'],

    'GET:/privacidade' => ['LegalController', 'privacy'],
    'GET:/termos' => ['LegalController', 'terms'],

    'GET:/health' => ['HealthController', 'index'],

    'GET:/robots.txt' => ['RobotsController', 'index'],
    'GET:/sitemap.xml' => ['SitemapController', 'index'],
    'GET:/.well-known/security.txt' => ['SecurityTxtController', 'index'],
    'POST:/lead' => ['LeadController', 'submit'],

    'GET:/login' => ['AuthController', 'loginForm'],
    'POST:/login' => ['AuthController', 'login'],
    'GET:/forgot-password' => ['AuthController', 'forgotForm'],
    'POST:/forgot-password' => ['AuthController', 'forgotSend'],
    'GET:/reset-password' => ['AuthController', 'resetForm'],
    'POST:/reset-password' => ['AuthController', 'resetSubmit'],
    'POST:/logout' => ['AuthController', 'logout', 'auth' => true],

    'GET:/dashboard' => ['DashboardController', 'index', 'auth' => true],

    'GET:/cars' => ['CarController', 'index', 'auth' => true],
    'GET:/cars/create' => ['CarController', 'createForm', 'auth' => true, 'role' => 'owner'],
    'POST:/cars' => ['CarController', 'create', 'auth' => true, 'role' => 'owner'],
    'GET:/cars/{id}' => ['CarController', 'show', 'auth' => true],
    'GET:/cars/{id}/edit' => ['CarController', 'editForm', 'auth' => true, 'role' => 'owner'],
    'POST:/cars/{id}/update' => ['CarController', 'update', 'auth' => true, 'role' => 'owner'],
    'POST:/cars/{id}/delete' => ['CarController', 'delete', 'auth' => true, 'role' => 'owner'],

    'GET:/customers' => ['CustomerController', 'index', 'auth' => true],
    'GET:/customers/create' => ['CustomerController', 'createForm', 'auth' => true],
    'POST:/customers' => ['CustomerController', 'create', 'auth' => true],
    'GET:/customers/{id}/edit' => ['CustomerController', 'editForm', 'auth' => true],
    'POST:/customers/{id}/update' => ['CustomerController', 'update', 'auth' => true],
    'GET:/customers/{id}/attachment' => ['CustomerController', 'attachment', 'auth' => true],

    'GET:/leads' => ['LeadsController', 'index', 'auth' => true, 'role' => 'owner'],
    'GET:/leads/export' => ['LeadsController', 'exportCsv', 'auth' => true, 'role' => 'owner'],
    'POST:/leads/{id}/status' => ['LeadsController', 'updateStatus', 'auth' => true, 'role' => 'owner'],
    'POST:/leads/{id}/convert' => ['LeadsController', 'convert', 'auth' => true, 'role' => 'owner'],

    'GET:/locations' => ['LocationController', 'index', 'auth' => true, 'role' => 'owner'],
    'GET:/locations/create' => ['LocationController', 'createForm', 'auth' => true, 'role' => 'owner'],
    'POST:/locations' => ['LocationController', 'create', 'auth' => true, 'role' => 'owner'],
    'GET:/locations/{id}/edit' => ['LocationController', 'editForm', 'auth' => true, 'role' => 'owner'],
    'POST:/locations/{id}/update' => ['LocationController', 'update', 'auth' => true, 'role' => 'owner'],

    'GET:/reservations' => ['ReservationController', 'index', 'auth' => true],
    'GET:/reservations/export' => ['ReservationController', 'exportCsv', 'auth' => true],
    'GET:/reservations/calendar' => ['ReservationController', 'calendar', 'auth' => true],
    'GET:/reservations/create' => ['ReservationController', 'createForm', 'auth' => true],
    'POST:/reservations' => ['ReservationController', 'create', 'auth' => true],
    'GET:/reservations/{id}' => ['ReservationController', 'show', 'auth' => true],
    'GET:/reservations/{id}/edit' => ['ReservationController', 'editForm', 'auth' => true],
    'POST:/reservations/{id}/update' => ['ReservationController', 'update', 'auth' => true],
    'POST:/reservations/{id}/cancel' => ['ReservationController', 'cancel', 'auth' => true],
    'POST:/reservations/{id}/confirm' => ['ReservationController', 'confirm', 'auth' => true],
    'POST:/reservations/{id}/activate' => ['ReservationController', 'activate', 'auth' => true],
    'POST:/reservations/{id}/complete' => ['ReservationController', 'complete', 'auth' => true],

    'GET:/users' => ['UserController', 'index', 'auth' => true, 'role' => 'owner'],
    'GET:/users/create' => ['UserController', 'createForm', 'auth' => true, 'role' => 'owner'],
    'POST:/users' => ['UserController', 'create', 'auth' => true, 'role' => 'owner'],
    'GET:/users/{id}/edit' => ['UserController', 'editForm', 'auth' => true, 'role' => 'owner'],
    'POST:/users/{id}/update' => ['UserController', 'update', 'auth' => true, 'role' => 'owner'],

    'GET:/reports' => ['ReportController', 'index', 'auth' => true, 'role' => 'owner'],
    'GET:/reports/export' => ['ReportController', 'exportCsv', 'auth' => true, 'role' => 'owner'],
    'GET:/audit' => ['AuditController', 'index', 'auth' => true, 'role' => 'owner'],

    'GET:/api/customers/search' => ['ApiController', 'customersSearch', 'auth' => true],
    'GET:/api/reservations/conflict' => ['ApiController', 'reservationConflict', 'auth' => true],
    'GET:/api/calendar/events' => ['ApiController', 'calendarEvents', 'auth' => true],
    'POST:/api/customers/quick' => ['ApiController', 'customersQuickCreate', 'auth' => true],
];
