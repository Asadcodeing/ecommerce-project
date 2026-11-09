# Online E-Commerce Website

A complete E-Commerce Web Application developed using PHP, MySQL, HTML, CSS and JavaScript.

This project demonstrates how an online shopping website works, from customer registration to product management, shopping cart, checkout and order management.

## 1. Project Overview

The application has two main modules:

**Customer Module**
- User registration and login
- Browse available products
- Add products to shopping cart
- Update product quantities
- Remove products from cart
- Checkout
- Cash on Delivery
- Order confirmation

**Admin Module**
- Secure admin login
- Admin dashboard
- Add new products
- View all products
- Edit product information
- Delete products
- Manage customer orders
- Update order status
- Create additional admin accounts

## 2. Technologies Used

| Technology | Purpose |
|---|---|
| HTML5 | Website structure |
| CSS3 | Website styling |
| JavaScript | Client-side interactions |
| PHP | Backend development |
| MySQL | Database management |
| phpMyAdmin | Database administration |
| XAMPP | Local development server |
| Git & GitHub | Version control |

## 3. Software Requirements

Install the following:

1. XAMPP
2. Visual Studio Code
3. Git
4. Web browser
5. GitHub account

## 4. Installation Guide

1. Clone this repository:

   `git clone https://github.com/Asadcodeing/ecommerce-project.git`

2. Move the project folder into:

   `C:\xampp\htdocs\`

3. Open XAMPP Control Panel.

4. Start Apache and MySQL.

5. Open phpMyAdmin:

   `http://localhost/phpmyadmin`

6. Create a database named `ecommerce1`.

7. Import the database SQL file supplied with this project.

8. Configure your local database connection in `includes/db.php`, using the example configuration as a reference.

9. Open the website:

   `http://localhost/ecommerce-project/`

**Important:** The database SQL export must be included in the repository for a fresh installation. Never include real database credentials or customer data.

## 5. How the Application Works

### Customer Workflow

1. Customer opens the website.
2. Customer registers an account.
3. Registration creates an account with the default role `user`.
4. Customer logs in.
5. Customer views products retrieved from MySQL.
6. Customer adds products to the cart.
7. Customer updates quantities or removes products.
8. Customer proceeds to checkout.
9. Customer enters shipping information.
10. Customer places an order.
11. Order details are stored in the database.
12. Customer receives an order confirmation.

### Admin Workflow

1. Admin opens the admin login page.
2. Admin enters registered admin credentials.
3. Backend verifies the password and admin role.
4. Admin accesses the dashboard.
5. Admin can add, edit and manage products.
6. Admin can view customer orders.
7. Admin updates order statuses.
8. Admin can create additional admin accounts through the protected admin management feature.

## 6. Database Overview

The application uses MySQL.

Main tables:

- `users` – Stores customer and admin accounts.
- `products` – Stores product information.
- `cart` – Stores shopping cart information.
- `orders` – Stores customer order and shipping information.
- `order_items` – Stores individual products belonging to each order.

See `docs/04_DATABASE_DESIGN.md` for the detailed database structure.

## 7. Important PHP Concepts Used

A fresher should understand these concepts before modifying the project:

- PHP sessions
- GET and POST requests
- Form handling
- PDO database connections
- Prepared SQL statements
- Password hashing and verification
- CRUD operations
- File uploads
- HTML form validation
- Database relationships
- Authentication and authorization

## 8. Project Documentation

Read these guides in order:

1. `docs/01_PROJECT_OVERVIEW.md`
2. `docs/02_INSTALLATION_GUIDE.md`
3. `docs/03_PROJECT_STRUCTURE.md`
4. `docs/04_DATABASE_DESIGN.md`
5. `docs/05_CUSTOMER_WORKFLOW.md`
6. `docs/06_ADMIN_WORKFLOW.md`
7. `docs/07_CODE_EXPLANATION.md`
8. `docs/08_SECURITY_GUIDE.md`
9. `docs/09_TROUBLESHOOTING.md`

## 9. Important Security Notes

- Public registration must create users with the `user` role.
- Only authorized admins can create additional admin accounts.
- Passwords must be stored using `password_hash()`.
- Passwords must be checked using `password_verify()`.
- Use prepared SQL statements.
- Protect admin pages using session and role verification.
- Validate uploaded images.
- Do not commit database passwords or private credentials to GitHub.

## 10. Project Status

This project is intended as a learning and portfolio project. Test all functionality and security controls before using it for real customers.

## 11. Author

**Asad Basha**

GitHub: https://github.com/Asadcodeing

---

If you are a fresher learning PHP and MySQL, start with the installation guide and follow the documentation in numerical order.
