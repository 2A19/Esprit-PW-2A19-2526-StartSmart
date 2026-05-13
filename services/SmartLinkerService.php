<?php
namespace Services;

use PDO;

class SmartLinkerService {
    private $db;

    // Hardcoded dictionary of major cities/countries for simple string matching
    // In a real app, this could be queried from the `projet` table.
    private $locationsDict = [
        'Paris' => ['aliases' => ['paris', 'france'], 'country' => 'France', 'lat' => 48.8566, 'lng' => 2.3522],
        'Tunis' => ['aliases' => ['tunis', 'tunisie', 'tunisia'], 'country' => 'Tunisia', 'lat' => 36.8065, 'lng' => 10.1815],
        'San Francisco' => ['aliases' => ['san francisco', 'silicon valley', 'usa', 'etats-unis'], 'country' => 'USA', 'lat' => 37.7749, 'lng' => -122.4194],
        'London' => ['aliases' => ['london', 'londres', 'uk', 'royaume-uni', 'angleterre', 'england'], 'country' => 'UK', 'lat' => 51.5074, 'lng' => -0.1278],
        'Berlin' => ['aliases' => ['berlin', 'germany', 'allemagne', 'deutschland'], 'country' => 'Germany', 'lat' => 52.5200, 'lng' => 13.4050],
        'Dubai' => ['aliases' => ['dubai', 'uae', 'emirats'], 'country' => 'UAE', 'lat' => 25.2048, 'lng' => 55.2708],
        'Montreal' => ['aliases' => ['montreal', 'canada', 'quebec', 'québec'], 'country' => 'Canada', 'lat' => 45.5017, 'lng' => -73.5673],
        'Dakar' => ['aliases' => ['dakar', 'senegal', 'sénégal'], 'country' => 'Senegal', 'lat' => 14.7167, 'lng' => -17.4677],
        'Abidjan' => ['aliases' => ['abidjan', 'cote d\'ivoire', 'côte d\'ivoire'], 'country' => 'Cote d\'Ivoire', 'lat' => 5.3599, 'lng' => -4.0083]
    ];

    private $categoriesDict = [
        'AI' => ['ai', 'machine learning', 'llm', 'intelligence artificielle', 'deep learning'],
        'Fintech' => ['finance', 'bank', 'crypto', 'payment', 'fintech', 'blockchain', 'paiement'],
        'Gaming' => ['game', 'gaming', 'unity', 'unreal', 'jeu vidéo', 'esport'],
        'HealthTech' => ['health', 'medical', 'doctor', 'santé', 'médecin', 'healthtech']
    ];

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function processPostData($titre, $contenu) {
        $text = strtolower($titre . " " . $contenu);
        
        return [
            'category_id' => $this->detectCategory($text),
            'location' => $this->detectLocation($text),
            'projet_id' => $this->detectProject($text)
        ];
    }

    private function detectCategory($text) {
        // Fetch categories from DB
        $stmt = $this->db->query("SELECT id, typeprojet FROM categorie");
        $dbCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($this->categoriesDict as $catName => $keywords) {
            foreach ($keywords as $keyword) {
                $pattern = '/\b' . preg_quote($keyword, '/') . '\b/iu';
                if (preg_match($pattern, $text)) {
                    // Match found! Find the ID in DB
                    foreach ($dbCategories as $dbCat) {
                        if (strtolower($dbCat['typeprojet']) === strtolower($catName) || strpos(strtolower($dbCat['typeprojet']), strtolower($catName)) !== false) {
                            return $dbCat['id'];
                        }
                    }
                }
            }
        }
        return null;
    }

    private function detectLocation($text) {
        foreach ($this->locationsDict as $cityName => $data) {
            if (isset($data['aliases'])) {
                foreach ($data['aliases'] as $alias) {
                    $pattern = '/\b' . preg_quote(strtolower($alias), '/') . '\b/iu';
                    if (preg_match($pattern, $text)) {
                        return [
                            'city' => ($cityName !== $data['country']) ? $cityName : null,
                            'country' => $data['country'],
                            'lat' => $data['lat'],
                            'lng' => $data['lng']
                        ];
                    }
                }
            }
        }
        return null;
    }

    private function detectProject($text) {
        $stmt = $this->db->query("SELECT id, nomprojet FROM projet WHERE statut = 'actif'");
        $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Sort projects by length descending to avoid partial matches on short names
        usort($projects, function($a, $b) {
            return strlen($b['nomprojet']) - strlen($a['nomprojet']);
        });

        foreach ($projects as $proj) {
            $projName = strtolower(trim($proj['nomprojet']));
            if (strlen($projName) > 2) {
                $pattern = '/\b' . preg_quote($projName, '/') . '\b/iu';
                if (preg_match($pattern, $text)) {
                    return $proj['id'];
                }
            }
        }
        return null;
    }
}
