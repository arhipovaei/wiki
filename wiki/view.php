<?php
	// главная страница
	header('Content-type: text/html; charset=utf-8');
	include 'auth.php';
	include 'func.php';
	include 'scripts.php';
	$con=connect();
	$title='Документы';
	$table='docs';
	if (!in_array($_SESSION['level'], array(10, 5, 1))) { // доступ разрешен только группе пользователей
		header("Location: login.php"); // остальных просим залогиниться
		exit;
	};
	$edit=in_array($_SESSION['level'], array(10, 5));
	$param_keys=array('cat_id', 'name', 'content', 'dt',
			'user_id', 'version', 'last_dt'); // названия полей в таблице БД
	$param_str=array('Категория', 'Наименование', 'Содержимое документа', 'Время создания',
			'Автор', 'Версия', 'Время последнего изменения'); // названия столбцов в таблице для отображения
	$param_ext=array('`cats`.`name`', 0, 0, 0, '`users`.`fio`', 0, 0); // поля для select
	$param_need='name'; // обязательные поля, без которых не сохранять данные
	$dates=array('dt', 'last_dt'); // поля типа "дата"
	$textarea=array('content'); // поля типа "textarea"
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
<?php
	include('menu.php');
?>

				<div class="main-content">

<?php
	$id=$_REQUEST['view_id'];
	if (empty($id)) $id=$_REQUEST['hidden_edit_id'];

	// если была произведена отправка формы
	if(isset($_FILES['file'])) {
		// проверяем, можно ли загружать файл
		$check = can_upload($_FILES['file']);

		if($check === true){
			// загружаем файл на сервер
			$newname=make_upload($_FILES['file'], $id);
			$query="
				INSERT INTO `files`
				SET
					`fname`='".$newname."',
					`fsize`=".$_FILES['file']['size'].",
					`request_id`=$id,
					`user_id`=".$_SESSION['id']."
			";
			mysqli_query($con, $query) or die(mysqli_error($con));

			$query="
				UPDATE `docs`
				SET
					version=version+1,
					last_dt=NOW()
				WHERE
					id=$id
			";
				mysqli_query($con, $query) or die(mysqli_error($con));
		}
		else{
			// выводим сообщение об ошибке
			//echo "<p><b>$check</b></p>";
		};
	};

	// сохранение комментария, если надо
	// если была произведена отправка формы
	if (!empty($_REQUEST['comment'])) {
		$content=htmlentities($_REQUEST['comment'], ENT_QUOTES, 'UTF-8');
		$query="
			INSERT INTO comments
			SET
					`content`='$content',
					`user_id`=$_SESSION[id],
					`dt`=NOW(),
					`doc_id`=$id
			";
		mysqli_query($con, $query) or die(mysqli_error($con));

		$query="
			UPDATE `docs`
			SET
				version=version+1,
				last_dt=NOW()
			WHERE
				id=$id
		";
			mysqli_query($con, $query) or die(mysqli_error($con));
	}
	else{
		// выводим сообщение об ошибке
//		echo "<p><b>Ошибка при добавлении комментария	</b></p>";
	};


	$buf='';
	foreach($param_keys as $param_key) {
		$buf.="`$param_key`, ";
	};
	$buf=trim($buf, ', ');
	$query="
		SELECT
			$buf
		FROM `$table`
		WHERE id=$id
	";
	$res=mysqli_query($con, $query) or die(mysqli_error($con));
	$row=mysqli_fetch_array($res);
	foreach($param_keys as $param_key) {
		$param_values[$param_key]=$row[$param_key];
	};


//	if (isset($_POST['btn_submit'])) // была нажата кнопка сохранить - не надо больше отображать id
//		$id=0;
?>

<form name="form" id="my_form" action="view.php" method="post" class="form-container" enctype="multipart/form-data">
	<p align="center"><b>Просмотр</b>

