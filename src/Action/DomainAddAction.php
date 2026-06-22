<?php

declare(strict_types=1);

namespace App\Action;

use App\Service\DomainService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class DomainAddAction
{
    public function __construct(
        private readonly Twig          $twig,
        private readonly DomainService $domainService,
    ) {}

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $error   = null;
        $success = null;

        if ($request->getMethod() === 'POST') {
            $body  = (array) $request->getParsedBody();
            $input = trim((string) ($body['domain'] ?? ''));

            try {
                $this->domainService->add($input);
                $success = "Domain \"$input\" added and queried successfully.";
            } catch (\InvalidArgumentException | \RuntimeException $e) {
                $error = $e->getMessage();
            }
        }

        return $this->twig->render($response, 'domain/add.html.twig', [
            'error'   => $error,
            'success' => $success,
        ]);
    }
}
