<?php

if($user->login_check() === false) {
	header("Location: login.php");
	die("Redirecting to login.php");
}