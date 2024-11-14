<?php
require_once "auth.php";
require_once "db_connection.php";

function getCertificates($studentID, $selectedYear) {
    global $pdo;

    $sql = "SELECT c.EventName, a.Name AS CertActivity, SUM(a.Mark) AS TotalMark
            FROM Certificate c
            INNER JOIN CertActivity a ON c.CertId = a.CertId
            WHERE YEAR(c.Date) = :selectedYear AND c.Status = 'Approved' AND c.StudentID = :studentID
            GROUP BY c.EventName, a.Name";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":selectedYear", $selectedYear, PDO::PARAM_INT);
    $stmt->bindParam(":studentID", $studentID, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get the student ID from the session
$studentID = $_SESSION["student_id"]; 

// Get the selected year from the GET request or default to the current year
$selectedYear = isset($_GET['year']) ? $_GET['year'] : date("Y");

// Fetch certificates for the logged-in student for the selected year
$certificates = getCertificates($studentID, $selectedYear);
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
<?php include "include/student-nav.php"; ?>
<div class="pt-12">
    <div class="container mx-auto max-w-4xl py-8 px-4">
        <h2 class="text-2xl font-bold mb-4">Student Transcript</h2>
        <div class="flex flex-col md:flex-row">
            <div class="w-full md:w-1/2 pr-4">
                <form method="get" class="mb-8 flex items-center">
                    <label for="year" class="mr-4">Select Year:</label>
                    <select name="year" id="year" class="border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <?php 
                        for ($year = date("Y"); $year >= 2000; $year--) {
                            echo "<option value='$year'" . ($selectedYear == $year ? " selected" : "") . ">$year</option>";
                        }
                        ?>
                    </select>
                    <input type="submit" value="Submit" class="ml-4 bg-indigo-500 text-white py-2 px-4 rounded-md hover:bg-indigo-600 focus:outline-none focus:bg-indigo-600">
                    <input type="button" value="Print Transcript" class="ml-4 bg-indigo-500 text-white py-2 px-4 rounded-md hover:bg-indigo-600 focus:outline-none focus:bg-indigo-600" onclick="printTranscript()">
                </form>
                <table class="w-full border border-gray-300">
                    <thead class="bg-indigo-300">
                        <tr>
                            <th class="border border-gray-300 px-4 py-2">Event Name</th>
                            <th class="border border-gray-300 px-4 py-2">Activity Name</th>
                            <th class="border border-gray-300 px-4 py-2">Total Mark</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        foreach ($certificates as $certificate) :
                    ?>
                        <tr>
                            <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($certificate['EventName']); ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($certificate['CertActivity']); ?></td>
                            <td class="border border-gray-300 px-4 py-2"><?php echo htmlspecialchars($certificate['TotalMark']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                        <tr class="bg-indigo-200">
                            <td colspan="2" class="border border-gray-300 px-4 py-2">Total:</td>
                            <td class="border border-gray-300 px-4 py-2">
                                <?php 
                                $totalMark = array_sum(array_column($certificates, 'TotalMark'));
                                echo $totalMark;
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="w-full md:w-1/2 pl-4">
                <canvas id="pieChart" class="mb-8" width="400" height="400"></canvas>
                <div id="legend" class="mt-4"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
<script>
    function printTranscript() {
        let selectedYear = document.getElementById('year').value;
        var printWindow = window.open('print.php?year=' + selectedYear , '_blank');
        printWindow.onload = function() {
            printWindow.print();
        };
    }

    let pieData = <?php echo json_encode($certificates); ?>;
    let activityCounts = {};

    pieData.forEach(certificate => {
        let activity = certificate['CertActivity'];
        activityCounts[activity] = (activityCounts[activity] || 0) + parseInt(certificate['TotalMark']);
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
            maintainAspectRatio: false
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
