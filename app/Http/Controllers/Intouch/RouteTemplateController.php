<?php

namespace App\Http\Controllers\Intouch;

use App\Http\Controllers\Controller;
use App\Models\RouteTemplate;
use Illuminate\Http\Request;

class RouteTemplateController extends Controller
{
    public function index()
    {
        $this->authorize('routes_manage');

        $templates = RouteTemplate::query()
            ->with(['distance', 'points'])
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return view('intouch.route-templates.index', ['templates' => $templates]);
    }

    public function create()
    {
        $this->authorize('routes_manage');

        $distances = \App\Models\Distance::query()->orderBy('sort_order')->get();

        return view('intouch.route-templates.create', ['distances' => $distances]);
    }

    public function store(Request $request)
    {
        $this->authorize('routes_manage');

        $data = $request->validate([
            'distance_id' => ['required', 'exists:distances,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'word' => ['nullable', 'file', 'mimes:doc,docx', 'max:20480'],
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $template = RouteTemplate::create([
            'distance_id' => $data['distance_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'sort_order' => RouteTemplate::max('sort_order') + 1,
        ]);

        if ($request->hasFile('word')) {
            $template->storeWord($request->file('word'));
        }
        if ($request->hasFile('pdf')) {
            $template->storePdf($request->file('pdf'));
        }

        return redirect()->route('intouch.route-templates.edit', $template)
            ->with('status', 'Route toegevoegd aan bibliotheek. Voeg punten toe en upload Word/PDF.');
    }

    public function edit(RouteTemplate $routeTemplate)
    {
        $this->authorize('routes_manage');

        $routeTemplate->load(['distance', 'points']);

        return view('intouch.route-templates.edit', ['template' => $routeTemplate]);
    }

    public function update(Request $request, RouteTemplate $routeTemplate)
    {
        $this->authorize('routes_manage');

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'word' => ['nullable', 'file', 'mimes:doc,docx', 'max:20480'],
            'pdf' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $routeTemplate->update([
            'title' => $data['title'],
            'description' => $data['description'],
        ]);

        if ($request->hasFile('word')) {
            $routeTemplate->storeWord($request->file('word'));
        }
        if ($request->hasFile('pdf')) {
            $routeTemplate->storePdf($request->file('pdf'));
        }

        $routeTemplate->points()->delete();
        $points = $request->input('points', []);
        $points = is_array($points) ? $points : [];
        foreach (array_values($points) as $i => $p) {
            $name = is_array($p) ? ($p['name'] ?? '') : (string) $p;
            if (trim($name) !== '') {
                $routeTemplate->points()->create(['name' => trim($name), 'sort_order' => $i]);
            }
        }

        return redirect()->route('intouch.route-templates.index')->with('status', 'Route bijgewerkt.');
    }

    public function destroy(RouteTemplate $routeTemplate)
    {
        $this->authorize('routes_manage');

        $routeTemplate->deleteWord();
        $routeTemplate->deletePdf();
        $routeTemplate->delete();

        return redirect()->route('intouch.route-templates.index')->with('status', 'Route verwijderd uit bibliotheek.');
    }

    public function deleteWord(RouteTemplate $routeTemplate)
    {
        $this->authorize('routes_manage');

        $routeTemplate->deleteWord();

        return redirect()->route('intouch.route-templates.edit', $routeTemplate)->with('status', 'Word-document verwijderd.');
    }

    public function deletePdf(RouteTemplate $routeTemplate)
    {
        $this->authorize('routes_manage');

        $routeTemplate->deletePdf();

        return redirect()->route('intouch.route-templates.edit', $routeTemplate)->with('status', 'PDF verwijderd.');
    }

    public function pdf(RouteTemplate $routeTemplate)
    {
        $this->authorize('routes_view');

        if (! $routeTemplate->pdf_path || ! \Storage::disk('public')->exists($routeTemplate->pdf_path)) {
            abort(404);
        }

        return \Storage::disk('public')->response(
            $routeTemplate->pdf_path,
            basename($routeTemplate->pdf_path),
            ['Content-Type' => 'application/pdf']
        );
    }

    public function word(RouteTemplate $routeTemplate)
    {
        $this->authorize('routes_view');

        if (! $routeTemplate->word_path || ! \Storage::disk('public')->exists($routeTemplate->word_path)) {
            abort(404);
        }

        $mime = str_ends_with(strtolower($routeTemplate->word_path), '.docx')
            ? 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            : 'application/msword';

        return \Storage::disk('public')->response(
            $routeTemplate->word_path,
            basename($routeTemplate->word_path),
            ['Content-Type' => $mime]
        );
    }
}
