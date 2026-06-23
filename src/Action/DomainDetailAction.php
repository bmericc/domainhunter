<?php

declare(strict_types=1);

namespace App\Action;

use App\Repository\DomainHistoryRepository;
use App\Repository\DomainRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class DomainDetailAction
{
    public function __construct(
        private readonly Twig                    $twig,
        private readonly DomainRepository        $domains,
        private readonly DomainHistoryRepository $history,
    ) {}

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $id     = (int) $args['id'];
        $domain = $this->domains->findById($id);

        if ($domain === null) {
            return $response->withHeader('Location', '/')->withStatus(302);
        }

        $history = $this->history->findByDomainId($id);

        return $this->twig->render($response, 'domain/detail.html.twig', [
            'domain'  => $domain,
            'history' => $history,
        ]);
    }
}
