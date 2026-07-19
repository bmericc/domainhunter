<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Repository\DomainHistoryRepository;
use App\Repository\DomainRepository;
use App\Service\DomainService;
use App\Tests\Support\FakeWhoisService;
use App\Tests\Support\InMemoryDatabase;
use App\Tests\Support\SpyMailer;
use BahriCanli\DomainHunter\DomainParser;
use PHPUnit\Framework\TestCase;

final class DomainServiceTest extends TestCase
{
    private FakeWhoisService $whois;
    private DomainRepository $repository;
    private DomainHistoryRepository $history;
    private SpyMailer $mailer;

    protected function setUp(): void
    {
        $pdo              = InMemoryDatabase::create();
        $this->whois      = new FakeWhoisService();
        $this->repository = new DomainRepository($pdo);
        $this->history     = new DomainHistoryRepository($pdo);
        $this->mailer      = new SpyMailer();
    }

    private function service(string $alertEmail = ''): DomainService
    {
        return new DomainService(
            $this->whois,
            new DomainParser($this->whois),
            $this->repository,
            $this->history,
            $alertEmail,
            $this->mailer,
            'domainhunter@example.com',
        );
    }

    public function testAddStoresNormalizedDomainAndRegistrationStatus(): void
    {
        $this->whois->willReturn('example', 'com', FakeWhoisService::resultWith([
            'registrar'      => 'Example Registrar',
            'nameServers'    => ['ns1.example.com', 'ns2.example.com'],
            'statuses'       => ['active'],
            'creationDate'   => '2020-01-01',
        ]));

        $result = $this->service()->add('example.com');

        self::assertSame(['domain' => 'EXAMPLE.COM', 'registered' => true], $result);

        $row = $this->repository->findByDomain('EXAMPLE.COM');
        self::assertSame('Example Registrar', $row['register']);
        self::assertSame('ns1.example.com', $row['nameserv1']);
    }

    public function testAddMarksUnregisteredDomainWhenWhoisReturnsNull(): void
    {
        $this->whois->willReturn('example', 'com', null);

        $result = $this->service()->add('example.com');

        self::assertSame(['domain' => 'EXAMPLE.COM', 'registered' => false], $result);
    }

    public function testAddThrowsOnDuplicateDomain(): void
    {
        $this->whois->willReturn('example', 'com', null);
        $this->service()->add('example.com');

        $this->expectException(\RuntimeException::class);
        $this->service()->add('example.com');
    }

    public function testAddThrowsOnInvalidDomainFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service()->add('not-a-domain');
    }

    public function testRefreshOneThrowsWhenDomainNotFound(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->service()->refreshOne('MISSING.COM');
    }

    public function testRefreshOneReturnsEmptyChangesWhenNothingChanged(): void
    {
        $this->whois->willReturn('example', 'com', FakeWhoisService::resultWith([
            'registrar' => 'Example Registrar',
        ]));
        $this->service()->add('example.com');

        $changes = $this->service()->refreshOne('EXAMPLE.COM');

        self::assertSame([], $changes);
        self::assertSame([], $this->mailer->sent);
    }

    public function testRefreshOneDetectsChangesRecordsHistoryAndSendsAlert(): void
    {
        $this->whois->willReturn('example', 'com', FakeWhoisService::resultWith([
            'registrar' => 'Old Registrar',
        ]));
        $this->service()->add('example.com');

        $this->whois->willReturn('example', 'com', FakeWhoisService::resultWith([
            'registrar' => 'New Registrar',
        ]));

        $changes = $this->service('alerts@example.com')->refreshOne('EXAMPLE.COM');

        self::assertCount(1, $changes);
        self::assertStringContainsString('Old Registrar', $changes[0]);
        self::assertStringContainsString('New Registrar', $changes[0]);
        self::assertCount(1, $this->mailer->sent);

        $row  = $this->repository->findByDomain('EXAMPLE.COM');
        $hist = $this->history->findByDomainId((int) $row['id']);
        self::assertCount(1, $hist);
        self::assertSame('register', $hist[0]['field']);
    }

    public function testRefreshAllRefreshesEveryStoredDomain(): void
    {
        $this->whois->willReturn('example', 'com', FakeWhoisService::resultWith(['registrar' => 'R1']));
        $this->whois->willReturn('other', 'net', FakeWhoisService::resultWith(['registrar' => 'R2']));
        $service = $this->service();
        $service->add('example.com');
        $service->add('other.net');

        $report = $service->refreshAll();

        self::assertArrayHasKey('EXAMPLE.COM', $report);
        self::assertArrayHasKey('OTHER.NET', $report);
    }
}
