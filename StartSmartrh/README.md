# StartSmart HR - Human Resources Management System

A professional MVC-based HR management system built with PHP and PDO, designed for managing job offers, applications, and employee records.

## Features

### For Job Seekers (FrontOffice)
- User registration and authentication
- Browse available job offers
- Search and filter jobs by keyword and location
- Submit job applications with resume upload
- Track application status
- Manage profile

### For Startups (BackOffice)
- Company registration and authentication
- Post and manage job offers
- View and manage job applications
- Review candidate profiles
- Manage employee records
- Dashboard with statistics and metrics

## Tech Stack

- **PHP 7.4+** - Server-side scripting
- **MySQL/MariaDB** - Database
- **PDO** - Database abstraction layer
- **HTML5** - Markup
- **CSS3** - Styling
- **JavaScript** - Frontend interactions
- **MVC Architecture** - Code organization
- **OOP** - Object-oriented programming

## Project Structure

```
StartSmartrh/
├── config/
│   └── database.php          # Database configuration
├── core/
│   ├── Controller.php        # Base controller class
│   └── Validator.php         # Custom form validation
├── models/
│   ├── User.php              # User model
│   ├── JobOffer.php          # Job offer model
│   ├── Application.php       # Application model
│   └── Employee.php          # Employee model
├── controllers/
│   ├── AuthController.php    # Authentication
│   ├── JobOfferController.php # Job offers CRUD
│   ├── ApplicationController.php # Applications
│   ├── EmployeeController.php   # Employees CRUD
│   ├── FrontendController.php   # Frontend pages
│   └── BackendController.php    # Backend dashboard
├── views/
│   ├── frontend/             # Job seeker views
│   ├── backend/              # Startup views
│   └── layouts/              # Shared layouts
├── public/
│   ├── css/
│   │   └── styles.css        # Main stylesheet
│   ├── js/                   # JavaScript files
│   └── uploads/              # User uploads
├── index.php                 # Main router
├── database.sql              # Database schema
└── README.md                 # This file
```

## Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher (or MariaDB)
- XAMPP or similar local server environment

### Step 1: Setup Database

1. Open phpMyAdmin or MySQL command line
2. Execute the SQL commands in `database.sql` to create the database and tables:
   ```sql
   -- Copy all content from database.sql and execute
   ```

### Step 2: Configure Database Connection

Edit `config/database.php` and set your database credentials:
```php
private $host = 'localhost';
private $db_name = 'startsmart_hr';
private $user = 'root';
private $password = '';
```

### Step 3: Access the Application

1. Place the project folder in your web root (e.g., `htdocs` for XAMPP)
2. Start your locahttpl server
3. Navigate to: `http://localhost/StartSmartrh`

## Default Login Credentials

**Job Seeker:**
- Email: `seeker@example.com`
- Password: `Password123`

**Startup:**
- Email: `startup@example.com`
- Password: `Password123`

## Custom Form Validation

The system uses PHP-based custom validation (NO HTML5 validation) for:
- Email format validation
- Password strength requirements
- Phone number format
- Numeric values
- Date format validation
- Salary range validation

Example validation usage:
```php
$validator = new Validator();
$validator->validateEmail($_POST['email'], 'Email');
$validator->validatePassword($_POST['password'], 'Password');

if (!$validator->isValid()) {
    $errors = $validator->getErrors();
    // Handle errors
}
```

## MVC Architecture

### Models
- Handle database operations
- Use PDO for prepared statements
- Implement CRUD operations
- Provide data validation at model level

### Views
- Display data to users
- Include forms for data input
- Use professional styling from CSS
- Include JavaScript for client-side interactions

### Controllers
- Handle user requests
- Process form submissions
- Interact with models
- Manage authentication and authorization
- Render appropriate views

## Color Scheme

Professional color palette matching StartSmart branding:
- **Primary Blue**: #1e3a8a
- **Accent Cyan**: #0891b2
- **Success Green**: #22c55e
- **Light Gray**: #f3f4f6
- **Dark Gray**: #1f2937

## Database Schema

### Users Table
- Stores user information (job seekers and startups)
- Password hashing with bcrypt
- Role-based access control

### Job Offers Table
- Job postings with details
- Salary range, location, requirements
- Status tracking (active/inactive)

### Applications Table
- Job applications from candidates
- Resume management
- Application status (pending/accepted/rejected)

### Employees Table
- Employee information for startups
- Salary, position, department tracking
- Employment dates and status

## Security Features

- PDO prepared statements prevent SQL injection
- Password hashing with bcrypt
- Session-based authentication
- Custom input validation (not relying on HTML5)
- CSRF protection ready
- XSS prevention with output escaping

## API Endpoints

### Authentication
- `POST /index.php?page=auth/login` - User login
- `POST /index.php?page=auth/register` - User registration
- `GET /index.php?page=auth/logout` - User logout

### Job Offers
- `GET /index.php?page=job-offer/index` - List all job offers
- `POST /index.php?page=job-offer/store` - Create new job offer
- `POST /index.php?page=job-offer/update` - Update job offer
- `POST /index.php?page=job-offer/delete` - Delete job offer

### Applications
- `GET /index.php?page=application/apply&id={id}` - Application form
- `POST /index.php?page=application/store` - Submit application
- `POST /index.php?page=application/updateStatus` - Update application status

### Employees
- `GET /index.php?page=employee/index` - List employees
- `POST /index.php?page=employee/store` - Add employee
- `POST /index.php?page=employee/update` - Update employee
- `POST /index.php?page=employee/delete` - Delete employee

## Development Notes

### Adding New Features
1. Create model if needed in `models/`
2. Create controller in `controllers/`
3. Create views in appropriate `views/` subdirectory
4. Add route mapping in `index.php`
5. Add validation in models and controllers

### File Uploads
- Resumes are stored in `public/uploads/resumes/`
- Directory is created automatically if it doesn't exist
- Supported formats: PDF, DOC, DOCX

### Extending Validation
Add custom validation methods to `Validator` class:
```php
public function validateCustomField($value, $fieldName = 'Field') {
    // Validation logic
    $this->addError("Custom error message");
}
```

## Future Enhancements

- Email notifications
- Advanced search filters
- Interview scheduling
- Performance reviews
- Contract management
- Advanced reporting
- API documentation
- Mobile app
- Payment integration

## License

This project is created for educational purposes.

## Support

For issues or questions, please refer to the documentation or contact the development team.

---

**Started**: 2026  
**Version**: 1.0  
**Status**: Active Development
