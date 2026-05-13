<?php

class Evenement
{
    private $connection = null;
    private $storageFile;

    public function __construct()
    {
        try {
            $this->connection = Database::getInstance()->getConnection();
        } catch (Throwable $exception) {
            $this->connection = null;
        }

        $directory = __DIR__ . '/../data';
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $this->storageFile = $directory . '/evenements.json';
        if (!is_file($this->storageFile)) {
            $this->writeAll($this->seedData());
        }

        if ($this->connection instanceof PDO) {
            $this->createTableIfNotExists();
            $this->initializeDatabase();
        }
    }

    public function all(): array
    {
        if ($this->connection instanceof PDO) {
            $statement = $this->connection->query(
                'SELECT id, titre, date_evenement, lieu, statut, capacite, description, image_url, created_at
                 FROM evenements
                 ORDER BY date_evenement DESC, id DESC'
            );
            return $statement !== false ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
        }

        $rows = $this->readAll();
        usort($rows, static fn(array $a, array $b): int => strcmp((string) $b['date_evenement'], (string) $a['date_evenement']));
        return $rows;
    }

    public function find(int $id): ?array
    {
        if ($this->connection instanceof PDO) {
            $statement = $this->connection->prepare(
                'SELECT id, titre, date_evenement, lieu, statut, capacite, description, image_url, created_at
                 FROM evenements
                 WHERE id = :id
                 LIMIT 1'
            );
            $statement->bindValue(':id', $id, PDO::PARAM_INT);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
            return is_array($result) ? $result : null;
        }

        foreach ($this->readAll() as $item) {
            if ((int) $item['id'] === $id) {
                return $item;
            }
        }
        return null;
    }

    public function create(array $data): int
    {
        if ($this->connection instanceof PDO) {
            $statement = $this->connection->prepare(
                'INSERT INTO evenements (titre, date_evenement, lieu, statut, capacite, description, image_url, created_at)
                 VALUES (:titre, :date_evenement, :lieu, :statut, :capacite, :description, :image_url, :created_at)'
            );
            $statement->bindValue(':titre', $data['titre']);
            $statement->bindValue(':date_evenement', $data['date_evenement']);
            $statement->bindValue(':lieu', $data['lieu']);
            $statement->bindValue(':statut', $data['statut'] !== '' ? $data['statut'] : 'Ouvert');
            $statement->bindValue(':capacite', $data['capacite'] !== '' ? (int) $data['capacite'] : 0, PDO::PARAM_INT);
            $statement->bindValue(':description', $data['description']);
            $statement->bindValue(':image_url', $data['image_url'] !== '' ? $data['image_url'] : $this->defaultImageByTitle($data['titre']));
            $statement->bindValue(':created_at', date('Y-m-d H:i:s'));
            $statement->execute();
            return (int) $this->connection->lastInsertId();
        }

        $rows = $this->readAll();
        $id = $this->nextId($rows);
        $rows[] = [
            'id' => $id,
            'titre' => $data['titre'],
            'date_evenement' => $data['date_evenement'],
            'lieu' => $data['lieu'],
            'statut' => $data['statut'] !== '' ? $data['statut'] : 'Ouvert',
            'capacite' => $data['capacite'] !== '' ? (int) $data['capacite'] : 0,
            'description' => $data['description'],
            'image_url' => $data['image_url'] !== '' ? $data['image_url'] : $this->defaultImageByTitle($data['titre']),
            'created_at' => date('Y-m-d H:i:s'),
        ];
        $this->writeAll($rows);
        return $id;
    }

    public function update(int $id, array $data): bool
    {
        if ($this->connection instanceof PDO) {
            $statement = $this->connection->prepare(
                'UPDATE evenements
                 SET titre = :titre,
                     date_evenement = :date_evenement,
                     lieu = :lieu,
                     statut = :statut,
                     capacite = :capacite,
                     description = :description,
                     image_url = :image_url
                 WHERE id = :id'
            );
            $statement->bindValue(':titre', $data['titre']);
            $statement->bindValue(':date_evenement', $data['date_evenement']);
            $statement->bindValue(':lieu', $data['lieu']);
            $statement->bindValue(':statut', $data['statut'] !== '' ? $data['statut'] : 'Ouvert');
            $statement->bindValue(':capacite', $data['capacite'] !== '' ? (int) $data['capacite'] : 0, PDO::PARAM_INT);
            $statement->bindValue(':description', $data['description']);
            $statement->bindValue(':image_url', $data['image_url'] !== '' ? $data['image_url'] : $this->defaultImageByTitle($data['titre']));
            $statement->bindValue(':id', $id, PDO::PARAM_INT);
            $statement->execute();
            return $statement->rowCount() > 0;
        }

        $rows = $this->readAll();
        $updated = false;
        foreach ($rows as $index => $item) {
            if ((int) $item['id'] !== $id) {
                continue;
            }
            $rows[$index] = [
                'id' => $id,
                'titre' => $data['titre'],
                'date_evenement' => $data['date_evenement'],
                'lieu' => $data['lieu'],
                'statut' => $data['statut'] !== '' ? $data['statut'] : 'Ouvert',
                'capacite' => $data['capacite'] !== '' ? (int) $data['capacite'] : 0,
                'description' => $data['description'],
                'image_url' => $data['image_url'] !== '' ? $data['image_url'] : $this->defaultImageByTitle($data['titre']),
                'created_at' => $item['created_at'] ?? date('Y-m-d H:i:s'),
            ];
            $updated = true;
            break;
        }
        if ($updated) {
            $this->writeAll($rows);
        }
        return $updated;
    }

