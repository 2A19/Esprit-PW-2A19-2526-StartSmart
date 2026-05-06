<?php
class EmailService {
    private $apiKey;
    private $apiUrl = 'https://api.brevo.com/v3/smtp/email';
    private $senderEmail = 'youssefboussetta333@gmail.com';
    private $senderName = 'StartSmart HR';

    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
    }

    /**
     * Send email when a new job offer is posted
     */
    public function sendJobOfferNotification($jobSeeker, $jobOffer, $company) {
        $to = [
            [
                'email' => $jobSeeker['email'],
                'name' => $jobSeeker['full_name']
            ]
        ];

        $subject = "New Job Offer: " . $jobOffer['title'] . " at " . $company['company_name'];
        
        $htmlContent = $this->getJobOfferTemplate($jobSeeker['full_name'], $jobOffer, $company);

        return $this->sendEmail($to, $subject, $htmlContent);
    }

    /**
     * Send email when a job seeker applies for a job
     */
    public function sendApplicationNotification($startup, $jobSeeker, $jobOffer) {
        $to = [
            [
                'email' => $startup['email'],
                'name' => $startup['full_name']
            ]
        ];

        $subject = "New Application: " . $jobSeeker['full_name'] . " applied for " . $jobOffer['title'];
        
        $htmlContent = $this->getApplicationTemplate($startup['full_name'], $jobSeeker, $jobOffer);

        return $this->sendEmail($to, $subject, $htmlContent);
    }

    /**
     * Send email when application is accepted
     */
    public function sendAcceptanceNotification($jobSeeker, $jobOffer, $company) {
        $to = [
            [
                'email' => $jobSeeker['email'],
                'name' => $jobSeeker['full_name']
            ]
        ];

        $subject = "Congratulations! Your application for " . $jobOffer['title'] . " has been accepted";
        
        $htmlContent = $this->getAcceptanceTemplate($jobSeeker['full_name'], $jobOffer, $company);

        return $this->sendEmail($to, $subject, $htmlContent);
    }

    /**
     * Send email when application is rejected
     */
    public function sendRejectionNotification($jobSeeker, $jobOffer, $company) {
        $to = [
            [
                'email' => $jobSeeker['email'],
                'name' => $jobSeeker['full_name']
            ]
        ];

        $subject = "Update on your application for " . $jobOffer['title'];
        
        $htmlContent = $this->getRejectionTemplate($jobSeeker['full_name'], $jobOffer, $company);

        return $this->sendEmail($to, $subject, $htmlContent);
    }

    /**
     * Generic email sending method
     */
    private function sendEmail($to, $subject, $htmlContent) {
        $payload = [
            'sender' => [
                'email' => $this->senderEmail,
                'name' => $this->senderName
            ],
            'to' => $to,
            'subject' => $subject,
            'htmlContent' => $htmlContent
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/json',
            'api-key: ' . $this->apiKey
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        error_log('=== EMAIL SEND ATTEMPT ===');
        error_log('Subject: ' . $subject);
        error_log('To: ' . json_encode($to));
        error_log('From: ' . $this->senderEmail);
        error_log('HTTP Code: ' . $httpCode);
        error_log('Response: ' . $response);
        error_log('Curl Error: ' . $curlError);
        error_log('======================');

        if ($httpCode >= 200 && $httpCode < 300) {
            error_log('✓ Email sent successfully. Subject: ' . $subject);
            return true;
        } else {
            error_log('✗ Failed to send email. Subject: ' . $subject . '. Response: ' . $response . ' HTTP Code: ' . $httpCode);
            return false;
        }
    }

    /**
     * Email template for new job offer
     */
    private function getJobOfferTemplate($name, $jobOffer, $company) {
        $jobUrl = "http://" . $_SERVER['HTTP_HOST'] . "/StartSmartrh/index.php?page=job-offer/view&id=" . $jobOffer['id'] . "&action=view";
        
        return "
        <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; }
                    .header { background: #2c3e50; color: white; padding: 20px; text-align: center; border-radius: 5px; }
                    .content { background: white; padding: 20px; margin-top: 20px; border-radius: 5px; }
                    .job-title { color: #2c3e50; font-size: 24px; font-weight: bold; }
                    .company { color: #666; font-size: 16px; }
                    .details { margin: 15px 0; }
                    .button { display: inline-block; padding: 12px 30px; background: #3498db; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
                    .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #999; text-align: center; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>New Job Opportunity!</h1>
                    </div>
                    <div class='content'>
                        <p>Hi " . htmlspecialchars($name) . ",</p>
                        <p>We found a job that matches your profile!</p>
                        
                        <div class='job-title'>" . htmlspecialchars($jobOffer['title']) . "</div>
                        <div class='company'>at " . htmlspecialchars($company['company_name']) . "</div>
                        
                        <div class='details'>
                            <p><strong>Location:</strong> " . htmlspecialchars($jobOffer['location']) . "</p>
                            <p><strong>Type:</strong> " . htmlspecialchars($jobOffer['type']) . "</p>
                            <p><strong>Salary Range:</strong> \$" . number_format($jobOffer['salary_min'], 2) . " - \$" . number_format($jobOffer['salary_max'], 2) . "</p>
                            <p><strong>Description:</strong></p>
                            <p>" . htmlspecialchars(substr($jobOffer['description'], 0, 300)) . "...</p>
                        </div>
                        
                        <a href='" . $jobUrl . "' class='button'>View Job Offer</a>
                        
                        <div class='footer'>
                            <p>&copy; 2026 StartSmart HR. All rights reserved.</p>
                        </div>
                    </div>
                </div>
            </body>
        </html>";
    }

    /**
     * Email template for new application
     */
    private function getApplicationTemplate($startupName, $jobSeeker, $jobOffer) {
        return "
        <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; }
                    .header { background: #2c3e50; color: white; padding: 20px; text-align: center; border-radius: 5px; }
                    .content { background: white; padding: 20px; margin-top: 20px; border-radius: 5px; }
                    .applicant-info { background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 15px 0; }
                    .button { display: inline-block; padding: 12px 30px; background: #27ae60; color: white; text-decoration: none; border-radius: 5px; margin-top: 20px; }
                    .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #999; text-align: center; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>New Application Received!</h1>
                    </div>
                    <div class='content'>
                        <p>Hi " . htmlspecialchars($startupName) . ",</p>
                        <p>You have received a new application for the position: <strong>" . htmlspecialchars($jobOffer['title']) . "</strong></p>
                        
                        <div class='applicant-info'>
                            <h3>Applicant Information:</h3>
                            <p><strong>Name:</strong> " . htmlspecialchars($jobSeeker['full_name']) . "</p>
                            <p><strong>Email:</strong> " . htmlspecialchars($jobSeeker['email']) . "</p>
                            <p><strong>Phone:</strong> " . htmlspecialchars($jobSeeker['phone']) . "</p>
                            <p><strong>Profession:</strong> " . htmlspecialchars($jobSeeker['profession']) . "</p>
                            <p><strong>Experience:</strong> " . htmlspecialchars($jobSeeker['experience']) . "</p>
                        </div>
                        
                        <p>Please log in to your dashboard to review the full application details.</p>
                        
                        <div class='footer'>
                            <p>&copy; 2026 StartSmart HR. All rights reserved.</p>
                        </div>
                    </div>
                </div>
            </body>
        </html>";
    }

    /**
     * Email template for acceptance
     */
    private function getAcceptanceTemplate($name, $jobOffer, $company) {
        return "
        <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; }
                    .header { background: #27ae60; color: white; padding: 20px; text-align: center; border-radius: 5px; }
                    .content { background: white; padding: 20px; margin-top: 20px; border-radius: 5px; }
                    .congratulations { font-size: 24px; font-weight: bold; color: #27ae60; text-align: center; }
                    .job-details { background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 15px 0; }
                    .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #999; text-align: center; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>🎉 Application Accepted!</h1>
                    </div>
                    <div class='content'>
                        <p>Hi " . htmlspecialchars($name) . ",</p>
                        
                        <div class='congratulations'>
                            Congratulations on your successful application!
                        </div>
                        
                        <p>We are pleased to inform you that your application for the following position has been accepted:</p>
                        
                        <div class='job-details'>
                            <p><strong>Position:</strong> " . htmlspecialchars($jobOffer['title']) . "</p>
                            <p><strong>Company:</strong> " . htmlspecialchars($company['company_name']) . "</p>
                            <p><strong>Contact Email:</strong> " . htmlspecialchars($company['email']) . "</p>
                        </div>
                        
                        <p>The company will be in touch with you shortly regarding the next steps. Please check your email for further instructions.</p>
                        
                        <p>Best regards,<br>StartSmart HR Team</p>
                        
                        <div class='footer'>
                            <p>&copy; 2026 StartSmart HR. All rights reserved.</p>
                        </div>
                    </div>
                </div>
            </body>
        </html>";
    }

    /**
     * Email template for rejection
     */
    private function getRejectionTemplate($name, $jobOffer, $company) {
        return "
        <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; }
                    .header { background: #e74c3c; color: white; padding: 20px; text-align: center; border-radius: 5px; }
                    .content { background: white; padding: 20px; margin-top: 20px; border-radius: 5px; }
                    .job-details { background: #f0f0f0; padding: 15px; border-radius: 5px; margin: 15px 0; }
                    .footer { margin-top: 20px; padding-top: 20px; border-top: 1px solid #ddd; font-size: 12px; color: #999; text-align: center; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>Application Status Update</h1>
                    </div>
                    <div class='content'>
                        <p>Hi " . htmlspecialchars($name) . ",</p>
                        
                        <p>Thank you for your interest in the following position:</p>
                        
                        <div class='job-details'>
                            <p><strong>Position:</strong> " . htmlspecialchars($jobOffer['title']) . "</p>
                            <p><strong>Company:</strong> " . htmlspecialchars($company['company_name']) . "</p>
                        </div>
                        
                        <p>After careful consideration of your qualifications, we regret to inform you that we have decided to move forward with other candidates at this time.</p>
                        
                        <p>We appreciate the time and effort you put into your application. We encourage you to apply for other positions that may be available in the future.</p>
                        
                        <p>Best regards,<br>StartSmart HR Team</p>
                        
                        <div class='footer'>
                            <p>&copy; 2026 StartSmart HR. All rights reserved.</p>
                        </div>
                    </div>
                </div>
            </body>
        </html>";
    }
}
?>
