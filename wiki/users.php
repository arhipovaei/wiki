<?php
	// главная страница
	header('Content-type: text/html; charset=utf-8');
	include 'auth.php';
	include 'func.php';
	include 'scripts.php';
	$con=connect();
	$title='Пользователи';
	$table='users';
	if (!in_array($_SESSION['level'], array(10, 5, 2, 1))) { // доступ разрешен только группе пользователей
		header("Location: login.php"); // остальных просим залогиниться
		exit;
	};
	$edit=in_array($_SESSION['level'], array(10));
	$param_keys=array('fio', 'birthday', 'email', 'level', 'date_reg', 'login', 'password'); // названия полей в таблице БД
	$param_str=array('ФИО', 'Дата рождения', 'Электронная почта', 'Уровень доступа', 'Дата регистрации', 'Логин', 'Пароль'); // названия столбцов в таблице для отображения
	$param_ext=array(0, 0, 0, '`levels`.`name`', 0, 0, 0); // поля для select
	$param_need='login'; // обязательные поля, без которых не сохранять данные
	$dates=array('birthday', 'date_reg'); // поля типа "дата"
	$textarea=array(); // поля типа "textarea"

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
	// если надо удалить
	if (!empty($_GET['delete_id'])) {
		$id=intval($_GET['delete_id']);
		$query="
			DELETE FROM `$table`
			WHERE id=$id
		";
		mysqli_query($con, $query) or die(mysqli_error($con));
	};

	// если надо редактировать, загружаем данные
	if (!empty($_GET['edit_id'])) {
		$id=intval($_GET['edit_id']);
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
	};

	// если надо сохранить (если не пусто)
	if (!empty($_POST[$param_need])) {
		foreach($param_keys as $param_key) {
			$param_values[$param_key]=mysqli_real_escape_string($con, trim($_POST[$param_key]));
		};


		$fields=''; // собираем строку вида: "`shifr`='$shifr', `organ_id`='$organ_id'";

		for($ind=0; $ind<count($param_keys); $ind++) {
			$fields.='`'.$param_keys[$ind].'`=\''.$param_values[$param_keys[$ind]].'\', ';
		};
		$fields=trim($fields, ', ');
		// если надо сохранить отредактированное
		if (!empty($_REQUEST['hidden_edit_id'])) {
			$id=intval($_REQUEST['hidden_edit_id']);
			$query="
				UPDATE `$table`
				SET
					$fields
				WHERE
					id=$id
			";
		}
		else { // добавление новой строки
			$query="
				INSERT INTO `$table`
				SET
					$fields
			";
		};

		mysqli_query($con, $query) or die(mysqli_error($con));
	};

	if (isset($_POST['btn_submit'])) // была нажата кнопка сохранить - не надо больше отображать id
		$id=0;

	// добавляем возможность удаления админам
	$delete_confirm="onClick=\"return window.confirm(\'Подтверждаете удаление?\');\"";
	$admin_delete=$edit ? ", CONCAT('<a href=\"$table.php?delete_id=', `$table`.id, '\" $delete_confirm>', '<center><img src=\"images\/drop.png\" height=\"24px\"></center>', '</a>') AS 'Удал.'" : '';
	// добавляем возможность редактирования админам
	$admin_edit=$edit ? ", CONCAT('<a href=\"$table.php?edit_id=', `$table`.id, '\">', '<center><img src=\"images\/edit.png\" height=\"24px\"></center>', '</a>') AS 'Ред.'" : '';
	$buf='';
	for($ind=0; $ind<count($param_keys); $ind++) {
		if (!empty($param_ext[$ind])) // если есть дополнительный код
			$buf.=$param_ext[$ind]." AS '".$param_str[$ind]."', ";
		else
			$buf.="`$table`.`".$param_keys[$ind]."` AS '".$param_str[$ind]."', ";
	};
	$buf=trim($buf, ', ');
	$query="
		SELECT
			$buf
			$admin_delete
			$admin_edit
		FROM
			`levels`, `$table`
		WHERE 1
			AND `$table`.`level`=`levels`.`id`
		ORDER BY `$table`.`id`
	";

	echo SQLResultTable($query, $con, '');
?>

<?php
	// доступ к редактированию только админу
	if ($edit) { // if ($edit)
?>
<form name="form" id="my_form" action="<?php echo $table?>.php" method="post" class="form-container">
	<p align="center"><b>Редактор</b>
	<!-- <?php if (!empty($id)) echo "(редактируется строка с кодом $id)";?></p>
	-->


<?php
	$buf='';
	$errorlevel=error_reporting();
	error_reporting(0);
	for($ind=0; $ind<count($param_keys); $ind++) {
		if (in_array($param_keys[$ind], $textarea)) { // если это textarea
			$buf.='
			<div class="form-field">
				<label for="'.$param_keys[$ind].'" class="my_label">'.$param_str[$ind].'</label>
				<textarea id="'.$param_keys[$ind].'" name="'.$param_keys[$ind].'" cols="60" rows="5">'.$param_values[$param_keys[$ind]].'</textarea>
			</div>
			';
		}
		elseif (!$param_ext[$ind]) { // обычное поле input
			$type= in_array($param_keys[$ind], $dates) ? 'datetime-local' : 'text'; // если это дата, сделать его type=date, иначе type=text
			$date=strtotime($param_values[$param_keys[$ind]]);
			if (empty($date)) {
				$date_str=date("Y-m-d H:i:s");
			}
			else {
				$date_str=date("Y-m-d H:i:s", $date);
			};
			if (in_array($param_keys[$ind], $dates)) $param_values[$param_keys[$ind]]=$date_str;
			$buf.='
			<div class="form-field">
				<label for="'.$param_keys[$ind].'" class="my_label">'.$param_str[$ind].'</label>
				<input id="'.$param_keys[$ind].'" name="'.$param_keys[$ind].'" type="'.$type.'" value="'.$param_values[$param_keys[$ind]].'">
			</div>
			';
		}
		else { // поле с выбором (select)
			$buf.='
			<div class="form-field">
				<label for="'.$param_keys[$ind].'" class="my_label">'.$param_str[$ind].'</label>
				<select "'.$param_keys[$ind].'" name="'.$param_keys[$ind].'">
			';
			list($buf_table, $buf_field) =explode('.', $param_ext[$ind]);
			$query="
				SELECT $buf_field AS `name`, `id`
				FROM $buf_table
				WHERE 1
				ORDER BY $buf_field
			";
			$res=mysqli_query($con, $query) or die(mysqli_error($con));
			while ($row=mysqli_fetch_array($res, MYSQLI_ASSOC)) {
				$selected= ($param_values[$param_keys[$ind]]==$row['id']) ? 'selected' : '';
				$buf.= "
							<option value='$row[id]' $selected>$row[name]</option>
				";
			};
			$buf.= '
						</select>
			</div>
			';
		};
	};
	$errorlevel=error_reporting();
	error_reporting($errorlevel);
	echo $buf;
?>

	<div class="form-field">
		<input name="hidden_edit_id" type="hidden" value="<?php if (!empty($id)) echo $id;?>">
	</div>
	<div class="form-field">
			<button id="btn_reset" type="reset" class="button">Очистить поля</button>
			<button id="btn_submit" name="btn_submit" type="submit" class="button"><?php if (!empty($id)) echo "Сохранить"; else echo "Добавить";?></button>
	</div>
</form>
<?php
	}; // if ($edit)
?>
        </div>
    </div>
<?php
	include('footer.php');
?>
</body>
</html>
