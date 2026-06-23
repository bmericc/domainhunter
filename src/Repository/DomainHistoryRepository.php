<?php

declare(strict_types=1);

namespace App\Repository;

class DomainHistoryRepository
{
    public function __construct(private readonly \PDO $pdo) {}

    public function insert(int $domainId, string $field, string $oldValue, string $newValue): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO monitor_history (domain_id, field, old_value, new_value, changed_at)
            VALUES (:domain_id, :field, :old_value, :new_value, :changed_at)
        ');
        $stmt->execute([
            ':domain_id'  => $domainId,
            ':field'      => $field,
            ':old_value'  => $oldValue,
            ':new_value'  => $newValue,
            ':changed_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function findByDomainId(int $domainId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM monitor_history
            WHERE domain_id = :id
            ORDER BY changed_at DESC
            LIMIT 500
        ');
        $stmt->execute([':id' => $domainId]);
        return $stmt->fetchAll();
    }

    public function deleteByDomainId(int $domainId): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM monitor_history WHERE domain_id = :id');
        $stmt->execute([':id' => $domainId]);
    }
}
