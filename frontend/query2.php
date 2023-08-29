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

//query
$query2 = "SELECT e.start_number, COUNT(e.climber_rank) AS entries, ROUND(AVG(e.climber_rank), 2) AS avg_rank, MIN(e.climber_rank) AS min_rank, MAX(e.climber_rank) AS max_rank
FROM comp_entry e
GROUP BY e.start_number
ORDER BY e.start_number;";

// execute query
$result2 = $conn->query($query2);
if ($result2) {
    $arr2 = $result2->fetch_all(MYSQLI_ASSOC);
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
        <h4 align="center">IFSC Bouldering: Start Number Success</h4>
        <h6 align="center">What is the correlation between start number and success in the competition?
        </h6><br>

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
            <table id="table2" class="display" cellspacing="0" style="width:100%">
                <thead style="font: bold; active" align="center">
                    <tr>
                        <td align=center>Start Number</td>
                        <td align=center># of Entries</td>
                        <td align=center>Average Rank</td>
                        <td align=center>Min Rank</td>
                        <td align=center>Max Rank</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($arr2 as $index => $unit) {
                        echo '<tr>';
                        echo '<td align= center>' . $unit['start_number'] . '</td>';
                        echo '<td align= center>' . $unit['entries'] . '</td>';
                        echo '<td align= center>' . $unit['avg_rank'] . '</td>';
                        echo '<td align= center>' . $unit['min_rank'] . '</td>';
                        echo '<td align= center>' . $unit['max_rank'] . '</td>';
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
                $('#table2').dataTable({
                    searching: false,
                    lengthChange: false,
                    paging: false
                });
            });
        </script>
</body>

</html>