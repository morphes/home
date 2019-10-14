<div class="pathBar">
	<?php
	$this->widget('application.components.widgets.EBreadcrumbs', array(
		'links' => array(
			'Заказы' => array('/tenders/list/'),
		),
	));
	?>
	<h1><?php echo $tender->name; ?></h1>

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
					<img src="/img/default/nophoto-minimini.png" width="23" height="23" />
				</div>
				<div class="coautor_info guest">
					<span class="">Организатор заказа скрыт. Для просмотра необходима <a href="#" class="-login">авторизация</a></span>
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
		<div class="btn_conteiner tender_add_button">
			<a href="#" class="btn_grey -guest">Откликнуться на заказ</a>
		</div>
		<div class="spacer-18"></div>
	</div>

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
	</div>
	<div class="spacer-18"></div>
	<div class="authorization_error shadow_block padding-18 pink">
		Чтобы получить больше возможностей в работе с заказами, <a class="-login" href="#">авторизуйтесь</a> или <a class="register_guest" href="/site/registration">зарегистрируйтесь</a>
	</div>
	<div style="margin-top: 15px;">
		<?php
		// Яндекс.Директ
         echo Yii::app()->controller->renderPartial('//widget/profitpartner/profit_partner_item_under');

        //$this->renderPartial('//widget/yandex/direct_3ad_tender_page');
		?>
	</div>
</div>
<div class="clear"></div>
<div class="spacer-30"></div>


<div class="-hidden">
	<div class="popup popup-message-guest" id="popup-message-guest">
		<div class="popup-header">
			<div class="popup-header-wrapper">
				Участие в заказе
			</div>
		</div>
		<div class="popup-body">
			Чтобы откликнуться на заказ, <a href="#" class="-login">авторизуйтесь</a> или <a href="/site/registration">зарегистрируйтесь</a>.
		</div>
	</div>
</div>