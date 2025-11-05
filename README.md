# VAC Course Registration Management System

A complete full-stack web application for managing Value Added Course (VAC) registrations with separate student and admin portals.

## Features

### Student Module
- **Registration**: Students can create accounts with validation
- **Login**: Secure authentication with session management
- **Dashboard**: Browse available VAC courses
- **Course Registration**: Register for courses with one-click
- **My Courses**: View registration status (Pending/Approved/Rejected)

### Admin Module
- **Admin Login**: Secure admin authentication
- **Dashboard**: View all student registrations with approval/rejection controls
- **Course Management**: Add, view, and delete courses
- **Registration Management**: Approve or reject student course registrations

## Technology Stack
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Backend**: PHP
- **Database**: MySQL
- **Security**: Password hashing, prepared statements, session management

## Installation

### Prerequisites
- XAMPP/WAMP/LAMP server
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Setup Instructions

1. **Clone/Download** the project files to your web server directory (htdocs for XAMPP)

2. **Database Setup**:
   - Start Apache and MySQL services
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the `database_setup.sql` file or run the SQL commands manually
   - This will create the database and tables with sample data

3. **Configuration**:
   - Update database credentials in `db.php` if needed
   - Default settings are for localhost with root user and no password

4. **Access the Application**:
   - Open http://localhost/[project-folder]/index.html
   - Default admin credentials: username: `admin`, password: `admin123`

## File Structure

```
├── index.html              # Landing page
├── db.php                  # Database connection
├── database_setup.sql      # Database schema and sample data
├── register.php            # Student registration
├── login.php              # Student login
├── student_dashboard.php   # Student course browsing
├── mycourses.php          # Student's registered courses
├── admin_login.php        # Admin login
├── admin_dashboard.php    # Admin registration management
├── manage_courses.php     # Admin course management
├── logout.php             # Session logout
└── README.md              # This file
```

## Database Schema

### Tables
- **students**: Student account information
- **admin**: Admin credentials
- **courses**: Available VAC courses
- **registrations**: Student course registrations with status tracking

## Security Features
- Password hashing using PHP's `password_hash()`
- Prepared SQL statements to prevent injection
- Session-based authentication
- Input validation and sanitization
- Access control for admin and student areas

## Usage Workflow

1. **Student Registration**: New students create accounts via registration form
2. **Student Login**: Students log in to access their dashboard
3. **Course Browsing**: Students view available courses and register
4. **Admin Review**: Admins log in to review and approve/reject registrations
5. **Status Updates**: Students can check their registration status

## Default Credentials
- **Admin**: username: `admin`, password: `admin123`

## Sample Courses Included
- Web Development Fundamentals
- Data Science with Python
- Digital Marketing
- Mobile App Development
- Cybersecurity Basics

## Customization
- Modify course categories in the database
- Update styling in Bootstrap classes
- Add email notifications (optional feature)
- Implement additional validation rules