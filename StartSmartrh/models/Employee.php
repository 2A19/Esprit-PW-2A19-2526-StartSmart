<?php
class Employee {
    private ?int $id;
    private ?int $userId;
    private ?int $jobOfferId;
    private ?string $fullName;
    private ?string $email;
    private ?string $phone;
    private ?string $position;
    private ?string $department;
    private ?float $salary;
    private ?DateTime $startDate;
    private ?string $status;
    private ?DateTime $createdAt;
    private ?DateTime $updatedAt;

    // Constructor
    public function __construct(?int $id = null, ?int $userId = null, ?int $jobOfferId = null, ?string $fullName = null, ?string $email = null, ?string $phone = null, ?string $position = null, ?string $department = null, ?float $salary = null, ?DateTime $startDate = null, ?string $status = null, ?DateTime $createdAt = null, ?DateTime $updatedAt = null) {
        $this->id = $id;
        $this->userId = $userId;
        $this->jobOfferId = $jobOfferId;
        $this->fullName = $fullName;
        $this->email = $email;
        $this->phone = $phone;
        $this->position = $position;
        $this->department = $department;
        $this->salary = $salary;
        $this->startDate = $startDate;
        $this->status = $status;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
    }

    public function show() {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>Full Name</th><th>Email</th><th>Phone</th><th>Position</th><th>Department</th><th>Salary</th><th>Start Date</th><th>Status</th></tr>";
        echo "<tr>";
        echo "<td>{$this->id}</td>";
        echo "<td>{$this->fullName}</td>";
        echo "<td>{$this->email}</td>";
        echo "<td>{$this->phone}</td>";
        echo "<td>{$this->position}</td>";
        echo "<td>{$this->department}</td>";
        echo "<td>{$this->salary}</td>";
        echo "<td>" . ($this->startDate ? $this->startDate->format('Y-m-d') : '') . "</td>";
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

    public function getJobOfferId(): ?int {
        return $this->jobOfferId;
    }

    public function setJobOfferId(?int $jobOfferId): void {
        $this->jobOfferId = $jobOfferId;
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

    public function getPosition(): ?string {
        return $this->position;
    }

    public function setPosition(?string $position): void {
        $this->position = $position;
    }

    public function getDepartment(): ?string {
        return $this->department;
    }

    public function setDepartment(?string $department): void {
        $this->department = $department;
    }

    public function getSalary(): ?float {
        return $this->salary;
    }

    public function setSalary(?float $salary): void {
        $this->salary = $salary;
    }

    public function getStartDate(): ?DateTime {
        return $this->startDate;
    }

    public function setStartDate(?DateTime $startDate): void {
        $this->startDate = $startDate;
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
