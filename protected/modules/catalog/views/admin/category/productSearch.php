<?php

$this->breadcrumbs=array(
        'Каталог товаров'=>array('/catalog/admin/category/index'),
        'Поиск',
);

// Подключаем скрипт корзины и инициализируем кнопки добавления
Yii::app()->clientScript->registerScriptFile('/js/admin/CCatGroupOperation.js');
Yii::app()->clientScript->registerScript('group_operation', 'groupOperation.initAddButtons("op_cart_button");');

Yii::app()->clientScript->registerScript('search', "
                $('#search-button').click(function(){
                        $('.search-form').toggle();
                        return false;
                });
        ");

?>

<h1>Результаты поиска по товарам</h1>

<?php echo CHtml::button('Расширенный поиск', array('class'=>'btn', 'id'=>'search-button', 'style'=>'margin-bottom: 10px;'));?>
<div class="search-form" style="display:none">
        <?php $this->renderPartial('_search',array(
		'model'      => $product,
		'date_from'  => $date_from,
		'date_to'    => $date_to,
		'bind_store' => $bind_store
	)); ?>
</div><!-- search-form -->


<div style="clear: both;"></div>

<?php $this->widget('ext.bootstrap.widgets.BootGridView', array(
        'id'=>'product-grid',
        'dataProvider'=>$dataProvider,
        'template'=>"{summary}\n{items}\n{pager}",
        'selectableRows'=> 1,
        'columns'=>array(
                'id',
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
                        'updateButtonUrl'=>'Yii::app()->createUrl("/catalog/admin/product/update", array("ids"=>$data->id, "category_id"=>$data->category_id))',
                        'deleteButtonUrl'=>'Yii::app()->createUrl("/catalog/admin/product/delete", array("id"=>$data->id))',
                        'buttons' => array(
                                'product_group_add' => array(
                                        'label'    => 'Добавить в корзину',
                                        'url'      => '"/catalog/admin/groupOperation/dealWithCart/id/".$data->id', // a PHP expression for generating the URL of the button
                                        'imageUrl' => '/img/admin/small/to_cart.png',
                                        'options'  => array('class' => 'op_cart_button'),
                                        'visible'  => ' ! GroupOperation::model()->exists("product_id = :pid", array(":pid" => $data->id)) ? true : false',
                                ),
                                'product_group_delete' => array(
                                        'label'    => 'Убрать из корзины',
                                        'url'      => '"/catalog/admin/groupOperation/dealWithCart/id/".$data->id', // a PHP expression for generating the URL of the button
                                        'imageUrl' => '/img/admin/small/in_cart.png',
                                        'options'  => array('class' => 'op_cart_button added_cart'),
                                        'visible'  => 'GroupOperation::model()->exists("product_id = :pid", array(":pid" => $data->id)) ? true : false',
                                )
                        ),
                ),
        ),
)); ?>