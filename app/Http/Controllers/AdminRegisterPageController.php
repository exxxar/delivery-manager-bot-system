<?php

namespace App\Http\Controllers;

use App\Models\AdminInviteToken;
use Inertia\Inertia;

class AdminRegisterPageController extends Controller
{
    public function show(string $token)
    {
        Inertia::setRootView("pwa");
        return Inertia::render('AdminRegister', [
            'token' => $token,
        ]);
    }
}
