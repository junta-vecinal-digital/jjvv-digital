<?php

namespace App\Http\Controllers;

class IngresoPendienteController extends Controller
{
    public function index()
    {
        return redirect()->route('ingresos.index');
    }
}
