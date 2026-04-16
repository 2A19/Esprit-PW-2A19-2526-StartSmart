<?php
require_once(__DIR__ . '/../config/database.php');
require_once(__DIR__ . '/JobOfferController.php');
require_once(__DIR__ . '/ApplicationController.php');
require_once(__DIR__ . '/EmployeeController.php');

class BackendController {

    private $jobOfferController;
    private $applicationController;
    private $employeeController;

    public function __construct() {
        $this->jobOfferController = new JobOfferController();
        $this->applicationController = new ApplicationController();
        $this->employeeController = new EmployeeController();
    }

    public function dashboard() {
        if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? null) !== 'startup') {
            header('Location: index.php?page=auth/login');
            exit;
        }

        try {
            // Get all job offers for the startup
            $jobOffers = $this->jobOfferController->listJobOffers($_SESSION['user_id']);
            $jobOffersArray = [];
            while ($job = $jobOffers->fetch(PDO::FETCH_ASSOC)) {
                $jobOffersArray[] = $job;
            }

            // Count applications for each job offer
            $totalApplications = 0;
            $recentApplications = [];
            
            foreach ($jobOffersArray as $job) {
                $apps = $this->applicationController->getByJobOfferId($job['id']);
                $totalApplications += count($apps);
                $recentApplications = array_merge($recentApplications, array_map(function($app) use ($job) {
                    $app['job_title'] = $job['title'];
                    return $app;
                }, $apps));
            }

            // Sort recent applications by date (newest first)
            usort($recentApplications, function($a, $b) {
                return strtotime($b['created_at'] ?? '') - strtotime($a['created_at'] ?? '');
            });
            
            // Get only 5 most recent
            $recentApplications = array_slice($recentApplications, 0, 5);

            // Get employee count
            $totalEmployees = $this->employeeController->countByCompany($_SESSION['user_id']);

            // Get job offer count
            $totalJobOffers = count($jobOffersArray);

            $data = [
                'jobOffers' => $jobOffersArray,
                'totalApplications' => $totalApplications,
                'totalEmployees' => $totalEmployees,
                'totalJobOffers' => $totalJobOffers,
                'recentApplications' => array_slice($recentApplications, 0, 5)
            ];

            $this->renderView('backend/dashboard', $data);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    private function renderView($viewPath, $data = []) {
        extract($data);
        include __DIR__ . '/../views/' . $viewPath . '.php';
    }
}
?>
