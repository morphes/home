<?php

// Подключаем скрипт корзины и инициализируем кнопки добавления
Yii::app()->clientScript->registerScriptFile('/js/admin/CCatGroupOperation.js');
Yii::app()->clientScript->registerScript('group_operation', 'groupOperation.initAddButtons("op_cart_button");');


$this->breadcrumbs=array(
	'Каталог товаров'=>array('/catalog/admin/category/index'),
	'Групповые операции'=>array('index'),
	'Список товаров',
);
?>

<h1>Список товаров по категориям для групповых операций</h1>


<?php
$lastCategory = 0;
for ($i = 0, $ci = count($models); $i < $ci; $i++)
{
	$model = $models[$i];

	if ($model->category_id != $lastCategory) {
		$linkCategory =  CHtml::link($model->category->name, '/catalog/admin/product/index/cid/'.$model->category_id);
		echo CHtml::tag('h3', array(), $linkCategory);
		echo CHtml::openTag('ul');
	}


	$linkImg =
		'<a class="op_cart_button added_cart" title="Убрать из корзины" href="/catalog/admin/groupOperation/dealWithCart/id/'.$model->product_id.'">
		<img src="/img/admin/small/in_cart.png" alt="Убрать из корзины">
		</a>';
	$linkProduct = CHtml::link($model->product->name, '/catalog/admin/product/update/category_id/'.$model->category_id.'/ids/'.$model->product_id);
        echo CHtml::tag('li', array(), $linkImg.' '.($model->product->vendor ? $model->product->vendor->name : "").': '.$linkProduct);


	if (
		(($i < $ci - 1) && $models[$i+1]->category_id != $model->category_id)
	    	||
		($i == $ci - 1)
	) {

		echo CHtml::closeTag('ul');


		if ($category_id == $model->category_id && ! empty($result))
			echo '<div class="alert-message warning span6">'.$result.'</div>';

		echo CHtml::link('действия', '#', array('class' => 'action_link', 'onclick' => '$(this).next("form").toggle(); return false;'));

		echo CHtml::openTag('form',array('action' => '', 'method' => 'post', 'class' => 'action_form'));

		echo CHtml::hiddenField('category_id', $model->category_id);


		echo CHtml::submitButton('Сделать аналогичными', array('name' => 'similar', 'class' => 'btn success'));

		echo '<br><br>';
		echo CHtml::dropDownList('new_status', $new_status, array(''=>'') + Product::$statuses);
		echo '&nbsp;';
		echo CHtml::submitButton('Сменить статус', array('name' => 'set_status', 'class' => 'btn primary'));

		echo '<br><br>';
		echo CHtml::submitButton('Редактировать', array('name' => 'redirect_edit', 'class' => 'btn info'));

		echo '<br><br>';
		echo CHtml::submitButton('Очистить группу', array('name' => 'clear_group', 'class' => 'btn'));

		echo CHtml::closeTag('form');


		echo CHtml::tag('div', array('style' => 'margin-bottom: 25px;'), '');
	}


	$lastCategory = $model->category_id;
}
?>

