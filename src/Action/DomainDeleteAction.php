<?php

declare(strict_types=1);

namespace App\Action;

use App\Service\DomainService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DomainDeleteAction
{
    public function __construct(
        private readonly DomainService $domainService,
    ) {}

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $this->domainService->delete((int) $args['id']);

        return $response->withHeader('Location', '/')->withStatus(302);
    }
}
