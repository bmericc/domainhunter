<?php

declare(strict_types=1);

namespace App\Action;

use App\Repository\DomainRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Views\Twig;

class DomainListAction
{
    public function __construct(
        private readonly Twig             $twig,
        private readonly DomainRepository $domains,
    ) {}

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $params  = $request->getQueryParams();
        $page    = max(1, (int) ($params['page'] ?? 1));
        $order   = $params['order'] ?? 'checked';
        $perPage = 20;

        $orderMap = [
            'expiry'  => 'expirate_date ASC',
            'updated' => 'update_date DESC',
            'checked' => 'hunter_update DESC',
        ];
        $orderSql = $orderMap[$order] ?? $orderMap['checked'];

        $total      = $this->domains->count();
        $items      = $this->domains->paginate($page, $perPage, $orderSql);
        $totalPages = (int) ceil($total / $perPage);

        return $this->twig->render($response, 'domain/list.html.twig', [
            'domains'     => $items,
            'total'       => $total,
            'page'        => $page,
            'total_pages' => $totalPages,
            'per_page'    => $perPage,
            'order'       => $order,
        ]);
    }
}
