<?php
/** @var $data Tender */
$isClosed = $data->getIsClosed();
$class = $isClosed ? 'item closed' : 'item';
$class = $data->hasAccess() ? $class.' my_tender' : $class;
?>
<?php echo CHtml::openTag('div', array('class'=>$class, 'id'=>$data->id)); ?>
	<div class="descript">
		<?php echo CHtml::link($data->name, '/tenders/'.$data->id); ?>
		<?php echo CHtml::tag('p', array(), $data->cutDesc); ?>
		<span class="city"><?php echo $data->getCityName(); ?></span>
		<span class="date"><?php echo Yii::app()->getDateFormatter()->format('d MMMM в HH:mm', $data->create_time); ?></span>
	</div>
	<div class="cost"><?php echo ($data->cost_flag == Tender::COST_COMPARE) ? 'Не указан' : Yii::app()->numberFormatter->formatDecimal($data->cost) . ' руб'; ?></div>
	<div class="response"><span class="quant"><?php echo $data->response_count; ?></span></div>
	<div class="respond">
		<?php 
		if ($isClosed) { // Закрыт
			echo 'Заказ закрыт<br> '.Yii::app()->getDateFormatter()->format('d MMMM yyyy', $data->expire);
		} else if ( $data->getIsAuthor() ) { // Автор
			echo '<a class="red_link close_tender" href="#">Завершить</a> '
				.CHtml::link( 'Редактировать', $data->getEditLink() );
		} elseif ( $data->hasAccess() ) { // есть доступ
			echo '<a class="red_link close_tender" href="#">Завершить</a> ';
		} else if ($isGuest) { // Гость
			echo CHtml::checkBox('', false);
		} else if (!$isSpecialist) { // user
			echo CHtml::checkBox('', false, array('disabled'=>'disabled'));
		} else { // Подрядчик
			/** @var $response TenderResponse */
			$response = TenderResponse::model()->findByAttributes( array('tender_id'=>$data->id, 'author_id'=>Yii::app()->getUser()->getId()) );
			if (!is_null($response)) {
				echo '<a class="reject_tender" href="#">Отказаться от участия</a><br>'
					.CHtml::link('Мой отклик', $response->getLink(), array('class'=>'my_respond'));
			} else {
				echo CHtml::checkBox('');
			}
		} ?>
		
		<?php if ($isGuest) : ?>
			<div class="shadow_block tender_respond guest" style="display: none;">
				<span>Для участия в заказах вам необходимо <a href="#" class="-login">войти</a> или <a href="/site/registration" class="">зарегистрироваться</a></span>
			</div>
		<?php endif; ?>
	</div>
	<div class="clear"></div>
<?php echo CHtml::closeTag('div'); ?>