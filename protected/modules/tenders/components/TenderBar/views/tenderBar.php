<div id="left_side">
	<?php echo CHtml::form('/tenders/list/', 'get', array('id'=>'filter_form')); ?>
		<div class="shadow_block padding-18 ideas_filter">
			<div class="filter_item spec_city">
				<p>Город</p>
				<div class="dropdown_input">
				<?php
				$this->widget('application.components.widgets.EAutoComplete', array(
					'valueName'	=> City::getNameById($cityId),
					'sourceUrl'	=> '/utility/autocompletecity',
					'value'		=> $cityId,
					'options'	=> array(
						'showAnim'  => 'fold',
						'minLength' => 3
					),
					'htmlOptions'	=> array('id'=>'city_id', 'name'=>'city_id', 'class'=>'textInput'),
					'cssFile' => null,
				));
				?>
				</div>
			</div>
			<div class="filter_item service_cat tender_dropdown">
				<p>Категория услуг</p>
				<div class="tender_s_type drop_down">
					<span class="exp_current">Не важно<i></i></span>
					<ul class="">
						<li data-value="0">Не важно</li>
						<?php foreach ($mainServices as $service) {
							echo CHtml::tag('li', array('data-value'=>$service->id), $service->name);
						} ?>
					</ul>
				</div>
				<?php echo CHtml::hiddenField('main_service', $mainService, array('id'=>'service_cat')); ?>
			</div>
			<div class="filter_item service_id tender_dropdown">
				<p>Услуга</p>
				<div class="tender_s_type drop_down">
					<span class="exp_current disabled">Не важно<i></i></span>
					<ul class="">
						<li data-value="0">Не важно</li>
						<?php foreach ($childServices as $service) {
							echo CHtml::tag('li', array('data-value'=>$service->id), $service->name);
						} ?>
					</ul>
				</div>
				<?php echo CHtml::hiddenField('child_service', $childService, array('id'=>'service_id')); ?>
			</div>
			<div class="tender_dropdown filter_item">
				<p>Показать</p>
				<div class="tender_status drop_down">
					<?php 
					if (!isset(Tender::$typeNames[$tenderType])) {
						$tenderType = @reset(array_keys(Tender::$typeNames)); 
					}
					?>
					<span class="exp_current"><?php echo Tender::$typeNames[$tenderType]; ?><i></i></span>
					<ul>
						<?php foreach (Tender::$typeNames as $key => $value) {
							echo CHtml::tag('li', array('data-value'=>$key), $value);					
						}?>
					</ul>
					<?php echo CHtml::hiddenField('tender_type', $tenderType); ?>
				</div>
				
			</div>

			<?php echo CHtml::hiddenField('sorttype', $sortType, array('id'=>'sorttype')); ?>
			<?php echo CHtml::hiddenField('pagesize', $pageSize, array('id'=>'pagesize')); ?>
			
			<div class="btn_conteiner yellow">
				<?php echo CHtml::submitButton('Показать', array('class'=>'btn_grey', 'name'=>'')); ?>
			</div>
		</div>
	<?php echo CHtml::endForm(); ?>
	<div class="spacer-20"></div>
	<div class="shadow_block padding-18 about_service">
		<p>Вы знаете, что хотите сделать в своем доме или на участке, но нужны квалифицированные исполнители? Смело создавайте заказ! Заполните простую форму заказа и — готово! Подрядчики найдут вас сами, а вы выберите лучших. За работу!</p>
	</div>


	<?php
    Yii::app()->controller->renderPartial('//widget/google/adsense_160x600_tender_list');
    // Яндекс.Директ
    Yii::app()->controller->renderPartial('//widget/profitpartner/profit_partner_vertical');
	?>
</div>