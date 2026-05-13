<?php
require_once 'config/Database.php';
require_once 'models/Projet.php';
require_once 'models/Categorie.php';

class MapController {
    private $db;
    private $projetModel;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->projetModel = new Projet($this->db);
    }

    // Render the Globe.gl view
    public function index() {
        // Fetch categories for the filter
        $categorieModel = new Categorie($this->db);
        $stmt = $categorieModel->readAllForSelect();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = "Ecosystème Startups - Map";
        
        ob_start();
        require_once 'views/map/index.php';
        $viewContent = ob_get_clean();
        
        require_once 'views/layout.php';
    }

    // JSON Endpoint for Globe.gl data
    public function apiData() {
        header('Content-Type: application/json');
        
        try {
            // Get all projects with a limit to avoid overloading
            $stmt = $this->projetModel->readAll("", null, "latest", null, 1000, 0);
            
            $projects = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Only include projects that have valid coordinates
                if ($row['latitude'] !== null && $row['longitude'] !== null) {
                    
                    // Determine trending status
                    $likes = (int)($row['likes_count'] ?? 0);
                    $comments = (int)($row['commentaires_count'] ?? 0);
                    $popularityScore = $likes * 2 + $comments;
                    
                    // Simple logic for trending: top quartile or arbitrarily > 10 score
                    $isTrending = $popularityScore >= 5; // Adjust threshold as needed
                    
                    $projects[] = [
                        'id' => $row['id'],
                        'name' => htmlspecialchars($row['nomprojet']),
                        'description' => htmlspecialchars($row['description']),
                        'city' => htmlspecialchars($row['city'] ?? 'Unknown'),
                        'country' => htmlspecialchars($row['country'] ?? 'Unknown'),
                        'lat' => (float)$row['latitude'],
                        'lng' => (float)$row['longitude'],
                        'category' => htmlspecialchars($row['categorie_nom'] ?? 'Général'),
                        'popularity' => $popularityScore,
                        'isTrending' => $isTrending,
                        'budget' => (float)($row['budget'] ?? 0)
                    ];
                }
            }
            
            echo json_encode(['success' => true, 'data' => $projects]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }
}
?>
