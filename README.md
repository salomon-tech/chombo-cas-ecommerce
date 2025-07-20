
# 🛒 Chombo-Cas E-commerce

**Chombo-Cas** is a dynamic e-commerce platform built for online phone sales. Designed to support vendors and customers in Goma (DR Congo), the platform offers a smooth buying experience, admin control panel, and mobile money integration.

---

## 🚀 Features

- 🛍️ Product catalog with images, prices, and descriptions
- 🧺 Shopping cart system
- 📱 Mobile Money integration (Airtel Money, M-Pesa, Orange Money)
- 👨‍💼 Admin panel to manage products and users
- 🔍 Search and filter products by category, brand, or price
- 📦 Order tracking and customer dashboard
- 🌍 Localized for the Congolese market

---

## 💻 Technologies Used

| Stack | Details |
|-------|---------|
| Frontend | HTML, CSS, JavaScript |
| Backend  | PHP (Procedural or OOP) |
| Database | MySQL (phpMyAdmin) |
| Server   | XAMPP / Apache |

---

## 📦 Installation Guide (Localhost)

> ⚠️ Requirements: XAMPP (PHP & MySQL)

### 1. Clone the repository
```bash
git clone https://github.com/salomon-tech/chombo-cas-ecommerce.git
```

### 2. Move the folder to `htdocs`
```bash
# Example path
C:\xampp\htdocs\chombo-cas-ecommerce
```

### 3. Create the database

- Open **phpMyAdmin**
- Create a database named: `chombo_cas`
- Import the SQL file from:  
  `chombo-cas-ecommerce/database/db.sql`

### 4. Configure the database connection

- Open the file:
  ```
  includes/db.php
  ```
- Set your local database info:
```php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'chombo_cas';
```

### 5. Run the app

- Start Apache & MySQL from XAMPP
- Go to your browser:
  ```
  http://localhost/chombo-cas-ecommerce/index.php
  ```
---

## 🙋‍♂️ About the Developer

**Salomon Mbilizi**  
Web Developer | Full-stack | Based in Goma, DRC 🇨🇩  
Passionate about using technology to solve real-world problems.

- 📧 Email: salomonmbilizi@gmail.com  
- 💼 GitHub: [@salomon-tech](https://github.com/salomon-tech)  
- 🌍 Portfolio: *Coming soon...*

---

## 📄 License

This project is licensed under the **MIT License** — you can use, modify, and distribute it freely.

---
2. Create a new branch (`feature/your-feature`)  
3. Commit your changes  
4. Push and create a Pull Request

---

*Built with ❤️ for the Congolese tech community.*
