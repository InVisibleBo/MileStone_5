<?php

	$mysqli = new mysqli("localhost","zshifour_zhongwu","307442570szw","ezcampus");
	if (mysqli_connect_errno()) {
		printf("Connect failed: %s\n", mysqli_connect_error());
		exit();
	}

	if (isset($_GET['email']) && isset($_GET['password']) && isset($_GET['hash'])) {
		$email = mysql_escape_string($_GET['email']);
		$password=mysql_escape_string($_GET['password']);
		$hash = mysql_escape_string($_GET['hash']);

		if ($stmt = $mysqli->prepare("SELECT hash,name,school FROM users WHERE email=? and password=?")) {
			$stmt->bind_param("ss",$email,$password);
			$stmt->execute();
			$stmt->bind_result($d_hash,$username,$school);
			while ($stmt->fetch()) {}
			$stmt->close();
		} else {
			printf("prepare error");
		}
		if ($hash == $d_hash) {
			if ($stmt = $mysqli->prepare("UPDATE users SET activated = 1 WHERE email=? and password=? and hash=?")) {
				$stmt->bind_param("sss",$email,$password,$hash);
				$stmt->execute();
				echo "You are successfully activated!";
				echo "Click <a href='login.php'>here</a> to log in!";

				$username = str_replace(' ', '_', $username);
				$school = str_replace(' ', '_', $school);
				if (!file_exists('users/' . $school . '/' . $username)) {
					
					mkdir('users/' . $school . '/' . $username,0777,true);
				}

				mkdir('users/' . $school . '/' . $username . '/profile',0777,true);
				mkdir('users/' . $school . '/' . $username . '/buy',0777,true);
				mkdir('users/' . $school . '/' . $username . '/sell',0777,true);
				mkdir('users/' . $school . '/' . $username . '/buy/',0777,true);
			} else {
				echo "prepare error";
			}
		} else {
			echo "unknown error";
		}

	}
?>