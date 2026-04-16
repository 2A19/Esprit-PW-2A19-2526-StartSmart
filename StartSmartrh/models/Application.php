<?php
class Application {
    private ?int $id;
    private ?int $jobOfferId;
    private ?int $userId;
    private ?string $fullName;
    private ?string $email;
    private ?string $phone;
    private ?string $experience;
    private ?string $coverLetter;
    private ?string $resume;
    private ?string $status;
    private ?DateTime $createdAt;
    private ?DateTime $updatedAt;

    // Constructor
    public function __construct(?int $id = null, ?int $jobOfferId = null, ?int $userId = null, ?string $fullName = null, ?string $email = null, ?string $phone = null, ?string $experience = null, ?string $coverLetter = null, ?string $resume = null, ?string $status = null, ?DateTime $createdAt = null, ?DateTime $updatedAt = null) {
        $this->id = $id;
        $this->jobOfferId = $jobOfferId;
        $this->userId = $userId;
        $this->fullName = $fullName;
        $this->email = $email;
        $this->phone = $phone;
        $this->experience = $experience;
        $this->coverLetter = $coverLetter;
        $this->resume = $resume;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function show() {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Full Name</th><th>Email</th><th>Phone</th><th>Experience</th><th>Status</th></tr>";
        echo "<tr>";
        echo "<td>{$this->id}</td>";
        echo "<td>{$this->fullName}</td>";
        echo "<td>{$this->email}</td>";
        echo "<td>{$this->phone}</td>";
        echo "<td>{$this->experience}</td>";
        echo "<td>{$this->status}</td>";
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

    public function getJobOfferId(): ?int {
        return $this->jobOfferId;
    }

    public function setJobOfferId(?int $jobOfferId): void {
        $this->jobOfferId = $jobOfferId;
    }

    public function getUserId(): ?int {
        return $this->userId;
    }

    public function setUserId(?int $userId): void {
        $this->userId = $userId;
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

    public function getPhone(): ?string {
        return $this->phone;
    }

    public function setPhone(?string $phone): void {
        $this->phone = $phone;
    }

    public function getExperience(): ?string {
        return $this->experience;
    }

    public function setExperience(?string $experience): void {
        $this->experience = $experience;
    }

    public function getCoverLetter(): ?string {
        return $this->coverLetter;
    }

    public function setCoverLetter(?string $coverLetter): void {
        $this->coverLetter = $coverLetter;
    }

    public function getResume(): ?string {
        return $this->resume;
    }

    public function setResume(?string $resume): void {
        $this->resume = $resume;
    }

    public function getStatus(): ?string {
        return $this->status;
    }

    public function setStatus(?string $status): void {
        $this->status = $status;
    }

    public function getCreatedAt(): ?DateTime {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTime $createdAt): void {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?DateTime {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt): void {
        $this->updatedAt = $updatedAt;
    }
}
?>
