<?php

namespace App\Http\Controllers\Intouch;

use App\Http\Controllers\Controller;
use App\Models\Edition;
use App\Models\EventDay;
use Illuminate\Http\Request;

class EditionController extends Controller
{
    public function index()
    {
        $this->authorize('editions_manage');

        $editions = Edition::query()
            ->withCount(['registrations', 'sponsors'])
            ->orderByDesc('start_date')
            ->get();

        $activeEdition = Edition::active();

        return view('intouch.editions.index', [
            'editions' => $editions,
            'activeEdition' => $activeEdition,
        ]);
    }

    public function create()
    {
        $this->authorize('editions_manage');

        return view('intouch.editions.create');
    }

    public function store(Request $request)
    {
        $this->authorize('editions_manage');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $edition = Edition::create([
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_active' => false,
        ]);

        foreach (['Dag 1', 'Dag 2', 'Dag 3', 'Dag 4'] as $i => $name) {
            EventDay::create([
                'edition_id' => $edition->id,
                'name' => $name,
                'sort_order' => $i + 1,
                'is_current' => $i === 0,
            ]);
        }

        Edition::activate($edition);

        return redirect()->route('intouch.editions.index')
            ->with('status', "Editie {$edition->name} is aangemaakt en actief.");
    }
}
