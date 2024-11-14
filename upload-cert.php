<?php
require_once "auth.php";
require_once "db_connection.php";

function uploadCertificate($studentID, $certId, $eventname, $place, $date, $duration, $level, $achievement, $award, $fileType, $fileTmpName, $fileOriginalName) {
    global $pdo;


    $uploadDirectory1 = "admin/uploads/";
    $uploadDirectory = "uploads/";
    $fileName = uniqid() . "_" . basename($fileOriginalName);
    
    if (move_uploaded_file($fileTmpName, $uploadDirectory1 . $fileName)) {
        $filePath = $uploadDirectory . $fileName; // This line ensures the correct file path
    
        $sql = "INSERT INTO Certificate (StudentID, CertId, EventName, Place, Date, Duration, Level, Achievement, Award, FilePath, Status)
                VALUES (:studentID, :certId, :eventname, :place, :date, :duration, :level, :achievement, :award, :filePath, 'Pending')";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":studentID", $studentID, PDO::PARAM_INT);
        $stmt->bindValue(":certId", $certId, PDO::PARAM_INT);
        $stmt->bindValue(":eventname", $eventname, PDO::PARAM_STR);
        $stmt->bindValue(":place", $place, PDO::PARAM_STR);
        $stmt->bindValue(":date", $date, PDO::PARAM_STR);
        $stmt->bindValue(":duration", $duration, PDO::PARAM_INT);
        $stmt->bindValue(":level", $level, PDO::PARAM_STR);
        $stmt->bindValue(":achievement", $achievement, PDO::PARAM_STR);
        $stmt->bindValue(":award", $award, PDO::PARAM_STR);
        $stmt->bindValue(":filePath", $filePath, PDO::PARAM_STR); // Use $filePath here
    
        return $stmt->execute();
    } else {
        return false;
    }
    
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["upload"])) {
    $studentID = $_SESSION["student_id"]; 
    $certId = $_POST["certId"];
    $place = $_POST["place"];
    $eventname = $_POST["eventname"];
    $date = $_POST["date"];
    $duration = $_POST["duration"];
    $level = $_POST["level"];
    $achievement = $_POST["achievement"];
    $award = $_POST["award"];
    $fileType = $_FILES["file"]["type"];
    $fileTmpName = $_FILES["file"]["tmp_name"];
    $fileOriginalName = $_FILES["file"]["name"];

    if (uploadCertificate($studentID, $certId, $eventname, $place, $date, $duration, $level, $achievement, $award, $fileType, $fileTmpName, $fileOriginalName)) {
        $successMessage = "Certificate uploaded successfully.";
    } else {
        $errorMessage = "Certificate upload failed.";
    }
}

function getCertificateTypes() {
    global $pdo;
    $sql = "SELECT CertId, Name FROM CertActivity";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$certificateTypes = getCertificateTypes();

if (empty($certificateTypes)) {
    echo "<h1>Under Maintenance</h1><p>This page is currently under maintenance. Please try again later.</p>";
    exit; 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Certificate</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<?php
include "include/student-nav.php";
?>
<div class="pt-10 ">
<section class="container mx-auto grid grid-cols-5 gap-6 px-6 py-12">
    <div class="col-span-2">
        <div class="bg-white shadow-md rounded px-6 py-4">
            <h2 class="text-2xl font-bold px-6 text-left mb-6">Upload Certificate</h2>
            <?php if (isset($successMessage)): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <p><?php echo htmlspecialchars($successMessage); ?></p>
                </div>
            <?php endif; ?>
            <?php if (isset($errorMessage)): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                    <p><?php echo htmlspecialchars($errorMessage); ?></p>
                </div>
            <?php endif; ?>
            <form class="bg-white rounded-xl p-8" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <div class="block mb-4">
                    <label>Certificate Type</label>
                    <select class="border rounded w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" name="certId">
                        <?php foreach ($certificateTypes as $type) : ?>
                            <option value="<?php echo $type['CertId']; ?>"><?php echo htmlspecialchars($type['Name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="block mb-4">
                    <label>Event Name</label>
                    <input class="appearance-none border rounded w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" type="text" name="eventname" required>
                </div>
                <div class="block mb-4">
                    <label>Place</label>
                    <input class="appearance-none border rounded w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" type="text" name="place" required>
                </div>
                <div class="block mb-4">
                    <label>Date</label>
                    <input class="appearance-none border rounded w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" type="date" name="date" required>
                </div>
                <div class="block mb-4">
                    <label>Duration</label>
                    <input class="appearance-none border rounded w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" type="text" name="duration" required>
                </div>
                <div class="block mb-4">
                    <label>Level</label>
                    <input class="appearance-none border rounded w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" type="text" name="level" required>
                </div>
                <div class="block mb-4">
                    <label>Achievement</label>
                    <input class="appearance-none border rounded w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" type="text" name="achievement" required>
                </div>
                <div class="block mb-4">
                    <label>Award</label>
                    <input class="appearance-none border rounded w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" type="text" name="award" required>
                </div>
                <div class="block mb-4">
                    <label>Upload Certificate (PDF or Image)</label>
                    <input class="appearance-none border rounded w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" type="file" name="file" required>
                </div>
                <div class="block mb-4">
                    <input type="submit" class="bg-blue-500 w-full hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" name="upload" value="Upload">
                </div>
            </form>
        </div>
    </div>

    <div class="col-span-3">
        <div class="bg-white shadow-md px-6 py-4">
            <h2 class="text-2xl px-6 font-bold text-left mb-6">View Certificate Status</h2>
            <?php
                $studentID = $_SESSION["student_id"]; 
                $sql = "SELECT CertificateID, Place, Date, Status FROM Certificate WHERE StudentID = :studentID";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(":studentID", $studentID, PDO::PARAM_INT);
                $stmt->execute();
                $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($certificates) {
            ?>
            <div class="w-full px-6">
                <table class="w-full table-auto rounded-xl">
                    <thead>
                        <tr>
                            <th class="border px-4 py-2">Certificate ID</th>
                            <th class="border px-4 py-2">Place</th>
                            <th class="border px-4 py-2">Date</th>
                            <th class="border px-4 py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($certificates as $certificate) : ?>
                            <tr>
                                <td class="border px-4 py-2"><?php echo htmlspecialchars($certificate['CertificateID']); ?></td>
                                <td class="border px-4 py-2"><?php echo htmlspecialchars($certificate['Place']); ?></td>
                                <td class="border px-4 py-2"><?php echo htmlspecialchars($certificate['Date']); ?></td>
                                <td class="border px-4 py-2"><?php echo htmlspecialchars($certificate['Status']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php
                } else {
                    echo "<p>No certificate data available.</p>";
                }
            ?>
        </div>
    </div>
</section>
</div>
</body>
</html>
