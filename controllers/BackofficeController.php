<?php

declare(strict_types=1);

namespace Controllers;

class BackofficeController
{
    public function index(): void
    {
        require BASE_PATH . '/views/backoffice/dashboard.php';
    }

    public function exportpdf(): void
    {
        require BASE_PATH . '/views/backoffice/export_pdf.php';
    }
}
