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
$query = "SELECT c.climber_nation, CONCAT(ROUND(AVG(c.climber_nation = co.comp_country)*100,2),'%') AS home_adv_pct,
    ROUND(AVG(CASE WHEN c.climber_nation = co.comp_country THEN e.climber_rank END),2) AS `avg_home_adv_rank`,
    ROUND(AVG(e.climber_rank),2) AS `avg_comp_rank`
    FROM climber c
    JOIN comp_entry e ON c.climber_id = e.climber_id
    JOIN competition co ON e.comp_id = co.comp_id
    GROUP BY c.climber_nation
    HAVING AVG(c.climber_nation = co.comp_country) > 0 # There must be at least one instance of a climber from nation of competition they're competing in
    ORDER BY AVG(c.climber_nation = co.comp_country) DESC, c.climber_nation;";

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
        <h4 align="center">IFSC Bouldering: Home-Field Advantage</h4>
        <h6 align="center">Is there a home-field advantage: Are climbers more successful in competitions in their home country?
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
            <table id="table" class="display" cellspacing="0" style="width:100%">
                <thead style="font: bold; active" align="center">
                    <tr>
                        <td align=center>Index</td>
                        <td align=center>Country</td>
                        <td align=center>Home Advantage Percentage</td>
                        <td align=center>Average Home Rank</td>
                        <td align=center>Average Overall Competition Rank</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($arr as $index => $unit) {
                        echo '<tr>';
                        echo '<td align= center>' . ($index + 1) . '</td>';
                        echo '<td align= center>' . $unit['climber_nation'] . '</td>';
                        echo '<td align= center>' . $unit['home_adv_pct'] . '</td>';
                        echo '<td align= center>' . $unit['avg_home_adv_rank'] . '</td>';
                        echo '<td align= center>' . $unit['avg_comp_rank'] . '</td>';
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