<?php
require_once "auth.php";
require_once "db_connection.php";


$student_id = $_SESSION['student_id'];

$sql = "SELECT 
            s.*,
            p.ProgramName
        FROM 
            Student s
        JOIN 
            Program p ON s.ProgramId = p.ProgramId
        WHERE 
            s.StudentID = :student_id";

$stmt = $pdo->prepare($sql);
$stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
$stmt->execute();
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    echo "Student not found.";
    exit();
}

// Update profile
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'];
    $matric_number = $_POST['matric_number'];
    $program_id = $_POST['program_id'];

    $update_sql = "UPDATE Student SET FullName = :full_name, MatricNumber = :matric_number, ProgramId = :program_id WHERE StudentID = :student_id";
    $update_stmt = $pdo->prepare($update_sql);
    $update_stmt->bindParam(':full_name', $full_name, PDO::PARAM_STR);
    $update_stmt->bindParam(':matric_number', $matric_number, PDO::PARAM_STR);
    $update_stmt->bindParam(':program_id', $program_id, PDO::PARAM_INT);
    $update_stmt->bindParam(':student_id', $student_id, PDO::PARAM_INT);
    $update_stmt->execute();

    // Reload the updated data
    header("Location: update-profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    
<?php include "include/student-nav.php"; ?>
    
<div class="container pt-10 h-screen flex justify-center items-center">
<div class="bg-white p-8 rounded shadow-md w-full text-center">
    <div class="mb-4">
    <?php if (!empty($student['img'])) { ?>
        <img src="<?php echo htmlspecialchars($student['img']); ?>" alt="Student Photo" class="w-32 h-32 rounded-full mx-auto">
    <?php } else { ?>
        <div class="w-32 h-32 rounded-full mx-auto bg-gray-300 flex justify-center items-center text-gray-500">
            No photo available
        </div>
    <?php } ?>
</div>

        <h2 class="text-2xl font-bold mb-4"><?php echo htmlspecialchars($student['FullName']); ?></h2>
        <p class="text-gray-700 mb-2"><strong>Matric Number:</strong> <?php echo htmlspecialchars($student['MatricNumber']); ?></p>
        <p class="text-gray-700 mb-2"><strong>Program:</strong> <?php echo htmlspecialchars($student['ProgramName']); ?></p>
        <button class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 focus:outline-none focus:bg-blue-600" onclick="openModal()">
            Update Profile
        </button>

    </div>

    <!-- Modal -->
    <div id="modal" class="fixed flex inset-0 bg-gray-600 bg-opacity-50 hidden justify-center items-center">
        <div class="bg-white p-8 rounded shadow-md max-w-md w-full">
            <h2 class="text-2xl font-bold mb-4">Edit Profile</h2>
            <form action="" method="post">
                <div class="mb-4">
                    <label class="block text-gray-700">Full Name:</label>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($student['FullName']); ?>" required class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-indigo-500">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Matric Number:</label>
                    <input type="text" name="matric_number" value="<?php echo htmlspecialchars($student['MatricNumber']); ?>" required class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-indigo-500">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Program:</label>
                    <select name="program_id" required class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:border-indigo-500">
                        <?php
                        $program_sql = "SELECT ProgramId, ProgramName FROM Program";
                        $program_stmt = $pdo->query($program_sql);
                        while ($program = $program_stmt->fetch(PDO::FETCH_ASSOC)) {
                            $selected = $student['ProgramId'] == $program['ProgramId'] ? 'selected' : '';
                            echo "<option value='{$program['ProgramId']}' $selected>{$program['ProgramName']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <input type="submit" name="update_profile" value="Update" class="w-full bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 focus:outline-none focus:bg-blue-600">
                </div>
            </form>
            <button class="w-full bg-red-500 text-white px-4 py-2 rounded-md mt-4 hover:bg-red-600 focus:outline-none focus:bg-red-600" onclick="closeModal()">
                Cancel
            </button>
        </div>
    </div>
    </div>
    <script>
        function openModal() {
            document.getElementById('modal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('modal').classList.add('hidden');
        }
    </script>
</body>
</html>
