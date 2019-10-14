<?php
$this->breadcrumbs=array(
	'Помощь' => array('list'),
);
$this->breadcrumbs[] = Help::$baseNames[$baseId];
?>
<h1><?php echo Help::$baseNames[$baseId]; ?></h1>
<ul>
	<li><?php echo CHtml::link('Разделы', $this->createUrl('/help/admin/section/list', array('base'=>$baseId)) ); ?></li>
	<li><?php echo CHtml::link('Популярные вопросы', $this->createUrl('/help/admin/faq/list', array('base'=>$baseId)) ); ?></li>
</ul>
