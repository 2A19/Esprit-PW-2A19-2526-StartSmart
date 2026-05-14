<?php
require_once 'config/Auth.php';
require_once 'config/Database.php';
require_once 'models/Categorie.php';

class CategorieController {
    private $db;
    private $categorie;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->categorie = new Categorie($this->db);
    }

    public function index() {
        requireRole(['ADMIN']);

        $search = isset($_GET['search']) ? trim($_GET['search']) : "";
        $stmt = $this->categorie->readAll($search);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stats = $this->categorie->getStats();

        $pageTitle = "Gestion des Catégories";
        ob_start();
        require_once 'views/categorie/index.php';
        $viewContent = ob_get_clean();

        require_once 'views/layout.php';
    }

    public function create() {
        requireRole(['ADMIN']);

        if ($_POST) {
            $this->categorie->num = $_POST['num'];
            $this->categorie->titre = $_POST['typeprojet'];
            $this->categorie->nom_investisseur = $_POST['nom_investisseur'];
            $this->categorie->created_by_id = currentUserId();

            if ($this->categorie->create()) {
                header("Location: index.php?controller=categorie&action=index");
                exit;
            } else {
                $error = "Erreur lors de la création.";
            }
        }

        $pageTitle = "Ajouter une Catégorie";
        ob_start();
        require_once 'views/categorie/create.php';
        $viewContent = ob_get_clean();

        require_once 'views/layout.php';
    }

    public function edit() {
        requireRole(['ADMIN']);

        $this->categorie->id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID not found.');

        if ($_POST) {
            $this->categorie->num = $_POST['num'];
            $this->categorie->titre = $_POST['typeprojet'];
            $this->categorie->nom_investisseur = $_POST['nom_investisseur'];

            if ($this->categorie->update()) {
                header("Location: index.php?controller=categorie&action=index");
                exit;
            } else {
                $error = "Erreur lors de la mise à jour.";
            }
        } else {
            $this->categorie->readOne();
        }

        $pageTitle = "Modifier la Catégorie";
        ob_start();
        require_once 'views/categorie/edit.php';
        $viewContent = ob_get_clean();

        require_once 'views/layout.php';
    }

    public function delete() {
        requireRole(['ADMIN']);

        if (isset($_GET['id'])) {
            $this->categorie->id = $_GET['id'];
            $this->categorie->delete();
        }
        header("Location: index.php?controller=categorie&action=index");
        exit;
    }
}
?>
