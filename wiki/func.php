<?php
	include 'database.php';
	function connect() {
		global $hostname;
		global $mysql_login;
		global $mysql_password;
		global $database;
		$con = mysqli_connect($hostname, $mysql_login, $mysql_password, $database) or die(mysqli_error($con));
		if (!$con) die('<h2>Ошибка подключения к серверу базы данных!</h2>');
		mysqli_set_charset($con,'utf8') or die(mysqli_error($con));
		return $con;
	};

// функция печатает результат запроса в виде html-таблицы
function SQLResultTable($Query, $con, $mask='', $text_length=0) {

	function mysqli_field_name($result, $field_offset) {
    $properties = mysqli_fetch_field_direct($result, $field_offset);
    return is_object($properties) ? $properties->name : null;
	};

	$Table = "";  //initialize table variable

	$Table.= "<table id='myTable' border='1' style=\"border-collapse: collapse;\" class=\"tablesorter\">"; //Open HTML Table

	$Result = mysqli_query($con, $Query); //Execute the query
	if(mysqli_error($con)) {
		$Table.= "<tr><td>MySQL ERROR: " . mysqli_error($con) . "</td></tr>";
	}
	else {
		//Header Row with Field Names
		$NumFields = mysqli_num_fields($Result);
		$Table.= "<thead>";
		$Table.= "<tr style=\"background-color: #DE7873; color: #FFFFFF;\">";
		for ($i=0; $i < $NumFields; $i++)
		{
			$Table.= "<th>" . mysqli_field_name($Result, $i) . "</th>";
		}
		$Table.= "</tr>";
		$Table.= "</thead>";

		//Loop thru results
		$Table.= "<tbody>";
		$RowCt = 0; //Row Counter
		while($Row = mysqli_fetch_assoc($Result))
		{
			//Alternate colors for rows
//			if($RowCt++ % 2 == 0) $Style = "background-color: #F0D2C1;";
//			else $Style = "background-color: #F0C1D0;";
$Style='';

			$Table.= "<tr style=\"$Style\">";
			//Loop thru each field
			foreach($Row as $field => $value)
			{				// обрезаем длинный текст
				if ($text_length>0) {					$ellipsis= ($text_length<mb_strlen($value, 'UTF-8'))? '...' : ''; // многоточие, если текст будет обрезаться
					$value= iconv('CP1251', 'UTF-8', substr(iconv('UTF-8', 'CP1251', $value), 0, $text_length)).$ellipsis;
				};
				// делаем подсветку найденного
				$value=str_replace($mask, "<font color='red'>$mask</font>", $value);
				// отображаем значение
				$Table.= "<td>$value</td>";
			}
			$Table.= "</tr>";
		}
//		$Table.= "<tr style=\"background-color: #000066; color: #FFFFFF;\"><td colspan='$NumFields'>Найдено записей: " . mysqli_num_rows($Result) . "</td></tr>";
	}
	$Table.= "</tbody>";
	$Table.= "</table>";
	$Table.='
<div id="pager" class="pager" style="cursor:pointer">
	<form style=\'display:"block;"\'>
		<span class="first"> Первая страница</span>
		<span class="prev">←</span>
		<input type="text" class="pagedisplay">
		<span class="next">→</span>
		<span class="last"> Последняя страница</span>
		<select class="pagesize">
			<option selected="selected" value="10">10</option>
			<option value="20">20</option>
			<option value="30">30</option>
			<option value="40">40</option>
		</select>
	</form>
</div>
';

	return $Table;
};

// функция печатает результат запроса в виде выпадающего списка Select
function SQLResultSelect($Query, $con, $select_name) {
	$res = mysqli_query($con, $Query); //Execute the query
	if(mysqli_error($con)) {
		$Table.= "<tr><td>MySQL ERROR: " . mysqli_error($con) . "</td></tr>";
	}
	else {
		$select="<select name='$select_name' id='$select_name'>";
		while($row = mysqli_fetch_assoc($res)) {
			$select.="<option value='$row[id]'>$row[name]</option>";
		};
		$select.='</select>';
	};
	return $select;
};





	function can_upload($file){
		// если имя пустое, значит файл не выбран
		if ($file['name'] == '')
			return 'Вы не выбрали файл.';

		/* если размер файла 0, значит его не пропустили настройки
		сервера из-за того, что он слишком большой */
		if($file['size'] == 0)
			return 'Файл слишком большой.';

		// разбиваем имя файла по точке и получаем массив
		$getMime = explode('.', $file['name']);
		// нас интересует последний элемент массива - расширение
		$mime = strtolower(end($getMime));
		// объявим массив допустимых расширений
		$types = array('doc', 'docx', 'zip', 'rar', 'jpg', 'png', 'gif', 'bmp', 'jpeg', 'xlsx', 'xls', 'pdf', 'txt');

		// если расширение не входит в список допустимых - return
		if(!in_array($mime, $types))
			return 'Недопустимый тип файла.';

		return true;
	};

	function make_upload($file, $id){
		// TODO: формируем уникальное имя файла
		$newname=translit($file['name']);
		/*
		$newname = urldecode( sanitize_title($newname) );
echo		$newname = preg_replace('/([^a-z\d\-\_])/', '', $newname);
*/
		copy($file['tmp_name'], 'upload/' . $newname);
		return $newname;
	};

	function translit($s) {
  $s = (string) $s; // преобразуем в строковое значение
  $s = strip_tags($s); // убираем HTML-теги
  $s = str_replace(array("\n", "\r"), " ", $s); // убираем перевод каретки
  $s = preg_replace("/\s+/", ' ', $s); // удаляем повторяющие пробелы
  $s = trim($s); // убираем пробелы в начале и конце строки
  $s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s); // переводим строку в нижний регистр (иногда надо задать локаль)
  $s = strtr($s, array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>''));
  $s = preg_replace("/[^0-9a-z-_\. ]/i", "", $s); // очищаем строку от недопустимых символов
  $s = str_replace(" ", "-", $s); // заменяем пробелы знаком минус
  return $s; // возвращаем результат
}



// печатает массив в виде таблицы
function print_table($header, $a) {
	$tmp= '<table width=70%>';
	$tmp.='<tr>';
	foreach($header as $col) {
		$tmp.= "<th align='left'>".$col."</th>";
	};
	$tmp.='</tr>';
	$tmp.='<tr>';
	for($i=0; $i<count($a); $i++) {
		$tmp.='<tr>';
		foreach($header as $col) {
			$tmp.= "<td>".$a[$i][$col]."</td>";
		};
		$tmp.='</tr>';
	};
	$tmp.= '</table>';
	return $tmp;
};

// печатает массив в виде таблицы
function print_table2($query, $con) {
	$v=array();	$res=mysqli_query($con, $query) or die(mysqli_error($con).' '.$query);
	while ($row=mysqli_fetch_array($res, MYSQLI_ASSOC)) {
		$v[]=$row;
	};
	return print_table(array_keys($v[0]), $v);
};


error_reporting(0);
?>