<?php

namespace App\Http\Controllers\Intouch;

use App\Http\Controllers\Controller;
use App\Models\ParticipantEmailTemplate;
use Illuminate\Http\Request;

class ParticipantEmailTemplateController extends Controller
{
    public function index()
    {
        $this->authorize('communicatie_templates');

        $templates = ParticipantEmailTemplate::query()->orderBy('sort_order')->orderBy('name')->get();

        return view('intouch.inschrijvingen.communicatie-templates.index', [
            'templates' => $templates,
        ]);
    }

    public function create()
    {
        $this->authorize('communicatie_templates');

        return view('intouch.inschrijvingen.communicatie-templates.form', [
            'template' => null,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('communicatie_templates');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $data['sort_order'] = ParticipantEmailTemplate::query()->max('sort_order') + 1;

        ParticipantEmailTemplate::create($data);

        return redirect()->route('intouch.registrations.communicatie.templates')
            ->with('status', 'Template aangemaakt.');
    }

    public function edit(ParticipantEmailTemplate $template)
    {
        $this->authorize('communicatie_templates');

        return view('intouch.inschrijvingen.communicatie-templates.form', [
            'template' => $template,
        ]);
    }

    public function update(Request $request, ParticipantEmailTemplate $template)
    {
        $this->authorize('communicatie_templates');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $template->update($data);

        return redirect()->route('intouch.registrations.communicatie.templates')
            ->with('status', 'Template bijgewerkt.');
    }

    public function destroy(ParticipantEmailTemplate $template)
    {
        $this->authorize('communicatie_templates');

        $template->delete();

        return redirect()->route('intouch.registrations.communicatie.templates')
            ->with('status', 'Template verwijderd.');
    }
}
