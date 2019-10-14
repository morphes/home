<?php $time = time(); ?>
<?php foreach ($dataProvider->getData() as $tender) : ?>
	<?php 
	$isClosed = $tender->getIsClosed();
	$class = $isClosed ? 'item closed' : 'item';
	?>
	<?php echo CHtml::openTag('div', array('class'=>$class, 'id'=>$tender->id)); ?>
		<div class="descript">
			<?php echo CHtml::link($tender->name, '/tenders/'.$tender->id); ?>
			<?php echo CHtml::tag('p', array(), $tender->cutDesc); ?>
			<span class="city"><?php echo $tender->getCityName(); ?></span>
			<span class="date"><?php echo Yii::app()->getDateFormatter()->format('d MMMM в HH:mm', $tender->create_time); ?></span>
		</div>
		<div class="cost">
			<?php if ($tender->cost_flag == Tender::COST_COMPARE) {
				echo 'Не указан';
			} else {
				echo CHtml::tag('span', array(), Yii::app()->numberFormatter->formatDecimal($tender->cost)). ' руб';
			}?>
		</div>
		<div class="response"><?php echo $tender->response_count; ?></div>
		<div class="respond">
			<?php 
			if ($isClosed) {
				echo 'Заказ закрыт<br> '.Yii::app()->getDateFormatter()->format('d MMMM yyyy', $tender->expire);
			} else {
				$hasResponse = TenderResponse::model()->exists('tender_id=:tid AND author_id=:uid', array(':tid'=>$tender->id, ':uid'=>Yii::app()->user->id));
				if ($hasResponse) {
					echo '<input class="added" type="checkbox" checked><br><a class="reject_tender" href="#">Отказаться от участия</a><br>'
						.CHtml::link('Мой отклик', "/tenders/{$tender->id}/", array('class'=>'my_respond'));
				} else {
					echo CHtml::checkBox('');
				}
			}
			?>
		</div>
		<div class="clear"></div>
	<?php echo CHtml::closeTag('div'); ?>
<?php endforeach; ?>