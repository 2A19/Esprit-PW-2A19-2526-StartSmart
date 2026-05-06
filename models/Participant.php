<?php

declare(strict_types=1);

namespace Models;

use Config\Database;
use PDO;

class Participant
{
    private PDO $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
        $this->createTableIfNotExists();
    }

	/**
	 * @return array<int, array<string, mixed>>
	 */
    public function allByEventId(int $eventId): array
    {
        $statement = $this->connection->prepare(
            'SELECT id, event_id, nom, prenom, age, email, telephone, projet, created_at
             FROM participants
             WHERE event_id = :event_id
             ORDER BY created_at DESC, id DESC'
        );
        $statement->bindValue(':event_id', $eventId, PDO::PARAM_INT);
        $statement->execute();

        /** @var array<int, array<string, mixed>> $rows */
        $rows = $statement->fetchAll();
        return $rows;
    }

	/**
	 * @param array<string, string> $data
	 */
    public function create(int $eventId, array $data): int
    {
        $statement = $this->connection->prepare(
            'INSERT INTO participants (event_id, nom, prenom, age, email, telephone, projet, statut)
             VALUES (:event_id, :nom, :prenom, :age, :email, :telephone, :projet, :statut)'
        );
        $statement->bindValue(':event_id', $eventId, PDO::PARAM_INT);
        $statement->bindValue(':nom', $data['nom']);
        $statement->bindValue(':prenom', $data['prenom']);
        $statement->bindValue(':age', (int) $data['age'], PDO::PARAM_INT);
        $statement->bindValue(':email', $data['email']);
        $statement->bindValue(':telephone', $data['telephone']);
        $statement->bindValue(':projet', $data['projet']);
        $statement->bindValue(':statut', 'inscrit');
        $statement->execute();

        return (int) $this->connection->lastInsertId();
    }

    public function countByEventId(int $eventId): int
    {
        $statement = $this->connection->prepare(
            'SELECT COUNT(*) FROM participants WHERE event_id = :event_id'
        );
        $statement->bindValue(':event_id', $eventId, PDO::PARAM_INT);
        $statement->execute();

        return (int) $statement->fetchColumn();
    }

	/**
	 * @param array<int, mixed> $eventIds
	 * @return array<int, int>
	 */
    public function countByEventIds(array $eventIds): array
    {
        $counts = [];
        foreach ($eventIds as $eventId) {
            $counts[(int) $eventId] = 0;
        }

        if ($eventIds === []) {
            return $counts;
        }

        $normalizedEventIds = array_map(static fn($eventId): int => (int) $eventId, $eventIds);
        $placeholders = implode(',', array_fill(0, count($normalizedEventIds), '?'));

        $statement = $this->connection->prepare(
            "SELECT event_id, COUNT(*) AS total
             FROM participants
             WHERE event_id IN ($placeholders)
             GROUP BY event_id"
        );

        foreach ($normalizedEventIds as $index => $eventId) {
            $statement->bindValue($index + 1, $eventId, PDO::PARAM_INT);
        }
        $statement->execute();

        /** @var array<int, array{event_id:mixed,total:mixed}> $rows */
        $rows = $statement->fetchAll();
        foreach ($rows as $row) {
            $counts[(int) $row['event_id']] = (int) $row['total'];
        }

        return $counts;
    }

    public function deleteByEventId(int $eventId): void
    {
        $statement = $this->connection->prepare('DELETE FROM participants WHERE event_id = :event_id');
        $statement->bindValue(':event_id', $eventId, PDO::PARAM_INT);
        $statement->execute();
    }

    private function createTableIfNotExists(): void
    {
        $this->connection->exec(
            'CREATE TABLE IF NOT EXISTS participants (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                event_id INT UNSIGNED NOT NULL,
                nom VARCHAR(255) NOT NULL,
                prenom VARCHAR(255) NOT NULL,
                age INT UNSIGNED NOT NULL,
                email VARCHAR(255) NOT NULL,
                telephone VARCHAR(100) NOT NULL DEFAULT \'\',
                projet VARCHAR(255) NOT NULL DEFAULT \'\',
                statut VARCHAR(50) NOT NULL DEFAULT \'inscrit\',
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_participants_event_id (event_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }
}
