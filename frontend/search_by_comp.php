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

// take compName from form
$compName = '';
if (!empty($_POST['compName'])) {
    $compName = '%' . $_POST['compName'] . '%';
}

//query
$query = "SELECT comp_name, comp_city, comp_country, start_date, end_date, 
    COUNT(comp_id) AS `entrants`, CONCAT(ROUND(SUM(r.tops)/SUM(r.top_attempts)*100,2),'%') AS `top_ratio` FROM competition
    JOIN comp_entry USING (comp_id)
    JOIN results r USING (entry_id)
    WHERE competition.comp_name LIKE ?
    GROUP BY comp_id
    ORDER BY comp_name, comp_country, comp_city, end_date;";

// prepared statement
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $compName);
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
        <h4 align="center">IFSC Bouldering: Competition Info</h4>
        <h6 align="center">Search from
            <b>
                <?php
                $query = "SELECT COUNT(comp_id) AS 'comp_count' FROM competition;";
                $result = mysqli_query($conn, $query);
                while ($row = $result->fetch_assoc()) {
                    echo $row['comp_count'];
                }
                ?>
            </b> Competitions
        </h6><br>

        <!-- User Input GUI -->
        <form class="myForm" method="post" enctype="application/x-www-form-urlencoded" action="search_by_comp.php">
            <div class="form-row" align="left">
                <div class="form-group col-md-3">
                    <label>Search by Competition Name:</label>
                    <input type="text" name="compName" class="form-control" placeholder="Enter competition name here">
                </div>
            </div>
            <div class="form-row" align="left">

                <div class="form-group col-md-4 offset-md-2">
                    <a href="search_by_comp.php" class="btn btn-danger btn-block"><i class="fa fa-refresh"></i>
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
                        <td align=center>Competition Name</td>
                        <td align=center>City</td>
                        <td align=center>Country</td>
                        <td align=center>Start Date</td>
                        <td align=center>End Date</td>
                        <td align=center># of Entrants</td>
                        <td align=center>Successful Top Rate</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($arr as $index => $unit) {
                        echo '<tr>';
                        echo '<td align= center>' . ($index + 1) . '</td>';
                        echo '<td align= center>' . $unit['comp_name'] . '</td>';
                        echo '<td align= center>' . $unit['comp_city'] . '</td>';
                        echo '<td align= center>' . $unit['comp_country'] . '</td>';
                        echo '<td align= center>' . $unit['start_date'] . '</td>';
                        echo '<td align= center>' . $unit['end_date'] . '</td>';
                        echo '<td align= center>' . $unit['entrants'] . '</td>';
                        echo '<td align= center>' . $unit['top_ratio'] . '</td>';
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