<?php
use App\Http\Controllers\BenefitsController;
use Illuminate\Support\Facades\Route;


Route::post('/benefits-list', [BenefitsController::class , 'benefitsList']);
