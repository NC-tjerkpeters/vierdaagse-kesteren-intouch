<?php

namespace App\Http\Controllers\Intouch;

use App\Http\Controllers\Controller;
use App\Models\Edition;
use Illuminate\Http\Request;

class EditionSelectorController extends Controller
{
    public function set(Request $request)
    {
        $editionId = $request->input('edition_id');
        if ($editionId !== null && $editionId !== '') {
            $request->validate(['edition_id' => ['exists:editions,id']]);
            $editionId = (int) $editionId;
        } else {
            $editionId = null;
        }

        if ($editionId) {
            session(['edition_id' => $editionId]);
        } else {
            session()->forget('edition_id');
        }

        return redirect()->back();
    }
}
