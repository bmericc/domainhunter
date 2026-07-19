<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Repository\DomainRepository;
use App\Tests\Support\InMemoryDatabase;
use PHPUnit\Framework\TestCase;

final class DomainRepositoryTest extends TestCase
{
    private DomainRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new DomainRepository(InMemoryDatabase::create());
    }

    public function testInsertAndFindByDomain(): void
    {
        $this->repository->insert($this->row('EXAMPLE.COM'));

        $row = $this->repository->findByDomain('EXAMPLE.COM');

        self::assertNotNull($row);
        self::assertSame('EXAMPLE.COM', $row['domain']);
        self::assertSame('Example Registrar', $row['register']);
    }

    public function testFindByDomainReturnsNullWhenMissing(): void
    {
        self::assertNull($this->repository->findByDomain('MISSING.COM'));
    }

    public function testExistsByDomain(): void
    {
        self::assertFalse($this->repository->existsByDomain('EXAMPLE.COM'));

        $this->repository->insert($this->row('EXAMPLE.COM'));

        self::assertTrue($this->repository->existsByDomain('EXAMPLE.COM'));
    }

    public function testUpdateChangesExistingRow(): void
    {
        $this->repository->insert($this->row('EXAMPLE.COM'));

        $updated = $this->row('EXAMPLE.COM');
        $updated[':register'] = 'New Registrar';
        $this->repository->update('EXAMPLE.COM', $updated);

        $row = $this->repository->findByDomain('EXAMPLE.COM');
        self::assertSame('New Registrar', $row['register']);
    }

    public function testDeleteRemovesRow(): void
    {
        $this->repository->insert($this->row('EXAMPLE.COM'));
        $id = $this->repository->findByDomain('EXAMPLE.COM')['id'];

        $this->repository->delete((int) $id);

        self::assertNull($this->repository->findByDomain('EXAMPLE.COM'));
    }

    public function testCountAndPaginateRespectSearch(): void
    {
        $this->repository->insert($this->row('EXAMPLE.COM'));
        $this->repository->insert($this->row('OTHER.NET'));

        self::assertSame(2, $this->repository->count());
        self::assertSame(1, $this->repository->count('EXAMPLE'));

        $page = $this->repository->paginate(1, 10, 'domain ASC', 'EXAMPLE');
        self::assertCount(1, $page);
        self::assertSame('EXAMPLE.COM', $page[0]['domain']);
    }

    private function row(string $domain): array
    {
        return [
            ':domain'        => $domain,
            ':register'      => 'Example Registrar',
            ':whois_serv'    => 'whois.example.com',
            ':ref_url'       => 'https://example.com',
            ':nameserv1'     => 'ns1.example.com',
            ':nameserv2'     => 'ns2.example.com',
            ':nameserv3'     => '',
            ':nameserv4'     => '',
            ':nameserv5'     => '',
            ':status1'       => 'active',
            ':status2'       => '',
            ':status3'       => '',
            ':create_date'   => '2020-01-01',
            ':update_date'   => '2025-01-01',
            ':expirate_date' => '2030-01-01',
        ];
    }
}
