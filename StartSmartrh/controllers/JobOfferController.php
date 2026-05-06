<?php
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/../models/JobOffer.php');
require_once(__DIR__ . '/../core/EmailService.php');

class JobOfferController {

    public function listJobOffers($userId = null) {
        $sql = "SELECT * FROM job_offers";
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

    public function addJobOffer(JobOffer $jobOffer) {
        $sql = "INSERT INTO job_offers (user_id, title, description, requirements, salary_min, salary_max, location, type, status) 
                VALUES (:userId, :title, :description, :requirements, :salaryMin, :salaryMax, :location, :type, :status)";
        $db = config::getConnexion();
        try {
            error_log('=== ADD JOB OFFER STARTED ===');
            $query = $db->prepare($sql);
            $query->execute([
                'userId' => $jobOffer->getUserId(),
                'title' => $jobOffer->getTitle(),
                'description' => $jobOffer->getDescription(),
                'requirements' => $jobOffer->getRequirements(),
                'salaryMin' => $jobOffer->getSalaryMin(),
                'salaryMax' => $jobOffer->getSalaryMax(),
                'location' => $jobOffer->getLocation(),
                'type' => $jobOffer->getType(),
                'status' => $jobOffer->getStatus()
            ]);
            $jobOfferId = $db->lastInsertId();
            error_log('Job offer created with ID: ' . $jobOfferId);
            
            // Send emails to all job seekers
            error_log('Emails enabled: ' . (EmailConfig::$emailsEnabled ? 'YES' : 'NO'));
            if (EmailConfig::$emailsEnabled) {
                error_log('Calling notifyJobSeekers...');
                $this->notifyJobSeekers($jobOfferId, $jobOffer->getUserId());
            }
            
            error_log('=== ADD JOB OFFER COMPLETED ===');
            return $jobOfferId;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
            error_log('Error in addJobOffer: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Notify all job seekers about a new job offer
     */
    private function notifyJobSeekers($jobOfferId, $startupId) {
        try {
            error_log('=== NOTIFY JOB SEEKERS STARTED ===');
            error_log('Job ID: ' . $jobOfferId . ', Startup ID: ' . $startupId);
            
            $db = config::getConnexion();
            
            // Get the job offer details
            $jobOffer = $this->showJobOffer($jobOfferId);
            error_log('Job Offer fetched: ' . ($jobOffer ? 'YES' : 'NO'));
            if (!$jobOffer) return;
            
            // Get startup details
            $startup = $this->getStartupInfo($startupId);
            error_log('Startup fetched: ' . ($startup ? 'YES' : 'NO'));
            if (!$startup) return;
            
            // Get all job seekers
            $sql = "SELECT * FROM users WHERE role = 'job_seeker' AND email IS NOT NULL";
            error_log('Query: ' . $sql);
            $query = $db->prepare($sql);
            $query->execute();
            $seekers = $query->fetchAll(PDO::FETCH_ASSOC);
            error_log('Job Seekers found: ' . count($seekers));
            
            if (count($seekers) > 0) {
                foreach ($seekers as $seeker) {
                    error_log('  - ' . $seeker['email'] . ' (' . $seeker['role'] . ')');
                }
            }
            
            // Send email to each seeker
            $emailService = new EmailService(EmailConfig::$brevoApiKey);
            foreach ($seekers as $seeker) {
                error_log('Sending email to: ' . $seeker['email']);
                $emailService->sendJobOfferNotification($seeker, $jobOffer, $startup);
            }
            error_log('=== NOTIFY JOB SEEKERS COMPLETED ===');
        } catch (Exception $e) {
            error_log('Error notifying job seekers: ' . $e->getMessage());
        }
    }

    /**
     * Get startup information
     */
    private function getStartupInfo($userId) {
        try {
            $sql = "SELECT * FROM users WHERE id = :id";
            $db = config::getConnexion();
            $query = $db->prepare($sql);
            $query->bindValue(':id', $userId);
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Error getting startup info: ' . $e->getMessage());
            return null;
        }
    }

    public function updateJobOffer(JobOffer $jobOffer, $id) {
        try {
            $db = config::getConnexion();
            $query = $db->prepare(
                'UPDATE job_offers SET 
                    title = :title,
                    description = :description,
                    requirements = :requirements,
                    salary_min = :salaryMin,
                    salary_max = :salaryMax,
                    location = :location,
                    type = :type,
                    status = :status,
                    updated_at = NOW()
                WHERE id = :id'
            );
            $query->execute([
                'id' => $id,
                'title' => $jobOffer->getTitle(),
                'description' => $jobOffer->getDescription(),
                'requirements' => $jobOffer->getRequirements(),
                'salaryMin' => $jobOffer->getSalaryMin(),
                'salaryMax' => $jobOffer->getSalaryMax(),
                'location' => $jobOffer->getLocation(),
                'type' => $jobOffer->getType(),
                'status' => $jobOffer->getStatus()
            ]);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function deleteJobOffer($id) {
        $sql = "DELETE FROM job_offers WHERE id = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function showJobOffer($id) {
        $sql = "SELECT jo.*, u.full_name, u.company_name, u.phone, u.email 
                FROM job_offers jo 
                JOIN users u ON jo.user_id = u.id 
                WHERE jo.id = :id";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->bindValue(':id', $id);

        try {
            $query->execute();
            $jobOffer = $query->fetch();
            return $jobOffer;
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function getAllActive() {
        $sql = "SELECT jo.*, u.full_name, u.company_name 
                FROM job_offers jo 
                JOIN users u ON jo.user_id = u.id 
                WHERE jo.status = 'active' 
                ORDER BY jo.created_at DESC";
        $db = config::getConnexion();
        
        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    public function searchJobOffers($keyword = '', $location = '') {
        $sql = "SELECT jo.*, u.full_name, u.company_name 
                FROM job_offers jo 
                JOIN users u ON jo.user_id = u.id 
                WHERE jo.status = 'active'";
        
        $params = [];
        
        if (!empty($keyword)) {
            $keywords = array_filter(array_map('trim', preg_split('/\s+/', strtolower($keyword))), function($word) {
                return strlen($word) > 2;
            });

            if (!empty($keywords)) {
                $conditions = [];
                $keywordIndex = 0;

                foreach ($keywords as $word) {
                    $idx = $keywordIndex;
                    // Create unique param names for each field
                    $paramTitle = 'kw_title_' . $idx;
                    $paramDesc = 'kw_desc_' . $idx;
                    $paramReq = 'kw_req_' . $idx;
                    $paramComp = 'kw_comp_' . $idx;
                    
                    $conditions[] = "(jo.title LIKE :$paramTitle OR jo.description LIKE :$paramDesc OR jo.requirements LIKE :$paramReq OR u.company_name LIKE :$paramComp)";
                    
                    $params[$paramTitle] = '%' . $word . '%';
                    $params[$paramDesc] = '%' . $word . '%';
                    $params[$paramReq] = '%' . $word . '%';
                    $params[$paramComp] = '%' . $word . '%';
                    
                    $keywordIndex++;
                }

                $sql .= " AND (" . implode(' OR ', $conditions) . ")";
            }
        }
        
        if (!empty($location)) {
            $sql .= " AND jo.location LIKE :location";
            $params['location'] = '%' . $location . '%';
        }
        
        $sql .= " ORDER BY jo.created_at DESC";
        
        $db = config::getConnexion();
        
        try {
            $query = $db->prepare($sql);
            
            foreach ($params as $key => $value) {
                $query->bindValue(':' . $key, $value);
            }
            
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('searchJobOffers SQL Error: ' . $e->getMessage());
            error_log('searchJobOffers SQL Query: ' . $sql);
            error_log('searchJobOffers Params: ' . json_encode($params));
            throw new Exception('Search failed: ' . $e->getMessage());
        }
    }

    public function countActive() {
        $sql = "SELECT COUNT(*) as count FROM job_offers WHERE status = 'active'";
        $db = config::getConnexion();
        
        try {
            $query = $db->prepare($sql);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_ASSOC);
            return $result['count'];
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    // View Methods (Display/Render)
    
    public function index() {
        // List all active job offers for job seekers
        if (empty($_SESSION['user_id'])) {
            header('Location: index.php?page=auth/login');
            exit;
        }

        $keyword = $_GET['keyword'] ?? '';
        $location = $_GET['location'] ?? '';
        
        if (!empty($keyword) || !empty($location)) {
            $jobs = $this->searchJobOffers($keyword, $location);
        } else {
            $jobs = $this->getAllActive();
        }

        $data = [
            'jobOffers' => $jobs,
            'keyword' => $keyword,
            'location' => $location,
            'currentView' => 'jobs'
        ];
        $this->renderView('frontend/frontend', $data);
    }

    public function myOffers() {
        // List user's job offers (startup only)
        if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? null) !== 'startup') {
            header('Location: index.php?page=auth/login');
            exit;
        }

        $result = $this->listJobOffers($_SESSION['user_id']);
        $jobOffers = [];
        while ($job = $result->fetch(PDO::FETCH_ASSOC)) {
            $jobOffers[] = $job;
        }

        $suggestedJobId = $_GET['suggest_job_id'] ?? null;
        $employeeSuggestions = [];
        $suggestedJob = null;

        if ($suggestedJobId) {
            require_once __DIR__ . '/../controllers/EmployeeController.php';
            $employeeController = new EmployeeController();
            $jobOffer = $this->showJobOffer($suggestedJobId);
            if ($jobOffer) {
                $suggestedJob = $jobOffer;
                $employeeSuggestions = $employeeController->suggestEmployeesForJob($_SESSION['user_id'], $jobOffer);
            }
        }

        $data = [
            'jobOffers' => $jobOffers,
            'currentView' => 'job-offers',
            'employeeSuggestions' => $employeeSuggestions,
            'suggestedJob' => $suggestedJob
        ];
        $this->renderView('backend/backend', $data);
    }

    public function view() {
        // Show single job offer detail
        if (empty($_SESSION['user_id'])) {
            header('Location: index.php?page=auth/login');
            exit;
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: index.php?page=job-offer/index');
            exit;
        }

        $jobOffer = $this->showJobOffer($id);
        if (!$jobOffer) {
            header('Location: index.php?page=job-offer/index');
            exit;
        }

        $data = ['jobOffer' => $jobOffer, 'currentView' => 'job-detail'];
        $this->renderView('frontend/frontend', $data);
    }

    public function create() {
        // Show create job offer form (startup only)
        if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? null) !== 'startup') {
            header('Location: index.php?page=auth/login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $jobOffer = new JobOffer(
                null,
                $_SESSION['user_id'],
                $_POST['title'] ?? '',
                $_POST['description'] ?? '',
                $_POST['requirements'] ?? '',
                floatval($_POST['salary_min'] ?? 0),
                floatval($_POST['salary_max'] ?? 0),
                $_POST['location'] ?? '',
                $_POST['type'] ?? 'full-time',
                'active'
            );

            $insertedId = $this->addJobOffer($jobOffer);
            if ($insertedId) {
                header('Location: index.php?page=job-offer/myOffers&suggest_job_id=' . urlencode($insertedId));
            } else {
                header('Location: index.php?page=job-offer/myOffers');
            }
            exit;
        }

        $data = ['currentView' => 'create-job-offer'];
        $this->renderView('backend/backend', $data);
    }

    public function edit() {
        // Show edit job offer form (startup only)
        if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? null) !== 'startup') {
            header('Location: index.php?page=auth/login');
            exit;
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: index.php?page=job-offer/myOffers');
            exit;
        }

        $jobOffer = $this->showJobOffer($id);
        if (!$jobOffer || $jobOffer['user_id'] != $_SESSION['user_id']) {
            header('Location: index.php?page=job-offer/myOffers');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $updatedJob = new JobOffer(
                $id,
                $_SESSION['user_id'],
                $_POST['title'] ?? '',
                $_POST['description'] ?? '',
                $_POST['requirements'] ?? '',
                floatval($_POST['salary_min'] ?? 0),
                floatval($_POST['salary_max'] ?? 0),
                $_POST['location'] ?? '',
                $_POST['type'] ?? 'full-time',
                $_POST['status'] ?? 'active'
            );

            $this->updateJobOffer($updatedJob, $id);
            header('Location: index.php?page=job-offer/myOffers');
            exit;
        }

        $data = ['jobOffer' => $jobOffer, 'currentView' => 'edit-job-offer'];
        $this->renderView('backend/backend', $data);
    }

    private function renderView($viewPath, $data = []) {
        extract($data);
        include __DIR__ . '/../views/' . $viewPath . '.php';
    }

    // AJAX API Endpoints (return JSON)

    public function store() {
        // API: Create new job offer (via AJAX)
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }

        if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? null) !== 'startup') {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        try {
            // Validate required fields
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $requirements = $_POST['requirements'] ?? '';
            $location = $_POST['location'] ?? '';
            $type = $_POST['type'] ?? '';
            $salaryMin = $_POST['salaryMin'] ?? '';
            $salaryMax = $_POST['salaryMax'] ?? '';

            $errors = [];

            if (empty(trim($title))) {
                $errors[] = 'Job Title is required';
            }
            if (empty(trim($description))) {
                $errors[] = 'Job Description is required';
            }
            if (empty(trim($requirements))) {
                $errors[] = 'Requirements is required';
            }
            if (empty(trim($location))) {
                $errors[] = 'Location is required';
            }
            if (empty($type)) {
                $errors[] = 'Job Type is required';
            }
            if (!is_numeric($salaryMin) || $salaryMin < 0) {
                $errors[] = 'Minimum Salary must be a valid number';
            }
            if (!is_numeric($salaryMax) || $salaryMax < 0) {
                $errors[] = 'Maximum Salary must be a valid number';
            }
            if (!empty($salaryMin) && !empty($salaryMax) && floatval($salaryMin) > floatval($salaryMax)) {
                $errors[] = 'Minimum salary cannot be greater than maximum salary';
            }

            if (!empty($errors)) {
                http_response_code(400);
                echo json_encode(['errors' => $errors]);
                exit;
            }

            $jobOffer = new JobOffer(
                null,
                $_SESSION['user_id'],
                $title,
                $description,
                $requirements,
                floatval($salaryMin),
                floatval($salaryMax),
                $location,
                $type,
                'active'
            );

            $this->addJobOffer($jobOffer);
            
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Job offer created successfully']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    public function update() {
        // API: Update job offer (via AJAX)
        header('Content-Type: application/json');
        
        if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? null) !== 'startup') {
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

            $jobOffer = new JobOffer(
                $id,
                $_SESSION['user_id'],
                $_POST['title'] ?? '',
                $_POST['description'] ?? '',
                $_POST['requirements'] ?? '',
                floatval($_POST['salaryMin'] ?? 0),
                floatval($_POST['salaryMax'] ?? 0),
                $_POST['location'] ?? '',
                $_POST['type'] ?? 'full-time',
                $_POST['status'] ?? 'active'
            );

            $this->updateJobOffer($jobOffer, $id);
            
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Job offer updated successfully']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }

    public function delete() {
        // API: Delete job offer (via AJAX)
        header('Content-Type: application/json');
        
        if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? null) !== 'startup') {
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

            $this->deleteJobOffer($id);
            
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Job offer deleted successfully']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
}
