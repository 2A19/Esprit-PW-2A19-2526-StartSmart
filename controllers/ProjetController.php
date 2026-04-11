<?php
require_once 'config/Database.php';
require_once 'models/Projet.php';

class ProjetController {
    private $db;
    private $projet;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->projet = new Projet($this->db);
    }

    public function index() {
        $search = isset($_GET['search']) ? $_GET['search'] : "";
        $stmt = $this->projet->readAll($search);
        $projets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $stats = $this->projet->getStats();

        $pageTitle = "Gestion des Projets";
        ob_start();
        require_once 'views/projet/index.php';
        $viewContent = ob_get_clean();

        require_once 'views/layout.php';
    }

    public function create() {
        if ($_POST) {
            $this->projet->num = $_POST['num'];
            $this->projet->nomprojet = $_POST['nomprojet'];
            $this->projet->datedebut = $_POST['datedebut'];
            $this->projet->datefin = $_POST['datefin'];
            $this->projet->budget = $_POST['budget'];
            $this->projet->gain = $_POST['gain'];

            if ($this->projet->create()) {
                header("Location: index.php?controller=projet&action=index");
                exit;
            } else {
                $error = "Erreur lors de la création.";
            }
        }
        
        $pageTitle = "Ajouter un Projet";
        ob_start();
        require_once 'views/projet/create.php';
        $viewContent = ob_get_clean();

        require_once 'views/layout.php';
    }

    public function edit() {
        $this->projet->id = isset($_GET['id']) ? $_GET['id'] : die('ERROR: ID not found.');

        if ($_POST) {
            $this->projet->num = $_POST['num'];
            $this->projet->nomprojet = $_POST['nomprojet'];
            $this->projet->datedebut = $_POST['datedebut'];
            $this->projet->datefin = $_POST['datefin'];
            $this->projet->budget = $_POST['budget'];
            $this->projet->gain = $_POST['gain'];

            if ($this->projet->update()) {
                header("Location: index.php?controller=projet&action=index");
                exit;
            } else {
                $error = "Erreur lors de la mise à jour.";
            }
        } else {
            $this->projet->readOne();
        }

        $pageTitle = "Modifier le Projet";
        ob_start();
        require_once 'views/projet/edit.php';
        $viewContent = ob_get_clean();

        require_once 'views/layout.php';
    }

    public function delete() {
        if (isset($_GET['id'])) {
            $this->projet->id = $_GET['id'];
            $this->projet->delete();
        }
        header("Location: index.php?controller=projet&action=index");
        exit;
    }
}
?>
