<?php
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../models/Application.php');

class ApplicationController {

    public function listApplications($jobOfferId = null, $userId = null) {
        $sql = "SELECT * FROM applications";
        $conditions = [];
        
        if ($jobOfferId) {
            $conditions[] = "job_offer_id = :jobOfferId";
        }
        if ($userId) {
            $conditions[] = "user_id = :userId";
        }
        
        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            if ($jobOfferId) {
                $query->bindValue(':jobOfferId', $jobOfferId);
            }
            if ($userId) {
                $query->bindValue(':userId', $userId);
            }
            $query->execute();
            return $query;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function addApplication(Application $application) {
        $sql = "INSERT INTO applications (job_offer_id, user_id, full_name, email, phone, experience, cover_letter, resume, status) 
                VALUES (:jobOfferId, :userId, :fullName, :email, :phone, :experience, :coverLetter, :resume, :status)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'jobOfferId' => $application->getJobOfferId(),
                'userId' => $application->getUserId(),
                'fullName' => $application->getFullName(),
                'email' => $application->getEmail(),
                'phone' => $application->getPhone(),
                'experience' => $application->getExperience(),
                'coverLetter' => $application->getCoverLetter(),
                'resume' => $application->getResume(),
                'status' => $application->getStatus()
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    public function updateApplication(Application $application, $id) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE applications SET 
                    full_name = :fullName,
                    email = :email,
                    phone = :phone,
                    experience = :experience,
                    cover_letter = :coverLetter,
                    resume = :resume,
                    status = :status,
                    updated_at = NOW()
                WHERE id = :id'
            );
            $query->execute([
                'id' => $id,
                'fullName' => $application->getFullName(),
                'email' => $application->getEmail(),
                'phone' => $application->getPhone(),
                'experience' => $application->getExperience(),
                'coverLetter' => $application->getCoverLetter(),
                'resume' => $application->getResume(),
                'status' => $application->getStatus()
            ]);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function deleteApplication($id) {
        $sql = "DELETE FROM applications WHERE id = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function showApplication($id) {
        $sql = "SELECT a.*, jo.title, jo.location, jo.description, u.company_name 
                FROM applications a 
                JOIN job_offers jo ON a.job_offer_id = jo.id 
                JOIN users u ON jo.user_id = u.id 
                WHERE a.id = :id";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->bindValue(':id', $id);

        try {
            $query->execute();
            $application = $query->fetch();
            return $application;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function getByJobOfferId($jobOfferId) {
        $sql = "SELECT * FROM applications 
                WHERE job_offer_id = :jobOfferId 
                ORDER BY created_at DESC";
        
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->bindValue(':jobOfferId', $jobOfferId);
        
        try {
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function getByUserId($userId) {
        $sql = "SELECT a.*, jo.title, jo.location, u.company_name 
                FROM applications a 
                JOIN job_offers jo ON a.job_offer_id = jo.id 
                JOIN users u ON jo.user_id = u.id 
                WHERE a.user_id = :userId 
                ORDER BY a.created_at DESC";
        
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

    public function updateStatus($id, $status) {
        $sql = "UPDATE applications 
                SET status = :status, updated_at = NOW() 
                WHERE id = :id";
        
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        
        try {
            $query->execute([
                'id' => $id,
                'status' => $status
            ]);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function hasApplied($jobOfferId, $userId) {
        $sql = "SELECT id FROM applications 
                WHERE job_offer_id = :jobOfferId AND user_id = :userId LIMIT 1";
        
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->bindValue(':jobOfferId', $jobOfferId);
        $query->bindValue(':userId', $userId);
        
        try {
            $query->execute();
            return $query->rowCount() > 0;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function countByStatus($status) {
        $sql = "SELECT COUNT(*) as count FROM applications WHERE status = :status";
        
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->bindValue(':status', $status);
        
        try {
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // View Methods (Display/Render)

    public function myApplications() {
        // Show user's applications (job seeker only)
        if (empty($_SESSION['user_id'])) {
            header('Location: index.php?page=auth/login');
            exit;
        }

        $applications = $this->getByUserId($_SESSION['user_id']);
        $data = ['applications' => $applications];
        $this->renderView('frontend/my-applications', $data);
    }

    public function apply() {
        // Show job application form
        if (empty($_SESSION['user_id'])) {
            header('Location: index.php?page=auth/login');
            exit;
        }

        $jobId = $_GET['id'] ?? null;
        if (!$jobId) {
            header('Location: index.php?page=job-offer/index');
            exit;
        }

        // Get user data
        require_once __DIR__ . '/../models/User.php';
        require_once __DIR__ . '/../controllers/UserController.php';
        $userController = new UserController();
        $user = $userController->findByEmail($_SESSION['user_email']);

        // Check if already applied
        if ($this->hasApplied($jobId, $_SESSION['user_id'])) {
            header('Location: index.php?page=job-offer/view&id=' . $jobId);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $application = new Application(
                null,
                $jobId,
                $_SESSION['user_id'],
                $_POST['full_name'] ?? $user['full_name'],
                $_POST['email'] ?? $user['email'],
                $_POST['phone'] ?? $user['phone'],
                $_POST['experience'] ?? '',
                $_POST['cover_letter'] ?? '',
                $_POST['resume'] ?? '',
                'pending'
            );

            $this->addApplication($application);
            header('Location: index.php?page=application/myApplications');
            exit;
        }

        // Get job offer data
        require_once __DIR__ . '/../controllers/JobOfferController.php';
        $jobOfferController = new JobOfferController();
        $jobOffer = $jobOfferController->showJobOffer($jobId);

        $data = [
            'jobOffer' => $jobOffer,
            'user' => $user
        ];
        $this->renderView('frontend/apply', $data);
    }

    public function jobApplications() {
        // Show applications for a specific job (startup only)
        if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? null) !== 'startup') {
            header('Location: index.php?page=auth/login');
            exit;
        }

        $jobId = $_GET['id'] ?? null;
        if (!$jobId) {
            header('Location: index.php?page=job-offer/myOffers');
            exit;
        }

        $applications = $this->getByJobOfferId($jobId);
        $data = [
            'applications' => $applications,
            'jobId' => $jobId
        ];
        $this->renderView('backend/job-applications', $data);
    }

    private function renderView($viewPath, $data = []) {
        extract($data);
        include __DIR__ . '/../views/' . $viewPath . '.php';
    }

    // AJAX API Endpoints (return JSON)

    public function store() {
        // API: Submit job application (via AJAX)
        header('Content-Type: application/json');
        
        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        try {
            // Check if already applied
            $jobOfferId = $_POST['jobOfferId'] ?? null;
            if ($this->hasApplied($jobOfferId, $_SESSION['user_id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'You have already applied for this job']);
                exit;
            }

            // Handle file upload if provided
            $resumePath = $_POST['resume'] ?? '';
            if (!empty($_FILES['resume']['name'])) {
                $uploadsDir = __DIR__ . '/../public/uploads/resumes/';
                if (!is_dir($uploadsDir)) {
                    mkdir($uploadsDir, 0755, true);
                }

                $fileExtension = strtolower(pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION));
                $allowedExtensions = ['pdf', 'doc', 'docx'];
                
                if (!in_array($fileExtension, $allowedExtensions)) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Resume must be PDF, DOC, or DOCX']);
                    exit;
                }

                if ($_FILES['resume']['size'] > 5 * 1024 * 1024) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Resume file size must not exceed 5MB']);
                    exit;
                }

                $fileName = uniqid() . '_' . basename($_FILES['resume']['name']);
                $filePath = $uploadsDir . $fileName;

                if (move_uploaded_file($_FILES['resume']['tmp_name'], $filePath)) {
                    $resumePath = 'public/uploads/resumes/' . $fileName;
                } else {
                    http_response_code(400);
                    echo json_encode(['error' => 'Failed to upload resume']);
                    exit;
                }
            }

            $application = new Application(
                null,
                $jobOfferId,
                $_SESSION['user_id'],
                $_POST['fullName'] ?? '',
                $_POST['email'] ?? '',
                $_POST['phone'] ?? '',
                $_POST['experience'] ?? '',
                $_POST['coverLetter'] ?? '',
                $resumePath,
                'pending'
            );

            $this->addApplication($application);
            
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Application submitted successfully']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
}
