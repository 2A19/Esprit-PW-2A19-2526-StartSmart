<?php
require_once 'config/Database.php';
require_once 'models/Post.php';

class ForumApiController {
    private $db;
    private $postModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->postModel = new Post($this->db);
    }

    // Core formula for trending score
    private function getPostsWithScore($limit = 100) {
        // We use raw SQL to compute score: likes * 2 + comments * 3 + recency_bonus
        // Recency: today = +10, last 7 days = +5, older = 0
        $query = "SELECT p.*, c.typeprojet AS categorie_nom, 
                    (SELECT COUNT(*) FROM reaction WHERE post_id = p.id_post AND type = 'LIKE') AS likes_count,
                    (SELECT COUNT(*) FROM commentaire WHERE post_id = p.id_post) AS commentaires_count
                  FROM post p
                  LEFT JOIN categorie c ON p.categorie_id = c.id
                  WHERE p.latitude IS NOT NULL AND p.longitude IS NOT NULL
                  ORDER BY p.id_post DESC
                  LIMIT :limit";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        $posts = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $likes = (int)$row['likes_count'];
            $comments = (int)$row['commentaires_count'];
            
            // Recency Bonus
            $postDate = new DateTime($row['date_creation']);
            $now = new DateTime();
            $interval = $now->diff($postDate)->days;
            
            $recencyBonus = 0;
            if ($interval == 0) $recencyBonus = 10;
            else if ($interval <= 7) $recencyBonus = 5;

            $score = ($likes * 2) + ($comments * 3) + $recencyBonus;

            $posts[] = [
                'id' => $row['id_post'],
                'title' => htmlspecialchars($row['titre']),
                'content' => htmlspecialchars(substr($row['contenu'], 0, 150)) . '...',
                'city' => htmlspecialchars($row['city']),
                'country' => htmlspecialchars($row['country']),
                'lat' => (float)$row['latitude'],
                'lng' => (float)$row['longitude'],
                'category' => htmlspecialchars($row['categorie_nom'] ?? 'General'),
                'score' => $score,
                'likes' => $likes,
                'comments' => $comments,
                'date' => $row['date_creation']
            ];
        }

        return $posts;
    }

    // GET /api/forum/feed
    public function feed() {
        header('Content-Type: application/json');
        try {
            $posts = $this->getPostsWithScore(500);
            echo json_encode(['success' => true, 'data' => $posts]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // GET /api/forum/trending
    public function trending() {
        header('Content-Type: application/json');
        try {
            $posts = $this->getPostsWithScore(500);
            
            // Sort by score descending
            usort($posts, function($a, $b) {
                return $b['score'] <=> $a['score'];
            });

            // Return top 10
            echo json_encode(['success' => true, 'data' => array_slice($posts, 0, 10)]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // GET /api/forum/location
    public function location() {
        header('Content-Type: application/json');
        try {
            $posts = $this->getPostsWithScore(500);
            
            $grouped = [];
            foreach ($posts as $post) {
                $country = $post['country'];
                $city = $post['city'];
                
                if (!isset($grouped[$country])) {
                    $grouped[$country] = [
                        'count' => 0,
                        'cities' => []
                    ];
                }
                $grouped[$country]['count']++;
                
                if (!isset($grouped[$country]['cities'][$city])) {
                    $grouped[$country]['cities'][$city] = 0;
                }
                $grouped[$country]['cities'][$city]++;
            }

            echo json_encode(['success' => true, 'data' => $grouped]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
    // POST /api/forum/translate
    public function translate() {
        header('Content-Type: application/json');
        try {
            $data = json_decode(file_get_contents("php://input"));
            $text = $data->text ?? '';
            $targetLang = $data->targetLang ?? 'en';
            
            if (empty($text)) {
                throw new Exception('Text is required');
            }

            require_once 'services/TranslationService.php';
            $translated = TranslationService::translate($text, $targetLang);
            
            echo json_encode(['success' => true, 'translatedText' => $translated]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
?>
