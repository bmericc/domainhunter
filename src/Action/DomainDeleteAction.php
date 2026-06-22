<?php

declare(strict_types=1);

namespace App\Action;

use App\Repository\DomainRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DomainDeleteAction
{
    public function __construct(
        private readonly DomainRepository $domains,
    ) {}

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id = (int) $args['id'];
        $this->domains->delete($id);

        return $response->withHeader('Location', '/')->withStatus(302);
    }
}
