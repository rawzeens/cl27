<?php
require_once "auth.php";
require_once "db_connection.php";

$sql_student = "SELECT s.*, f.FacultyName, p.ProgramName, r.ReligionName
                FROM Student s
                LEFT JOIN Faculty f ON s.FacultyId = f.FacultyId
                LEFT JOIN Program p ON s.ProgramId = p.ProgramId
                LEFT JOIN Religion r ON s.ReligionId = r.ReligionId
                WHERE s.StudentID = :studentID";
$stmt_student = $pdo->prepare($sql_student);
$stmt_student->bindParam(":studentID", $_SESSION['student_id'], PDO::PARAM_INT);
$stmt_student->execute();
$student = $stmt_student->fetch(PDO::FETCH_ASSOC);
$selectedYear = isset($_GET['year']) ? $_GET['year'] : date("Y");

$sql = "SELECT c.EventName, a.Name AS CertActivity, SUM(a.Mark) AS TotalMark
        FROM Certificate c
        INNER JOIN CertActivity a ON c.CertId = a.CertId
        WHERE YEAR(c.Date) = :selectedYear AND c.Status = 'Approved'
        GROUP BY c.EventName, a.Name";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":selectedYear", $selectedYear, PDO::PARAM_INT);
$stmt->execute();
$certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Transcript</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<div class="pt-12">
    <div class="container mx-auto w-full flex flex-wrap justify-center">
        <!-- Student Credentials -->
        <div class="w-full md:w-1/2 px-4 mb-4">
            <div class="rounded px-8 pt-6 pb-8 mb-4">
                <h2 class="text-2xl font-bold mb-4">Student Transcript</h2>
                <h1 class="text-xl font-semibold mb-4">Transcript for <?php echo $selectedYear; ?></h1>
                <div class="mb-4">
                    <span class="font-semibold">Student Name:</span> <?php echo $student['FullName']; ?>
                </div>
                <div class="mb-4">
                    <span class="font-semibold">Faculty:</span> <?php echo $student['FacultyName']; ?>
                </div>
                <div class="mb-4">
                    <span class="font-semibold">Program:</span> <?php echo $student['ProgramName']; ?>
                </div>
                <div class="mb-4">
                    <span class="font-semibold">Religion:</span> <?php echo $student['ReligionName']; ?>
                </div>
            </div>
        </div>
        <!-- Pie Chart -->
        <div class="w-full md:w-1/2 px-4 mb-4">
            <div class="flex justify-end rounded px-8 pt-6 pb-8 mb-4">
                <canvas id="pieChart" class="mb-8" width="200"></canvas>
                <div id="legend" class="mt-4"></div>
            </div>
        </div>
        <!-- Table -->
        <div class="w-full px-4 mb-4">
            <div class="rounded px-8 pt-6 pb-8">
                <table class="w-full border border-gray-300">
                    <thead class="bg-indigo-300">
                        <tr>
                            <th class="border border-gray-300 px-4 py-2">Name</th>
                            <th class="border border-gray-300 px-4 py-2">Mark</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($certificates as $certificate) : ?>
                            <tr>
                                <td class="border border-gray-300 px-4 py-2"><?php echo $certificate['EventName']; ?></td>
                                <td class="border border-gray-300 px-4 py-2"><?php echo $certificate['TotalMark']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="bg-indigo-200">
                            <td class="border border-gray-300 px-4 py-2 font-semibold">Total:</td>
                            <td class="border border-gray-300 px-4 py-2"><?php echo array_sum(array_column($certificates, 'TotalMark')); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
<script>
    let pieData = <?php echo json_encode($certificates); ?>;
    let activityCounts = {};

    pieData.forEach(certificate => {
        let activity = certificate['CertActivity'];
        activityCounts[activity] = (activityCounts[activity] || 0) + certificate['TotalMark'];
    });

    let totalCount = Object.values(activityCounts).reduce((a, b) => a + b, 0);

    let labels = [];
    let data = [];
    let percentages = [];

    Object.entries(activityCounts).forEach(([activity, count]) => {
        labels.push(activity);
        data.push(count);
        percentages.push(((count / totalCount) * 100).toFixed(2));
    });

    let ctx = document.getElementById('pieChart').getContext('2d');
    let pieChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Activity Count',
                data: data,
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: false,
            maintainAspectRatio: false,
            animation: false
        }
    });

    let legendHTML = '<ul>';
    labels.forEach((label, index) => {
        legendHTML += `<li><span style="background-color: ${pieChart.data.datasets[0].backgroundColor[index]}"></span>${label}</li>`;
    });
    legendHTML += '</ul>';
    document.getElementById('legend').innerHTML = legendHTML;
</script>
</body>
</html>
