<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'APIs for legacy sys_collection DB tables',
    'description' => 'Adds PHP classes, TCA configuration and database tables for generic record collections.',
    'category' => 'misc',
    'state' => 'stable',
    'clearCacheOnLoad' => 0,
    'author' => 'Benni Mack',
    'author_email' => 'benni@typo3.org',
    'author_company' => '',
    'version' => '2.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.0-12.4.99',
            'frontend' => '12.4.0-12.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
