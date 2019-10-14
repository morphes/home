<?php
$this->pageTitle = 'Ошибка — MyHome.ru';
$this->breadcrumbs=array(
	'Error',
);
?>

<div class="inner">
	<h1><?php echo $title;?></h1>
	<p><?php $p = new CHtmlPurifier(); echo $p->purify($message);?></p>
	<p><a href="mailto:info@myhome.ru">info@myhome.ru</a></p>
</div>