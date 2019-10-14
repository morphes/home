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
if ((int)$_GET["uid"]==0) {
	echo '
	<title>Список пользователей</title>
	<script src="jquery.min.js" type="text/javascript"></script>
	<script type="text/javascript">
$(document).ready(function() {
	$("#search").keyup(function() {
	var l=$(this).val().length;
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
	if (isset($arr[$uid])) {
		echo '
	<title>'.$arr[$uid]["name"].'</title>
	<meta name="robots" content="noindex, nofollow" />
	</head>
	<body>	';


		echo "
		<script type=\"text/javascript\">
	var _gaq = _gaq || [];
	_gaq.push(['_setAccount', 'UA-26286775-1']);
	_gaq.push(['_addOrganic', 'images.yandex.ru',  'q', true]);
	_gaq.push(['_addOrganic', 'blogsearch.google.ru',  'q', true]);
	_gaq.push(['_addOrganic', 'blogs.yandex.ru',  'text', true]);
	_gaq.push(['_addOrganic', 'go.mail.ru',  'q']);
	_gaq.push(['_addOrganic', 'nova.rambler.ru',  'query']);
	_gaq.push(['_addOrganic', 'nigma.ru', 's']);
	_gaq.push(['_addOrganic', 'webalta.ru', 'q']);
	_gaq.push(['_addOrganic', 'aport.ru', 'r']);
	_gaq.push(['_addOrganic', 'poisk.ru', 'text']);
	_gaq.push(['_addOrganic', 'km.ru', 'sq']);
	_gaq.push(['_addOrganic', 'liveinternet.ru', 'ask']);
	_gaq.push(['_addOrganic', 'quintura.ru', 'request']);
	_gaq.push(['_addOrganic', 'search.qip.ru', 'query']);
	_gaq.push(['_addOrganic', 'gde.ru', 'keywords']);
	_gaq.push(['_addOrganic', 'gogo.ru', 'q']);
	_gaq.push(['_addOrganic', 'ru.yahoo.com', 'p']);
	_gaq.push(['_addOrganic', 'poisk.ngs.ru', 'q']);
	_gaq.push(['_addOrganic', 'meta.ua', 'q']);
	_gaq.push(['_addOrganic', 'bigmir.net', 'q']);
	_gaq.push(['_addOrganic', 'i.ua', 'q']);
	_gaq.push(['_addOrganic', 'online.ua', 'q']);
	_gaq.push(['_addOrganic', 'a.ua', 's']);
	_gaq.push(['_addOrganic', 'ukr.net', 'search_query']);
	_gaq.push(['_addOrganic', 'search.com.ua', 'q']);
	_gaq.push(['_addOrganic', 'search.ua', 'query']);
	_gaq.push(['_addOrganic', 'search.ukr.net', 'search_query']);

			_gaq.push(['_setCustomVar',1,'User','Guest',2]);

	_gaq.push(['_trackPageview']);

	(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	})();
	// Yandex.Metrika counter
	(function (d, w, c) {
		(w[c] = w[c] || []).push(function() {
			try {
				w.yaCounter11382007 = new Ya.Metrika({id:11382007, enableAll: true, trackHash:true, webvisor:true});
			} catch(e) {}
		});

		var n = d.getElementsByTagName(\"script\")[0],
			s = d.createElement(\"script\"),
			f = function () { n.parentNode.insertBefore(s, n); };
		s.type = \"text/javascript\";
		s.async = true;
		s.src = (d.location.protocol == \"https:\" ? \"https:\" : \"http:\") + \"//mc.yandex.ru/metrika/watch.js\";

		if (w.opera == \"[object Opera]\") {
			d.addEventListener(\"DOMContentLoaded\", f);
		} else { f(); }
	})(document, window, \"yandex_metrika_callbacks\");
</script>
<noscript><div><img src=\"//mc.yandex.ru/watch/11382007\" style=\"position:absolute; left:-9999px;\" alt=\"\" /></div></noscript>
<!-- /Yandex.Metrikacounter -->
		<h1>{$arr[$uid]["name"]}</h1>
			<p>Чтобы вставить кнопку на сайте, Вам необходимо выбрать кнопку, скопировать ее код (окошко под кнопкой) и вставить этот код в шаблон(ы) сайта в любое подходящее место перед закрывающим тегом &lt;/BODY&gt;. Вставлять код кнопки необходимо непосредственно в HTML-код сайта.</p>";
			$opismyhome_arr=array('интернет портал о ремонте, дизайне интерьера и благоустройстве',
			'интернет портал о дизайне интерьера, ремонте и благоустройстве',
			'сайт о ремонте, дизайне интерьера и благоустройстве ',
			'сайт о дизайне интерьера, ремонте и благоустройстве',
			'идеи интерьера, полезные советы по ремонту квартиры, благоустройству');
			$opismyhome=$opismyhome_arr[rand(0,(count($opismyhome_arr)-1))];
		for($i=1;$i<=6;$i++){

echo '
<h2>Код кнопки №'.$i.':</h2>
<div style="background:url(http://www.myhome.ru/img/logos/spc-'.$i.'.png) no-repeat left top;display: block;overflow: hidden;height: 31px;width: 87px;text-indent: -10000px;"><a href="http://www.myhome.ru/" title="MyHome.ru" target="_blank" style="height:31px;width:30px;display:block;float:left;">MyHome.ru: '.$opismyhome.'</a><a href="'.$arr[$uid]["url"].'" title="профиль специалиста" target="_blank" style="height: 31px;width: 57px;display:block;float:right;">'.$arr[$uid]["desc"].'</a></div>
<textarea cols="100" rows="10" readonly="readonly" onclick="this.select()"><div style="background: #fff url(http://www.myhome.ru/img/logos/spc-'.$i.'.png) no-repeat left top;display: block;overflow: hidden;height: 31px;width: 87px;text-indent: -10000px;"><a href="http://www.myhome.ru/" title="MyHome.ru" target="_blank" style="height:31px;width:30px;display:block;float:left;">MyHome.ru: '.$opismyhome.'</a><a href="'.$arr[$uid]["url"].'" title="профиль специалиста" target="_blank" style="height: 31px;width: 57px;display:block;float:right;">'.$arr[$uid]["desc"].'</a></div></textarea>
		';

		}
	}

}

?>
</body>
</html>