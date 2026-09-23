📦 Enterprise Resource & Inventory Management System (ERP)
A comprehensive internal management platform built with PHP, MySQL, HTML, CSS and JavaScript, designed for businesses to track inventory, suppliers, sales orders, and departmental permissions — all from a single, role-based dashboard.
    
________________________________________
📖 About the Project
Many small and medium businesses still manage inventory, suppliers, and sales manually — leading to stockouts, lost records, and no control over who can access what. This ERP system solves that by providing:
•	A centralized dashboard with real-time stock and sales insights
•	Automatic stock updates whenever a sales order is placed or cancelled
•	Role-based access control, so each staff member only sees and does what their role permits
•	A clean, modern, responsive interface
________________________________________
✨ Features
🔐 Authentication & Access Control
•	Secure login with hashed passwords (password_hash / password_verify)
•	Role-based permission system (View / Add / Edit / Delete per module)
•	Session-based access guarding on every page
📊 Dashboard
•	Live summary cards: total products, low-stock alerts, suppliers, total sales
•	Instantly reflects database changes
📋 Inventory / Products
•	Full CRUD (Create, Read, Update, Delete)
•	Category and supplier linking
•	Automatic low-stock and out-of-stock badges based on reorder level
🚚 Suppliers
•	Full CRUD with search
•	Linked to products for traceability
🛒 Sales Orders
•	Multi-item order creation
•	Automatic stock deduction on order completion (using SQL transactions)
•	Stock validation (cannot oversell)
•	Order cancellation returns stock automatically
•	Printable order view
👥 Users, Roles & Departments
•	Admin can create/edit/delete users
•	Assign roles and departments
•	Live permission matrix — changes apply instantly, no re-login required
•	Admin role is protected from lockout
________________________________________
🗂️ Tech Stack
Layer	Technology
Server	Apache (via XAMPP)
Backend	PHP (PDO for secure database access)
Database	MySQL
Frontend	HTML5, CSS3, Vanilla JavaScript
________________________________________
📁 Project Structure
erp/
├── config/
│   └── db.php              # Database connection
├── includes/
│   ├── auth.php             # Login guard + permission functions
│   ├── header.php           # Sidebar + layout header
│   └── footer.php
├── assets/
│   └── css/
│       └── style.css        # All styling
├── products/                # Inventory CRUD
├── suppliers/                # Suppliers CRUD
├── orders/                   # Sales orders
├── users/                    # Users, roles, permissions, departments
├── login.php
├── logout.php
├── dashboard.php
└── database.sql              # Full database schema + sample data
________________________________________
⚙️ Installation & Setup
Prerequisites
•	XAMPP (Apache + MySQL + PHP)
Steps
1.	Clone this repository into your XAMPP htdocs folder:
2.	cd C:\xampp\htdocs
3.	git clone https://github.com/your-username/erp-system.git erp
4.	Start Apache and MySQL from the XAMPP Control Panel.
5.	Import the database:
o	Open http://localhost/phpmyadmin
o	Create a new database named erp_system
o	Go to the Import tab, select database.sql from this project, and click Go
6.	Configure the database connection (already set for default XAMPP):
7.	// config/db.php
8.	$host = 'localhost';
9.	$db   = 'erp_system';
10.	$user = 'root';
11.	$pass = '';
12.	Create the first admin account:
o	Open http://localhost/erp/create_admin.php once
o	Delete this file immediately after use for security
13.	Open the app:
14.	http://localhost/erp/login.php
Default Login (after running create_admin.php)
Field	Value
Email	admin@erp.com
Password	admin123
⚠️ Change this password immediately after your first login.
________________________________________
🖼️ Screenshots
Login Page
 

Dashboard
 
Inventory
 
Sales Order
 
________________________________________
🔒 Security Notes
•	All database queries use prepared statements (protection against SQL injection)
•	Passwords are hashed with password_hash(), never stored in plain text
•	Every page checks login status and role permission before rendering
•	create_admin.php must be deleted after the first admin is created
________________________________________
🚀 Roadmap
•	[ ] Sales & inventory reports with charts (Chart.js)
•	[ ] PDF export for reports and invoices
•	[ ] Email notifications for low stock
•	[ ] Multi-language support
________________________________________
👤 Author
Nayab Software Engineering Student, Capital University of Science and Technology
________________________________________
📄 License
This project is open for educational use. Feel free to fork and build on it.

