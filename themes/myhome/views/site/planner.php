<?php
$this->pageTitle = 'Онлайн-планировщик — MyHome.ru';
?>

<div class="pathBar">

	<?php $this->widget('application.components.widgets.EBreadcrumbs', array(
		'links'=>array(
		),
		'encodeLabel' => false
	));?>

	<h1>Онлайн-планировщик</h1>
	<div class="spacer"></div>
</div>


<div style="height: 700px;" class="loading -gutter-bottom-dbl">
	<iframe  src="https://planner5d.com/app/?theme=gray&lang=ru" style="width:940px;height:700px;border:none;"></iframe>
</div>


<?php
// Яндекс.Директ
Yii::app()->controller->renderPartial('//widget/profitpartner/profit_partner_item_under');
?>
