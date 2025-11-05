# Recipe-Finder
A PHP-based web application that allows users to search for recipes, view ingredients and preparation steps, and explore new dishes easily. The project also includes user registration and login functionality.
Features
🔍 Search recipes by name or ingredients

🍽️ View detailed recipe information

👤 User registration and login system

💾 MySQL database integration

🌐 Simple, responsive UI

Installation Guide
Install XAMPP
Download and install XAMPP for your operating system.

After installation, open the XAMPP Control Panel.

Start the following services:

✅ Apache

✅ MySQL

Move the project folder to the XAMPP htdocs directory:
C:\xampp\htdocs\

Setup the Database
Open your browser and go to:
http://localhost/phpmyadmin

Create a new database (for example):
recipe_finder

Configure Database Connection
Open the file config.php  in the project folder.
php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "recipe_finder";

Run the Project
Open your browser and go to:
http://localhost/recipe-finder/
You should now see the home page of your Recipe Finder Website 
