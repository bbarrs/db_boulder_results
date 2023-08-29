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

// take climberFirst from form
$climberFirst = '';
if (!empty($_POST['climberFirst'])) {
    $climberFirst = '%' . $_POST['climberFirst'] . '%';
}
// take climberLast from form
$climberLast = '';
if (!empty($_POST['climberLast'])) {
    $climberLast = '%' . $_POST['climberLast'] . '%';
}


//query
$query = "SELECT c.climber_first, c.climber_last, climber_nation, COUNT(e.climber_rank) AS `entries`, IFNULL(a.win_count, 0) AS `wins`,
    CONCAT(ROUND(IFNULL(a.win_count,0)/COUNT(e.climber_rank)*100,2),'%') AS `win_pct`, 
    ROUND(AVG(e.climber_rank),2) AS `avg_rank` 
    FROM climber c JOIN comp_entry e USING (climber_id)
    LEFT JOIN (
        SELECT climber_id, COUNT(e2.climber_rank) AS win_count 
        FROM climber c2 JOIN comp_entry e2 USING (climber_id)
        WHERE climber_rank = 1 GROUP BY c2.climber_id
    ) a USING (climber_id) 
    WHERE climber_first LIKE ?
    OR climber_last LIKE ?
    GROUP BY c.climber_id
    ORDER BY climber_last, climber_nation, `wins` DESC;
";

// prepared statement
$stmt = $conn->prepare($query);
$stmt->bind_param('ss', $climberFirst, $climberLast);
$stmt->execute();
$result = $stmt->get_result() or die($conn->error . __LINE__);
$arr = $result->fetch_all(MYSQLI_ASSOC);
?>

<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=yes">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.11.6/js/jquery.dataTables.min.js"></script>

</head>

<body>
    <div align="center" class="container mt-5">
        <h4 align="center">IFSC Bouldering: Climber Info</h4>
        <h6 align="center">Search from
            <b>
                <?php
                $query = "SELECT COUNT(climber_id) AS 'climber_count' FROM climber;";
                $result = mysqli_query($conn, $query);
                while ($row = $result->fetch_assoc()) {
                    echo $row['climber_count'];
                }
                ?>
            </b> Climbers
        </h6><br>

        <!-- User Input GUI -->
        <form class="myForm" method="post" enctype="application/x-www-form-urlencoded" action="search_by_climber.php">
            <div class="form-row" align="left">
                <div class="form-group col-md-3">
                    <label>Search by Climber First:</label>
                    <input type="text" name="climberFirst" class="form-control" placeholder="Enter first name here">
                </div>
                <div class="form-group col-md-3">
                    <label>Climber Last:</label>
                    <input type="text" name="climberLast" class="form-control" placeholder="Enter last name here">
                </div>
            </div>
            <div class="form-row" align="left">

                <div class="form-group col-md-4 offset-md-2">
                    <a href="search_by_climber.php" class="btn btn-danger btn-block"><i class="fa fa-refresh"></i>
                        Reset</a></span>
                </div>
                <div class="form-group col-md-4">
                    <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-paper-plane"></i>
                        Submit</button>
                </div>

            </div>
        </form>
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
                        <td align=center>Result</td>
                        <td align=center>Climber Name</td>
                        <td align=center>Nationality</td>
                        <td align=center># of Competitions</td>
                        <td align=center># of Wins</td>
                        <td align=center>Win Percentage</td>
                        <td align=center>Average Rank</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // print each row of results
                    foreach ($arr as $index => $unit) {
                        echo '<tr>';
                        echo '<td align= center>' . ($index + 1) . '</td>';
                        echo '<td align= center>' . $unit['climber_first'] . ' ' . $unit['climber_last'] . '</td>';
                        echo '<td align= center>' . $unit['climber_nation'] . '</td>';
                        echo '<td align= center>' . $unit['entries'] . '</td>';
                        echo '<td align= center>' . $unit['wins'] . '</td>';
                        echo '<td align= center>' . $unit['win_pct'] . '</td>';
                        echo '<td align= center>' . $unit['avg_rank'] . '</td>';
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