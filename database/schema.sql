-- MediRecord: Hospital / Patient Record System (XAMPP / phpMyAdmin)
-- Import this file from phpMyAdmin > Import. It creates the database and tables.

CREATE DATABASE IF NOT EXISTS hospital_db CHARACTER SET utf8mb4;
USE hospital_db;

-- Hospital staff accounts
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','staff') NOT NULL DEFAULT 'staff',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Patients
CREATE TABLE patients (
  id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(50) NOT NULL,
  last_name VARCHAR(50) NOT NULL,
  birth_date DATE NOT NULL,
  gender VARCHAR(10) NOT NULL,
  blood_type VARCHAR(10) NOT NULL DEFAULT 'Unknown',
  phone VARCHAR(20) NOT NULL,
  emergency_contact VARCHAR(100) NOT NULL,
  address VARCHAR(150) NOT NULL,
  allergies VARCHAR(200) NOT NULL DEFAULT '',
  status ENUM('outpatient','admitted','discharged') NOT NULL DEFAULT 'outpatient',
  created_by INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Medical records (one patient has many records)
CREATE TABLE records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  user_id INT NULL,
  visit_date DATE NOT NULL,
  diagnosis VARCHAR(150) NOT NULL,
  treatment TEXT NOT NULL,
  notes TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Test accounts:  admin@test.com / Admin@123   and   staff@test.com / Staff@123
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@test.com', '$2y$10$X5kPW7fZ3MK/kmqwv11OZe19hCqJ/R8RRLcl44GgrljF9ZXsIOnSu', 'admin'),
('Dr. Staff', 'staff@test.com', '$2y$10$0EHO58yowCXBA9Gb0APA.eldkBDOFWTf6OtlTulY7zBI8bOJTaoMi', 'staff');

-- Sample (fictional) patients and records
INSERT INTO patients (first_name, last_name, birth_date, gender, blood_type, phone, emergency_contact, address, allergies, status, created_by) VALUES
('Grechelle', 'Algas', '1990-04-12', 'Female', 'O+', '0917-555-0101', 'Majie Algas - 0917-555-0102', '12 Rizal St., Laoag City', 'Penicillin', 'admitted', 1),
('Lex', 'Pogi', '1975-09-30', 'Male', 'A+', '0917-555-0103', 'Ana Dela Cruz - 0917-555-0104', '45 Mabini Ave., Batac', '', 'outpatient', 2),
('Erickson', 'the Great', '2015-01-20', 'Female', 'B+', '0917-555-0105', 'Pedro Reyes - 0917-555-0106', '8 Luna St., Paoay', 'Peanuts, Dust', 'outpatient', 2),
('Mark', 'Mejia', '1958-11-05', 'Male', 'AB-', '0917-555-0107', 'Luz Mejia - 0917-555-0108', '77 Quezon Blvd., Laoag City', 'None known', 'discharged', 1);

INSERT INTO records (patient_id, user_id, visit_date, diagnosis, treatment, notes) VALUES
(1, 2, CURDATE(), 'Acute appendicitis', 'Appendectomy performed. IV antibiotics (non-penicillin).', 'Stable after surgery. Monitor for 48 hours.'),
(2, 2, CURDATE(), 'Hypertension', 'Prescribed daily medication and low-salt diet.', 'Follow-up check in 2 weeks.'),
(3, 1, CURDATE(), 'Seasonal allergic rhinitis', 'Antihistamine for 7 days.', 'Avoid dust exposure.'),
(4, 1, '2026-09-01', 'Pneumonia', 'Completed antibiotic course. Chest X-ray clear.', 'Discharged in good condition.');
