<?php
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../models/Employee.php');

class EmployeeController {

    public function listEmployees($userId = null) {
        $sql = "SELECT * FROM employees";
        if ($userId) {
            $sql .= " WHERE user_id = :userId";
        }
        $sql .= " ORDER BY created_at DESC";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            if ($userId) {
                $query->bindValue(':userId', $userId);
            }
            $query->execute();
            return $query;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function addEmployee(Employee $employee) {
        $sql = "INSERT INTO employees (user_id, job_offer_id, full_name, email, phone, position, department, salary, start_date, status) 
                VALUES (:userId, :jobOfferId, :fullName, :email, :phone, :position, :department, :salary, :startDate, :status)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'userId' => $employee->getUserId(),
                'jobOfferId' => $employee->getJobOfferId(),
                'fullName' => $employee->getFullName(),
                'email' => $employee->getEmail(),
                'phone' => $employee->getPhone(),
                'position' => $employee->getPosition(),
                'department' => $employee->getDepartment(),
                'salary' => $employee->getSalary(),
                'startDate' => $employee->getStartDate() ? $employee->getStartDate()->format('Y-m-d') : null,
                'status' => $employee->getStatus()
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function updateEmployee(Employee $employee, $id) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE employees SET 
                    full_name = :fullName,
                    email = :email,
                    phone = :phone,
                    position = :position,
                    department = :department,
                    salary = :salary,
                    status = :status,
                    updated_at = NOW()
                WHERE id = :id'
            );
            $query->execute([
                'id' => $id,
                'fullName' => $employee->getFullName(),
                'email' => $employee->getEmail(),
                'phone' => $employee->getPhone(),
                'position' => $employee->getPosition(),
                'department' => $employee->getDepartment(),
                'salary' => $employee->getSalary(),
                'status' => $employee->getStatus()
            ]);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function deleteEmployee($id) {
        $sql = "DELETE FROM employees WHERE id = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function showEmployee($id) {
        $sql = "SELECT e.*, u.company_name, jo.title 
                FROM employees e 
                LEFT JOIN users u ON e.user_id = u.id 
                LEFT JOIN job_offers jo ON e.job_offer_id = jo.id 
                WHERE e.id = :id";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->bindValue(':id', $id);

        try {
            $query->execute();
            $employee = $query->fetch();
            return $employee;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function searchEmployees($userId, $keyword = '') {
        $sql = "SELECT * FROM employees WHERE user_id = :userId";
        
        if (!empty($keyword)) {
            $sql .= " AND (full_name LIKE :keyword OR email LIKE :keyword OR position LIKE :keyword)";
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->bindValue(':userId', $userId);
        
        if (!empty($keyword)) {
            $query->bindValue(':keyword', '%' . $keyword . '%');
        }
        
        try {
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function countByDepartment($userId) {
        $sql = "SELECT department, COUNT(*) as count 
                FROM employees 
                WHERE user_id = :userId AND status = 'active' 
                GROUP BY department";
        
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->bindValue(':userId', $userId);
        
        try {
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function countByCompany($userId) {
        $sql = "SELECT COUNT(*) as count FROM employees WHERE user_id = :userId AND status = 'active'";
        
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->bindValue(':userId', $userId);
        
        try {
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // View Methods (Display/Render)
    
    public function index() {
        // List employees (startup only)
        if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? null) !== 'startup') {
            header('Location: index.php?page=auth/login');
            exit;
        }

        $keyword = $_GET['keyword'] ?? '';
        
        if (!empty($keyword)) {
            $employees = $this->searchEmployees($_SESSION['user_id'], $keyword);
        } else {
            $result = $this->listEmployees($_SESSION['user_id']);
            $employees = [];
            while ($employee = $result->fetch(PDO::FETCH_ASSOC)) {
                $employees[] = $employee;
            }
        }

        // Get department breakdown
        $departments = $this->countByDepartment($_SESSION['user_id']);
        $totalCount = count($employees);

        $data = [
            'employees' => $employees,
            'keyword' => $keyword,
            'totalCount' => $totalCount,
            'departments' => $departments
        ];
        $this->renderView('backend/employees', $data);
    }

    private function renderView($viewPath, $data = []) {
        extract($data);
        include __DIR__ . '/../views/' . $viewPath . '.php';
    }

    // AJAX API Endpoints (return JSON)

    public function store() {
        // API: Create new employee (via AJAX)
        header('Content-Type: application/json');
        
        if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? null) !== 'startup') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        try {
            $startDate = $_POST['startDate'] ?? null;
            if ($startDate) {
                $startDate = DateTime::createFromFormat('Y-m-d', $startDate);
            }

            $employee = new Employee(
                null,
                $_SESSION['user_id'],
                $_POST['jobOfferId'] ?? null,
                $_POST['fullName'] ?? '',
                $_POST['email'] ?? '',
                $_POST['phone'] ?? '',
                $_POST['position'] ?? '',
                $_POST['department'] ?? '',
                floatval($_POST['salary'] ?? 0),
                $startDate,
                $_POST['status'] ?? 'active'
            );

            $this->addEmployee($employee);
            
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Employee added successfully']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    public function update() {
        // API: Update employee (via AJAX)
        header('Content-Type: application/json');
        
        if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? null) !== 'startup') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        try {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'No ID provided']);
                exit;
            }

            $employee = new Employee(
                $id,
                $_SESSION['user_id'],
                $_POST['jobOfferId'] ?? null,
                $_POST['fullName'] ?? '',
                $_POST['email'] ?? '',
                $_POST['phone'] ?? '',
                $_POST['position'] ?? '',
                $_POST['department'] ?? '',
                floatval($_POST['salary'] ?? 0),
                null,
                $_POST['status'] ?? 'active'
            );

            $this->updateEmployee($employee, $id);
            
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Employee updated successfully']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    public function delete() {
        // API: Delete employee (via AJAX)
        header('Content-Type: application/json');
        
        if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? null) !== 'startup') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        try {
            $id = $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'No ID provided']);
                exit;
            }

            $this->deleteEmployee($id);
            
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Employee deleted successfully']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
}
?>
