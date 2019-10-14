<?php

$rootParent = $root->parent()->find();
if(!$rootParent) {
        $rootParent = new Category();
        $rootParent->name = 'Корень';
        $rootParent->id = 1;
}

if($root->name == 'root')
        $root->name = 'Корень';

$this->breadcrumbs=array(
        'Каталог товаров'=>array('index'),
        $rootParent->name=>array('index', 'cid'=>$rootParent->id),
	'Список'
);
Yii::app()->clientScript->registerScript('search', "
                $('#search-button').click(function(){
                        $('.search-form').toggle();
                        return false;
                });
        ");
?>

<h1><?php echo $root->name; ?></h1>

<?php echo CHtml::button('Расширенный поиск', array('class'=>'btn', 'id'=>'search-button', 'style'=>'margin-bottom: 10px;'));?>
<div class="search-form" style="display:none">
        <?php $this->renderPartial('_search',array(
		'model'      => $product,
		'date_from'  => $date_from,
		'date_to'    => $date_to,
		'bind_store' => $bind_store
        )); ?>
</div><!-- search-form -->

<div>
        <?php echo !empty($rootParent) ? CHtml::button("Вверх ↑ ", array('class'=>'btn','style'=>'float:left', 'onclick'=>'document.location = \''.$this->createUrl('/catalog2/admin/category/index/cid/' . @$rootParent->id).'\'')) : '';?>

        <?php echo CHtml::button('Добавить подкатегорию', array('class'=>'primary btn','style'=>'float:right', 'onclick'=>'document.location = \''.$this->createUrl('/catalog2/admin/category/create/in/' . $root->id).'\''))?>

	<?php echo CHtml::link('Статистика', '#', array('class'=>'btn show_product_stat','style'=>'float:right; margin-right: 10px;'));?>
</div>
<div style="clear: both;"></div>
<div style="margin-top: 10px;">
        Товаров в категории: <strong><?php echo $root->descendantsProductQt; ?></strong>
</div>

<script type="text/javascript">
	$(document).ready(function(){

		$('.show_product_stat').click(function(){
			$('#product_stat').slideToggle();
		});

		$('form.form_stat').submit(function(){
			var $tbody = $('table.stat_result tbody');
			$tbody.html('');
                        var total_cnt = 0;

			$.get(
				'/catalog2/admin/product/getStatProduct',
				{
					stat_from: $("#stat_from").val(),
					stat_to: $('#stat_to').val()
				},
				function(data) {

					if (data.length > 0) {
						for (var i = 0, ci = data.length; i < ci; i++) {
							$tbody.append('<tr>' +
								'<td>'+(i+1)+'</td>' +
								'<td>'+data[i].user_name+'</td>' +
								'<td>'+data[i].cnt+'</td>' +
								'</tr>');
                                                        total_cnt = total_cnt + data[i].cnt;
						}
                                                $tbody.append('<tr><td></td><td>ИТОГО</td><td>'+total_cnt+'</td></tr>');
					} else {
						alert('Нет результатов');
					}

				}, 'json'
			);

			return false;
		});
	});
</script>

<div class="well" id="product_stat" style="display: none;">

	<h4 style="margin-bottom: 18px;">Статистика по кол-ву добавлений товаров разными модераторами за период</h4>

	<div class="row">
		<div class="span7">
			<form action="" class="form_stat">
				<div class="clearfix">
					<?php echo CHtml::label('От', 'date_from')?>
					<div class="input">
						<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
						'name'=>'stat_from',
						'value'	=> date('d.m.Y'),
						'language'	=> 'ru',
						'options'=>array('dateFormat'=>'dd.mm.yy'),
						'htmlOptions'=>array(
							'style'=>'width:150px;'
						),
					));?>
					</div>
				</div>


				<div class="clearfix">
					<?php echo CHtml::label('До', 'date_to')?>
					<div class="input">
						<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
						'name'=>'stat_to',
						'value'=> date('d.m.Y'),
						'language'	=> 'ru',
						'options'=>array('dateFormat'=>'dd.mm.yy'),
						'htmlOptions'=>array(
							'style'=>'width:150px;'
						),
					));?>
					</div>
				</div>

				<div class="actions">
					<?php echo CHtml::submitButton('Показать', array('class' => 'btn large get_stat'));?>
				</div>

			</form>
		</div>
		<div class="span10">
			<table class="bordered-table zebra-striped stat_result">
				<thead>
				<tr>
					<th>#</th>
					<th>Автор</th>
					<th>Кол-во добавленных продуктов</th>
				</tr>
				</thead>
				<tbody>

				</tbody>
			</table>
		</div>
	</div>
</div>

<?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'category-grid',
	'dataProvider'=>$dataProvider,
        'template'=>'{items}',
	'columns'=>array(
		array(
                        'name'=>'Категория',
                        'type'=>'raw',
                        'value'=>'CHtml::link($data->name, "/catalog2/admin/category/index/cid/" . $data->id)',
                ),
                array(
                        'name'=>'Подкатегорий',
                        'type'=>'raw',
                        'value'=>'$data->children()->count()',
                        'htmlOptions'=>array('width'=>'100px'),
                ),
		array(
			'class'=>'CButtonColumn',
                        'htmlOptions'=>array('width'=>'130px'),
                        'buttons'=>array(
				'settings' => array(
					'label'=>'Настройки',
					'options'=>array('title'=>'Настройки'),
					'url'=>'Yii::app()->createUrl("/catalog2/admin/category/settings/", array("id"=>$data->id))',
					'imageUrl'=>'/img/admin/small/settings.png',
				),
                                'sort' => array(
                                        'label'=>'сорт.',
                                        'options'=>array('title'=>'Сортировать опции и значения категории'),
                                        'url'=>'Yii::app()->createUrl("/catalog2/admin/category/sort/", array("id"=>$data->id))',
					'imageUrl'=>'/img/admin/small/sort.png',
                                ),
                                'catUp' => array(
                                        'label'=>'⇑',
                                        'options'=>array('title'=>'Переместить выше', 'style'=>'font-size: 14pt;'),
                                        'url'=>'Yii::app()->createUrl("/catalog2/admin/category/move/", array("cid"=>$data->id, "to"=>"up"))',
                                ),
                                'catDown' => array(
                                        'label'=>'⇓',
                                        'options'=>array('title'=>'Переместить ниже', 'style'=>'font-size: 14pt;'),
                                        'url'=>'Yii::app()->createUrl("/catalog2/admin/category/move/", array("cid"=>$data->id, "to"=>"down"))',
                                ),
                        ),
                        'template'=>'{sort} {catUp} {catDown} {settings} {update} {delete}',
		),
	),
)); ?>
