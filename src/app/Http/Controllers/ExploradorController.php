<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ExploradorController extends Controller
{
    public function index(Request $request)
    {
        $archivos = $request->user()->archivos()
            ->whereNull('carpeta_id')
            ->latest()
            ->get();

        return view('dashboard', [
            'archivos' => $archivos,
            'usuario' => $request->user(),
        ]);
    }
}
