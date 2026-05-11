<?php
class User {
    private ?int $id;
    private ?string $fullName;
    private ?string $email;
    private ?string $password;
    private ?string $role;
    private ?string $companyName;
    private ?string $phone;
    private ?string $profession;
    private ?string $experience;
    private ?string $resume;
    private ?DateTime $createdAt;

    // Constructor
    public function __construct(?int $id = null, ?string $fullName = null, ?string $email = null, ?string $password = null, ?string $role = null, ?string $companyName = null, ?string $phone = null, ?string $profession = null, ?string $experience = null, ?string $resume = null, ?DateTime $createdAt = null) {
        $this->id = $id;
        $this->fullName = $fullName;
        $this->email = $email;
        $this->password = $password;
        $this->role = $role;
        $this->companyName = $companyName;
        $this->phone = $phone;
        $this->profession = $profession;
        $this->experience = $experience;
        $this->resume = $resume;
        $this->createdAt = $createdAt;
    }

    public function show() {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Full Name</th><th>Email</th><th>Role</th><th>Company</th><th>Phone</th><th>Created At</th></tr>";
        echo "<tr>";
        echo "<td>{$this->id}</td>";
        echo "<td>{$this->fullName}</td>";
        echo "<td>{$this->email}</td>";
        echo "<td>{$this->role}</td>";
        echo "<td>{$this->companyName}</td>";
        echo "<td>{$this->phone}</td>";
        echo "<td>" . ($this->createdAt ? $this->createdAt->format('Y-m-d H:i:s') : '') . "</td>";
        echo "</tr>";
        echo "</table>";
    }

    // Getters and Setters
    public function getId(): ?int {
        return $this->id;
    }

    public function setId(?int $id): void {
        $this->id = $id;
    }

    public function getFullName(): ?string {
        return $this->fullName;
    }

    public function setFullName(?string $fullName): void {
        $this->fullName = $fullName;
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

    public function getRole(): ?string {
        return $this->role;
    }

    public function setRole(?string $role): void {
        $this->role = $role;
    }

    public function getCompanyName(): ?string {
        return $this->companyName;
    }

    public function setCompanyName(?string $companyName): void {
        $this->companyName = $companyName;
    }

    public function getPhone(): ?string {
        return $this->phone;
    }

    public function setPhone(?string $phone): void {
        $this->phone = $phone;
    }

    public function getProfession(): ?string {
        return $this->profession;
    }

    public function setProfession(?string $profession): void {
        $this->profession = $profession;
    }

    public function getExperience(): ?string {
        return $this->experience;
    }

    public function setExperience(?string $experience): void {
        $this->experience = $experience;
    }

    public function getResume(): ?string {
        return $this->resume;
    }

    public function setResume(?string $resume): void {
        $this->resume = $resume;
    }

    public function getCreatedAt(): ?DateTime {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTime $createdAt): void {
        $this->createdAt = $createdAt;
    }
}
?>
