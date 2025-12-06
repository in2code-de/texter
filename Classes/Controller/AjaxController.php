<?php

declare(strict_types=1);

namespace In2code\Texter\Controller;

use In2code\Texter\Domain\Repository\LlmRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;

class AjaxController
{
    public function __construct(
        protected readonly LlmRepository $llmRepository,
    ) {
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $input = json_decode($request->getBody()->getContents(), true);
        $text = $input['text'] ?? '';
        $pageId = $input['pageId'] ?? '0';

        if ($text === '') {
            return new JsonResponse([
                'success' => false,
                'error' => 'No text provided'
            ], 400);
        }

        try {
            return new JsonResponse([
                'success' => true,
                'text' => $this->llmRepository->getText($text, $pageId)
            ]);

        } catch (\Throwable $e) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Failed to process text: ' . $e->getMessage()
            ], 500);
        }
    }
}