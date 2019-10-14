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
					<span>Организатор заказа</span>
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
			<i></i><?php 
				
				if ($hasResponse) {
					echo CHtml::tag('span', array('class'=>'allready_respond'), 'Вы откликнулись');
				} else {
					$cnt = $responseProvider->getTotalItemCount();
					echo CHtml::openTag('span');
					if ($cnt == 0) {
						echo 'Откликов нет';
					} else {
						echo CFormatterEx::formatNumeral($cnt, array('Получен ', 'Получено ', 'Получено '), true)
						.CFormatterEx::formatNumeral($cnt, array('отклик', 'отклика', 'откликов')); 
					}
					echo CHtml::closeTag('span');
				}		
			?>
		</div>
			<?php if (in_array(Yii::app()->user->role, array(User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR, User::ROLE_POWERADMIN)) ) { ?>
			<div data-value="<?php echo $tender->id; ?>" class="btn_conteiner tender_add_button <?php echo $hasResponse ? 'hide' : ''; ?>">
				<a href="#" class="btn_grey">Откликнуться на заказ</a>
			</div>
			<?php } ?>
		
		<?php endif; ?>
		<div class="spacer-18"></div>
	</div>



	<?php if (!$isClosed) : ?>
	<div class="tender_respond tender_page">
		<i></i>
		<div class="row">
			<span>Комментарий к отклику</span>
			<textarea name="name" class="textInput tender_page" maxlength="1000"></textarea>
		</div>
		<div class="row">
			<span>Ориентировочная стоимость</span>
			<input type="text" class="textInput" maxlength="15">
			<span class="currency">руб.</span>
		</div>

		<div class="btn_conteiner">
			<a class="btn_grey">Откликнуться</a>
		</div>
	</div>
	<?php endif; ?>
	<div style="margin-top: 15px;">
		<?php
		// Яндекс.Директ
		$this->renderPartial('//widget/google/adsense_160x600_tender_view');
		?>
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
	<div class="spacer-18"></div>
	<div style="margin-top: 15px;">
        <?php
		// Яндекс.Директ
        Yii::app()->controller->renderPartial('//widget/profitpartner/profit_partner_item_above');
		?>
	</div>
	<div class="padding-18 tender_comments <?php echo ($responseProvider->getTotalItemCount() == 0) ? 'hide' : ''; ?>">
		<h5 class="block_headline"><?php echo CFormatterEx::formatNumeral($responseProvider->getTotalItemCount(), array('отклик', 'отклика', 'откликов')); ?></h5>
		
		<?php if ($responseProvider->getTotalItemCount() > 0) : ?>
		<?php //<span class="phone_head">Телефон</span> ?>
		<?php /** @var $response TenderResponse */
		foreach ($responseProvider->getData() as $response) : ?>
			<?php $responseUser = $response->user; 
				if (is_null($responseUser)) {
					continue;
				}
				$isAuthor = $response->author_id == Yii::app()->user->id;
			?>
			<div class="tender_comment " id="<?php echo $response->getHash(); ?>" data-value="<?php echo $tender->id; ?>">
				<div class="item_head">
					<?php echo CHtml::image('/'.$responseUser->getPreview( Config::$preview['crop_23'] ), '', array('width'=>23, 'height'=>23)); ?>
					<?php echo CHtml::link($responseUser->name, "/users/{$responseUser->login}/", array('class'=>'post_author_name')); ?>
					<span class="post_date"><?php echo Yii::app()->getDateFormatter()->format('d MMMM в HH:mm', $response->create_time); ?></span>
				</div>
				<div class="tender_comment_text">
					<?php
					if ($isAuthor) {
						echo CHtml::tag('span', array('class'=>'can_edit'), nl2br($response->content));
						echo CHtml::tag('textarea', array('class'=>'textInput hide'), nl2br($response->content));
					} else {
						echo CHtml::tag('span', array(), nl2br($response->content));
					}
					?>
					<?php if ($isAuthor) : ?>
					<div class="price">
						<span>Ориентировочная стоимость</span>
						<p><?php echo $response->cost; ?></p>
					</div>
					<?php endif; ?>
				</div>
				<div class="tender_comment_tel">
					<?php if ($isAuthor) { ?>
					<div class="del_comment">
						<a href="#">Отказаться от участия</a>
					</div>
					<?php } ?>
				</div>
				<div class="clear"></div>
			</div>
		<?php endforeach; ?>
		
		<?php endif; ?>
	</div>
</div>
<div class="clear"></div>
<div class="spacer-30"></div>