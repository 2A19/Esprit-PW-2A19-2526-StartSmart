/**
 * Application Constants
 */

const APP = {
    // URLs
    BASE_URL: 'http://localhost/StartSmartrh',
    
    // Roles
    ROLE_JOB_SEEKER: 'job_seeker',
    ROLE_STARTUP: 'startup',
    ROLE_ADMIN: 'admin',
    
    // Application Status
    STATUS_PENDING: 'pending',
    STATUS_ACCEPTED: 'accepted',
    STATUS_REJECTED: 'rejected',
    
    // Job Type
    JOB_TYPE_FULL_TIME: 'Full-time',
    JOB_TYPE_PART_TIME: 'Part-time',
    JOB_TYPE_CONTRACT: 'Contract',
    JOB_TYPE_FREELANCE: 'Freelance',
    
    // Employee Status
    EMPLOYEE_ACTIVE: 'active',
    EMPLOYEE_INACTIVE: 'inactive',
    
    // Messages
    SUCCESS_MESSAGE: 'Operation completed successfully',
    ERROR_MESSAGE: 'An error occurred. Please try again.',
    
    // Validation
    MIN_PASSWORD_LENGTH: 8,
    MIN_NAME_LENGTH: 2,
    MAX_NAME_LENGTH: 100,
    
    // Pagination
    ITEMS_PER_PAGE: 10,
    
    // API Endpoints
    ENDPOINTS: {
        LOGIN: '/index.php?page=auth/login',
        REGISTER: '/index.php?page=auth/register',
        LOGOUT: '/index.php?page=auth/logout',
        JOB_OFFERS: '/index.php?page=job-offer/index',
        APPLICATIONS: '/index.php?page=application/index',
        EMPLOYEES: '/index.php?page=employee/index'
    }
};

// Log app info
console.log('StartSmart HR App Constants Loaded');
