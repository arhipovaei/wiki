<?php
	// главная страница
	header('Content-type: text/html; charset=utf-8');
error_reporting(E_ALL);
	include 'scripts.php';
	$title='Авторизация';
	if (session_id() == '') session_start();
	include "database.php";
	include "func.php";
	$con=connect();
ы?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="style.css">
    <title><?php echo $title;?></title>
</head>
<body>
    <div class="banner">
        <h1><?php echo $title;?></h1>
    </div>
    <div class="content">
<?php
//	include('menu.php');
?>
        <div class="main-content">


<?php
	if(isset($_POST['submit'])){
		$login=htmlentities(trim($_POST['login']), ENT_QUOTES, 'UTF-8');
		$password=htmlentities(trim($_POST['password']), ENT_QUOTES, 'UTF-8');
		// ищем такой логин и пароль
		$query="
			SELECT id, login, level
			FROM users
			WHERE 1
				AND (users.login='$login'	OR users.id='$login')
				AND (
					users.password=MD5('$password')
					OR users.password='$password'
				)
			LIMIT 1
		";
		$res=mysqli_query($con, $query) or die(mysqli_error($con));
		$row=mysqli_fetch_array($res, MYSQLI_ASSOC);
		if (
			(!empty($row['id']))
		)	{ //успешный вход

			$_SESSION['login'] =  $row['login'];
			$_SESSION['level'] =  $row['level'];
			$_SESSION['id'] =  $row['id'];
			header("Location: index.php");
			exit;
		}
		else
			echo '<div class="form-field">Логин или пароль неверны!</div>';
	};
?>

<body>
<form id="login_form" action="login.php" method="post">
	<div class="form-field">
		<label for="login" class="my_label">Логин:</label>
	    <input type="text" name="login" value="" id="login" />
	</div>

	<div class="form-field">
	    <label for="password" class="my_label">Пароль:</label>
    	<input type="password" name="password" value="" id="password" />
	</div>

	<div class="form-field">
		<button type="reset" class="button">Очистить</button>
		<button type="submit" name="submit" class="button">Войти</button>
	</div>

	<div class="form-field">
		Нет логина и пароля? <button type="button" onclick="location.href='reg.php'" class="button">Регистрация</button>
	</div>
</form>




        </div>
    </div>
<?php
	include('footer.php');
?>
</body>
</html>
