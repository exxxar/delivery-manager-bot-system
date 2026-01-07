<?php

use App\Facades\StartCodesService;

StartCodesService::bot()
    ->controller(\App\Http\Controllers\StartCodesHandlerController::class)
    ->regular("/^([a-f\d]{32})$/i", "roleInviteAction");





