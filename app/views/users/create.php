<?php
declare(strict_types=1);
?>
<div class="page-head">
    <h1 class="page-title"><?= Lang::e('user.create') ?></h1>
    <a class="btn btn-secondary" href="<?= Router::url('/users') ?>"><?= Lang::e('actions.back') ?></a>
</div>
<form class="card form-stack" method="post" action="<?= Router::url('/users') ?>">
    <?= Csrf::field() ?>
    <label class="label"><?= Lang::e('customer.name') ?></label>
    <input class="input" name="name" required>
    <label class="label"><?= Lang::e('auth.email') ?></label>
    <input class="input" type="email" name="email" required>
    <label class="label"><?= Lang::e('auth.password') ?></label>
    <input class="input" type="password" name="password" required minlength="8">
    <label class="label"><?= Lang::e('user.role') ?></label>
    <select class="input" name="role" id="user-role-select">
        <option value="owner"><?= Lang::e('user.owner') ?></option>
        <option value="operator"><?= Lang::e('user.operator') ?></option>
    </select>
    <label class="label"><?= Lang::e('customer.phone') ?></label>
    <input class="input" name="phone">
    <label class="label"><?= Lang::e('user.lang_pref') ?></label>
    <select class="input" name="lang_pref">
        <option value="pt-BR">pt-BR</option>
        <option value="en-US">en-US</option>
    </select>
    <label class="checkbox"><input type="checkbox" name="is_active" value="1" checked> <?= Lang::e('location.active') ?></label>
    <button class="btn btn-primary" type="submit"><?= Lang::e('actions.save') ?></button>
</form>
