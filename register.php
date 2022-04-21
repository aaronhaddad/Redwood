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
				$password = password_hash($password, PASSWORD_DEFAULT);
				$status = 1; # As najjamech nabath mail, l users lkol activé mellowel

				// //generate code
				// $set='123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
				// $code=substr(str_shuffle($set), 0, 12);

				try{
					$stmt = $conn->prepare("INSERT INTO users (email, password, firstname, lastname, status, created_on) VALUES (:email, :password, :firstname, :lastname, :status, :now)");
					$stmt->execute(['email'=>$email, 'password'=>$password, 'firstname'=>$firstname, 'lastname'=>$lastname, 'status'=>$status, 'now'=>$now]);
					$userid = $conn->lastInsertId();

					header('location: login.php');

					// $message = "
					// 	<h2>Merci de votre inscription.</h2>
					// 	<p>Votre compte:</p>
					// 	<p>Email: ".$email."</p>
					// 	<p>Mot de passe: ".$_POST['password']."</p>
					// 	<p>Veuillez activer votre compte en cliquant sur le lien suivant:</p>
					// 	<a href='http://localhost/ecommerce/activate.php?code=".$code."&user=".$userid."'>Activer le compte</a>
					// ";
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