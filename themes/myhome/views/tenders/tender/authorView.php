<div class="pathBar">
	<?php
	$this->widget('application.components.widgets.EBreadcrumbs', array(
		'links' => array(
			'Заказы' => array('/tenders/list/'),
		),
	));
	?>
	<h1><?php /** @var $tender Tender */
		echo $tender->name;
	?></h1>

	<div class="spacer"></div>
	<div class="tender_head_info">
		<span class="tender_date"><?php echo Yii::app()->getDateFormatter()->format('Размещен d MMMM yyyy в HH:mm', $tender->create_time); ?></span>   |   <span class="view_counter"><?php echo CFormatterEx::formatNumeral($viewsCount, array('просмотр', 'просмотра', 'просмотров')); ?></span>
	</div>
</div>
<div id="left_side">
	<div class="shadow_block">
		<div class="tender_autor">
			<div class="coautor">
				<div class="coautor_photo">
					<?php echo CHtml::image('/'.$tender->getAuthorPreview( Config::$preview['crop_23'] ), '', array('width'=>23, 'height'=>23)); ?>
				</div>

				<div class="coautor_info">
					<span class="organizer">Организатор заказа</span>
					<?php if ( !empty($tender->author_id)) : ?>
						<?php echo CHtml::link($tender->getAuthorName(), $tender->getAuthorUrl()); ?>
					<?php else: ?>
						<?php echo CHtml::tag('p', array(), $tender->getAuthorName()); ?>
					<?php endif; ?>
				</div>
				<div class="clear"></div>
				
			</div>
			<div class="clear"></div>
		</div>
		<div class="tender_params">
			<div class="param_conteiner">
				<div class="param">
					<span>Бюджет</span>
					<p><?php echo ($tender->cost_flag==Tender::COST_COMPARE) ? 'Не указан' : Yii::app()->numberFormatter->formatDecimal($tender->cost).' руб.'; ?></p>
				</div>
				<div class="param">
					<span>Отклики принимаются до</span>
					<p><?php echo Yii::app()->getDateFormatter()->format('d MMMM yyyy', $tender->expire); ?></p>
				</div>
			</div>
		</div>
		
		<?php if ($isClosed) : ?>
		<div class="responds tender_closed">
			<i></i><span class="allready_respond">Заказ закрыт</span>
		</div>
		<?php else : ?>
		<div class="responds">
			<i></i><span><?php 
				$cnt = $responseProvider->getTotalItemCount();
				if ($cnt == 0) {
					echo 'Откликов нет';
				} else {
					echo CFormatterEx::formatNumeral($cnt, array('Получен ', 'Получено ', 'Получено '), true)
					.CFormatterEx::formatNumeral($cnt, array('отклик', 'отклика', 'откликов')); 
				}
			?></span>
		</div>
		<div data-value="<?php echo $tender->id; ?>" class="btn_conteiner tender_add_button red">
			<a href="#" class="btn_grey">Закрыть заказ</a>
		</div>
		<?php if ($tender->getIsAuthor()) : ?>
		<div class="edit_tender">
			<?php echo CHtml::link('<i></i>Редактировать заказ', $tender->getEditLink()); ?>
		</div>
		<?php endif; ?>
		<?php endif; ?>
		
		<div class="spacer-18"></div>
	</div>
</div>
<div id="right_side">
	<div class="shadow_block padding-18">
		<div class="tender_descript">
			<h3 class="h_block">Описание</h3>
			<p><?php echo nl2br($tender->desc); ?></p>
		</div>

		<div class="tender_services">
			<h3 class="h_block">Необходимые услуги</h3>
			<?php 
			$cnt = 0;
			foreach ($serviceList as $service) {
				if ($cnt > 0)
					echo ', ';
				echo CHtml::link($service->name, '/tenders/list/?child_service='.$service->id);
				$cnt++;
			}
			?>
		</div>

		<div class="tender_city">
			<h3 class="h_block">Город</h3>
			<p><?php echo $tender->getCityName(); ?></p>
		</div>
		<div class="clear"></div>

		<?php if (!empty($files)) : ?>
		<h5 class="block_headline">Дополнительные материалы</h5>
		<div class="image_uploaded tender_page">
			<?php foreach ($files as $file) { ?>

				<div class="uploaded_files">
					<div class="uploaded_files_name image">
						<i></i>
						<?php echo CHtml::link($file['name'].'.'.$file['ext'], Yii::app()->controller->createUrl('/download/tenderfile/', array('id'=>$file['id']))); ?>
					</div>
					<div class="uploaded_files_description">
						<?php echo $file['desc']; ?>
					</div>
					<div class="uploaded_files_filesize">
						<?php echo CFormatterEx::formatFileSize($file['size']); ?>
					</div>
					<div class="clear"></div>
				</div>

			<?php } ?>

		</div>
		<?php endif; ?>
	</div>
	<div class="spacer-30"></div>
	<?php if ($responseProvider->getTotalItemCount() > 0) : ?>
	<div class="tender_comments tender_autor_layer">
		<h5 class="block_headline"><?php echo CFormatterEx::formatNumeral($responseProvider->getTotalItemCount(), array('отклик', 'отклика', 'откликов')); ?></h5>
		<span class="budget_head">Стоимость</span>
		<span class="phone_head">Телефон</span>
		<?php /** @var $response TenderResponse */
		foreach ($responseProvider->getData() as $response) : ?>
			<?php $responseUser = $response->user; 
				if (is_null($responseUser))
					continue;
			?>
			<div class="tender_comment " id="<?php echo $response->getHash(); ?>">
				<div class="item_head">
					<?php echo CHtml::image('/'.$responseUser->getPreview( Config::$preview['crop_23'] ), '', array('width'=>23, 'height'=>23)); ?>
					<?php echo CHtml::link($responseUser->name, "/users/{$responseUser->login}/", array('class'=>'post_author_name')); ?>
					<span class="post_date"><?php echo Yii::app()->getDateFormatter()->format('d MMMM в HH:mm', $response->create_time); ?></span>
				</div>
				<div class="tender_comment_text">
					<?php if (mb_strlen($response->content, 'utf-8') <= 200) : ?>
						<span><?php echo nl2br($response->content); ?></span>
					<?php else : ?>
						<span><?php echo nl2br(Amputate::getLimb($response->content, 200, '')); ?></span>
						<span class="hide"><?php echo nl2br($response->content); ?></span>
						<a href="#" class="show_more">раскрыть</a>
					<?php endif; ?>
				</div>
				<div class="tender_comment_budjet"><?php echo $response->cost; ?></div>
				<div class="tender_comment_tel"><?php echo $responseUser->getUserPhone(); ?><!--<a class='guest' href="#">Отправить сообщение</a>--></div>
				<div class="clear"></div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

</div>
<div class="clear"></div>
<div class="spacer-30"></div>