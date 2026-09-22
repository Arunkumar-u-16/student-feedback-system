# Student Feedback System - Documentation

## 1. Introduction
This is a production-ready Student Feedback System built for academic institutions to collect, analyze, and report faculty performance based on student responses.

## 2. Features
- **Role-Based Access Control (RBAC):** Separate dashboards for Admin, Student, and Faculty.
- **Dynamic Questions:** Admin can manage the feedback questionnaire.
- **Anonymity:** Student data is disconnected from answers for privacy.
- **Analytics:** Statistical visualization using Chart.js.
- **Secure:** Protection against SQLi, XSS, and CSRF.

## 3. Setup Instructions (XAMPP)

### Prerequisites:
1. Install **XAMPP** (PHP 8.0+ recommended).
2. Start **Apache** and **MySQL** services.

### Installation Steps:
1. **Copy Files:** Extract the project folder into `C:\xampp\htdocs\student feedback`.
2. **Database Setup:**
   - Open your browser and go to `http://localhost/phpmyadmin`.
   - Create a new database named `student_feedback_db`.
   - Click **Import** and select the file located at `database/schema.sql`.
3. **Configuration:**
   - Open `config/database.php` and verify the database credentials.
   - Open `config/config.php` and verify the `BASE_URL`.
4. **Access the Application:**
   - Open browser and navigate to `http://localhost/student feedback`.

### Default Credentials:
- **Admin:** `admin@feedback.com` / `123456`
- *Note: Faculty and Students can be created from the Admin panel.*

## 4. Security Implementation
- **SQL Injection:** All queries use PHP PDO with Prepared Statements.
- **XSS Prevention:** Output is escaped using the `e()` utility function.
- **CSRF Protection:** All POST requests require a valid `csrf_token` stored in the session.
- **Password Hashing:** Uses PHP's `password_hash()` with `bcrypt`.

## 5. Future Enhancements
- **Multi-Department Reports:** department-wise comparison.
- **PDF Export:** Downloadable reports for faculty members.
- **Email Notifications:** Alert faculty when fresh feedback is available.
- **Live Search:** AJAX-based searching in data tables.
- **Custom Branding:** Ability to upload college logo and theme colors.
