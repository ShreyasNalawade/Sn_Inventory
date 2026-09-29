<?php

namespace App\Http\Controllers;

use App\Services\StoreAssistant;
use Illuminate\Http\Request;

class AiChatController extends Controller
{
    public function index()
    {
        return view('admin.aiChat');
    }

    public function ask(Request $request, StoreAssistant $assistant)
    {
        $request->merge([
            'message' => trim((string) $request->input('message', '')),
        ]);

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:500'],
        ]);

        return response()->json([
            'answer' => $assistant->reply($validated['message']),
        ]);
    }
}
