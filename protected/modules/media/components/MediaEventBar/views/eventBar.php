<div class="ideas_showed"><?php
		echo CFormatterEx::formatNumeral($dataProvider->getTotalItemCount(), array('Показан', 'Показано', 'Показано'), true).' '
		. CFormatterEx::formatNumeral($dataProvider->getTotalItemCount(), array('событие', 'события', 'событий'));
	?></div>

<?php echo CHtml::form(MediaEvent::getListLink(), 'get', array('id'=>'filter_form')); ?>
	<div class="shadow_block padding-18 calendar_filter">
		<div class="filter_hint">
			<i></i>
			Показать <a class="" href="#"><span>22</span> события</a>
		</div>
		<div class="filter_item">
			<div class="filter_name">Город</div>
			<?php
			$this->widget('application.components.widgets.EAutoComplete', array(
				'valueName'	=> City::getNameById($cityId),
				'sourceUrl'	=> '/utility/autocompletecity',
				'value'		=> $cityId,
				'options'	=> array(
					'showAnim'	=>'fold',
					'open' => 'js:function(){
						$(".ui-autocomplete").css("width", "168px");
					}',
					'minLength' => 3
				),
				'htmlOptions'	=> array('id'=>'city_id', 'name'=>'city_id', 'class' => 'textInput'),
				'cssFile' => null,
			));
			?>
		</div>
		<div class="filter_item date_filter">
			<div class="filter_name">Дата проведения</div>
			<input type="text" id="date_from" class="textInput" value="<?php echo date('d.m.y', $startTime); ?>"/> — <input type="text" id="date_to" class="textInput" value="<?php echo empty($endTime) ? '' : date('d.m.y', $endTime); ?>"/>
			<?php echo CHtml::hiddenField('start_time', $startTime); ?>
			<?php echo CHtml::hiddenField('end_time', empty($endTime) ? '' : $endTime); ?>
		</div>
		<div class=" filter_item">
			<div class="filter_name">Тип события</div>
			<div class="checkbox-list">
				<ul class="visible_types">
					<?php /** @var $eventType MediaEventType */
					foreach ($eventTypes as $eventType) : ?>
					<li><label><?php echo CHtml::checkBox('type[]', false, array('value'=>$eventType->id)) . $eventType->name; ?></label></li>
					<?php endforeach; ?>
				</ul>
				<input type="hidden" name="" value="<?php echo Yii::app()->getRequest()->getParam('event_types', ''); ?>">
			</div>
		</div>
		<div class=" filter_item">
			<div class="filter_name">Тематика</div>
			<div class="checkbox-list">
				<ul class="visible_types">
				<?php /** @var $theme MediaTheme */
				foreach ($themes as $theme) : ?>
					<li><label><?php echo CHtml::checkBox('theme[]', false, array('class'=>'check_all', 'value'=>$theme->id)) . $theme->name; ?></label></li>
				<?php endforeach; ?>
				</ul>
				<input type="hidden" name="" value="<?php echo Yii::app()->getRequest()->getParam('event_theme', ''); ?>">
			</div>
		</div>
		<div class=" filter_item">
			<div class="filter_name">Кому это интересно</div>
			<div class="checkbox-list">
				<ul class="visible_types">
					<li><label><input class="check_all" name="int_spec" value="<?php echo MediaEvent::WHOM_SPEC; ?>" type="checkbox">Специалистам</label></li>
					<li><label><input class="check_all" name="int_user" value="<?php echo MediaEvent::WHOM_USER; ?>" type="checkbox">Пользователям</label></li>
				</ul>
				<input type="hidden" name="whom_interest" value="<?php echo Yii::app()->getRequest()->getParam('whom_interest'); ?>">
			</div>
		</div>
		<div class=" filter_item">
			<label><?php echo CHtml::checkBox('is_online', (bool)Yii::app()->getRequest()->getParam('is_online') ); ?>Онлайн-мероприятие</label>
		</div>
		<div class="btn_conteiner yellow">
			<input class="btn_grey" type="submit" value="Показать" name="">
		</div>
		<?php /*<input type="hidden" name="viewtype" value="<?php echo $viewType; ?>" />*/ ?>
		<input type="hidden" name="pagesize" value="<?php echo $pageSize; ?>" />
		<input type="hidden" name="sort" value="<?php echo $sortType; ?>" />
		<input type="hidden" name="sortdirect" value="<?php echo $sortDirect; ?>" />
	</div>
<?php echo CHtml::endForm(); ?>