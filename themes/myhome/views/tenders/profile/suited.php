<?php Yii::app()->clientScript->registerCssFile('/css/style.css'); ?>

<div class="portfolio_head ">

	<div class="btn_conteiner btn_project">
		<?php echo CHtml::link('Создать заказ <i></i>',
			'/tenders/create',
			array('class'=>'btn_grey add_tender')
		); ?>
	</div>
	<div class="clear"></div>
</div>
<div class="tenders_head">
	<div id="tabs">
		<span class="current"><a data-value="1" id="all_tenders" href="#">Все</a></span>
		<span><a data-value="2" id="opened_tenders" class="" href="#">Открытые</a></span>
		<span><a data-value="3" id="closed_tenders" class="" href="#">Завершенные</a></span>
	</div>
	<div class="cost">Бюджет</div>
	<div class="clear"></div>
</div>
<div class="tender_list">
	<?php $this->renderPartial('_tenderSuitedList', array('dataProvider'=>$dataProvider)); ?>
</div>
<div class="respond_conteiner">
	<div class="shadow_block tender_respond">
		<span>Комментарий к отклику</span>
		<textarea name="name" class="textInput " maxlength="255"></textarea>
		<span>Ориентировочная стоимость, руб.</span>
		<input type="text" class="textInput" maxlength="15">
		<div class="btn_conteiner">
			<a class="btn_grey">Откликнуться</a>
		</div>
	</div>
</div>