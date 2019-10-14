<!DOCTYPE HTML>
<html>
<head>
<title><?php echo CHtml::encode($this->pageTitle); ?></title>
<meta charset="utf-8">
<meta name="description" content="<?php echo CHtml::encode($this->description); ?>" />
<meta name="keywords" content="<?php echo CHtml::encode($this->keywords); ?>" />
<link rel="stylesheet" href="/css/style.css">
<style>
	html, body { height: 100%; }
</style>
</head>

<body>
	

	<?php echo $content; ?>
<!-- Yandex.Metrika counter --> <script type="text/javascript" > (function(m,e,t,r,i,k,a){m[i]=m[i]||function(){(m[i].a=m[i].a||[]).push(arguments)}; m[i].l=1*new Date();k=e.createElement(t),a=e.getElementsByTagName(t)[0],k.async=1,k.src=r,a.parentNode.insertBefore(k,a)}) (window, document, "script", "https://mc.yandex.ru/metrika/tag.js", "ym"); ym(42935859, "init", { id:42935859, clickmap:true, trackLinks:true, accurateTrackBounce:true, webvisor:true, trackHash:true }); </script> <noscript><div><img src="https://mc.yandex.ru/watch/42935859" style="position:absolute; left:-9999px;" alt="" /></div></noscript> <!-- /Yandex.Metrika counter -->
</body>
</html>
