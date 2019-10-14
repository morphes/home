<?php

        $rootParent = $category->parent()->find();
        if(!$rootParent) {
                $rootParent = new Category();
                $rootParent->name = 'Корень';
                $rootParent->id = 1;
        }

        $this->breadcrumbs=array(
                'Каталог товаров'=>array('/catalog2/admin/category/index'),
                $rootParent->name=>array('/catalog2/admin/category/index', 'cid'=>$rootParent->id),
                'Список',
        );

	// Подключаем скрипт корзины и инициализируем кнопки добавления
	Yii::app()->clientScript->registerScriptFile('/js/admin/CCatGroupOperation.js');
	Yii::app()->clientScript->registerScript('group_operation', 'groupOperation.initAddButtons("op_cart_button");');


        Yii::app()->clientScript->registerScript('search', "
                $('#search-button').click(function(){
                        $('.search-form').toggle();
                        return false;
                });
                $('.search-form form').submit(function(){
                        $.fn.yiiGridView.update('product-grid', {
                                data: $(this).serialize()
                        });
                        return false;
                });
        ");
?>

<h1>Товары (<?php echo $category->name; ?>)</h1>

<?php echo CHtml::button('Расширенный поиск', array('class'=>'btn', 'id'=>'search-button', 'style'=>'margin-bottom: 10px;'));?>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
        'date_from'=>$date_from,
        'date_to'=>$date_to,
)); ?>
</div><!-- search-form -->

<div>
        <?php echo !empty($rootParent) ? CHtml::button("Вверх ↑ ", array('class'=>'btn','style'=>'float:left', 'onclick'=>'document.location = \''.$this->createUrl('/catalog2/admin/category/index/cid/' . @$rootParent->id).'\'')) : '';?>
        <?php echo CHtml::button('Добавить товары', array('class'=>'primary btn','style'=>'float:right; margin-left: 10px;', 'onclick'=>'document.location = \''.$this->createUrl('/catalog2/admin/product/create/', array('category_id'=>$category->id)).'\''))?>
        <?php echo !($category->productExists) ? CHtml::button('Добавить подкатегорию', array('class'=>'primary btn','style'=>'float:right;', 'onclick'=>'document.location = \''.$this->createUrl('/catalog2/admin/category/create/in/' . $category->id).'\'')) : ''; ?>

</div>

<div style="clear: both;"></div>

<?php $this->widget('ext.bootstrap.widgets.BootGridView', array(
	'id'=>'product-grid',
	'dataProvider'=>$dataProvider,
        'template'=>"{summary}\n{items}\n{pager}",
        'selectableRows'=> 2,
	'columns'=>array(
		'id',
                array(
                        'header'=>'Изображение',
                        'type'=>'raw',
                        'value'=>'isset($data->cover) ? CHtml::image("/".$data->cover->getPreviewName(Config::$preview["crop_60"]), "", array("width"=>60, "height"=>60)) : ""',
                ),
                array(
                        'header'=>'Производитель',
                        'type'=>'raw',
                        'value'=>'!empty($data->vendor->name) ? $data->vendor->name : ""',
                ),
                array(
                        'name' => 'name',
                        'type' => 'raw',
                        'value' => '($data->status == Product::STATUS_ACTIVE) ? CHtml::link($data->name, Product::getLink($data->id, null, $data->category_id)) : $data->name'
                ),
		//'barcode',
                array(
                        'header'=>'Статус',
                        'type'=>'raw',
                        'value'=>'Product::$statuses[$data->status]'
                ),
                array(
                        'header'=>'Дата создания',
                        'type'=>'raw',
                        'value'=>'date("d.m.Y H:i", $data->create_time);'
                ),
                array(
                        'header'=>'Дата изменения',
                        'type'=>'raw',
                        'value'=>'date("d.m.Y H:i", $data->update_time);'
                ),
		array(
			'name' => 'user_id',
			'type' => 'raw',
			'value' => 'User::model()->findByPk($data->user_id)->name'
		),
                array(
                        'class'=>'CButtonColumn',
                        'template'=>'{product_group_add} {product_group_delete} {update} {delete}',
                        'updateButtonUrl'=>'Yii::app()->createUrl("/catalog2/admin/product/update", array("ids"=>$data->id, "category_id"=>$data->category_id))',
			'buttons' => array(
				'product_group_add' => array(
					'label'    => 'Добавить в корзину',
					'url'      => '"/catalog2/admin/groupOperation/dealWithCart/id/".$data->id', // a PHP expression for generating the URL of the button
					'imageUrl' => '/img/admin/small/to_cart.png',
					'options'  => array('class' => 'op_cart_button'),
					'visible'  => ' ! GroupOperation::model()->exists("product_id = :pid", array(":pid" => $data->id)) ? true : false',
				),
				'product_group_delete' => array(
					'label'    => 'Убрать из корзины',
					'url'      => '"/catalog2/admin/groupOperation/dealWithCart/id/".$data->id', // a PHP expression for generating the URL of the button
					'imageUrl' => '/img/admin/small/in_cart.png',
					'options'  => array('class' => 'op_cart_button added_cart'),
					'visible'  => 'GroupOperation::model()->exists("product_id = :pid", array(":pid" => $data->id)) ? true : false',
				)
			),

                ),
	),
)); ?>

<script type="text/javascript">
        function update(){
                var products = $.fn.yiiGridView.getSelection("product-grid");
                if(products.length == 0) return;
                window.location = '<?php echo $this->createUrl('update', array('category_id'=>$category->id)); ?>/ids/'+products;
        }
        function remove(){
                var products = $.fn.yiiGridView.getSelection("product-grid");
                if(products.length == 0) return;
                if (confirm("Вы действительно хотите удалить выбранные товары?")) {
                        $.post("<?php echo $this->createUrl('delete'); ?>/id/"+products, function(response) {
                                location.reload(true);
                        }, "json");
                }
        }
</script>

<div>
        <?php echo CHtml::button('Редактировать выбранное', array('class'=>'btn primary','style'=>'float:right', 'onclick'=>'update();')); ?>
        <?php echo CHtml::button('Удалить выбранное', array('class'=>'btn danger','style'=>'float:left', 'onclick'=>'remove();')); ?>
</div>
