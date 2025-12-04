<?php

declare(strict_types=1);

use In2code\Texter\Controller\AjaxController;

/**
 * Definitions of routes
 */
return [
    'texter_process_text' => [
        'path' => '/texter/process-text',
        'target' => AjaxController::class,
        'access' => 'user,group',
        'methods' => ['POST'],
    ],
];
