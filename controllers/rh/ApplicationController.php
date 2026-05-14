<?php
require_once(__DIR__ . '/../../config/Database.php');
require_once(__DIR__ . '/../../models/rh/Application.php');
require_once(__DIR__ . '/../../core/rh/EmailService.php');

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
            
            // Send email notification to startup
            if (EmailConfig::$emailsEnabled) {
                $this->notifyStartupOfApplication($application);
            }
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    /**
     * Notify startup of a new application
     */
    private function notifyStartupOfApplication(Application $application) {
        try {
            $db = config::getConnexion();
            
            // Get job offer details
            $jobOfferSql = "SELECT * FROM job_offers WHERE id = :jobOfferId";
            $jobOfferQuery = $db->prepare($jobOfferSql);
            $jobOfferQuery->bindValue(':jobOfferId', $application->getJobOfferId());
            $jobOfferQuery->execute();
            $jobOffer = $jobOfferQuery->fetch(PDO::FETCH_ASSOC);
            
            if (!$jobOffer) return;
            
            // Get startup details
            $startupSql = "SELECT * FROM users WHERE id = :userId";
            $startupQuery = $db->prepare($startupSql);
            $startupQuery->bindValue(':userId', $jobOffer['user_id']);
            $startupQuery->execute();
            $startup = $startupQuery->fetch(PDO::FETCH_ASSOC);
            
            if (!$startup) return;
            
            // Get job seeker details
            $seekerSql = "SELECT * FROM users WHERE id = :userId";
            $seekerQuery = $db->prepare($seekerSql);
            $seekerQuery->bindValue(':userId', $application->getUserId());
            $seekerQuery->execute();
            $seeker = $seekerQuery->fetch(PDO::FETCH_ASSOC);
            
            if (!$seeker) return;
            
            // Send email
            $emailService = new EmailService(EmailConfig::$brevoApiKey);
            $emailService->sendApplicationNotification($startup, $seeker, $jobOffer);
        } catch (Exception $e) {
            error_log('Error notifying startup of application: ' . $e->getMessage());
        }
    }

    public function updateApplication(Application $application, $id) {
        try {
            $db = config::getConnexion();
            
            // Get old status to check if it's being changed
            $oldStatusSql = "SELECT status FROM applications WHERE id = :id";
            $oldStatusQuery = $db->prepare($oldStatusSql);
            $oldStatusQuery->bindValue(':id', $id);
            $oldStatusQuery->execute();
            $oldResult = $oldStatusQuery->fetch(PDO::FETCH_ASSOC);
            $oldStatus = $oldResult['status'] ?? null;
            
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
            
            // Send email notification if status changed to accepted/rejected (non-blocking)
            if (EmailConfig::$emailsEnabled && $oldStatus !== $application->getStatus()) {
                try {
                    $this->notifySeekerOfDecision($id, $application->getStatus());
                } catch (Exception $e) {
                    // Log error but don't block the response
                    error_log('Email notification failed for application ' . $id . ': ' . $e->getMessage());
                }
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    /**
     * Notify job seeker of application decision
     */
    private function notifySeekerOfDecision($applicationId, $newStatus) {
        try {
            $db = config::getConnexion();
            
            // Get application details
            $appSql = "SELECT * FROM applications WHERE id = :id";
            $appQuery = $db->prepare($appSql);
            $appQuery->bindValue(':id', $applicationId);
            $appQuery->execute();
            $application = $appQuery->fetch(PDO::FETCH_ASSOC);
            
            if (!$application) return;
            
            // Get job offer details
            $jobOfferSql = "SELECT * FROM job_offers WHERE id = :jobOfferId";
            $jobOfferQuery = $db->prepare($jobOfferSql);
            $jobOfferQuery->bindValue(':jobOfferId', $application['job_offer_id']);
            $jobOfferQuery->execute();
            $jobOffer = $jobOfferQuery->fetch(PDO::FETCH_ASSOC);
            
            if (!$jobOffer) return;
            
            // Get startup/company details
            $companySql = "SELECT * FROM users WHERE id = :userId";
            $companyQuery = $db->prepare($companySql);
            $companyQuery->bindValue(':userId', $jobOffer['user_id']);
            $companyQuery->execute();
            $company = $companyQuery->fetch(PDO::FETCH_ASSOC);
            
            if (!$company) return;
            
            // Use application email directly (it's already the correct one)
            $seeker = [
                'email' => $application['email'],
                'full_name' => $application['full_name']
            ];
            
            // Send appropriate email based on status
            $emailService = new EmailService(EmailConfig::$brevoApiKey);
            if ($newStatus === 'accepted') {
                $emailService->sendAcceptanceNotification($seeker, $jobOffer, $company);
            } elseif ($newStatus === 'rejected') {
                $emailService->sendRejectionNotification($seeker, $jobOffer, $company);
            }
        } catch (Exception $e) {
            error_log('Error notifying seeker of decision: ' . $e->getMessage());
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
        $sql = "SELECT a.*, jo.title, jo.location, jo.description, u.nom_startup AS company_name 
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
        $sql = "SELECT a.*, jo.title, jo.location, u.nom_startup AS company_name 
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
        
        $query->execute([
            'id' => $id,
            'status' => $status
        ]);
        
        // Send email notification if status changed to accepted/rejected (non-blocking)
        if (EmailConfig::$emailsEnabled && in_array($status, ['accepted', 'rejected'])) {
            try {
                $this->notifySeekerOfDecision($id, $status);
            } catch (Exception $e) {
                // Log error but don't block the response
                error_log('Email notification failed for application ' . $id . ': ' . $e->getMessage());
            }
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
            header('Location: login.php');
            exit;
        }

        $applications = $this->getByUserId($_SESSION['user_id']);
        $data = ['applications' => $applications, 'currentView' => 'my-applications'];
        $this->renderView('frontend/frontend', $data);
    }

    public function apply() {
        // Show job application form
        if (empty($_SESSION['user_id'])) {
            header('Location: login.php');
            exit;
        }

        $jobId = $_GET['id'] ?? null;
        if (!$jobId) {
            header('Location: rh.php?page=job-offer/index');
            exit;
        }

        // Get user data (RH users table — must use controllers/rh/UserController)
        require_once __DIR__ . '/UserController.php';
        $userController = new UserController();
        // Try by email first (set by RH AuthController), fall back to user_id
        $user = null;
        if (!empty($_SESSION['user_email'])) {
            $user = $userController->findByEmail((string) $_SESSION['user_email']);
        }
        if (!$user && !empty($_SESSION['user_id'])) {
            $user = $userController->showUser($_SESSION['user_id']);
        }
        if (!$user) {
            header('Location: login.php');
            exit;
        }
        $displayName = trim((string)($user['full_name'] ?? ''));
        if ($displayName === '') {
            $displayName = trim((string)($user['prenom'] ?? '') . ' ' . (string)($user['nom'] ?? ''));
        }
        $user['full_name'] = $displayName !== '' ? $displayName : (string)($user['email'] ?? '');
        $user['phone'] = $user['telephone'] ?? ($user['phone'] ?? '');

        // Check if already applied
        if ($this->hasApplied($jobId, $_SESSION['user_id'])) {
            header('Location: rh.php?page=job-offer/view&id=' . $jobId);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $application = new Application(
                null,
                $jobId,
                $_SESSION['user_id'],
                $_POST['fullName'] ?? $user['full_name'],
                $_POST['email'] ?? $user['email'],
                $_POST['phone'] ?? $user['phone'],
                $_POST['experience'] ?? '',
                $_POST['coverLetter'] ?? '',
                $_POST['resume'] ?? '',
                'pending'
            );

            $this->addApplication($application);
            header('Location: rh.php?page=application/myApplications');
            exit;
        }

        // Get job offer data
        require_once __DIR__ . '/JobOfferController.php';
        $jobOfferController = new JobOfferController();
        $jobOffer = $jobOfferController->showJobOffer($jobId);

        $data = [
            'jobOffer' => $jobOffer,
            'user' => $user,
            'currentView' => 'apply'
        ];
        $this->renderView('frontend/frontend', $data);
    }

    public function suggestJobs() {
        header('Content-Type: application/json');

        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            $input = $_POST;
        }
        
        // Debug logging
        error_log('DEBUG suggestJobs - input: ' . json_encode($input));
        error_log('DEBUG suggestJobs - _POST: ' . json_encode($_POST));

        $position = trim($input['position'] ?? '');
        $experience = trim($input['experience'] ?? '');
        $coverLetter = trim($input['coverLetter'] ?? '');

        $keyword = trim($position . ' ' . $experience . ' ' . $coverLetter);
        if (empty($keyword)) {
            http_response_code(400);
            echo json_encode(['error' => 'Please provide your desired position, experience or cover letter text to get suggestions.']);
            exit;
        }

        require_once __DIR__ . '/JobOfferController.php';
        $jobOfferController = new JobOfferController();
        try {
            $suggestedJobs = $jobOfferController->searchJobOffers($keyword);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Search failed: ' . $e->getMessage()]);
            exit;
        }

        // Debug: add keyword to response
        $response = ['jobs' => array_slice($suggestedJobs, 0, 5)];
        if (empty($suggestedJobs)) {
            $response['debug'] = ['keyword' => $keyword, 'position' => $position, 'experience' => $experience];
        }

        http_response_code(200);
        echo json_encode($response);
        exit;
    }

    public function jobApplications() {
        // Show applications for a specific job (startup only)
        if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? null) !== 'startup') {
            header('Location: login.php');
            exit;
        }

        $jobId = $_GET['id'] ?? null;
        if (!$jobId) {
            header('Location: rh.php?page=job-offer/myOffers');
            exit;
        }

        $applications = $this->getByJobOfferId($jobId);
        // Get job offer title for display
        require_once __DIR__ . '/../controllers/JobOfferController.php';
        $jobOfferController = new JobOfferController();
        $jobOffer = $jobOfferController->showJobOffer($jobId);
        
        $data = [
            'applications' => $applications,
            'jobOffer' => $jobOffer,
            'jobId' => $jobId,
            'currentView' => 'job-applications'
        ];
        $this->renderView('backend/backend', $data);
    }

    private function renderView($viewPath, $data = []) {
        extract($data);
        include __DIR__ . '/../../views/rh/' . $viewPath . '.php';
    }

    public function viewApplication() {
        if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? null) !== 'startup') {
            header('Location: login.php');
            exit;
        }
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: rh.php?page=job-offer/myOffers');
            exit;
        }
        $application = $this->showApplication($id);
        if (!$application) {
            header('Location: rh.php?page=job-offer/myOffers');
            exit;
        }
        $data = ['application' => $application, 'currentView' => 'view-application'];
        $this->renderView('backend/backend', $data);
    }

    public function updateStatusApi() {
        header('Content-Type: application/json');
        if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? null) !== 'startup') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        $id     = $_POST['id']     ?? null;
        $status = $_POST['status'] ?? null;
        if (!$id || !in_array($status, ['pending', 'accepted', 'rejected'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid parameters']);
            exit;
        }
        try {
            $this->updateStatus($id, $status);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
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

    /**
     * API: Delete application (via AJAX)
     */
    public function delete() {
        header('Content-Type: application/json');

        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        try {
            $id = $_POST['id'] ?? $_GET['id'] ?? null;
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'No ID provided']);
                exit;
            }

            $this->deleteApplication($id);

            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Application deleted successfully']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
}
