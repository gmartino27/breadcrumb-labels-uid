<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Breadcrumb Labels UID',
    'description' => 'Restores the pre-v14 backend behaviour of appending the record uid to breadcrumb labels.',
    'category' => 'be',
    'author' => 'Gianluigi Martino',
    'author_email' => 'gmartino@gmartino.com',
    'state' => 'stable',
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '14.0.0-14.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
