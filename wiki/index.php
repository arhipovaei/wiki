<?php
	// главная страница
	header('Content-type: text/html; charset=utf-8');
error_reporting(E_ALL);
	include 'auth.php';
	include 'func.php';
	include 'scripts.php';
	$title='Главная';
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
			<h2>корпоративная база знаний и документации<br>
			</h2>

<!-- блок новостей-->
<?php
	$con=connect();
		$query="
			SELECT *, DATE_FORMAT(dt, '%d.%m.%Y') AS dt2
			FROM `news`
			WHERE 1
			ORDER BY `dt` DESC
			LIMIT 10
		";
		$res=mysqli_query($con, $query) or die(mysqli_error($con));
		$results=array();
		while($row=mysqli_fetch_array($res)) {
			$results[]=$row;
		};
		if (count($results)>0) {
			echo "<p><b>Последние новости</b></p>";
			foreach($results as $result) {
				echo "<p>$result[dt2] $result[note]</p>";
			};
		};
?>

        </div>
    </div>
<?php
	include('footer.php');
?>
</body>
</html>
