<?php
class JobOffer {
    private ?int $id;
    private ?int $userId;
    private ?string $title;
    private ?string $description;
    private ?string $requirements;
    private ?float $salaryMin;
    private ?float $salaryMax;
    private ?string $location;
    private ?string $type;
    private ?string $status;
    private ?DateTime $createdAt;
    private ?DateTime $updatedAt;

    // Constructor
    public function __construct(?int $id = null, ?int $userId = null, ?string $title = null, ?string $description = null, ?string $requirements = null, ?float $salaryMin = null, ?float $salaryMax = null, ?string $location = null, ?string $type = null, ?string $status = null, ?DateTime $createdAt = null, ?DateTime $updatedAt = null) {
        $this->id = $id;
        $this->userId = $userId;
        $this->title = $title;
        $this->description = $description;
        $this->requirements = $requirements;
        $this->salaryMin = $salaryMin;
        $this->salaryMax = $salaryMax;
        $this->location = $location;
        $this->type = $type;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function show() {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Title</th><th>Location</th><th>Type</th><th>Salary Min</th><th>Salary Max</th><th>Status</th></tr>";
        echo "<tr>";
        echo "<td>{$this->id}</td>";
        echo "<td>{$this->title}</td>";
        echo "<td>{$this->location}</td>";
        echo "<td>{$this->type}</td>";
        echo "<td>{$this->salaryMin}</td>";
        echo "<td>{$this->salaryMax}</td>";
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

    public function getUserId(): ?int {
        return $this->userId;
    }

    public function setUserId(?int $userId): void {
        $this->userId = $userId;
    }

    public function getTitle(): ?string {
        return $this->title;
    }

    public function setTitle(?string $title): void {
        $this->title = $title;
    }

    public function getDescription(): ?string {
        return $this->description;
    }

    public function setDescription(?string $description): void {
        $this->description = $description;
    }

    public function getRequirements(): ?string {
        return $this->requirements;
    }

    public function setRequirements(?string $requirements): void {
        $this->requirements = $requirements;
    }

    public function getSalaryMin(): ?float {
        return $this->salaryMin;
    }

    public function setSalaryMin(?float $salaryMin): void {
        $this->salaryMin = $salaryMin;
    }

    public function getSalaryMax(): ?float {
        return $this->salaryMax;
    }

    public function setSalaryMax(?float $salaryMax): void {
        $this->salaryMax = $salaryMax;
    }

    public function getLocation(): ?string {
        return $this->location;
    }

    public function setLocation(?string $location): void {
        $this->location = $location;
    }

    public function getType(): ?string {
        return $this->type;
    }

    public function setType(?string $type): void {
        $this->type = $type;
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
