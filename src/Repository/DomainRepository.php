<?php

declare(strict_types=1);

namespace App\Repository;

class DomainRepository
{
    public function __construct(private readonly \PDO $pdo) {}

    public function count(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(id) FROM monitors')->fetchColumn();
    }

    public function paginate(int $page, int $perPage, string $orderSql): array
    {
        $offset = ($page - 1) * $perPage;
        $stmt   = $this->pdo->prepare("SELECT * FROM monitors ORDER BY $orderSql LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit',  $perPage, \PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function all(string $orderSql = 'hunter_update DESC'): array
    {
        return $this->pdo->query("SELECT * FROM monitors ORDER BY $orderSql")->fetchAll();
    }

    public function findByDomain(string $domain): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM monitors WHERE domain = :domain');
        $stmt->execute([':domain' => $domain]);
        return $stmt->fetch() ?: null;
    }

    public function existsByDomain(string $domain): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(id) FROM monitors WHERE domain = :domain');
        $stmt->execute([':domain' => $domain]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function insert(array $data): void
    {
        $data[':hunter_update'] = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare('
            INSERT INTO monitors
                (domain, register, whois_serv, ref_url,
                 nameserv1, nameserv2, nameserv3, nameserv4, nameserv5,
                 status1, status2, status3,
                 create_date, update_date, expirate_date, hunter_update)
            VALUES
                (:domain, :register, :whois_serv, :ref_url,
                 :nameserv1, :nameserv2, :nameserv3, :nameserv4, :nameserv5,
                 :status1, :status2, :status3,
                 :create_date, :update_date, :expirate_date, :hunter_update)
        ');
        $stmt->execute($data);
    }

    public function update(string $domain, array $data): void
    {
        $data[':hunter_update'] = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare('
            UPDATE monitors SET
                register      = :register,
                whois_serv    = :whois_serv,
                ref_url       = :ref_url,
                nameserv1     = :nameserv1,
                nameserv2     = :nameserv2,
                nameserv3     = :nameserv3,
                nameserv4     = :nameserv4,
                nameserv5     = :nameserv5,
                status1       = :status1,
                status2       = :status2,
                status3       = :status3,
                create_date   = :create_date,
                update_date   = :update_date,
                expirate_date = :expirate_date,
                hunter_update = :hunter_update
            WHERE domain = :domain
        ');
        $stmt->execute($data);
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM monitors WHERE id = :id');
        $stmt->execute([':id' => $id]);
    }

    public function deleteByDomain(string $domain): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM monitors WHERE domain = :domain');
        $stmt->execute([':domain' => $domain]);
        return $stmt->rowCount() > 0;
    }
}
