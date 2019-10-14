<?php
$this->breadcrumbs=array(
	'Управление почтой' => array('index'),
	'Шаблоны сообщений' => array('index'),
	'Просмотр'
);
?>


<h1>Просмотр шаблона #<?php echo $template->key; ?></h1>

<?php
$this->widget('ext.bootstrap.widgets.BootDetailView', array(
	'data' => $template,
	'htmlOptions' => array('class' => 'zebra-striped'),
	'attributes' => array(
		'key',
		'name',
		'subject',
                'author',
		'from',
		'keywords',
		'data:html',
		array(
			'label' => $template->getAttributeLabel('create_time'),
			'value' => date("d.m.Y", $template->create_time),
		),
		array(
			'label' => $template->getAttributeLabel('update_time'),
			'value' => date("d.m.Y", $template->update_time),
		),
	),
));
?>

<div class="actions">
	<?php echo CHtml::link('Редактировать', array('update', 'key' => $template->key), array('class' => 'btn primary'));?>
</div>