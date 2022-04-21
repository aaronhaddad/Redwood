<?php
	include 'includes/session.php';
	$conn = $pdo->open();

	if(isset($_POST['login'])){
		
		$email = $_POST['email'];
		$password = $_POST['password'];

		try{

			$stmt = $conn->prepare("SELECT *, COUNT(*) AS numrows FROM users WHERE email = :email");
			$stmt->execute(['email'=>$email]);
			$row = $stmt->fetch();
			if($row['numrows'] > 0){
				if($row['status']){
					if(password_verify($password, $row['password'])){
						if($row['type']){
							$_SESSION['admin'] = $row['id'];
						}
						else{
							$_SESSION['user'] = $row['id'];
						}
					}
					else{
						$_SESSION['error'] = 'MDP ghalet';
					}
				}
				else{
					$_SESSION['error'] = 'Compte non activé.';
				}
			}
			else{
				$_SESSION['error'] = 'Email introuvable';
			}
		}
		catch(PDOException $e){
			echo "Un problème de connexion est survenu: " . $e->getMessage();
		}

	}
	else{
		$_SESSION['error'] = 'Veuillez d\'abord entrez vos coordonnées';
	}

	$pdo->close();

	header('location: login.php');

?>