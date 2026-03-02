# Evaluatiemodule – ontwerp

Evaluatiemodule voor het verzamelen van feedback van deelnemers na de Vierdaagse Kesteren.

---

## 1. Doel

Na afloop van het evenement een eenvoudige manier bieden om deelnemers te vragen:
- Hoe tevreden ze waren
- Wat goed ging
- Wat beter kan
- Of ze volgend jaar weer meedoen

De organisatie krijgt zo inzicht om het evenement te verbeteren.

---

## 2. Waar in de applicatie

**Locatie:** Inschrijvingen → Evaluatie (nieuwe submenu-optie in de dropdown)

Reden: Evaluatie richt zich op deelnemers (inschrijvingen), net als Communicatie. Logisch om het daar te groeperen.

**Permissies:**
- `evaluatie_view` – Overzicht bekijken, resultaten inzien
- `evaluatie_send` – Evaluatie aanmaken en versturen
- `evaluatie_manage` – Vragen bewerken, evaluatie verwijderen (optioneel, kan samenvallen met `evaluatie_send`)

---

## 3. Gebruikersflow

### 3.1 Organisatie (Intouch)

1. **Overzicht** – Lijst van evaluaties voor de geselecteerde editie
   - Naam, datum aangemaakt, verzenddatum, aantal reacties / totaal deelnemers, status (concept / verstuurd / afgesloten)

2. **Nieuwe evaluatie aanmaken**
   - Naam (bijv. "Evaluatie Vierdaagse 2026")
   - Doelgroep:
     - Alle ingeschreven deelnemers (betaald)
     - Alleen deelnemers die alle 4 avonden hebben voltooid
     - Alleen deelnemers met medaille (wants_medal = true)
   - Vragen toevoegen (zie vraagtypen hieronder)
   - Introtekst (boven het formulier)
   - Bedanktekst (na invullen)
   - Optioneel: sluitingsdatum (na deze datum kan het formulier niet meer worden ingevuld)

3. **Versturen**
   - E-mail naar alle deelnemers in de doelgroep
   - Unieke, signed link per deelnemer (zo kunnen we respons koppelen)
   - Onderwerp en korte mailtekst (met link) aanpasbaar

4. **Resultaten bekijken**
   - Respons: "47 van 312 deelnemers hebben gereageerd (15%)"
   - Per vraag: samenvatting
   - Export naar CSV/Excel

### 3.2 Deelnemer

1. Ontvangt e-mail met link: `inschrijven.vierdaagsekesteren.nl/evaluatie/{token}`
2. Klikt op link → opent evaluatieformulier
3. Ziet introtekst, vult vragen in
4. Klikt Versturen → bedanktmelding
5. Bij opnieuw klikken op link: "Je hebt deze evaluatie al ingevuld. Bedankt!"

---

## 4. Vraagtypen

| Type | Omschrijving | Voorbeeld |
|------|--------------|-----------|
| **Beoordeling (1–5)** | Sterren of cijfers 1–5 | "Hoe tevreden was je over de organisatie?" |
| **NPS (0–10)** | Net Promoter Score | "Hoe waarschijnlijk is het dat je volgend jaar weer meedoet? (0 = zeer onwaarschijnlijk, 10 = zeer waarschijnlijk)" |
| **Meerkeuze (enkel)** | Eén optie kiezen | "Hoe heb je ons gevonden?" → [Website / Social media / Via anderen / Anders] |
| **Open vraag** | Vrije tekst | "Wat ging er goed? Wat kan beter?" |

Standaard vragenlijst (optioneel template):
- NPS: "Hoe waarschijnlijk is het dat je volgend jaar weer meedoet?"
- Beoordeling: "Hoe tevreden was je over de organisatie?"
- Beoordeling: "Hoe tevreden was je over de route(s)?"
- Open: "Wat vond je het beste?"
- Open: "Wat kunnen we volgend jaar verbeteren?"

---

## 5. Schermen (wireframe-achtig)

### 5.1 Overzicht (Intouch)

