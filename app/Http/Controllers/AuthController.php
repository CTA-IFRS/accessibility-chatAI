<?php

namespace App\Http\Controllers;
use MongoDB\Laravel\Auth\User as Authenticatable;
use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login()
    {
        return Socialite::driver('google')
            ->stateless()
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = Usuario::where('google_id', $googleUser->getId())->first();

        if (!$user) {
            $user = Usuario::create([
                'google_id' => $googleUser->getId(),
                'nome' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
            ]);

            Auth::login($user);
            return redirect('/')->with([
                'create' => 'Conta criada com sucesso! Faça login novamente para conversar.'
            ]);
        } else {
            $user->update([
                'nome' => $googleUser->getName(),
            ]);
            Auth::login($user, true);
            return redirect('/chat');
        }

    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }

    public function guest()
    {
        if (Auth::check()) {
            Auth::logout();
        }
        return redirect('/chat');
    }

    public function delete()
    {
        $user = Auth::user();

        if ($user) {
            Auth::logout();
            $user->delete();
            return redirect('/')->with('delete', 'Conta deletada com sucesso.');
        }
        return redirect('/')->with('error', 'Usuário não encontrado.');
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            $user->nome = $request->username;
            $user->save();
            return redirect('/gear')->with('update', 'Nome de usuário atualizado com sucesso.');
        }
    }
}