<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompteController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Routes d'authentification (sans middleware d'authentification)
Route::prefix('v1/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])
        ->name('auth.login');

    Route::post('/refresh', [AuthController::class, 'refresh'])
        ->name('auth.refresh');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth:api')
        ->name('auth.logout');
});

// Routes API v1 avec authentification
Route::prefix('v1')->middleware(['auth:api'])->group(function () {

    // Routes pour les comptes (sans scope spécifique pour le moment)
    Route::get('/comptes', [CompteController::class, 'index'])
        ->middleware('logging')
        ->name('comptes.index');

    Route::get('/comptes/{compte}', [CompteController::class, 'show'])
        ->middleware('logging')
        ->name('comptes.show');

    Route::post('/comptes', [CompteController::class, 'store'])
        ->middleware('logging')
        ->name('comptes.store');

    // Route pour mettre à jour les informations client d'un compte
    Route::patch('/comptes/{compte}', [CompteController::class, 'update'])
        ->middleware('logging')
        ->name('comptes.update');

    // Route pour supprimer un compte
    Route::delete('/comptes/{compte}', [CompteController::class, 'destroy'])
        ->middleware('logging')
        ->name('comptes.destroy');

    // Routes admin (scope: admin) - à développer plus tard
    // Route::middleware('scope:admin')->prefix('admin')->group(function () {
    //     // Routes d'administration
    // });

});

// Route pour servir le fichier JSON de documentation Swagger
Route::get('/docs/api-docs.json', function () {
    $path = storage_path('api-docs/api-docs.json');

    if (!file_exists($path)) {
        abort(404, 'Documentation file not found');
    }

    return response()->file($path, [
        'Content-Type' => 'application/json',
        'Access-Control-Allow-Origin' => '*',
    ]);
})->name('swagger.json');

// Route alternative pour Swagger UI
Route::get('/api/docs', function () {
    return redirect('/docs');
});

// Route existante pour l'authentification (temporairement conservée)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

