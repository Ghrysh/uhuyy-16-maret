<?php

use App\Http\Controllers\Api\UserCheckController;
use App\Http\Controllers\Api\SatkerController;
use App\Http\Controllers\Api\PenugasanController;

Route::get('/penugasan', [PenugasanController::class, 'index']);
Route::get('/check-user/{nip}', [UserCheckController::class, 'checkByNip']);
Route::get('/check-role/{nip}', [UserCheckController::class, 'checkRoleByNip']);
Route::get('/satker', [SatkerController::class, 'index']);