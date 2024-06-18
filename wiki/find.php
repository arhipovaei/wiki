<?php
	// главная страница
	header('Content-type: text/html; charset=utf-8');
error_reporting(E_ALL);
	include 'auth.php';
	include 'func.php';
	include 'scripts.php';
	$title='Поиск документа';
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

<form name="form" id="form" method="POST" action="find.php">
<?php
	$con=connect();
		$query="
			SELECT name, id
			FROM `cats`
			WHERE 1
			ORDER BY `name` ASC
		";
		$res=mysqli_query($con, $query) or die(mysqli_error($con));
		$results=array();
		while($row=mysqli_fetch_array($res)) {
			$results[]=$row;
		};

		if (count($results)>0) {
			echo "<p><b>Категория: </b>";
			$select="<select name='cat_id' id='cat_id'>";
			foreach($results as $row) {
				$select.="<option value='$row[id]'>$row[name]</option>";
			};
			$select.='</select>';
		};
		echo "$select</p>";
?>

<!--
			<button id="btn_reset" type="reset" class="button">Очистить поля</button>
-->
			<button id="btn_submit" name="btn_submit" type="submit" class="button">Искать</button>
</form>

<div>
<?php
	if (!empty($_REQUEST['cat_id'])) {
		// отображаем название категории		$cat_id=abs(intval($_REQUEST['cat_id']));
		$query="
			SELECT name
			FROM cats
			WHERE id=$cat_id
		";
		$res=mysqli_query($con, $query) or die(mysqli_error($con));
		$row=mysqli_fetch_array($res);
		echo "<div>Категория: <b>$row[name]</b></div>";

		// отображаем список ссылок на просмотр документов в данной категории
		$view=", CONCAT('<a href=\"view.php?view_id=', `docs`.id, '\">', '<center><img src=\"images\/view.svg\" height=\"24px\"></center>', '</a>') AS 'Просмотр'";
		$query="
			SELECT
				id, name, content, dt, version
				$view
			FROM docs
			WHERE 1
				AND cat_id=$cat_id
		";
		echo SQLResultTable($query, $con, '');
	};
?>
</div>

</div>
    </div>
<?php
	include('footer.php');
?>
</body>
</html>
