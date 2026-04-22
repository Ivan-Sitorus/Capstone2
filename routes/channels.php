<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Public channel — no auth needed for kasir order badge
// (Kasir already behind auth middleware in web.php)
Broadcast::channel('orders', fn() => true);
