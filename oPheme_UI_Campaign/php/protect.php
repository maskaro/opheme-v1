<?php

if($user->login_check() === false) {
	header("Location: /logout");
	die("Invalid Session. Logging out ...");
}