<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Texter - AI generated texts in TYPO3',
    'description' => 'Using AI to generate texts in TYPO3 backend',
    'category' => 'plugin',
    'version' => '1.0.0',
    'author' => 'Alex Kellner',
    'author_email' => 'alexander.kellner@in2code.de',
    'author_company' => 'in2code.de',
    'state' => 'stable',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.0-14.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
