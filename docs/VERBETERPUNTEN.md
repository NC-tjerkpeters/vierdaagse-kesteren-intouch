# Verbeterpunten & roadmap

**Belangrijk voor de ontwikkelaar (agent):** dit document is het centrale geheugen voor voortgang en openstaand werk in dit project. Bij elke sessie hier kijken om context te hebben; na afronden of starten van werk de status en voortgangslog hier bijwerken.

---

## Voor de ontwikkelaar – hoe dit document te gebruiken

- **Bij start sessie:** even door status-kolommen lopen om te zien wat 🔲 Todo / 🔄 Bezig is; prioriteit "Hoog" eerst als er geen specifieke opdracht is.
- **Bij start van een punt:** status naar 🔄 Bezig zetten; eventueel in notities of voortgangslog korte regel toevoegen (bijv. "2.1 gestart – polling-endpoint").
- **Na afronden:** status naar ✅ Gereed; in voortgangslog regel toevoegen met datum + wat er is gedaan (en in welke bestanden).
- **Code vinden:** sectie "Relevante bestanden" onderaan + notities in de tabellen verwijzen naar controllers, views, jobs.
- **Context van de app:** Laravel-project "Intouch Vierdaagse Kesteren" – inschrijvingen, sponsors, vrijwilligers, loopoverzicht, financiën, QR-scanner, evaluatiemodule. Intouch = beheeromgeving; inschrijven-domein = publiek. Zie ook `docs/EVALUATIE_MODULE.md`, `docs/ARCHITECTUUR.md`.

**Laatst bijgewerkt:** 2026-03-04

---

## Legenda status

| Status   | Betekenis        |
|----------|------------------|
| 🔲 Todo  | Nog te doen      |
| 🔄 Bezig | In ontwikkeling  |
| ✅ Gereed| Afgerond         |

---

## 1. Evaluatiemodule – uitbreidingen

| # | Onderdeel | Status | Prioriteit | Notities |
|---|-----------|--------|------------|----------|
| 1.1 | **Herinnering** – Na X dagen automatische herinneringsmail naar niet-respondenten | ✅ Gereed | Hoog | SendEvaluationReminderJob, veld reminder_days in form/store/update, planning bij send(). |
| 1.2 | **Vragenlijst-template** – Bij nieuwe evaluatie optie "Starten met standaardvragen" (NPS, tevredenheid, open vragen) | ✅ Gereed | Medium | config/evaluation.php default_questions, create?template=1, link op create-pagina. |
| 1.3 | **Vergelijking jaren** – Op resultatenpagina: "NPS dit jaar 8,2 / vorig jaar 7,8" | 🔲 Todo | Laag | Alleen tonen als er voor vorige editie een evaluatie met dezelfde vraag(soort) is. |

---

## 2. Versturen evaluaties – gebruiksvriendelijkheid

| # | Onderdeel | Status | Prioriteit | Notities |
|---|-----------|--------|------------|----------|
| 2.1 | **Voortgang bij versturen** – Tonen hoeveel mails al verzonden zijn (bijv. "Verstuurd: 45/312") | ✅ Gereed | Hoog | invitations_sent_count/total, sendStatus()-endpoint, polling in show.blade.php. |
| 2.2 | **Queue-monitoring** – In Beheer → Status: queue-length, of worker draait, eventueel failed jobs | ✅ Gereed | Medium | App\Health\Checks\QueueCheck, queue size + failed_jobs in statuspagina. |

---

## 3. Betrouwbaarheid & beheer

| # | Onderdeel | Status | Prioriteit | Notities |
|---|-----------|--------|------------|----------|
| 3.1 | **Featuretests** – Kritieke flows: inschrijving, (Mollie) betaling, evaluatie aanmaken/versturen/formulier | ✅ Gereed | Hoog | tests/Feature/EvaluationTest.php: send-status JSON, evaluatieformulier met signed URL. |
| 3.2 | **Back-up / export** – Afspraken documenteren: frequentie back-up, export "alle inschrijvingen + evaluaties editie X" | ✅ Gereed | Medium | Sectie "Back-up en export" in PRODUCTIE_INSTRUCTIES.md. |
| 3.3 | **Logging bij falen** – Bij falen SendEvaluationInvitationJob duidelijke log; optioneel "Laatste fouten" in Beheer → Status | 🔲 Todo | Medium | Job heeft al Log::warning. QueueCheck toont failed count; evt. later aparte "Laatste fouten". |

