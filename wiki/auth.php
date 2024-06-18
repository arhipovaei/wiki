<?php
//	sleep(1); // защита от подбора пароля
	if (session_id() == '') session_start();
	if (isset($_GET['do']) && $_GET['do'] == 'exit'){
		unset($_SESSION['login']);
		session_destroy();
	};
	if (!isset($_SESSION['login']) || !$_SESSION['login']){
		header("Location: login.php");
		exit;
	};
?>