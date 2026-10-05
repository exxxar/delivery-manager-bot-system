<?php


namespace App\Http\Controllers;

use Inertia\Inertia;

class SupplierRegisterPageController extends Controller
{
    public function show(string $token)
    {

        Inertia::setRootView("pwa");
        return Inertia::render('SupplierRegister', [
            'token' => $token,
        ]);
    }
}
