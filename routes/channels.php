<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('admin', function (User $user) {
    return $user->isAdmin();
});

Broadcast::channel('team.{tlEmail}', function (User $user, string $tlEmail) {
    return $user->email === $tlEmail || $user->isAdmin();
});
