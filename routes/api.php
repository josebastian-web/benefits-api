<?php
use App\Http\Controllers\BenefitsController;
use Illuminate\Support\Facades\Route;


Route::get('test', [BenefitsController::class , 'benefitsList']);
