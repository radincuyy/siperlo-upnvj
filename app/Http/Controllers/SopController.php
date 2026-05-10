<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class SopController extends Controller
{
    public function index(): View
    {
        return view('sop.index');
    }
}
