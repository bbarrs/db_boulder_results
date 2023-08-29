<?php
session_start();

// Create connection
$conn = new mysqli("boulder-results.cwytbia1ujp1.us-east-1.rds.amazonaws.com", "admin", "AdamGoldstein", 'boulder_results');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$msg="";

if(isset($_POST['login'])){
	$username = $_POST['username'];
	$password = $_POST['password'];
	$password = sha1($password);


	$sql = "SELECT * FROM users WHERE username=? AND password=?";

	$stmt=$conn->prepare($sql);
	$stmt->bind_param("ssss", $username, $password);
	$stmt->execute();
	$result = $stmt->get_result();
	$row = $result->fetch_assoc();
	session_regenerate_id();
	$_SESSION['username'] = $row['username'];
	$_SESSION['user_fullname'] = $row['user_fullname'];
	session_write_close();

	if($result->num_rows==1){
		header("location:z_index.php");
	} else {
		$msg = "Username or Password Invalid";
	}	
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>IFSC Bouldering Leaderboard</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	  
</head>

<body class="bg-dark">
<div class="container">
	<div class="row justify-content-center">
		<div class="col-lg-5 bg-light text-dark mt-5 px-0">
			<h3 class="text-center text-dark bg-success p-3">Login</h3>
			<form action="<?=$_SERVER['PHP_SELF'] ?>" method="post" class="p-4">
				<div class="form-group">
					<input type="text" name="username" class="form-control from-control-lg" placeholder="Username" required>
				</div>
				<div class="form-group">
					<input type="password" name="password" class="form-control from-control-lg" placeholder="Password" required>
				</div>
				<div class="form-group">
					<input type="submit" name="login" name="login" class= "btn bg-success btn-block">
					
				</div>
				<div class="form-group">
				<h6 class="text-danger text-center"><?= $msg; ?></h6>
				<br>
				<p>Not yet a member? <a href="registration.php">Sign up</a></p>
			</form>
		</div>
		</div>
</div>
</body>
</html>