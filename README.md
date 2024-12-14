
<div align="center">
# 📚 BiblioSwap - Modern Digital Library Platform

![Header](https://github.com/user-attachments/assets/d8c21663-d59d-48f6-a961-8977eb04a455)

A modern and intuitive digital library platform for sharing and discovering books, built with a focus on security and user experience.

[Live Demo](https://votre-demo-url.com)
</div>

## ✨ Features
- 📱 Modern responsive design with fluid animations
- 🔐 Secure user authentication system
- 📖 PDF book upload and management
- 🔍 Real-time search functionality
- 👤 User roles (Admin/User)
- 📊 Admin dashboard
- 🎨 SCSS architecture with BEM methodology

## 🛠️ Technologies Used
- PHP 8
- MySQL
- PDO for secure database operations
- SCSS/CSS3
- JavaScript
- HTML5
- Flexbox & Grid
- Font Awesome icons

## 📦 Installation
1. **Clone the repository**
```bash
git clone https://github.com/Samuelalhadef/BiblioSwap
```

2. **Configure Database**
```sql
CREATE DATABASE biblioswap;
USE biblioswap;
```

3. **Import Database Structure**
- Import the `biblioswap.sql` file in your MySQL server

4. **Configure Connection**
```php
$config = [
    'host' => '127.0.0.1',
    'dbname' => 'biblioswap',
    'username' => 'your_username',
    'password' => 'your_password'
];
```

5. **Launch Application**
- Configure your web server to point to the project directory
- Or use a local development environment like XAMPP

## 📚 Project Structure
```
biblioswap/
│
├── index.php          # Main page
├── login.php          # Authentication
├── register.php       # User registration
├── add_book.php       # Book upload
├── book_details.php   # Single book view
│
├── css/
│   ├── style.scss     # Main styles
│   └── style.css      # Compiled styles
│
├── uploads/           # Book files storage
│   ├── covers/        # Book covers
│   └── pdf/           # PDF files
│
└── includes/
    └── config.php     # Database configuration
```

## 🔋 Core Features

### 📱 Modern UI/UX
- Responsive design
- Fluid animations
- Intuitive navigation
- Modern aesthetics

### 🔐 Security
- Secure password hashing
- PDO prepared statements
- File upload validation
- User role management

### 📖 Book Management
- PDF upload system
- Cover image handling
- Book details management
- Search functionality

## 🔄 Project Status
🚀 **Active Development**

Currently working on:
- Enhanced search filters
- User bookmarks
- Rating system
- Social sharing features

## 🙏 Acknowledgments
- [Plus Jakarta Sans](https://fonts.google.com/specimen/Plus+Jakarta+Sans) for typography
- [FontAwesome](https://fontawesome.com/) for icons
- Open source community

## 📫 Contact
SAMUEL ALHADEF
- [TWITTER](https://x.com/SAMUELALHADEF)
- [LINKEDIN](https://www.linkedin.com/in/samuel-alhadef-190951257/)

Project Link: [https://github.com/Samuelalhadef/Biblio_Swapp](https://github.com/Samuelalhadef/Biblio_Swapp)

---
<div align="center">
Developed with 💙 by [SAMUEL ALHADEF](https://github.com/Samuelalhadef)
</div>
