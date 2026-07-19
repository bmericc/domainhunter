<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Repository\DomainHistoryRepository;
use App\Tests\Support\InMemoryDatabase;
use PHPUnit\Framework\TestCase;

final class DomainHistoryRepositoryTest extends TestCase
{
    private DomainHistoryRepository $history;

    protected function setUp(): void
    {
        $this->history = new DomainHistoryRepository(InMemoryDatabase::create());
    }

    public function testInsertAndFindByDomainId(): void
    {
        $this->history->insert(1, 'register', 'Old Registrar', 'New Registrar');
        $this->history->insert(1, 'status1', 'active', 'expired');
        $this->history->insert(2, 'register', 'Other Old', 'Other New');

        $rows = $this->history->findByDomainId(1);

        self::assertCount(2, $rows);
        self::assertSame('register', $rows[0]['field']);
        self::assertSame('New Registrar', $rows[0]['new_value']);
    }

    public function testFindByDomainIdReturnsEmptyArrayWhenNoHistory(): void
    {
        self::assertSame([], $this->history->findByDomainId(99));
    }

    public function testDeleteByDomainIdRemovesOnlyThatDomainsHistory(): void
    {
        $this->history->insert(1, 'register', 'a', 'b');
        $this->history->insert(2, 'register', 'c', 'd');

        $this->history->deleteByDomainId(1);

        self::assertSame([], $this->history->findByDomainId(1));
        self::assertCount(1, $this->history->findByDomainId(2));
    }
}
