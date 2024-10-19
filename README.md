
# Time-Tool

## Original Creator
- **Jeroen Soree** (soree-it.nl) started this project 30 years ago. I'm excited to announce that I’m rebranding it into a blazingly fast web application. Originally developed in C#, I have now transitioned it to a modern web app.

## New Features
- **Account System**: Users can create and manage their accounts for a personalized experience.
- **SQL Database**: A robust backend for efficient data storage and retrieval.
- **Rebranded UI**: A fresh, modern interface to enhance user experience.
- **Minimalistic and Sleek Design**: Focused on simplicity and usability.

## Setup Instructions

1. **Configure Database Connection**  
   - Open the file located at `/database/db.php`.
   - Add your database connection details in the following format:
     ```php
     <?php
     $host = 'localhost'; // Database host
     $db   = 'task_manager'; // Database name
     $user = 'your_username'; // Database username
     $pass = 'your_password'; // Database password
     ```

2. **Upload SQL File**  
   - Use phpMyAdmin (or your preferred database management tool) to upload the `sql.sql` file.
   - Alternatively, you can execute the SQL queries contained in the `sql.sql` file directly in phpMyAdmin.

3. **Create an Account**  
   - After setting up the database, navigate to the registration page of your application.
   - Fill in the required fields to create a new account. Make sure to follow any validation rules specified on the page.

4. **Enjoy Your Application!**  
   - Once your account is created, log in and explore the features of your application. If you encounter any issues, check the logs for error messages and troubleshoot accordingly.

## Technologies Used
[![My Skills](https://skillicons.dev/icons?i=sql,php,js,html,css&perline=10)](https://skillicons.dev)

**Last Update**: 10/18/2024