---

## 4. Organisatie & dagelijks gebruik

| # | Onderdeel | Status | Prioriteit | Notities |
|---|-----------|--------|------------|----------|
| 4.1 | **Editie-selector** – Gekozen editie overal duidelijk zichtbaar (header/sidebar) | 🔲 Todo | Medium | Controleer intouch layout: editie-dropdown/tekst altijd in beeld. |
| 4.2 | **Checklist** – Koppelen aan stappen o.a. "Evaluatie verstuurd", "Herinnering gepland"; korte instructie evaluatie in app | 🔲 Todo | Laag | Zie editions checklist. Optioneel checklist-item "Evaluatie uitgezet" + evt. "Herinnering gepland". |
| 4.3 | **Rollen** – Controleren of "alleen resultaten bekijken" vs "ook versturen" in praktijk voldoende is | 🔲 Todo | Laag | Huidig: evaluatie_view (incl. resultaten), evaluatie_send, evaluatie_manage. Geen wijziging tot behoefte. |

---

## 5. Kleinere verbeteringen

| # | Onderdeel | Status | Prioriteit | Notities |
|---|-----------|--------|------------|----------|
| 5.1 | **Evaluatie-overzicht** – Kolom "Respons %" (bijv. 15%) naast aantal reacties | ✅ Gereed | Laag | Stond al in index (respons_count/targetCount * 100). |
| 5.2 | **Sluitingsdatum zichtbaar** – Op evaluatie-detailpagina tonen: "Formulier sluit op …" | ✅ Gereed | Laag | show.blade.php: regel "Formulier sluit op …" + herinnering. |
| 5.3 | **Foutafhandeling queue/mail** – Duidelijke melding in UI als jobs niet in queue gezet kunnen worden | ✅ Gereed | Medium | EvaluationController::send – try/catch, with('error', ...) bij falen. |

---

## Voortgang log

**Ontwikkelaar:** bij afronden van een punt of belangrijke beslissing hier een regel toevoegen. Helpt volgende sessies (en jou) om te zien wat er is gedaan.

| Datum | Onderwerp | Notitie |
|-------|-----------|---------|
| 2026-03-02 | Document aangemaakt | Verbeterpunten uit gesprek vastgelegd; status overal Todo. |
| 2026-03-04 | 1.1 Herinnering | Migratie reminder_days + invitations_*; SendEvaluationReminderJob; form + store/update + send(). |
| 2026-03-04 | 2.1 Voortgang versturen | Kolommen invitations_sent_count/total; sendStatus() route; Job increment; show.blade.php polling. |
| 2026-03-04 | 2.2 Queue-monitoring | QueueCheck (queue size + failed_jobs) in AppServiceProvider. |
| 2026-03-04 | 1.2 Standaardvragen | config/evaluation.php, create?template=1, initialQuestions in _form. |
| 2026-03-04 | 3.1 Featuretests | EvaluationTest: send-status JSON, evaluatieformulier signed URL. |
| 2026-03-04 | 3.2 Back-up doc | PRODUCTIE_INSTRUCTIES.md: sectie Back-up en export. |
| 2026-03-04 | 5.1–5.3 | 5.1 al aanwezig; 5.2 sluitingsdatum + herinnering in show; 5.3 try/catch in send(). |

---

## Relevante bestanden

**Ontwikkelaar:** gebruik deze lijst om snel de juiste plek in de codebase te vinden; voeg bestanden toe zodra je nieuwe onderdelen aanraakt.

- Evaluatie: `app/Http/Controllers/Intouch/EvaluationController.php`, `app/Jobs/SendEvaluationInvitationJob.php`, `app/Jobs/SendEvaluationReminderJob.php`, `resources/views/intouch/inschrijvingen/evaluatie/`
- Evaluatie-config: `config/evaluation.php` (default_questions)
- Beheer status: `app/Http/Controllers/Intouch/HealthStatusController.php`, `app/Health/Checks/QueueCheck.php`, route `intouch.beheer.status`
- Permissies: `config/permissions.php`, `database/seeders/PermissionSeeder.php`
- Checklist: `resources/views/intouch/editions/checklist.blade.php`
- Layout: `resources/views/intouch/layout.blade.php`
