<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php
error_reporting(0);
$filename="users.csv";
$f = fopen($filename,"r");

while (!feof ($f))
    {
        $buf = fgets($f, 4096);

        if ($buf!="") {
        	$pr=explode("|",$buf);
	        $id=(int)$pr[0];
		$arr[$id]=array('name' => $pr[1], 'desc' => $pr[2], 'url' => str_replace("\r\n","",$pr[3]));
        }
    }
if ((int)$_GET["uid"]==0) {	echo '
	<title>Список пользователей</title>
	<script src="jquery.min.js" type="text/javascript"></script>
	<script type="text/javascript">
$(document).ready(function() {
	$("#search").keyup(function() {	var l=$(this).val().length;
	var txt=$(this).val();
	var reg = new RegExp(txt, "ig");
	if (l>2) {
	$("li").hide();
	$("li").each(function() {
		var str=$("a",this).html();
		if (reg.test(str)) {
 			$(this).show();
		}

		});
	} else {$("li").show();}
return true;
	});
});
	</script>
    <meta name="robots" content="noindex, nofollow" />
	</head>
	<body>	<p>Фильтр по фразе: <input type="text" id="search"></p>';
	echo "<ul>";
	foreach($arr as $key=>$val) {echo '<li><a href="?uid='.$key.'">'.$val["name"].'</a></li>';}
	echo "</ul>";
} else {
	$uid=(int)$_GET["uid"];
	if (isset($arr[$uid])) {		echo '
	<title>'.$arr[$uid]["name"].'</title>
	<meta name="robots" content="noindex, nofollow" />
	</head>
	<body>	';


		echo "<h1>{$arr[$uid]["name"]}</h1>
			<p>Чтобы вставить код....</p>";
			$opismyhome_arr=array('интернет портал о ремонте, дизайне интерьера и благоустройстве',
			'интернет портал о дизайне интерьера, ремонте и благоустройстве',
			'сайт о ремонте, дизайне интерьера и благоустройстве ',
			'сайт о дизайне интерьера, ремонте и благоустройстве',
			'идеи интерьера, полезные советы по ремонту квартиры, благоустройству');
			$opismyhome=$opismyhome_arr[rand(0,(count($opismyhome_arr)-1))];
		for($i=1;$i<=6;$i++){

echo '
<h2>Код кнопки №'.$i.':</h2>
<div style="background: #fff url(http://www.myhome.ru/img/logos/exp-'.$i.'.png) no-repeat left top;display: block;overflow: hidden;height: 31px;width: 87px;text-indent: -10000px;"><a href="http://www.myhome.ru/" title="MyHome.ru" target="_blank" style="height:31px;width:30px;display:block;float:left;">MyHome.ru: '.$opismyhome.'</a><a href="'.$arr[$uid]["url"].'" title="профиль специалиста" target="_blank" style="height: 31px;width: 57px;display:block;float:right;">'.$arr[$uid]["desc"].'</a></div>
<textarea cols="100" rows="10" readonly="readonly" onclick="this.select()"><div style="background: #fff url(http://www.myhome.ru/img/logos/exp-'.$i.'.png) no-repeat left top;display: block;overflow: hidden;height: 31px;width: 87px;text-indent: -10000px;"><a href="http://www.myhome.ru/" title="MyHome.ru" target="_blank" style="height:31px;width:30px;display:block;float:left;">MyHome.ru: '.$opismyhome.'</a><a href="'.$arr[$uid]["url"].'" title="профиль эксперта" target="_blank" style="height: 31px;width: 57px;display:block;float:right;">'.$arr[$uid]["desc"].'</a></div></textarea>
';

		}
	}

}

?>
</body>
</html>