<?php
	$buf='';
	$errorlevel=error_reporting();
	error_reporting(0);
	for($ind=0; $ind<count($param_keys); $ind++) {
		if (in_array($param_keys[$ind], $textarea)) { // если это textarea
			$buf.='
			<div class="form-field">
				<label for="'.$param_keys[$ind].'" class="my_label">'.$param_str[$ind].'</label>
				<textarea readonly id="'.$param_keys[$ind].'" name="'.$param_keys[$ind].'" cols="60" rows="5">'.$param_values[$param_keys[$ind]].'</textarea>
			</div>
			';
		}
		elseif (!$param_ext[$ind]) { // обычное поле input
			$type= in_array($param_keys[$ind], $dates) ? 'datetime-local' : 'text'; // если это дата, сделать его type=date, иначе type=text
			$date=strtotime($param_values[$param_keys[$ind]]);
			if (empty($date)) {
				$date_str=date("Y-m-d H:i");
			}
			else {
				$date_str=date("Y-m-d H:i", $date);
			};
			if (in_array($param_keys[$ind], $dates)) $param_values[$param_keys[$ind]]=$date_str;
			$buf.='
			<div class="form-field">
				<label for="'.$param_keys[$ind].'" class="my_label">'.$param_str[$ind].'</label>
				'.$param_values[$param_keys[$ind]].'
			</div>
			';
		}
		else { // поле с выбором (select)
			$buf.='
			<div class="form-field">
				<label for="'.$param_keys[$ind].'" class="my_label">'.$param_str[$ind].'</label>
			';
			list($buf_table, $buf_field) =explode('.', $param_ext[$ind]);
			$query="
				SELECT $buf_field AS `name`, `id`
				FROM $buf_table
				WHERE 1
				ORDER BY $buf_field
			";
if ($param_keys[$ind]=='user_id') {	$query="
		SELECT fio AS name, id
		FROM users
		WHERE 1
	";};
			$res=mysqli_query($con, $query) or die(mysqli_error($con));
			while ($row=mysqli_fetch_array($res, MYSQLI_ASSOC)) {
				if ($param_values[$param_keys[$ind]]==$row['id']) {
					$buf.= $row['name'];
				};
			};
			$buf.= '
			</div>
			';
		};
	};
	$errorlevel=error_reporting();
	error_reporting($errorlevel);
	echo $buf;
?>

<?php
	if ($id) {		// отображение файлов
		$query="
			SELECT `fname`, `fsize`, DATE_FORMAT(`dt`, '%d.%m.%Y %H:%i:%s ') AS `dt`, `users`.`login`
			FROM `files`, `users`
			WHERE 1
				AND `files`.`request_id`=$id
				AND `users`.`id`=`files`.`user_id`
		";
		$request_files=array();
		$res=mysqli_query($con, $query) or die(mysqli_error($con));
		while ($row=mysqli_fetch_array($res, MYSQLI_ASSOC)) {
			$request_files[]=$row;
		};

		if (count($request_files)) {
			echo '
				<p>
			';
			foreach($request_files as $request_file) {
				echo "Файл <a target='blank' href='upload/".iconv('UTF-8', 'CP1251', $request_file['fname'])."'>$request_file[fname]<img src='images/save.png' title='Скачать' width='24px' height='24px'></a>
					$request_file[fsize] байт добавлен $request_file[login] $request_file[dt]<br>";
			};
			echo '
				</p>
			';
		};

		// отображение комментариев
		$query="
			SELECT `content`, DATE_FORMAT(`dt`, '%d.%m.%Y %H:%i:%s ') AS `dt`, CONCAT(`users`.`fio`, ' (', `users`.`login`, ')') AS login
			FROM `comments`, `users`
			WHERE 1
				AND `comments`.`doc_id`=$id
				AND `users`.`id`=`comments`.`user_id`
		";
		$comments=array();
		$res=mysqli_query($con, $query) or die(mysqli_error($con));
		while ($row=mysqli_fetch_array($res, MYSQLI_ASSOC)) {
			$comments[]=$row;
		};

		if (count($comments)) {
			echo '
				<p>
			';
			foreach($comments as $comment) {
				echo "<p>$comment[login] $comment[dt] <p><i>$comment[content]</i></p>";
			};
			echo '
				</p>
			';
		};

	};
?>

			<div class="form-field">
				<label for="comment" class="my_label">Комментарий</label>
				<textarea id="comment" name="comment" cols="60" rows="5"></textarea>
			</div>


	<input name="hidden_edit_id" type="hidden" value="<?php echo $id;?>">

	<div class="form-field">
		<label for="file" class="my_label">Прикрепить файл</label>
		<input id="file" name="file" type="file">
	</div>

	<div class="form-field">
			<button id="btn_reset" type="reset" class="button">Очистить поля</button>
			<button id="btn_submit" name="btn_submit" type="submit" class="button"><?php if (!empty($id)) echo "Сохранить"; else echo "Добавить";?></button>
	</div>
</form>

        </div>
    </div>
<?php
	include('footer.php');
?>
</body>
</html>
