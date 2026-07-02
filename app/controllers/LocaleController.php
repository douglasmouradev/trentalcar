<?php

declare(strict_types=1);

final class LocaleController
{
    public function update(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            Flash::error(Lang::get('error.csrf'));
            Redirect::to('/');
        }
        $lang = (string) ($_POST['lang'] ?? '');
        if (!in_array($lang, ['pt-BR', 'en-US'], true)) {
            Redirect::to('/');
        }
        Lang::setLocale($lang);
        if (Auth::check()) {
            $uid = Auth::id();
            if ($uid !== null) {
                $stmt = Database::prepare('UPDATE users SET lang_pref = ? WHERE id = ?');
                $stmt->execute([$lang, $uid]);
                Auth::refreshUserFromDb();
            }
        }
        $fallback = Auth::check()
            ? Router::url(Auth::isPartner() ? '/partner/profile' : '/dashboard')
            : Router::url('/');
        $redirect = trim((string) ($_POST['redirect'] ?? ''));
        $target = $redirect !== '' ? $redirect : $fallback;
        $app = Config::app();
        $target = SafeRedirect::sameOriginOr($fallback, $target, $app['url'] ?? '');
        Redirect::toUrl($target);
    }
}
