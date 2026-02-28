<?php

return [
    /*
    |--------------------------------------------------------------------------
    | E-mailtemplates voor communicatie naar deelnemers
    |--------------------------------------------------------------------------
    |
    | Plaatshouders: {{voornaam}}, {{achternaam}}, {{afstand}}, {{edition_name}},
    | {{start_datum}}, {{eind_datum}}, {{inschrijf_url}}
    |
    */
    'templates' => [
        'voorbereiding' => [
            'name' => 'Voorbereiding – week voor start',
            'subject' => 'Over een week begint de Vierdaagse Kesteren {{edition_name}}!',
            'body' => <<<'HTML'
<p>Beste {{voornaam}},</p>

<p>Over een week begint de Vierdaagse Kesteren {{edition_name}}. We kijken ernaar uit je te verwelkomen!</p>

<p><strong>Je gegevens</strong></p>
<ul>
    <li><strong>Naam</strong>: {{voornaam}} {{achternaam}}</li>
    <li><strong>Afstand</strong>: {{afstand}}</li>
</ul>

<p><strong>Praktische info</strong></p>
<ul>
    <li>Data: {{start_datum}} t/m {{eind_datum}}</li>
    <li>Neem je ticket mee (digitaal of geprint) – de QR-code wordt bij start en controlepunten gescand</li>
    <li>De routes vind je op <a href="{{inschrijf_url}}">onze website</a></li>
</ul>

<p>Tot snel!</p>
<p>Met sportieve groet,<br>Vierdaagse Kesteren</p>
HTML
        ],
        'herinnering' => [
            'name' => 'Herinnering – dag voor start',
            'subject' => 'Morgen begint de Vierdaagse Kesteren {{edition_name}}',
            'body' => <<<'HTML'
<p>Beste {{voornaam}},</p>

<p>Morgen is het zover: de eerste avond van de Vierdaagse Kesteren {{edition_name}}!</p>

<p>Vergeet niet je ticket mee te nemen (digitaal of geprint) met de QR-code. We scannen deze bij de start en op de controlepunten.</p>

<p>Tot morgen!</p>
<p>Met sportieve groet,<br>Vierdaagse Kesteren</p>
HTML
        ],
        'routes_bekend' => [
            'name' => 'Routes bekend',
            'subject' => 'De routes van de Vierdaagse Kesteren {{edition_name}} zijn bekend',
            'body' => <<<'HTML'
<p>Beste {{voornaam}},</p>

<p>De wandelroutes van de Vierdaagse Kesteren {{edition_name}} zijn nu online beschikbaar.</p>

<p>Je kunt ze bekijken en downloaden op: <a href="{{inschrijf_url}}">{{inschrijf_url}}</a></p>

<p>Je loopt de afstand <strong>{{afstand}}</strong>. Controleer voor welke avonden jouw route loopt.</p>

<p>Tot op de route!</p>
<p>Met sportieve groet,<br>Vierdaagse Kesteren</p>
HTML
        ],
        'algemeen' => [
            'name' => 'Algemeen bericht (eigen tekst)',
            'subject' => '',
            'body' => '',
            'custom' => true,
        ],
    ],
];
