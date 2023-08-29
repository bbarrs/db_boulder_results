<?php
session_start();
// if(!isset($_SESSION["username"])){
// header("Location: z_login.php");
// exit(); }
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

// compound search prep
$conditions = array();
$types = '';
$bindParams = array();

// take compName from form
$compName = '';
if (!empty($_POST['compName'])) {
    $compName = '%' . $_POST['compName'] . '%';
    $bindParams[] = $compName;
    $conditions[] = "co.comp_name LIKE ?";
    $types .= 's';
}
// take compCountry from form
$compCountry = '';
if (!empty($_POST['compCountry'])) {
    $compCountry = '%' . $_POST['compCountry'] . '%';
    $bindParams[] = $compCountry;
    $conditions[] = "co.comp_country LIKE ?";
    $types .= 's';
}

// take fromDate from form
$fromDate = '';
if (!empty($_POST['fromDate'])) {
    $fromDate = $_POST['fromDate'];
    $bindParams[] = $fromDate;
    $conditions[] = "co.start_date >= ?";
    $types .= 's';

}

// take toDate from form
$toDate = '';
if (!empty($_POST['toDate'])) {
    $toDate = $_POST['toDate'];
    $bindParams[] = $toDate;
    $conditions[] = "co.end_date <= ?";
    $types .= 's';
}

// take climberFirst from form
$climberFirst = '';
if (!empty($_POST['climberFirst'])) {
    $climberFirst = '%' . $_POST['climberFirst'] . '%';
    $bindParams[] = $climberFirst;
    $conditions[] = "climber_first LIKE ?";
    $types .= 's';
}
// take climberLast from form
$climberLast = '';
if (!empty($_POST['climberLast'])) {
    $climberLast = '%' . $_POST['climberLast'] . '%';
    $bindParams[] = $climberLast;
    $conditions[] = "climber_last LIKE ?";
    $types .= 's';
}
// take climberNation from form
$climberNation = '';
if (!empty($_POST['climberNation'])) {
    $climberNation = '%' . $_POST['climberNation'] . '%';
    $bindParams[] = $climberNation;
    $conditions[] = "climber_nation LIKE ?";
    $types .= 's';
}

//query
$query = "SELECT co.comp_name, co.comp_city, co.comp_country, co.start_date, co.end_date, 
    cl.climber_first, cl.climber_last, cl.climber_nation,
    e.start_number, e.climber_rank, q.q_res, s.s_res, f.f_res
    FROM comp_entry e
    JOIN competition co USING (comp_id) JOIN climber cl USING (climber_id)
    LEFT JOIN (
		SELECT entry_id, CONCAT(tops,'/', top_attempts,' T ',zones,'/',zone_attempts, ' Z') AS `q_res`
        FROM results WHERE level = 'q'
    ) q USING (entry_id)
    LEFT JOIN (
		SELECT entry_id, CONCAT(tops,'/', top_attempts,' T ',zones,'/',zone_attempts, ' Z') AS `s_res`
        FROM results WHERE level = 's'
    ) s USING (entry_id)
    LEFT JOIN (
		SELECT entry_id, CONCAT(tops,'/', top_attempts,' T ',zones,'/',zone_attempts, ' Z') AS `f_res`
        FROM results WHERE level = 'f'
    ) f USING (entry_id)
    WHERE 1=1"
;
$end = " ORDER BY co.comp_name, e.climber_rank, cl.climber_last, cl.climber_first;";

