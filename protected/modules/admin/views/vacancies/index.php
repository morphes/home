<?php
$this->breadcrumbs=array(
	'Вакансии'=>array('index'),
	'Список',
);

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
/*
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('vacancies-grid', {
		data: $(this).serialize()
	});
	return false;
});
*/
");
?>

<h1>Список вакансий</h1>


<?php echo CHtml::link('Фильтр поиска','#',array('class'=>'search-button')); ?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->

<div style="margin-top:15px;">
<?php echo CHtml::link('Добавить вакансию', '/admin/vacancies/create', array('class' => 'btn primary'));?>
</div>

<?php
$dragableItems = array();
$pathImg = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.modules.admin.assets'));
foreach($dataProvider->getData() as $data) {
	$data->actions = '
		<a class="view" title="Просмотреть" href="/admin/vacancies/view/id/'.$data->id.'"><img src="'.$pathImg.'/view.png" alt="Просмотреть"></a>
		<a class="update" title="Редактировать" href="/admin/vacancies/update/id/'.$data->id.'"><img src="'.$pathImg.'/update.png" alt="Редактировать"></a>
		<a class="delete" title="Удалить" onclick="if (confirm(\'Удалить запись?\')) return true; else return false;" href="/admin/vacancies/delete/id/'.$data->id.'"><img src="'.$pathImg.'/delete.png" alt="Удалить"></a>
	';
	$data->status = Vacancies::$nameStatus[$data->status];
	$data->update_time = date('d.m.Y H:i', $data->update_time);
	$dragableItems[ 'item_'.$data['id'] ] = $this->renderPartial('_sortableItem', array('data' => $data), true);
}
?>

<div style="margin-top:15px;"></div>

<div class="drag-vac-head">
	<?php
	// Выводим шапку для sortable таблицы
	$this->renderPartial('_sortableItem', array(
		'data' => Vacancies::model()->attributeLabels()
	));
	?>
</div>
<?php
// Выводим sortable таблицу
$this->widget('zii.widgets.jui.CJuiSortable', array(
	'items'=>$dragableItems,
	// additional javascript options for the accordion plugin
	'options'=>array(
		'update' => 'js:function(event, ui){
			var dragPosition = newDragPosition = nextPosition = prevPosition = id = 0;


			// Получаем номер позиции перетаскиваемого элемента
			dragPosition = parseInt( $(ui.item).find(".element-position").text() );
			id = parseInt( $(ui.item).find(".element-id").text() );
			
			// Полаучаем позицию следующего элемента
			var $nextElement = $(ui.item).next("li");
			if ($nextElement) {
				nextPosition = parseInt( $nextElement.find(".element-position").text() );
			}
			
			// Получаем позицию предыдущего элемента
			var $prevElement = $(ui.item).prev("li");
			if ($prevElement) {
				prevPosition = parseInt( $prevElement.find(".element-position").text() );
			}

			if ($nextElement.length && (dragPosition > nextPosition))
			{
				// Если переместили вверх
				$("ul.drag-vac li").each(function(index, elem){
					$posElem = $(elem).find(".element-position");
					var pos = parseInt( $posElem.text() );

					if (pos >= nextPosition && pos < dragPosition) {
						$posElem.text(pos+1);
					}
				});

				newDragPosition = nextPosition;
			}
			else if ( ! $nextElement.length || dragPosition < nextPosition)
			{
				// Если переместили вниз
				$("ul.drag-vac li").each(function(index, elem){
					$posElem = $(elem).find(".element-position");
					var pos = parseInt( $posElem.text() );

					if (pos > dragPosition && pos <= prevPosition) {
						$posElem.text(pos-1);
					}
				});

				newDragPosition = prevPosition;
			}

			// Сохраняем новый номер перетаскиваемой позиции
			$(ui.item).find(".element-position").text(newDragPosition);
			
			
			$.get(
				"/admin/vacancies/changeposition/id/"+id+"/pos/"+newDragPosition,
				function(response){
					if ( ! response.success)
						alert("Ошибка обновления позиции.");
				},
				"json"
			);
			
		}',
		
	),
	'htmlOptions' => array('class' => 'drag-vac')
));
?>
<div class="pagination">
	<?php $this->widget('ext.bootstrap.widgets.BootPager', array(
	'pages' => $pages,
	)); ?>
</div>