<?php
	// главная страница
	header('Content-type: text/html; charset=utf-8');
error_reporting(E_ALL);
	include 'func.php';
	include 'scripts.php';
	include "database.php";
	$title='Регистрация';
	$con=connect();
?>
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
        <div class="main-content">

<?php
	// если надо сохранить (если не пусто логин и пароль)
	if (!empty($_POST['login']) && !empty($_POST['password']) ) {
		$fio=mysqli_real_escape_string($con, trim($_POST['fio']));
		$email=mysqli_real_escape_string($con, trim($_POST['email']));
		$rank='';//mysqli_real_escape_string($con, trim($_POST['rank']));
		$password=mysqli_real_escape_string($con, trim($_POST['password']));
		$login=mysqli_real_escape_string($con, trim($_POST['login']));

		$fields="
				`fio`='$fio',
				`rank`='$rank',
				`email`='$email',
				`level`='1',
				`password`='$password',
				`login`='$login'
		";

		$query="
			SELECT COUNT(*)
			FROM `users`
			WHERE 1
				AND `login`='$login'
		";
		$res=mysqli_query($con, $query) or die(mysqli_error($con));
		if (mysqli_fetch_array($res, MYSQLI_BOTH)[0]) {
			echo '<p>Пользователь с таким логином уже существует!</p>';
		}
		else {
			$query="
				INSERT INTO `users`
				SET
					$fields
			";
			$res=mysqli_query($con, $query);
			if ($res) {
				echo '<p>Регистрация прошла успешно!
				<a href="login.php"><u>Авторизуйтесь в системе</u></a>
				</p>';
			}
			else {
				die(mysqli_error($con));
			};
		};
	}
	else if (!empty($_POST['btn_submit'])){
		echo '<p>Введите логин и пароль!</p>';
	};
?>

<form name="form" id="reg_form" action="reg.php" method="post">
	<div class="form-field">
		<label for="fio" class="my_label">ФИО:</label>
		<input type="text" id="fio" name="fio" placeholder="Иванов Иван Иванович" value="<?php if (!empty($fio)) echo $fio;?>">
	</div>

	<div class="form-field">
		<label for="login" class="my_label">Логин:</label>
		<input type="text" id="login" name="login" placeholder="login" value="<?php if (!empty($login)) echo $login;?>">
	</div>

	<div class="form-field">
		<label for="password" class="my_label">Пароль:</label>
		<input type="password" id="password" name="password" placeholder="password" value="<?php if (!empty($password)) echo $password;?>">
	</div>

	<div class="form-field">
		<label for="email" class="my_label">Email:</label>
		<input type="text" id="email" name="email" placeholder="example@domain.com" value="<?php if (!empty($email)) echo $email;?>">
	</div>

	<div class="form-field">
		<input type="reset" value="Очистить поля" class="button">
		<input type="submit" name="btn_submit" value="Зарегистрироваться" class="button">
		<button type="button" onclick="location.href='login.php'" class="button">Авторизация</button>
	</div>
</form>

        </div>
    </div>
<?php
	include('footer.php');
?>
</body>
</html>
