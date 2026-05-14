# AutoPro Garage Management System 🚗🔧

AutoPro Garage is a comprehensive, multi-role web application designed to streamline the operations of modern auto repair shops. It provides dedicated portals for customers, mechanics, receptionists, and administrators, all wrapped in a premium, minimalist chic UI/UX with seamless dark mode support.

## ✨ Features

### 👤 Customer Portal
- **Seamless Booking:** Customers can easily book appointments for vehicle repairs and servicing.
- **My Bookings:** A dedicated portal for customers to track the real-time status of their repairs.
- **Service History:** View past services and invoices.

### 👩‍💻 Receptionist Portal
- **Task Management:** Assign repair tasks and bookings to available mechanics.
- **Point of Sale (POS):** Process payments, manage walk-in customers, and generate invoices.
- **Parts Management:** Sell parts over the counter and track immediate inventory changes.

### 🛠️ Mechanic Portal
- **Task Dashboard:** Mechanics can view their assigned vehicles and tasks for the day.
- **Digital Inspections:** Process digital checklists and vehicle inspections directly from their devices.
- **Parts Usage:** Request and log inventory parts used during repairs.
- **Real-time Updates:** Update repair statuses (e.g., "In Progress", "Completed") which automatically sync to the customer and reception portals.

### 👑 Admin Portal
- **Master Dashboard:** High-level overview of garage operations, revenue, and performance.
- **Inventory Management:** Add, update, and manage the stock levels of auto parts and supplies.
- **Analytics:** Track business metrics and employee performance.

## 🛠️ Technology Stack

- **Frontend:** HTML5, Vanilla CSS (Custom Design System with Dark Mode), JavaScript
- **Backend:** PHP 8+
- **Database:** MySQL / MariaDB (PDO for secure database interactions)
- **Architecture:** Role-based access control (RBAC) architecture

## 🚀 Installation & Setup

1. **Clone the repository:**
   ```bash
   git clone https://github.com/itskenzzoo/AutoPro-Garage.git
   cd AutoPro-Garage
   ```

2. **Environment Setup:**
   - Ensure you have a local server environment running (e.g., XAMPP, MAMP, or LAMP stack).
   - Move the project folder into your web server's root directory (e.g., `htdocs` for XAMPP).

3. **Database Configuration:**
   - Create a new MySQL database named `autopro_garage` (or your preferred name).
   - Import the provided schema:
     ```bash
     mysql -u root -p autopro_garage < database_schema.sql
     ```
   - Update the database credentials in `includes/db.php`:
     ```php
     $host = '127.0.0.1';
     $db = 'autopro_garage';
     $user = 'root'; // Your DB username
     $pass = '';     // Your DB password
     ```

4. **Run the Application:**
   - Access the application via your web browser: `http://localhost/AutoPro-Garage/`

## 🎨 UI/UX Design

The platform was built with a strong focus on aesthetics and usability:
- **Minimalist Chic:** Clean interfaces devoid of clutter.
- **Dynamic Interactions:** Subtle micro-animations enhance user experience.
- **Responsive:** Fully functional on desktops, tablets, and mobile devices (crucial for mechanics on the shop floor).
- **Dark Mode:** System-wide dark mode toggle for comfortable viewing in varied lighting conditions.

## 🔐 Security
- Secure password hashing.
- Role-based route protection to prevent unauthorized access.
- Prepared SQL statements (PDO) to prevent SQL injection attacks.

---
*Built to empower the modern auto shop.*
