# EXspensePro — Expense Tracker

A sleek, full-stack expense tracking application designed to help users manage their finances with real-time analytics and a clean interface[cite: 4].

## 🚀 Features
- **User Authentication**: Secure register, login, and logout functionality[cite: 3].
- **Live Dashboard**: View total balance, income, and expenses at a glance[cite: 4].
- **Automated Analytics**: Monthly breakdowns and 6-month trends powered by Chart.js[cite: 4].
- **Transaction Management**: Easily add or delete income and expense records[cite: 1, 4].
- **History & Filters**: Search and filter your complete transaction history[cite: 4].

## 🛠️ Tech Stack
- **Frontend**: HTML5, CSS3 (Custom Variables), and Vanilla JavaScript[cite: 4].
- **Backend**: PHP for API logic and session management[cite: 1, 3].
- **Database**: MySQL with prepared statements for security[cite: 2, 3].
- **Charts**: Chart.js for data visualization[cite: 4].

## ⚙️ Setup Instructions

### 1. Local Server
Place this project folder in your local server directory (e.g., `C:/xampp/htdocs/expense-tracker`)[cite: 4].

### 2. Database Configuration
Create a database named `expense_tracker` in your MySQL manager[cite: 2]. Use the following structure for your tables:

- **Users**: Stores name, email, and hashed passwords[cite: 3].
- **Transactions**: Stores user-linked financial records[cite: 1].

### 3. Connection
Ensure your `db.php` file matches your local database credentials (default is `root` with no password)[cite: 2].

### 4. Launch
Access the app by navigating to `http://localhost/expense-tracker/index.html` in your web browser[cite: 4].
