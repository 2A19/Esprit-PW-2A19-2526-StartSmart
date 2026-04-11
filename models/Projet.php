<?php
class Projet {
    private $conn;
    private $table_name = "projet";

    public $id;
    public $num;
    public $nomprojet;
    public $datedebut;
    public $datefin;
    public $budget;
    public $gain;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function readAll($search = "") {
        $query = "SELECT * FROM " . $this->table_name;
        if (!empty($search)) {
            $query .= " WHERE nomprojet LIKE :search OR num LIKE :search";
        }
        $query .= " ORDER BY id DESC";

        $stmt = $this->conn->prepare($query);

        if (!empty($search)) {
            $search = "%{$search}%";
            $stmt->bindParam(":search", $search);
        }

        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET num=:num, nomprojet=:nomprojet, datedebut=:datedebut, datefin=:datefin, budget=:budget, gain=:gain";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->num = htmlspecialchars(strip_tags($this->num));
        $this->nomprojet = htmlspecialchars(strip_tags($this->nomprojet));
        $this->datedebut = htmlspecialchars(strip_tags($this->datedebut));
        $this->datefin = htmlspecialchars(strip_tags($this->datefin));
        $this->budget = htmlspecialchars(strip_tags($this->budget));
        $this->gain = htmlspecialchars(strip_tags($this->gain));

        // Bind
        $stmt->bindParam(":num", $this->num);
        $stmt->bindParam(":nomprojet", $this->nomprojet);
        $stmt->bindParam(":datedebut", $this->datedebut);
        $stmt->bindParam(":datefin", $this->datefin);
        $stmt->bindParam(":budget", $this->budget);
        $stmt->bindParam(":gain", $this->gain);

        return $stmt->execute();
    }

    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->num = $row['num'];
            $this->nomprojet = $row['nomprojet'];
            $this->datedebut = $row['datedebut'];
            $this->datefin = $row['datefin'];
            $this->budget = $row['budget'];
            $this->gain = $row['gain'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET num=:num, nomprojet=:nomprojet, datedebut=:datedebut, datefin=:datefin, budget=:budget, gain=:gain 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $this->num = htmlspecialchars(strip_tags($this->num));
        $this->nomprojet = htmlspecialchars(strip_tags($this->nomprojet));
        $this->datedebut = htmlspecialchars(strip_tags($this->datedebut));
        $this->datefin = htmlspecialchars(strip_tags($this->datefin));
        $this->budget = htmlspecialchars(strip_tags($this->budget));
        $this->gain = htmlspecialchars(strip_tags($this->gain));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":num", $this->num);
        $stmt->bindParam(":nomprojet", $this->nomprojet);
        $stmt->bindParam(":datedebut", $this->datedebut);
        $stmt->bindParam(":datefin", $this->datefin);
        $stmt->bindParam(":budget", $this->budget);
        $stmt->bindParam(":gain", $this->gain);
        $stmt->bindParam(":id", $this->id);

        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);
        return $stmt->execute();
    }

    public function getStats() {
        $query = "SELECT nomprojet, budget, gain FROM " . $this->table_name . " LIMIT 10"; // Top 10 for charts
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
