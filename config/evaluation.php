<?php

return [
    /*
    | Standaardvragen voor een nieuwe evaluatie (optioneel template).
    | Gebruikt bij "Starten met standaardvragen" op de aanmaakpagina.
    */
    'default_questions' => [
        [
            'type' => 'nps',
            'question_text' => 'Hoe waarschijnlijk is het dat je volgend jaar weer meedoet? (0 = zeer onwaarschijnlijk, 10 = zeer waarschijnlijk)',
            'is_required' => true,
            'options' => [],
        ],
        [
            'type' => 'rating',
            'question_text' => 'Hoe tevreden was je over de organisatie?',
            'is_required' => true,
            'options' => [],
        ],
        [
            'type' => 'rating',
            'question_text' => 'Hoe tevreden was je over de route(s)?',
            'is_required' => true,
            'options' => [],
        ],
        [
            'type' => 'text',
            'question_text' => 'Wat vond je het beste?',
            'is_required' => false,
            'options' => [],
        ],
        [
            'type' => 'text',
            'question_text' => 'Wat kunnen we volgend jaar verbeteren?',
            'is_required' => false,
            'options' => [],
        ],
    ],
];
