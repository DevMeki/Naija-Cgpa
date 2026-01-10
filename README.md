# Naija Cgpa | Ultimate CGPA & Grade Point Calculator

Naija Cgpa is a modern, responsive web application designed for students in Nigeria to calculate and track their Academic Performances (GPA and CGPA) based on the Nigerian University Commission (NUC) 5.0 scale.

![Naija Cgpa](https://img.shields.io/badge/Version-3.0-emerald)
![PHP](https://img.shields.io/badge/PHP-7.4+-blue)
![MySQL](https://img.shields.io/badge/MySQL-8.0-orange)
![TailwindCSS](https://img.shields.io/badge/Tailwind-4.0-cyan)

## Key Features (v3.0)

### Core Calculation

- **Accurate GPA & CGPA**: Calculates dynamically based on the NUC 5.0 scale.
- **Dual Mode Input**:
  - **Quick Entry**: For power users who know their units and GP.
  - **Detail Mode**: Enter course codes, units, and scores/grades.
- **Single Semester View**: Toggle seamlessly between 1st and 2nd Semester views.
- **Live Stats**: Real-time update of Quality Points (QP), Total Units (TNU), and Grade Points (GP).

### Advanced Sharing & Export

- **PDF Export**: Generate a standard, branded PDF report of your semester results.
- **Image Export (PNG)**: Save a high-quality "Result Slip" image for social media.
- **Smart Share Links**: Generate unique, shareable links to a public "Statement of Result".
- **Certificate View**: A formal, document-style public page for viewing shared results.
- **Social Integration**: One-click sharing to WhatsApp, X (Twitter), Facebook, and LinkedIn.

### User System

- **Secure Authentication**: Signup, Login, and Guest Mode.
- **Data Persistence**: Automatic syncing of data across devices for registered users.
- **Password Recovery**: Secure OTP-based password reset via Email (PHPMailer).
- **Admin Dashboard**: Manage users and monitor growth metrics.

### UI/UX Excellence

- **Premium Design**: "Midnight Slate" & "Electric Indigo" color scheme.
- **Responsive**: Fully mobile-optimized with sticky navigation and touch-friendly controls.
- **Smart Notifications**: Toast popups for actions like "Saved", "Copied", or "Exported".
- **Educational Guide**: Built-in "How to Calculate" modal for guidance.

## Tech Stack

- **Backend**: PHP (PDO)
- **Frontend**: HTML5, Vanilla CSS, Tailwind CSS v4
- **Database**: MySQL
- **Charts**: Chart.js
- **Email**: PHPMailer (SMTP)
- **Icons**: Heroicons (Inline SVG)

## Installation

1. **Clone the repository**:

   ```bash
   git clone https://github.com/yourusername/cgpa-calculator.git
   ```

2. **Database Setup**:

   - Create a database in MySQL (e.g., `cgpa_calculator`).
   - Import the `schema.sql` file to create the necessary tables.

3. **Configuration**:

   - Update `db.php` with your database credentials (host, dbname, username, password).
   - Update `config.php` with your SMTP details for the "Forgot Password" feature.

4. **Web Server**:
   - Move the project to your local server directory (e.g., `C:/xampp/htdocs/`).
   - Ensure `.htaccess` is present for clean URLs.

## Admin Access

The Admin Dashboard is restricted by email. Currently set to allow:

- `your_email@example.com`

You can modify the `$allowed_email` variable in `admin_dashboard.php` to change this.