```
┌─────────────────────────────────────────────────────────────────┐
│ Evaluatie                                    [Nieuwe evaluatie] │
├─────────────────────────────────────────────────────────────────┤
│ Editie: 2026 ▾                                                   │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│ ┌──────────────────────────────────────────────────────────────┐ │
│ │ Evaluatie Vierdaagse 2026                    Verstuurd 15 mrt │ │
│ │ 47 / 312 reacties (15%)                      [Resultaten]     │ │
│ └──────────────────────────────────────────────────────────────┘ │
│                                                                  │
│ ┌──────────────────────────────────────────────────────────────┐ │
│ │ Evaluatie Vierdaagse 2025                    Afgesloten       │ │
│ │ 89 / 298 reacties (30%)                      [Resultaten]     │ │
│ └──────────────────────────────────────────────────────────────┘ │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### 5.2 Evaluatie aanmaken / bewerken (Intouch)

```
┌─────────────────────────────────────────────────────────────────┐
│ ← Terug   Evaluatie aanmaken                                     │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│ Naam *                                                            │
│ [Evaluatie Vierdaagse 2026                              ]         │
│                                                                  │
│ Doelgroep *                                                       │
│ ○ Alle betaalde deelnemers                                        │
│ ● Alleen deelnemers die alle 4 avonden hebben voltooid            │
│ ○ Alleen deelnemers die een medaille willen                       │
│                                                                  │
│ Sluitingsdatum (optioneel)                                        │
│ [2026-04-01    ] Na deze datum kan het formulier niet meer ingevuld│
│                                                                  │
│ ─── Vragen ─────────────────────────────────────────────────     │
│                                                                  │
│ 1. [Hoe waarschijnlijk is het dat je volgend jaar weer meedoet?]  │
│    Type: [NPS (0-10)     ▾]                          [Verwijder]  │
│                                                                  │
│ 2. [Hoe tevreden was je over de organisatie?            ]         │
│    Type: [Beoordeling 1-5▾]                         [Verwijder]   │
│                                                                  │
│ 3. [Wat kunnen we volgend jaar verbeteren? (optioneel)   ]        │
│    Type: [Open vraag     ▾]                         [Verwijder]   │
│                                                                  │
│                                        [+ Vraag toevoegen]        │
│                                                                  │
│ ─── Teksten ────────────────────────────────────────────────     │
│                                                                  │
│ Introtekst (boven het formulier)                                  │
│ [Bedankt voor je deelname aan de Vierdaagse Kesteren!     ]       │
│ [We horen graag je mening om het evenement volgend jaar   ]       │
│ [nog beter te maken. Het duurt ongeveer 2 minuten.        ]       │
│                                                                  │
│ Bedanktekst (na versturen)                                        │
│ [Bedankt voor je feedback! We nemen je suggesties mee.    ]       │
│                                                                  │
│ ─── Versturen ───────────────────────────────────────────────    │
│                                                                  │
│ Mailonderwerp *                                                   │
│ [Jouw mening over de Vierdaagse Kesteren 2026             ]       │
│                                                                  │
│ Mailtekst (korte uitleg + de link wordt automatisch toegevoegd)   │
│ [Bedankt voor je deelname! Vul onderstaande enquête in:   ]       │
│ [                                              ]                  │
│ [{{link}} wordt vervangen door de persoonlijke link       ]       │
│                                                                  │
│ [Opslaan als concept]  [Versturen naar 312 deelnemers]            │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### 5.3 Resultaten (Intouch)

```
┌─────────────────────────────────────────────────────────────────┐
│ ← Terug   Resultaten: Evaluatie Vierdaagse 2026                  │
├─────────────────────────────────────────────────────────────────┤
│ 47 van 312 deelnemers hebben gereageerd (15%)    [Export CSV]    │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│ Hoe waarschijnlijk is het dat je volgend jaar weer meedoet?      │
│ (NPS 0-10)                                                        │
│ ┌────────────────────────────────────────────────────────────┐   │
│ │ 0  1  2  3  4  5  6  7  8  9  10                           │   │
│ │ ·  ·  ·  ·  ·  ·  █  ██ ███ █████ ████                      │   │
│ │ Gemiddeld: 8.2   Promoters (9-10): 68%  Passives (7-8): 21% │   │
│ └────────────────────────────────────────────────────────────┘   │
│                                                                  │
│ Hoe tevreden was je over de organisatie? (1-5)                    │
│ ★★★★★ 4.3 gemiddeld   [32] [10] [3] [1] [1]  (antwoorden per ★) │
│                                                                  │
│ Wat kunnen we volgend jaar verbeteren? (open)                     │
│ ┌────────────────────────────────────────────────────────────┐   │
│ │ "Meer parkeerplaatsen bij de start"                         │   │
│ │ "Routebeschrijving was soms onduidelijk"                    │   │
│ │ "Mooi georganiseerd, niks aan te veranderen!"               │   │
│ │ ...                                           [Toon alle]   │   │
│ └────────────────────────────────────────────────────────────┘   │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### 5.4 Deelnemerformulier (inschrijven-domein, publiek)

```
┌─────────────────────────────────────────────────────────────────┐
│                    Vierdaagse Kesteren                           │
│                    Evaluatie 2026                                │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│ Bedankt voor je deelname aan de Vierdaagse Kesteren!             │
│ We horen graag je mening om het evenement volgend jaar nog       │
│ beter te maken. Het duurt ongeveer 2 minuten.                    │
│                                                                  │
│ ─────────────────────────────────────────────────────────────    │
│                                                                  │
│ Hoe waarschijnlijk is het dat je volgend jaar weer meedoet?      │
│                                                                  │
│  [0] [1] [2] [3] [4] [5] [6] [7] [8] [9] [10]                   │
│       Zeer onwaarschijnlijk  ←→  Zeer waarschijnlijk             │
│                                                                  │
│ Hoe tevreden was je over de organisatie?                         │
│                                                                  │
│  ○ ★  ○ ★★  ○ ★★★  ○ ★★★★  ○ ★★★★★                              │
│                                                                  │
│ Wat kunnen we volgend jaar verbeteren? (optioneel)               │
│ ┌────────────────────────────────────────────────────────────┐   │
│ │                                                            │   │
│ └────────────────────────────────────────────────────────────┘   │
│                                                                  │
│                    [Versturen]                                   │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