    public function delete(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $deletedInDatabase = false;
        if ($this->connection instanceof PDO) {
            try {
                $statement = $this->connection->prepare('DELETE FROM evenements WHERE id = :id');
                $statement->bindValue(':id', $id, PDO::PARAM_INT);
                $statement->execute();
                $deletedInDatabase = $statement->rowCount() > 0;
            } catch (Throwable $exception) {
                $deletedInDatabase = false;
            }
        }

        $deletedInFile = $this->deleteFromFileStorage($id);
        return $deletedInDatabase || $deletedInFile;
    }

    private function createTableIfNotExists(): void
    {
        try {
            $this->connection->exec(
                "CREATE TABLE IF NOT EXISTS evenements (
                    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    titre VARCHAR(255) NOT NULL,
                    date_evenement DATE NOT NULL,
                    lieu VARCHAR(255) NOT NULL,
                    statut ENUM('Ouvert','Complet','Ferme','actif','annulé','terminé') NOT NULL DEFAULT 'Ouvert',
                    capacite INT UNSIGNED NOT NULL DEFAULT 0,
                    description TEXT NOT NULL DEFAULT '',
                    image_url VARCHAR(500) NOT NULL DEFAULT '',
                    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_evenements_date (date_evenement),
                    INDEX idx_evenements_statut (statut)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        } catch (Throwable $exception) {
            // Table already exists or DB error — continue
        }
    }

    private function initializeDatabase(): void
    {
        try {
            $this->syncJsonSeedToDatabase();
        } catch (Throwable $exception) {
        }
    }

    private function syncJsonSeedToDatabase(): void
    {
        $rows = $this->readAll();
        if ($rows === []) {
            return;
        }

        $existingIds = [];
        $statement = $this->connection->query('SELECT id FROM evenements');
        if ($statement !== false) {
            foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $item) {
                $existingIds[] = (int) ($item['id'] ?? 0);
            }
        }

        $statement = $this->connection->prepare(
            'INSERT INTO evenements (id, titre, date_evenement, lieu, statut, capacite, description, image_url, created_at)
             VALUES (:id, :titre, :date_evenement, :lieu, :statut, :capacite, :description, :image_url, :created_at)'
        );

        foreach ($rows as $item) {
            $id = (int) ($item['id'] ?? 0);
            if ($id <= 0 || in_array($id, $existingIds, true)) {
                continue;
            }
            $statement->bindValue(':id', $id, PDO::PARAM_INT);
            $statement->bindValue(':titre', (string) ($item['titre'] ?? ''));
            $statement->bindValue(':date_evenement', (string) ($item['date_evenement'] ?? ''));
            $statement->bindValue(':lieu', (string) ($item['lieu'] ?? ''));
            $statement->bindValue(':statut', (string) ($item['statut'] ?? 'Ouvert'));
            $statement->bindValue(':capacite', (int) ($item['capacite'] ?? 0), PDO::PARAM_INT);
            $statement->bindValue(':description', (string) ($item['description'] ?? ''));
            $statement->bindValue(':image_url', (string) ($item['image_url'] ?? $this->defaultImageByTitle((string) ($item['titre'] ?? ''))));
            $statement->bindValue(':created_at', (string) ($item['created_at'] ?? date('Y-m-d H:i:s')));
            $statement->execute();
        }
    }

    private function deleteFromFileStorage(int $id): bool
    {
        $rows = $this->readAll();
        $filtered = array_values(array_filter($rows, static fn(array $item): bool => (int) $item['id'] !== $id));
        if (count($filtered) === count($rows)) {
            return false;
        }
        $this->writeAll($filtered);
        return true;
    }

    private function seedData(): array
    {
        return [
            [
                'id' => 1,
                'titre' => 'Startup Pitch Night',
                'date_evenement' => '2026-06-15',
                'lieu' => 'Casablanca Tech Hub',
                'statut' => 'Ouvert',
                'capacite' => 180,
                'description' => 'Session de pitch avec investisseurs, mentors et startups early-stage.',
                'image_url' => 'https://images.unsplash.com/photo-1559136555-9303baea8ebd?auto=format&fit=crop&w=1200&q=80',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 2,
                'titre' => 'Forum Financement Collaboratif',
                'date_evenement' => '2026-07-10',
                'lieu' => 'Rabat Innovation Center',
                'statut' => 'Ouvert',
                'capacite' => 300,
                'description' => 'Rencontrez des contributeurs et preparez votre campagne de crowdfunding.',
                'image_url' => 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=1200&q=80',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 3,
                'titre' => 'Atelier MVP & Go-To-Market',
                'date_evenement' => '2026-07-25',
                'lieu' => 'En ligne',
                'statut' => 'Ouvert',
                'capacite' => 120,
                'description' => 'Atelier pratique pour structurer votre MVP et accelerer votre mise sur le marche.',
                'image_url' => 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=1200&q=80',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];
    }

    private function readAll(): array
    {
        if (!is_file($this->storageFile)) {
            return [];
        }
        $raw = (string) file_get_contents($this->storageFile);
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function writeAll(array $rows): void
    {
        file_put_contents($this->storageFile, json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);
    }

    private function nextId(array $rows): int
    {
        if ($rows === []) {
            return 1;
        }
        $ids = array_map(static fn(array $row): int => (int) $row['id'], $rows);
        return max($ids) + 1;
    }

    private function defaultImageByTitle(string $title): string
    {
        $hash = crc32($title);
        $images = [
            'https://images.unsplash.com/photo-1552664730-d307ca884978?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1556761175-4b46a572b786?auto=format&fit=crop&w=1200&q=80',
            'https://images.unsplash.com/photo-1460925895917-afdab827c52f?auto=format&fit=crop&w=1200&q=80',
        ];
        return $images[$hash % count($images)];
    }
}
