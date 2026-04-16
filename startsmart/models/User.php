<?php

/**
 * User Entity/DTO
 * Contains only properties with getters and setters
 */
class User
{
    private ?int $id;
    private ?string $nom;
    private ?string $prenom;
    private ?string $email;
    private ?string $password;
    private ?string $telephone;
    private ?string $date_naissance;
    private ?string $role;
    private ?string $statut;
    private ?string $date_inscription;
    private ?string $derniere_connexion;

    public function __construct(
        ?int $id = null,
        ?string $nom = null,
        ?string $prenom = null,
        ?string $email = null,
        ?string $password = null,
        ?string $telephone = null,
        ?string $date_naissance = null,
        ?string $role = null,
        ?string $statut = null,
        ?string $date_inscription = null,
        ?string $derniere_connexion = null
    ) {
        $this->id = $id;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->password = $password;
        $this->telephone = $telephone;
        $this->date_naissance = $date_naissance;
        $this->role = $role;
        $this->statut = $statut;
        $this->date_inscription = $date_inscription;
        $this->derniere_connexion = $derniere_connexion;
    }

    // Getters and Setters
    public function getId(): ?int {
        return $this->id;
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function getNom(): ?string {
        return $this->nom;
    }

    public function setNom(?string $nom): void {
        $this->nom = $nom;
    }

    public function getPrenom(): ?string {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): void {
        $this->prenom = $prenom;
    }

    public function getEmail(): ?string {
        return $this->email;
    }

    public function setEmail(?string $email): void {
        $this->email = $email;
    }

    public function getPassword(): ?string {
        return $this->password;
    }

    public function setPassword(?string $password): void {
        $this->password = $password;
    }

    public function getTelephone(): ?string {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): void {
        $this->telephone = $telephone;
    }

    public function getDateNaissance(): ?string {
        return $this->date_naissance;
    }

    public function setDateNaissance(?string $date_naissance): void {
        $this->date_naissance = $date_naissance;
    }

    public function getRole(): ?string {
        return $this->role;
    }

    public function setRole(?string $role): void {
        $this->role = $role;
    }

    public function getStatut(): ?string {
        return $this->statut;
    }

    public function setStatut(?string $statut): void {
        $this->statut = $statut;
    }

    public function getDateInscription(): ?string {
        return $this->date_inscription;
    }

    public function setDateInscription(?string $date_inscription): void {
        $this->date_inscription = $date_inscription;
    }

    public function getDerniereConnexion(): ?string {
        return $this->derniere_connexion;
    }

    public function setDerniereConnexion(?string $derniere_connexion): void {
        $this->derniere_connexion = $derniere_connexion;
    }
}
