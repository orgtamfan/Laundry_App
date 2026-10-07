<?php 
	require_once(__DIR__ . '/../includes/_functions.php');

	if (isset($_SESSION['login']) == '') {
		header('Location: ' . url('login.php'));
		exit();
	} 

	session_unset();
	session_destroy();
	$_SESSION = [];

	header('Location: ' . url('login.php'));
	exit();


?>
