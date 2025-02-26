# DK-KS Library Management System (LMS)

## Overview
The Library Management System (LMS) is a web-based application designed to efficiently manage books, users, and transactions in a library. The system supports both admin and client roles with different access levels and functionalities.

## Features

### 1. **User Role Selection Popup**
- The system starts with a popup page asking whether the user is an **Admin** or a **Client**.
- ![image](https://github.com/user-attachments/assets/454cc092-f25f-49db-a154-d584093d2b22)

### 2. **Login & Signup Page**
- Both admins and clients can sign up and log in.
- Clients require admin approval before their account becomes active.
- **Admin Login & Signup:**
- ![image](https://github.com/user-attachments/assets/ad90fcea-5e79-40f3-9237-b89acde5f607)
- ![image](https://github.com/user-attachments/assets/f400c663-e81d-4313-a299-8795fd269de0)
- **Client Login & Signup:**
- ![image](https://github.com/user-attachments/assets/0da6bf97-0aa3-4017-a056-34bdb1de1787)
- ![image](https://github.com/user-attachments/assets/5288da24-761e-4f4a-ad85-2df5eb8c3be0)

### 3. **Client Dashboard**
- Clients can view:
  - Available books
  - Borrowed books
  - Returned books
- ![image](https://github.com/user-attachments/assets/9082639c-e75c-4945-a331-229a7352c09a)
- ![image](https://github.com/user-attachments/assets/158b6437-f394-498c-987d-90e90bc3eb57)

### 4. **Admin Dashboard**
- Admins can access:
  - Users list
  - Books list
  - Borrowed books list
  - Returned books list
  - Ability to export records as Excel files
- ![image](https://github.com/user-attachments/assets/9394c77a-6904-4421-b4d4-573496bbf514)
- ![image](https://github.com/user-attachments/assets/1a54fd02-407d-4225-a5ff-9ba7ea4ccf54)

### 5. **Borrow & Return Books**
- Admins issue books by entering:
  - **Book ID** & **User ID** → Generates a **Borrow ID**
- Clients return books using the **Borrow ID**, and the database updates automatically when the admin enters the borrow ID.
- ![image](https://github.com/user-attachments/assets/bb87c192-b3d0-4b50-b720-664579b41e60)
- ![image](https://github.com/user-attachments/assets/524c2e95-91e8-4624-a6bf-a55f75e63fbe)

### 6. **Add/Remove Books**
- Admins can:
  - Add new books
  - Increase stock of existing books
  - Remove books that are no longer required.
- ![image](https://github.com/user-attachments/assets/39255fd1-f619-4e7b-b772-6177b24f1737)
- ![image](https://github.com/user-attachments/assets/6050ab74-a8fe-4a51-9e7d-6c6a54070f69)

### 7. **Visitor Records**
- Normal visitors (without accounts) have their **entry time** recorded.
- Using their visitor ID, their **exit time** is recorded.
- ![image](https://github.com/user-attachments/assets/9f4d1192-be90-415e-a31d-06cdf80e7db6)

### 8. **Manage Users**
- Admins can:
  - Activate newly created client accounts
  - Deactivate active accounts
- ![image](https://github.com/user-attachments/assets/2b6b349d-0923-4794-bb86-35b8635ba5c5)

### 9. **Reports Page**
- Admins can view:
  - **Total Users**  
  - **Total Books**  
  - **Available Books**  
  - **Issued Books**  
  - **Recent Transactions Table**
  - ![image](https://github.com/user-attachments/assets/39411047-8409-4698-bf81-3d61469f7a6b)

## Installation
1. Clone the repository:
   ```sh
   git clone https://github.com/yourusername/lms-system.git
   ```
2. Install XAMPP and start Apache & MySQL.
3. Move the project folder to the XAMPP `htdocs` directory.
4. Create a database:
   ```sh
   # Example for MySQL
   CREATE DATABASE lms_db;
   ```
5. Import the SQL file provided in the project to set up tables.
6. Run the application by opening the project URL in the browser:
   ```sh
   http://localhost/lms-system/
   ```

## Technologies Used
- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Server:** XAMPP (Apache, MySQL, PHP)

## Contribution
1. Fork the repository
2. Create a new branch (`feature-branch`)
3. Commit changes
4. Open a Pull Request

## License
This project is licensed under the MIT License.

