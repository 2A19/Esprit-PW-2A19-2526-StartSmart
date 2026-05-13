<?php

class Participant
{
    private $connection;

    public function __construct()
    {
        $this->connection = Database::getInstance()->getConnection();
        $this->createTableIfNotExists();
    }

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
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(int $eventId, array $data, ?int $capacity = null): int
    {
        if (!$this->eventExists($eventId)) {
            throw new RuntimeException('Evenement invalide.');
        }

        if ($capacity !== null && !$this->hasCapacityForEvent($eventId, $capacity)) {
            throw new RuntimeException('L\'evenement est complet (capacite atteinte).');
        }

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

    public function hasCapacityForEvent(int $eventId, int $capacity): bool
    {
        if ($capacity <= 0) {
            return false;
        }
        return $this->countByEventId($eventId) < $capacity;
    }

    public function eventExists(int $eventId): bool
    {
        $statement = $this->connection->prepare('SELECT 1 FROM evenements WHERE id = :id LIMIT 1');
        $statement->bindValue(':id', $eventId, PDO::PARAM_INT);
        $statement->execute();
        return (bool) $statement->fetchColumn();
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

        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
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
