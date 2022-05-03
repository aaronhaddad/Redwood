<?php
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\Exception;

	include 'includes/session.php';

	if(isset($_POST['signup'])){
		$firstname = $_POST['firstname'];
		$lastname = $_POST['lastname'];
		$email = $_POST['email'];
		$password = $_POST['password'];
		$repassword = $_POST['repassword'];

		$_SESSION['firstname'] = $firstname;
		$_SESSION['lastname'] = $lastname;
		$_SESSION['email'] = $email;

		if($password != $repassword){
			$_SESSION['error'] = 'Les mdps mahomech kif kif';
			header('location: signup.php');
		}
		else{
			$conn = $pdo->open();

			$stmt = $conn->prepare("SELECT COUNT(*) AS numrows FROM users WHERE email=:email");
			$stmt->execute(['email'=>$email]);
			$row = $stmt->fetch();
			if($row['numrows'] > 0){
				$_SESSION['error'] = 'Email dèja pris';
				header('location: signup.php');
			}
			else{
				$now = date('Y-m-d');
				$password = password_hash($password, PASSWORD_DEFAULT); # nhashi l pass b algo te3 php
				$status = 1; # As najjamech nabath mail, l users lkol activé mellowel

				try{
					// ? Admin signup: izid admin fel name
					if (strpos($firstname, 'admin') !== false) {
						echo '<script>alert("admin")</script>';
						$type = 1;
						$f_name = explode("admin", $firstname);
						// Admin
						$stmt = $conn->prepare("INSERT INTO users (email, password, firstname, lastname, status, type, created_on) VALUES (:email, :password, :f_name, :lastname, :status, :type, :now)");
						$stmt->execute(['email'=>$email, 'password'=>$password, 'f_name'=>$f_name[0], 'lastname'=>$lastname, 'status'=>$status, 'type'=>(string)$type, 'now'=>$now]);
						$userid = $conn->lastInsertId();

						header('location: login.php');
					} else {
						echo '<script>alert("user")</script>';
						// User aadi
						$stmt = $conn->prepare("INSERT INTO users (email, password, firstname, lastname, status, created_on) VALUES (:email, :password, :firstname, :lastname, :status, :now)");
						$stmt->execute(['email'=>$email, 'password'=>$password, 'firstname'=>$firstname, 'lastname'=>$lastname, 'status'=>$status, 'now'=>$now]);
						$userid = $conn->lastInsertId();

						header('location: login.php');
					}
				}
				catch(PDOException $e){
					$_SESSION['error'] = $e->getMessage();
					header('location: login.php');
				}

				$pdo->close();

			}

		}

	}
	else{
		$_SESSION['error'] = 'Veuillez vous inscrire d\'abord';
		header('location: signup.php');
	}

?>