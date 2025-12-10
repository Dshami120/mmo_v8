📘 Mad Money Online – Personal Finance Manager

A complete full-stack PHP + MySQL personal finance web application that allows users to track income, expenses, savings, accounts, budgets, and financial history with interactive analytics and Chart.js visualizations.

This system is designed to be simple, modern, and fully functional, capable of running on any local machine or shared hosting environment.

🚀 Features
✔ User Authentication

Login / Logout

Signup

Session security

✔ Finance Modules

Income tracking

Expense tracking

Savings accounts

Account management

Budgeting

Transaction history

Categories & account types (via SQL seed tables)

✔ Dashboard & Analytics

Fully visualized Chart.js graphs

Filtering by Day / Week / Month / Year / All Time

Auto-updating summary cards

Clean Bootstrap 5 UI

✔ Database Architecture

Your SQL dumps define the backend schema, including:

money_account_type 

madmoneyonline_money_account_ty…

money_category 

madmoneyonline_money_category

money_user 

madmoneyonline_money_user

monery_transactions (typo preserved unless you choose to rename) 

madmoneyonline_monery_transacti…

money_account 

madmoneyonline_money_account

These provide all core system data tables.

✔ User Interface

Responsive (Bootstrap 5)

Custom styling (styles.css) with background video overlay


styles

Landing page with animated money graphics

Smooth navigation

Professional aesthetic

📂 Project Structure
MadMoneyOnline/
│
├── index.html                # Landing page
├── login.php
├── logout.php
├── signup.php
├── dashboard.php
├── income.php
├── expenses.php
├── savings.php
├── budget.php
├── accounts.php
├── history.php
├── settings.php
│
├── dbOperations.php          # Database helper functions
├── settings.php              # Database credentials
│
├── css/
│   └── styles.css            # Custom CSS
│
├── images/
│   ├── moneyMng.PNG
│   ├── moneyFall.gif
│   ├── moneyRain.gif
│   └── (any other UI assets)
│
├── videos/
│   └── moneyRainVid.mp4      # Background video
│
└── sql/
    ├── money_account_type.sql
    ├── money_category.sql
    ├── money_user.sql
    ├── money_account.sql
    └── monery_transactions.sql

🛠️ Technologies Used
Frontend

HTML5

CSS3 (with custom animations)

Bootstrap 5

Chart.js

JavaScript

Backend

PHP 8+

MySQL / MariaDB

PDO or MySQLi (depending on chosen configuration)

💻 Installation Guide (Local Machine)

Follow these steps to run the project locally on Windows / Mac / Linux.

🔧 1. Install Required Software
Option A — XAMPP (Recommended)

Download and install:
👉 https://www.apachefriends.org/download.html

This gives you:

Apache (web server)

PHP

MySQL / MariaDB

phpMyAdmin

Option B — MAMP (Mac Users)

https://www.mamp.info/en/

📁 2. Place the Project Files in Your Server Folder
For XAMPP:
C:\xampp\htdocs\MadMoneyOnline\

For MAMP:
Applications/MAMP/htdocs/MadMoneyOnline/

🗄️ 3. Configure the Database

Start Apache and MySQL from XAMPP Control Panel.

Open your browser and enter:

http://localhost/phpmyadmin


Click New → Create Database
Name it:

madmoneyonline


Import all SQL tables one by one:

money_account_type.sql

money_category.sql

money_user.sql

money_account.sql

monery_transactions.sql

This will create the correct schema and seed sample data.

🔒 4. Configure Database Credentials

Open:

settings.php


And set:

<?php
$host = "localhost";
$dbname = "madmoneyonline";
$username = "root";
$password = "";  // default XAMPP password is empty
?>


If your MySQL root user does have a password, fill it in.

▶️ 5. Run the Application

Open your browser:

http://localhost/MadMoneyOnline/


You should now see the animated landing page.

Login with one of the seeded accounts, such as:

Email: daniyal.shami@madmoney.com
Password: 123456


(From money_user.sql seed data.)

🧩 Troubleshooting
❌ White screen / PHP errors

Enable PHP error reporting by adding this inside any PHP file temporarily:

error_reporting(E_ALL);
ini_set('display_errors', 1);

❌ Database connection failed

Check:

MySQL is running

Correct database name

Correct username/password

$host = "localhost" is valid

❌ Missing CSS or images

Ensure the file paths (relative paths) match your environment.

❌ Video does not play

Place videos inside:

videos/moneyRainVid.mp4

☁️ Optional: Deploying to Shared Hosting / cPanel

Upload all project files to /public_html

Create a MySQL database in cPanel

Import all SQL tables via phpMyAdmin

Update settings.php with hosting provider’s DB credentials

Visit your domain — done.

📄 License

This project can be used, modified, and extended freely.