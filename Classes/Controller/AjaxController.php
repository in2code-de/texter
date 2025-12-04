<?php

declare(strict_types=1);

namespace In2code\Texter\Controller;

use In2code\Texter\Domain\Repository\LlmRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AjaxController
{
    public function __construct(
        protected readonly LlmRepository $llmRepository,
    ) {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        // Get JSON input
        $input = json_decode($request->getBody()->getContents(), true);
        $text = $input['text'] ?? '';
        
        if ($text === '') {
            return new JsonResponse([
                'success' => false,
                'error' => 'No text provided'
            ], 400);
        }
        
        try {
            // Use LlmRepository to get AI-generated text
            $llmRepository = GeneralUtility::makeInstance(LlmRepository::class);
            $processedText = $llmRepository->getText($text);
            
            return new JsonResponse([
                'success' => true,
                'text' => $processedText
            ]);
            
        } catch (\Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Failed to process text: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function debugModels(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $llmRepository = GeneralUtility::makeInstance(LlmRepository::class);
            $models = $llmRepository->listModels();
            
            return new JsonResponse([
                'success' => true,
                'models' => $models
            ]);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}