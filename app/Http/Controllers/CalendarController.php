<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Evento;

class CalendarController extends Controller
{
    public function index()
    {
        return Evento::all();
    }

    public function store(Request $request)
    {
        $evento = Evento::create([
            'title' => $request->title,
            'start' => $request->start,
            'end'   => $request->end,
            'color' => $request->color
        ]);

        return response()->json($evento);
    }
}
