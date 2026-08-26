<?php
use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite; 
use App\Models\Usuario;       
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ConversaController;
use App\Http\Controllers\MensagemController;
use App\Http\Models\Mensagem;

//rotas para abas
Route::get('/', function () { return view('welcome');});
Route::get('/chat', [ConversaController::class, 'index']);
Route::get('/guest', [AuthController::class, 'guest']);
Route::get('/gear', function(){return view('profile');})->middleware('auth');

//rotas de autenticação
Route::get('/auth/redirect', [AuthController::class, 'login']);
Route::get('/auth/callback', [AuthController::class, 'callback']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');

//Rotas da barra de conversas
Route::get('/conversas', [ConversaController::class,'getAll'])->middleware('auth');
Route::post('/conversas/store', [ConversaController::class, 'store'])->middleware('auth');
Route::put('/conversas/edit/{id}', [ConversaController::class, 'edit'])->middleware('auth');
Route::delete('/conversas/{id}', [ConversaController::class, 'destroy'])->middleware('auth');
Route::get('/conversas/show/{id}', [ConversaController::class, 'show'])->middleware('auth');
Route::get('/conversas/refresh', [ConversaController::class, 'reload']);

//Rotas de gerenciamento do perfil
Route::delete('/delete', [AuthController::class, 'delete'])->middleware('auth');
Route::put('/profile/edit', [AuthController::class, 'update'])->middleware('auth');

//Rotas de mensagem
Route::post('/mensagens/store', [MensagemController::class, 'store'])->middleware('auth');
Route::get('/mensagens/{id}/getall', [MensagemController::class, 'getAll'])->middleware('auth');
Route::delete('/mensagens/{id}', [MensagemController::class, 'destroy'])->middleware('auth');
Route::post('/perguntar', [MensagemController::class, 'perguntar']);
Route::post('/mensagens/edit', [MensagemController::class,'edit'])->middleware('auth');

