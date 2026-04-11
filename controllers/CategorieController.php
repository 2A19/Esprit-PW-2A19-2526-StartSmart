<?php
require_once 'config/Database.php';
require_once 'models/Categorie.php';
require_once 'models/Projet.php';

class CategorieController {
    private $db;
    private $categorie;
    private $projet;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->categorie = new Categorie($this->db);
        $this->projet = new Projet($this->db);
    }

    public function index() {
        $search = isset($_GET['search']) ? $_GET['search'] : "";
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
        if ($_POST) {
            $this->categorie->num = $_POST['num'];
            $this->categorie->typeprojet = $_POST['typeprojet'];
            $this->categorie->nom_investisseur = $_POST['nom_investisseur'];
            $this->categorie->projet_id = !empty($_POST['projet_id']) ? $_POST['projet_id'] : null;

            if ($this->categorie->create()) {
                header("Location: index.php?controller=categorie&action=index");
                exit;
            } else {
                $error = "Erreur lors de la création.";
            }
        }
        
        $stmtProjets = $this->projet->readAll();
        $projetsList = $stmtProjets->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = "Ajouter une Catégorie";
        ob_start();
        require_once 'views/categorie/create.php';
        $viewContent = ob_get_clean();

        require_once 'views/layout.php';
    }

    public function edit() {
        $this->categorie->id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID not found.');

        if ($_POST) {
            $this->categorie->num = $_POST['num'];
            $this->categorie->typeprojet = $_POST['typeprojet'];
            $this->categorie->nom_investisseur = $_POST['nom_investisseur'];
            $this->categorie->projet_id = !empty($_POST['projet_id']) ? $_POST['projet_id'] : null;

            if ($this->categorie->update()) {
                header("Location: index.php?controller=categorie&action=index");
                exit;
            } else {
                $error = "Erreur lors de la mise à jour.";
            }
        } else {
            $this->categorie->readOne();
        }

        $stmtProjets = $this->projet->readAll();
        $projetsList = $stmtProjets->fetchAll(PDO::FETCH_ASSOC);

        $pageTitle = "Modifier la Catégorie";
        ob_start();
        require_once 'views/categorie/edit.php';
        $viewContent = ob_get_clean();

        require_once 'views/layout.php';
    }

    public function delete() {
        if (isset($_GET['id'])) {
            $this->categorie->id = $_GET['id'];
            $this->categorie->delete();
        }
        header("Location: index.php?controller=categorie&action=index");
        exit;
    }
}
?>
