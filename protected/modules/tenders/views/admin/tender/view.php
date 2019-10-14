<?php 
	$cs = Yii::app()->clientScript;
	$cs->registerCoreScript('jquery');
?>

<?php
$this->breadcrumbs=array(
	'Заказы'=>array('/tenders/admin/tender/list'),
        'Просмотр заказа',
);
?>

<h1>Заказ #<?php /** @var $tender Tender */
	echo $tender->id;?> - "<?php echo $tender->name;?>"</h1>

<?php
$this->widget('ext.bootstrap.widgets.BootDetailView', array(
    'data'=>$tender,
    'attributes'=>array(
        array(
            'label'=>'Название',
            'type'=>'html',
            'value'=>"<b>".$tender->name."</b>",
        ),
        array(
            'label'=>'Автор',
            'type'=>'html',
            'value'=>is_null($tender->author_id) ? 'Гость' : CHtml::link($user->login.' ('.$user->name.')', $this->createUrl("/users/{$user->login}/")),
        ),
	array(
		'label'=>'Email',
		'type'=>'html',
		'value'=>$tender->getAuthorEmail(),
	),
	array(
	    'label'=>'Город',
	    'type'=>'html',
	    'value'=>"<b>".$tender->getCityName()."</b>",
	),
	array(
	    'label'=>'Количество отзывов',
	    'type'=>'html',
	    'value'=>$tender->response_count,
	),
	array(
	    'label'=>'Вознаграждение',
	    'type'=>'raw',
	    'value'=> ($tender->cost_flag==Tender::COST_COMPARE) ? 'Не указан' : Yii::app()->numberFormatter->formatDecimal($tender->cost).' руб.',
	),
	array(
            'label'=>'Открыт до',
            'type'=>'raw',
            'value'=>date('d.m.Y H:i', $tender->expire),
        ),
        array(
            'label'=>'Дата создания',
            'type'=>'raw',
            'value'=>date('d.m.Y H:i', $tender->create_time),
        ),
        array(
            'label'=>'Дата обновления',
            'type'=>'raw',
            'value'=>date('d.m.Y H:i', $tender->update_time),
        ),
        array(
            'label'=>'Статус',
            'type'=>'html',
            'value'=>"<span class='label success'>".Tender::$statusNames[$tender->status]."</span>",
        ),
	array(
	    'label'=>'Описание',
	    'type'=>'html',
	    'value'=>nl2br($tender->desc),
	),
    ),
));
?>

<div class="tender_services">
	<h3 class="h_block">Необходимые услуги</h3>
	<?php 
	$cnt = 0;
	foreach ($serviceList as $service) {
		if ($cnt > 0)
			echo ', ';
		echo $service->name;
		$cnt++;
	}
	?>
</div>

<?php if (!empty($files)) : ?>
<div class="tender_response">
	<h3>Дополнительные материалы</h3>
	<table>
		<tbody>
		<?php foreach ($files as $file) { ?>
			<tr>
				<th><?php echo CHtml::link($file['name'].'.'.$file['ext'], Yii::app()->controller->createUrl('/download/tenderfile/', array('id'=>$file['id']))); ?></th>
				<td><?php echo $file['desc']; ?></td>
				<th><?php echo CFormatterEx::formatFileSize($file['size']); ?></th>
			</tr>
			
		<?php } ?>
		</tbody>
	</table>
</div>
<?php endif; ?>


<div class="actions">

        <?php echo CHtml::button('Редактировать', array('class'=>'primary btn',
                'onclick' => "document.location='{$this->createUrl('/tenders/admin/tender/update', array('id' => $tender->id))}'"
        )); ?>
</div>
