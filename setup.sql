CREATE DATABASE IF NOT EXISTS startsmart;
USE startsmart;

CREATE TABLE IF NOT EXISTS projet (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    num INT(11) NOT NULL,
    nomprojet VARCHAR(100) NOT NULL,
    datedebut DATE NOT NULL,
    datefin DATE NOT NULL,
    budget DECIMAL(15,2) NOT NULL,
    gain DECIMAL(15,2) NOT NULL
);

CREATE TABLE IF NOT EXISTS categorie (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    num INT(11) NOT NULL,
    typeprojet VARCHAR(100) NOT NULL,
    nom_investisseur VARCHAR(100) NOT NULL,
    projet_id INT(11),
    FOREIGN KEY (projet_id) REFERENCES projet(id) ON DELETE SET NULL
);
