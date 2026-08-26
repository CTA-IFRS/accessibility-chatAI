<?php

namespace App\Http\Controllers;

use App\Models\Conversa;
use App\Models\Usuario;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Services\ConversaService;
class ConversaController extends Controller
{

    public function index()
    {
        $conversas = (new ConversaService)->getConversas();
        return view('chat', ['conversas' => $conversas]);
    }

    public function getAll()
    {
        $conversa = (new ConversaService)->getConversas();
        return response()->json(['conversas' => $conversa]);
    }
    public function store(Request $request)
    {
        $titulo = $request->input('titulo');
        $conversa = (new ConversaService)->store($titulo);
        return response()->json(['id' => $conversa->id]);
    }

    public function edit(Request $request, ConversaService $conversaService)
    {
        $conversaService->update($request->input('id'), $request->input('nome'));

        return response()->json(['sucesso' => true]);
    }
    public function destroy($id)
    {
        (new ConversaService)->delete($id);
        return response()->json(['message' => 'Conversa deletada com sucesso!'], 200);
    }

    public function reload()
    {
        return redirect('/chat');
    }
}