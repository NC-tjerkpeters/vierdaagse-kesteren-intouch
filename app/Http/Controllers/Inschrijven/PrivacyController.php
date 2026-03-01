<?php

namespace App\Http\Controllers\Inschrijven;

use App\Http\Controllers\Controller;

class PrivacyController extends Controller
{
    public function show()
    {
        return view('inschrijven.privacy');
    }
}