if (!empty($conditions)) {
    $query .= " AND " . implode(" AND ", $conditions);
    $query .= $end;
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param($types, ...$bindParams);
    } else {
        echo "Statement preparation failed: " . $conn->error;
    }

    $stmt->execute();

    $result = $stmt->get_result() or die($conn->error . __LINE__);

} else {
    $query .= $end;
    $result = mysqli_query($conn, $query);
}


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
        <h4 align="center">IFSC Bouldering: Results Search</h4>
        <h6 align="center">Search from
            <b>
                <?php
                $query = "SELECT COUNT(entry_id) AS 'entry_count' FROM comp_entry;";
                $result = mysqli_query($conn, $query);
                while ($row = $result->fetch_assoc()) {
                    echo $row['entry_count'];
                }
                ?>
            </b> Competition Entries
        </h6><br>

        <!-- User Input GUI -->
        <form class="myForm" method="post" enctype="application/x-www-form-urlencoded" action="search_for_results.php">
            <div class="form-row" align="left">
                <div class="form-group col-md-3">
                    <label>Competition Name:</label>
                    <input type="text" name="compName" class="form-control" placeholder="Enter competition name here" value=
                        "<?php echo isset($_POST['compName']) ? htmlspecialchars($_POST['compName']) : ''; ?>">
                </div>
                <div class="form-group col-md-3">
                    <label>Competition Country:</label>
                    <input type="text" name="compCountry" class="form-control" placeholder="Enter 3-letter country code" value=
                        "<?php echo isset($_POST['compCountry']) ? htmlspecialchars($_POST['compCountry']) : ''; ?>">
                </div>
                <div class="form-group col-md-3">
                    <label>From Date:</label>
                    <input type="date" class="datepicker btn-block" name="fromDate" id="fromDate"
                        Placeholder="Select From Date"
                        value="<?php echo isset($_POST['fromDate']) ? $_POST['fromDate'] : '' ?>">
                </div>
                <div class="form-group col-md-3">
                    <label>To Date:</label>
                    <input type="date" name="toDate" id="toDate" class="datepicker btn-block"
                        Placeholder="Select To Date"
                        value="<?php echo isset($_POST['toDate']) ? $_POST['toDate'] : '' ?>">
                </div>
            </div>
            <div class="form-row" align="left">
                <div class="form-group col-md-3">
                    <label>Search by Climber First:</label>
                    <input type="text" name="climberFirst" class="form-control" placeholder="Enter first name here" value=
                        "<?php echo isset($_POST['climberFirst']) ? htmlspecialchars($_POST['climberLast']) : ''; ?>">
                </div>
                <div class="form-group col-md-3">
                    <label>Climber Last:</label>
                    <input type="text" name="climberLast" class="form-control" placeholder="Enter last name here" value=
                        "<?php echo isset($_POST['climberLast']) ? htmlspecialchars($_POST['climberLast']) : ''; ?>">
                </div>
                <div class="form-group col-md-3">
                    <label>Climber Nationality:</label>
                    <input type="text" name="climberNation" class="form-control" placeholder="Enter 3-letter country code" value=
                        "<?php echo isset($_POST['climberNation']) ? htmlspecialchars($_POST['climberNation']) : ''; ?>">
                </div>
            </div>
            <div class="form-row" align="left">

                <div class="form-group col-md-4 offset-md-2">
                    <a href="search_for_results.php" class="btn btn-danger btn-block"><i class="fa fa-refresh"></i>
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
                        <!--<td align=center>Result</td>-->
                        <td align=center>Competition</td>
                        <td align=center>City</td>
                        <td align=center>Country</td>
                        <td align=center>Dates</td>
                        <td align=center>Climber</td>
                        <td align=center>Nationality</td>
                        <td align=center>Start Number</td>
                        <td align=center>Rank</td>
                        <td align=center>Qualifiers</td>
                        <td align=center>Semis</td>
                        <td align=center>Finals</td>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($arr as $index => $unit) {
                        echo '<tr>';
                        //echo '<td align= center>' . ($index + 1) . '</td>';
                        echo '<td align= center>' . $unit['comp_name'] . '</td>';
                        echo '<td align= center>' . $unit['comp_city'] . '</td>';
                        echo '<td align= center>' . $unit['comp_country'] . '</td>';
                        echo '<td align= center>' . $unit['start_date'] . ' - ' . $unit['end_date'] . '</td>';
                        echo '<td align= center>' . $unit['climber_first'] . ' ' . $unit['climber_last'] . '</td>';
                        echo '<td align= center>' . $unit['climber_nation'] . '</td>';
                        echo '<td align= center>' . $unit['start_number'] . '</td>';
                        echo '<td align= center>' . $unit['climber_rank'] . '</td>';
                        echo '<td align= center>' . $unit['q_res'] . '</td>';
                        echo '<td align= center>' . $unit['s_res'] . '</td>';
                        echo '<td align= center>' . $unit['f_res'] . '</td>';
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