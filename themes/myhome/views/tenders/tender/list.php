<?php $this->pageTitle = 'Заказы — MyHome.ru'; ?>
<?php
// ----- ОПТИМИЗАЦИЯ ДЛЯ СОЦСЕТЕЙ -----

Yii::app()->openGraph->title = 'Заказы';
Yii::app()->openGraph->description = 'Заполните простую форму заказа и специалисты по ремонту и благоустройству дома сами найдут вас!';
Yii::app()->openGraph->image = Yii::app()->homeUrl.'/img/logo-tb.png';

Yii::app()->openGraph->renderTags();
?>

<div class="pathBar">
	<?php $this->widget('application.components.widgets.EBreadcrumbs', array(
		'links'=>array(),
	));?>
	
	<?php echo CHtml::tag('h1', array(), 'Заказы '.CHtml::tag( 'span', array('class'=>'section_items_count'), Tender::getTendersQuantity() ), true); ?>
	
	<div class="btn_conteiner btn_tenderlist">
		<?php echo CHtml::link('Создать заказ <i></i>',
			'/tenders/create/',
			array('class'=>'btn_grey')
		); ?>
	</div>

	<div class="spacer"></div>
</div>

<?php 
	$this->widget('tenders.components.TenderBar.TenderBar', array(
		'sortType' => $sortType,
		'pageSize' => $pageSize,
		'mainService' => $mainService,
		'childService' => $childService,
		'tenderType' => $tenderType,
		'cityId' => $cityId,
	));

	$this->widget('tenders.components.TenderList.TenderList', array(
		'dataProvider' => $dataProvider,
		'availablePageSizes' => Tender::$listPageSizes,
		'sortType' => $sortType,
		'pageSize' => $pageSize,
	));
?>
<div class="clear"></div>
<div class="spacer-30"></div>
