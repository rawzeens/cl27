<?php
session_start();
require_once "db_connection.php";

$errors = [];

function isEmailUnique($email) {
    global $pdo;

    $sql = "SELECT COUNT(*) FROM Student WHERE Email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    return $count == 0;
}

function registerStudent($fullName, $email, $password, $phoneNumber, $gender, $faculty, $program, $religion, $imgPath) {
    global $pdo;

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO Student (FullName, Email, Password, PhoneNumber, Gender, FacultyId, ProgramId, ReligionId, img) 
            VALUES (:fullName, :email, :password, :phoneNumber, :gender, :faculty, :program, :religion, :img)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":fullName", $fullName, PDO::PARAM_STR);
    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
    $stmt->bindParam(":password", $hashedPassword, PDO::PARAM_STR);
    $stmt->bindParam(":phoneNumber", $phoneNumber, PDO::PARAM_STR);
    $stmt->bindParam(":gender", $gender, PDO::PARAM_STR);
    $stmt->bindParam(":faculty", $faculty, PDO::PARAM_INT);
    $stmt->bindParam(":program", $program, PDO::PARAM_INT);
    $stmt->bindParam(":religion", $religion, PDO::PARAM_INT);
    $stmt->bindParam(":img", $imgPath, PDO::PARAM_STR);
    $stmt->execute();
    $studentID = $pdo->lastInsertId();

    return $studentID;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["register"])) {
    $fullName = $_POST["fullName"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $phoneNumber = $_POST["phoneNumber"];
    $gender = $_POST["gender"];
    $faculty = $_POST["faculty"];
    $program = $_POST["program"];
    $religion = $_POST["religion"];
    $imgPath = "";

    if (empty($fullName) || empty($email) || empty($password) || empty($phoneNumber) || empty($gender) || empty($faculty) || empty($program) || empty($religion)) {
        $errors[] = "All fields are required.";
    } elseif (!isEmailUnique($email)) {
        $errors[] = "Email already exists. Please use a different email address.";
    } else {
        // Check if file is uploaded and no errors
        if (isset($_FILES["profilePicture"]) && $_FILES["profilePicture"]["error"] == UPLOAD_ERR_OK) {
            // Define upload directory
            $uploadDir = 'uploads/img/';
            
            // Generate a unique filename to avoid overwriting existing files
            $imgName = uniqid() . '_' . basename($_FILES["profilePicture"]["name"]);
            
            // Path to store the uploaded image
            $imgPath = $uploadDir . $imgName;
            
            // Move the uploaded file to the upload directory
            if (move_uploaded_file($_FILES["profilePicture"]["tmp_name"], $imgPath)) {
                // File moved successfully, now store the file path in the database
                $studentID = registerStudent($fullName, $email, $password, $phoneNumber, $gender, $faculty, $program, $religion, $imgPath);
                if ($studentID) {
                    $_SESSION['StudentID'] = $studentID;
                    header("Location: update-profile.php");
                    exit();
                } else {
                    $errors[] = "Registration failed.";
                }
            } else {
                $errors[] = "Error uploading file.";
            }
        } else {
            // No file uploaded or file upload error
            // Handle accordingly, you can make the image field optional if needed
            $errors[] = "Profile picture is required.";
        }
    }
}

function authenticateStudent($email, $password) {
    global $pdo;

    $sql = "SELECT StudentID, Password FROM Student WHERE Email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && password_verify($password, $row['Password'])) {
        return $row['StudentID'];
    }

    return false;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["login"])) {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $studentID = authenticateStudent($email, $password);

    if ($studentID !== false) {
        $_SESSION['student_id'] = $studentID;
        header("Location: update-profile.php");
        exit();
    } else {
        $errors[] = "Invalid email or password.";
    }
}

$sqlFaculties = "SELECT * FROM Faculty";
$stmtFaculties = $pdo->query($sqlFaculties);
$faculties = $stmtFaculties->fetchAll(PDO::FETCH_ASSOC);

$sqlPrograms = "SELECT * FROM Program";
$stmtPrograms = $pdo->query($sqlPrograms);
$programs = $stmtPrograms->fetchAll(PDO::FETCH_ASSOC);

$sqlReligions = "SELECT * FROM Religion";
$stmtReligions = $pdo->query($sqlReligions);
$religions = $stmtReligions->fetchAll(PDO::FETCH_ASSOC);

$showForm = (count($faculties) > 0 || count($programs) > 0 || count($religions) > 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration and Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 py-6 flex justify-center items-center">
<div class="w-full max-w-md bg-white p-8 rounded shadow-md">
    <h2 class="text-2xl font-semibold mb-4">Student Registration and Login</h2>

    <?php if (!empty($errors)) { ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
            <?php foreach ($errors as $error) { echo $error . "<br>"; } ?>
        </div>
    <?php } ?>

    <div id="signup-form" class="hidden">
        <h2 class="text-xl font-semibold mb-4">Sign Up</h2>
        <form action="" method="post" enctype="multipart/form-data" class="space-y-6">
            <div>
                <label class="block">Full Name</label>
                <input type="text" name="fullName" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block">Email</label>
                <input type="email" name="email" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block">Password</label>
                <input type="password" name="password" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block">Phone Number</label>
                <input type="text" name="phoneNumber" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block">Gender</label>
                <select name="gender" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
            <div>
                <label class="block">Faculty</label>
                <select name="faculty" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
                    <option value="">Select Faculty</option>
                    <?php foreach ($faculties as $faculty) { ?>
                        <option value="<?php echo $faculty['FacultyId']; ?>"><?php echo $faculty['FacultyName']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div>
                <label class="block">Program</label>
                <select name="program" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
                    <option value="">Select Program</option>
                    <?php foreach ($programs as $program) { ?>
                        <option value="<?php echo $program['ProgramId']; ?>"><?php echo $program['ProgramName']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div>
                <label class="block">Religion</label>
                <select name="religion" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
                    <option value="">Select Religion</option>
                    <?php foreach ($religions as $religion) { ?>
                        <option value="<?php echo $religion['ReligionId']; ?>"><?php echo $religion['ReligionName']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div>
                <label class="block">Profile Picture</label>
                <input type="file" name="profilePicture" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <input type="submit" name="register" value="Register" class="w-full bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
            </div>
        </form>
        <p class="mt-4">Already have an account? <a href="#" id="show-login" class="text-blue-500">Login here</a></p>
    </div>

    <div id="login-form">
        <h2 class="text-xl font-semibold mb-4">Login</h2>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-4">
            <div>
                <label class="block">Email</label>
                <input type="email" name="email" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block">Password</label>
                <input type="password" name="password" class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <input type="submit" name="login" value="Login" class="w-full bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
            </div>
        </form>
        <p class="mt-4">Don't have an account? <a href="#" id="show-signup" class="text-blue-500">Sign up here</a></p>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const loginForm = document.getElementById('login-form');
        const signupForm = document.getElementById('signup-form');
        const showLogin = document.getElementById('show-login');
        const showSignup = document.getElementById('show-signup');

        showSignup.addEventListener('click', function(event) {
            event.preventDefault();
            signupForm.classList.remove('hidden');
            loginForm.classList.add('hidden');
        });

        showLogin.addEventListener('click', function(event) {
            event.preventDefault();
            loginForm.classList.remove('hidden');
            signupForm.classList.add('hidden');
        });
    });
</script>
</body>
</html>
