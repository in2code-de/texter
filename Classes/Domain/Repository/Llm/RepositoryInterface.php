<?php

declare(strict_types=1);

namespace In2code\Texter\Domain\Repository\Llm;

interface RepositoryInterface
{
    public function checkApiKey(): void;
    public function getApiUrl(): string;
    public function getText(string $prompt, string $pageId = '0'): string;
}
