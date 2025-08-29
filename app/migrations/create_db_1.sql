-- Creem baza de date
CREATE DATABASE spital_php CHARACTER SET=utf8mb4;

-- Creem userul si ii dam privilegii
-- Creem userul "robi"@127.0.0.1 identified by 'robi'
CREATE USER 'robi'@'127.0.0.1' IDENTIFIED BY 'robi';
CREATE USER 'robi'@'localhost' IDENTIFIED BY 'robi';

GRANT ALL ON phpspital.* to 'robi'@'127.0.0.1';
GRANT ALL ON phpspital.* to 'robi'@'localhost';

-- daca rulam comenzile din phpmyadmin comenteaza urmatoarea linie
USE spital_php

-- creem tabelele
CREATE TABLE user_roles(
    id INTEGER AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(128) UNIQUE
) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE users(
    id INTEGER AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(128),
    last_name VARCHAR(128),
    email VARCHAR(128),
    password VARCHAR(128),
    role_id INTEGER NOT NULL,
    send_notification BOOLEAN DEFAULT FALSE,
    FOREIGN KEY(role_id) REFERENCES user_roles(id) ON DELETE RESTRICT
)ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE patients(
    user_id INT PRIMARY KEY,
    cnp VARCHAR(13) UNIQUE,
    phone VARCHAR(32),
    address TEXT,
    blood_type VARCHAR(5),
    allergies TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE doctors(
    user_id INT AUTO_INCREMENT,
    specialization VARCHAR(128),
    department VARCHAR(128),
    grade VARCHAR(64),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE appointments(
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    doctor_id INT NOT NULL,
    status BOOLEAN,
    date DATETIME,
    FOREIGN KEY(patient_id) REFERENCES patients(id),
    FOREIGN KEY(doctor_id) REFERENCES doctors(id)
)ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE medical_record(
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT,
    doctor_id INT,
    initial_observations TEXT
) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE consultations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    medical_record_id INT NOT NULL,
    doctor_id INT NOT NULL,
    consultation_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    notes text,
    diagnosis TEXT.
    FOREIGN KEY (medical_record_id) REFERENCES medical_record(id) ON DELETE CASCADE,
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) ON DELETE RESTRICT
)ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE prescriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    patient_id INT NOT NULL,
    prescription text
    FOREIGN KEY (doctor_id) REFERENCES doctors(id) 
    FOREIGN KEY (patient_id) REFERENCES patients(id)
)ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE departments(
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(128) NOT NULL UNIQUE,
    description text
)ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE rooms(
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_id, int not null,
    room_number VARCHAR(32) not null,
    capacity int DEFAULT 4,
    description text,
    FOREIGN KEY (department_id) REFERENCES departments(id) on DELETE CASCADE
)ENGINE=InnoDB CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE admissions(
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    room_id INT NOT NULL,
    admission_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    discharge_date DATETIME NULL,
    reason text,
    FOREIGN KEY (patient_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE RESTRICT
)
CREATE TABLE IF NOT EXISTS admissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  patient_id INT NOT NULL,
  room_id INT NOT NULL,
  admission_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  discharge_date DATETIME NULL,
  reason TEXT NULL,
  CONSTRAINT fk_adm_patient FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
  CONSTRAINT fk_adm_room    FOREIGN KEY (room_id)    REFERENCES rooms(id)    ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS medications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    source VARCHAR(100),
    imported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_source (source)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

insert into user_roles (name) values('admin');
insert into user_roles (name) values('pacient');
insert into user_roles (name) values('guest');
insert into user_roles (name) values('doctor');
insert into user_roles (name) values('receptionist');

INSERT INTO departments (name, description) VALUES
('Cardiologie', 'Departament pentru afecțiuni cardiace'),
('Neurologie', 'Departament pentru boli ale sistemului nervos'),
('Chirurgie', 'Departament chirurgical general'),
('Pediatrie', 'Departament pentru copii'),
('Radiologie', 'Departament imagistică și radiologie'),
('Oncologie', 'Departament pentru boli oncologice');