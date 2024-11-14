CREATE TABLE Student (
    StudentID INT PRIMARY KEY AUTO_INCREMENT,
    FullName VARCHAR(255),
    MatricNumber VARCHAR(50),
    ICOrPassportNo VARCHAR(50),
    PhoneNumber VARCHAR(20),
    Address VARCHAR(255),
    DateOfBirth DATE,
    Email VARCHAR(255),
    img VARCHAR(255),
    Password VARCHAR(255)
);



CREATE TABLE Program (
    ProgramId INT PRIMARY KEY AUTO_INCREMENT,
    ProgramName VARCHAR(100) NOT NULL
);


CREATE TABLE TypeActivity (
    ActivityID INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(255),
    Date DATE
);


CREATE TABLE Transcript (
    TranscriptID INT PRIMARY KEY AUTO_INCREMENT,
    StudentID INT,
    ActivityID INT,
    CertificateID INT,
    MeritPoints INT,
    TotalMark INT, 
    Date DATE 
);

CREATE TABLE CertActivity (
    CertId INT PRIMARY KEY AUTO_INCREMENT,
    Name VARCHAR(255),
    Mark INT,
    Date DATE
);
CREATE TABLE Certificate (
    CertificateID INT PRIMARY KEY AUTO_INCREMENT,
    StudentID INT,
    CertId INT,
    EventName VARCHAR(255),
    Place VARCHAR(255),
    Date DATE,
    Duration INT,
    Level VARCHAR(50),
    Achievement VARCHAR(255),
    Award VARCHAR(255),
    FilePath VARCHAR(255),
    Status ENUM('Pending', 'Approved', 'Declined') DEFAULT 'Pending',
    UploadDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



CREATE TABLE Application (
    ApplicationID INT PRIMARY KEY,
    ActivityID INT,
    Status VARCHAR(50)
);

CREATE TABLE Administrator (
    AdminID INT PRIMARY KEY,
    Email VARCHAR(255),
    Password VARCHAR(255)

);

INSERT INTO Administrator (AdminID, Email, Password) VALUES
(1, 'admin@example.com', 'admin123'),
(2, 'superadmin@example.com', 'superadmin456'),
(3, 'root@example.com', 'root789');

 
INSERT INTO Religion (ReligionName) VALUES
('Christianity'),
('Islam'),
('Buddhism'),
('Hinduism'),
('Judaism'),
('Sikhism');

INSERT INTO Faculty (FacultyName) VALUES
('Faculty of Engineering'),
('Faculty of Medicine'),
('Faculty of Arts'),
('Faculty of Science'),
('Faculty of Business'),
('Faculty of Law');

INSERT INTO Program (ProgramName) VALUES
('Computer Science'),
('Mechanical Engineering'),
('Medicine'),
('Economics'),
('Psychology'),
('Law');


INSERT INTO CertActivity (Name, Mark, Date) VALUES
('STEM', 85, '2023-08-10'),
('Debate', 78, '2023-07-15'),
('STEM', 92, '2023-09-20'),
('Community Service', 95, '2023-06-05'),
('Leadership', 88, '2023-10-30'),
('Sports', 70, '2023-11-12');
