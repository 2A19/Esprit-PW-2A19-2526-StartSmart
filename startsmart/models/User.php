<?php

/**
 * User Entity/DTO
 * Contains only properties with getters and setters
 * Now merged with Startup functionality - role='startup' indicates a startup account
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
    private ?string $profile_picture;
    // Startup-specific fields
    private ?string $nom_startup;
    private ?string $nom_responsable;
    private ?string $prenom_responsable;
    private ?string $secteur;
    private ?string $site_web;
    private ?string $stade;

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
        ?string $derniere_connexion = null,
        ?string $profile_picture = null,
        ?string $nom_startup = null,
        ?string $nom_responsable = null,
        ?string $prenom_responsable = null,
        ?string $secteur = null,
        ?string $site_web = null,
        ?string $stade = null
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
        $this->profile_picture = $profile_picture;
        $this->nom_startup = $nom_startup;
        $this->nom_responsable = $nom_responsable;
        $this->prenom_responsable = $prenom_responsable;
        $this->secteur = $secteur;
        $this->site_web = $site_web;
        $this->stade = $stade;
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

    public function getProfilePicture(): ?string {
        return $this->profile_picture;
    }

    public function setProfilePicture(?string $profile_picture): void {
        $this->profile_picture = $profile_picture;
    }

    // Startup-specific getters and setters
    public function getNomStartup(): ?string {
        return $this->nom_startup;
    }

    public function setNomStartup(?string $nom_startup): void {
        $this->nom_startup = $nom_startup;
    }

    public function getNomResponsable(): ?string {
        return $this->nom_responsable;
    }

    public function setNomResponsable(?string $nom_responsable): void {
        $this->nom_responsable = $nom_responsable;
    }

    public function getPrenomResponsable(): ?string {
        return $this->prenom_responsable;
    }

    public function setPrenomResponsable(?string $prenom_responsable): void {
        $this->prenom_responsable = $prenom_responsable;
    }

    public function getSecteur(): ?string {
        return $this->secteur;
    }

    public function setSecteur(?string $secteur): void {
        $this->secteur = $secteur;
    }

    public function getSiteWeb(): ?string {
        return $this->site_web;
    }

    public function setSiteWeb(?string $site_web): void {
        $this->site_web = $site_web;
    }

    public function getStade(): ?string {
        return $this->stade;
    }

    public function setStade(?string $stade): void {
        $this->stade = $stade;
    }
}
