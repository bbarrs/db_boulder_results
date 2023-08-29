<?php
session_start();
?>
<?php
// incorporate header file
include("z_header.php");
?>
<?php
// database connection
$servername = "boulder-results.cwytbia1ujp1.us-east-1.rds.amazonaws.com";
$username = "admin";
$password = "AdamGoldstein";
$database = 'boulder_results';

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//query 1
$query = "WITH best_climbers AS (
    SELECT c.climber_id, AVG(e.climber_rank) AS avg_rank
    FROM comp_entry e
    JOIN climber c ON e.climber_id = c.climber_id
    GROUP BY c.climber_id
    HAVING avg_rank <= 0.4*(SELECT AVG(climber_rank) FROM comp_entry) # arbitrary threshold
)
SELECT c.comp_name, SUM(r.tops) AS total_tops,SUM(r.top_attempts) AS total_top_attempts, SUM(r.tops) / SUM(r.top_attempts) AS total_top_success_rate,
SUM(CASE WHEN bc.avg_rank IS NOT NULL THEN r.tops ELSE 0 END) AS elite_tops,
SUM(CASE WHEN bc.avg_rank IS NOT NULL THEN r.top_attempts ELSE 0 END) AS elite_top_attempts,
SUM(CASE WHEN bc.avg_rank IS NOT NULL THEN r.tops ELSE 0 END) / SUM(CASE WHEN bc.avg_rank IS NOT NULL THEN r.top_attempts ELSE 0 END) AS elite_top_success_rate
FROM results r
JOIN comp_entry e ON r.entry_id = e.entry_id
JOIN competition c ON e.comp_id = c.comp_id
LEFT JOIN best_climbers bc ON e.climber_id = bc.climber_id
GROUP BY e.comp_id
ORDER BY total_top_success_rate DESC;";

// execute query
$result = $conn->query($query);
if ($result) {
    $arr = $result->fetch_all(MYSQLI_ASSOC);
} else {
    echo "Error: " . $conn->error;
}
?>

<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=yes">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.11.6/js/jquery.dataTables.min.js"></script>

    <style>
        .table-container {
            max-height: 400px; /* adjust height as needed */
            overflow: auto;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div align="center" class="container mt-5">
        <h4 align="center">IFSC Bouldering: Competition Difficulty</h4>
        <h6 align="center">What competitions have the most successful tops? How does the difficulty of the competition affect the success of highly-ranked climbers?
        </h6><br>

        <br>
        <style type="text/css">
            @media screen and (max-width: 767px) {
                .tg {
                    width: auto !important;
                }

                .tg col {
                    width: auto !important;
                }

                .tg-wrap {
                    overflow-x: auto;
                    -webkit-overflow-scrolling: touch;
                    margin: auto 0px;
                }
            }
        </style>

        <!-- Search Results Table Display -->
        <div class="tg-wrap">
            <table id="table" class="display" cellspacing="0" style="width:100%">
                <thead style="font: bold; active" align="center">
                    <tr>
                        <td align=center>Index</td>
                        <td align=center>Competition Name</td>
                        <td align=center>Total Tops</td>
                        <td align=center>Total Top Attempts</td>
                        <td align=center>Total Top Success Rate</td>
                        <td align=center>Elite Tops</td>
                        <td align=center>Elite Top Attempts</td>
                        <td align=center>Elite Top Success Rate</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($arr as $index => $unit) {
                        echo '<tr>';
                        echo '<td align= center>' . ($index + 1) . '</td>';
                        echo '<td align= center>' . $unit['comp_name'] . '</td>';
                        echo '<td align= center>' . $unit['total_tops'] . '</td>';
                        echo '<td align= center>' . $unit['total_top_attempts'] . '</td>';
                        echo '<td align= center>' . $unit['total_top_success_rate'] . '</td>';
                        echo '<td align= center>' . $unit['elite_tops'] . '</td>';
                        echo '<td align= center>' . $unit['elite_top_attempts'] . '</td>';
                        echo '<td align= center>' . $unit['elite_top_success_rate'] . '</td>';
                        echo '</tr>';
                        echo '<tr align= center>';
                        echo '</tr>';
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
        <br>
        <br><br>
        <script type="text/javascript">
            $(document).ready(function () {
                $('#table').dataTable({
                    searching: false,
                    lengthChange: false,
                    paging: false
                });
            });
        </script>
</body>

</html>