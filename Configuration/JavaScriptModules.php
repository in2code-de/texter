<?php

return [
    'dependencies' => [
        'backend',
        'rte_ckeditor',
    ],
    'tags' => [
        'backend.form',
    ],
    'imports' => [
        '@in2code/texter/ai-text-plugin.js' => 'EXT:texter/Resources/Public/JavaScript/ai-text-plugin.js',
    ],
];