### 5.5 Bedanktpagina (na versturen)

```
┌─────────────────────────────────────────────────────────────────┐
│                    Vierdaagse Kesteren                           │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│                    ✓ Bedankt!                                    │
│                                                                  │
│        Bedankt voor je feedback! We nemen je suggesties mee      │
│        bij de voorbereiding van volgend jaar.                    │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 6. Datamodel (voorstel)

```
evaluations
├── id
├── edition_id
├── name
├── target (enum: all_paid, all_finished, medal_only)
├── intro_text (text)
├── thank_you_text (text)
├── closes_at (nullable, datetime)
├── sent_at (nullable, datetime)
├── mail_subject
├── mail_body (text, met {{link}} placeholder)
├── created_at, updated_at
└── created_by (user_id, nullable)

evaluation_questions
├── id
├── evaluation_id
├── type (enum: rating, nps, choice, text)
├── question_text
├── sort_order
├── options (JSON, voor choice: ["Optie A", "Optie B"])
├── is_required (boolean, default true)
└── created_at, updated_at

evaluation_responses
├── id
├── evaluation_id
├── registration_id (koppeling naar deelnemer)
├── submitted_at
└── created_at

evaluation_answers
├── id
├── response_id
├── question_id
├── value (text/JSON: "8" voor NPS, "4" voor rating, "Optie A", vrije tekst)
└── created_at
```

**Tokens voor link:**  
Signed URL met `registration_id` en `evaluation_id`. Laravel `signed` middleware.  
Route: `GET /evaluatie/{evaluation}/invitation/{registration}?signature=...`

---

## 7. Technische overwegingen

- **E-mail:** Via bestaande Communicatie/Graph-setup. Evaluatie-e-mail kan als aparte job of via dezelfde queue.
- **Signed URLs:** Geldigheid bijv. 30 dagen (deelnemer hoeft niet meteen te reageren).
- **Privacy:** Respons is gekoppeld aan inschrijving (persoon). Vermeld in privacyverklaring dat we evaluaties gebruiken om het evenement te verbeteren. Optioneel: anonieme modus (geen registration_id opslaan).
- **Bestaande Communicatie:** Evaluatie is een specifieke flow; geen volledige merge met Communicatie-module. Wel hergebruik van mail-infrastructuur.

---

## 8. Optionele uitbreidingen (later)

- **Anonieme modus** – Geen koppeling naar deelnemer, alleen aggregaat.
- **Templates** – Standaard vragenlijst als startpunt.
- **Herinnering** – Na X dagen automatische herinnering naar niet-respondenten.
- **Vergelijking jaren** – "NPS 2025: 7.8, 2026: 8.2".
- **Vragenbank** – Herbruikbare vragen over meerdere evaluaties.

---

## 9. Volgende stap

Als dit ontwerp akkoord is, kan de module worden uitgewerkt in:
1. Migraties en modellen  
2. Controllers en routes (Intouch + inschrijven-domein)  
3. Views (overzicht, aanmaken, resultaten, deelnemerformulier)  
4. E-mailverzending en PermissionSeeder-update  
