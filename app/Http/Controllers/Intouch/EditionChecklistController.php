<?php

namespace App\Http\Controllers\Intouch;

use App\Http\Controllers\Controller;
use App\Models\Edition;
use App\Models\EditionChecklistItem;
use Illuminate\Http\Request;

class EditionChecklistController extends Controller
{
    public function index(Edition $edition)
    {
        $this->authorize('editions_manage');

        $edition->load('checklistItems');

        return view('intouch.editions.checklist', [
            'edition' => $edition,
        ]);
    }

    public function update(Request $request, Edition $edition)
    {
        $this->authorize('editions_manage');

        $items = $edition->checklistItems;
        $rules = [];
        foreach ($items as $item) {
            $rules["done_{$item->id}"] = ['nullable', 'boolean'];
            $rules["note_{$item->id}"] = ['nullable', 'string', 'max:1000'];
        }
        $data = $request->validate($rules);

        foreach ($items as $item) {
            $item->update([
                'is_done' => (bool) ($data["done_{$item->id}"] ?? false),
                'note' => $data["note_{$item->id}"] ?? null,
            ]);
        }

        return redirect()
            ->route('intouch.beheer.editions.checklist', $edition)
            ->with('status', 'Checklist opgeslagen.');
    }

    public function addItem(Request $request, Edition $edition)
    {
        $this->authorize('editions_manage');

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $maxOrder = $edition->checklistItems()->max('sort_order') ?? 0;
        EditionChecklistItem::create([
            'edition_id' => $edition->id,
            'title' => $data['title'],
            'sort_order' => $maxOrder + 1,
        ]);

        return redirect()
            ->route('intouch.beheer.editions.checklist', $edition)
            ->with('status', 'Item toegevoegd.');
    }

    public function initFromDefaults(Edition $edition)
    {
        $this->authorize('editions_manage');

        if ($edition->checklistItems()->exists()) {
            return redirect()
                ->route('intouch.beheer.editions.checklist', $edition)
                ->with('info', 'De checklist heeft al items.');
        }

        foreach (config('edition_checklist.default_items', []) as $i => $title) {
            EditionChecklistItem::create([
                'edition_id' => $edition->id,
                'title' => $title,
                'sort_order' => $i + 1,
            ]);
        }

        return redirect()
            ->route('intouch.beheer.editions.checklist', $edition)
            ->with('status', 'Standaard checklist geladen.');
    }
}
