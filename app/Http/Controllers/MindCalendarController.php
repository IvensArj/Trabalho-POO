<?php

namespace App\Http\Controllers;

class MindCalendarController extends Controller
{
    /**
     * Página principal do MindCalendar.
     *
     * A renderização e a lógica de criação de eventos são feitas pelo
     * componente Livewire `App\Livewire\MindCalendar\Index`, portanto este
     * controller apenas devolve a view que o carrega.
     */
    public function index()
    {
        return view('mindcalendar.index');
    }
}
