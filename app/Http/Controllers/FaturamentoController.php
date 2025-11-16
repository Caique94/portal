<?php

namespace App\Http\Controllers;

use App\Models\Faturamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FaturamentoController extends Controller
{

    public function view()
    {
        $user = Auth::user();
        return view('faturamento', compact('user'));
    }

}