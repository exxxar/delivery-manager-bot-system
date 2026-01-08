<?php

use App\Facades\StartCodesService;

StartCodesService::bot()
    ->controller(\App\Http\Controllers\StartCodesHandlerController::class)
    ->regular("/^([a-f\d]{32})$/i", "roleInviteAction")
    ->regular("/\b([0-9]{8,10})role\b/", "acceptRoleChange");





