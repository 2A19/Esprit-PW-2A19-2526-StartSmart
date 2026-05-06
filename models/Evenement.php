<?php

declare(strict_types=1);

namespace Models;

class Evenement
{
    private string $storageFile;

    public function __construct()
    {
        $directory = BASE_PATH . '/data';
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $this->storageFile = $directory . '/evenements.json';
        if (!is_file($this->storageFile)) {
            $this->writeAll($this->seedData());
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $rows = $this->readAll();
        usort(
            $rows,
            static fn(array $a, array $b): int => strcmp((string) $b['date_evenement'], (string) $a['date_evenement'])
        );
        return $rows;
    }

    /**
     * @param int $id
     * @return array<string, mixed>|null
     */
    public function find(int $id): ?array
    {
        foreach ($this->readAll() as $item) {
            if ((int) $item['id'] === $id) {
                return $item;
            }
        }

        return null;
    }

    /**
     * @param array<string, string> $data
     */
    public function create(array $data): int
    {
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

    /**
     * @param array<string, string> $data
     */
    public function update(int $id, array $data): bool
    {
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
        $rows = $this->readAll();
        $filtered = array_values(array_filter(
            $rows,
            static fn(array $item): bool => (int) $item['id'] !== $id
        ));

        if (count($filtered) === count($rows)) {
            return false;
        }

        $this->writeAll($filtered);
        return true;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function seedData(): array
    {
        return [
            [
                'id' => 1,
                'titre' => 'Startup Pitch Night',
                'date_evenement' => '2026-05-12',
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
                'date_evenement' => '2026-05-28',
                'lieu' => 'Rabat Innovation Center',
                'statut' => 'Complet',
                'capacite' => 300,
                'description' => 'Rencontrez des contributeurs et preparez votre campagne de crowdfunding.',
                'image_url' => 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=1200&q=80',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'id' => 3,
                'titre' => 'Atelier MVP & Go-To-Market',
                'date_evenement' => '2026-06-03',
                'lieu' => 'En ligne',
                'statut' => 'Ouvert',
                'capacite' => 120,
                'description' => 'Atelier pratique pour structurer votre MVP et accelerer votre mise sur le marche.',
                'image_url' => 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?auto=format&fit=crop&w=1200&q=80',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function readAll(): array
    {
        $raw = (string) file_get_contents($this->storageFile);
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    private function writeAll(array $rows): void
    {
        file_put_contents(
            $this->storageFile,
            json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            LOCK_EX
        );
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
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
