<div class="section" id="section_<?php echo $itemSection->id; ?>" item_section_id="<?php echo $itemSection->id; ?>">
	<hr />

	<div class="clearfix">
		<?php echo CHtml::activeLabel($itemSection, "section_id"); ?>
		<div class="input">
			<?php echo CHtml::activeDropDownList($itemSection,"section_id", array(''=>'Выберите раздел')+BannerItemSection::getAvailableSections($itemSection->item->type_id), array('class'=>'span3 available_sections')); ?>
		</div>
	</div>

	<div class="clearfix">
		<?php echo CHtml::activeLabel($itemSection, "tariff_id"); ?>
		<div class="input">
			<?php echo CHtml::activeDropDownList($itemSection,"tariff_id", array(''=>'Выберите тариф')+BannerItemSection::$tariffLabels, array('class'=>'span3')); ?>
		</div>
	</div>

	<div class="clearfix">
		<?php echo CHtml::label('Начало показов', 'reg')?>

		<div class="input">
			<?php $this->widget('application.components.widgets.AjaxDateTimePicker', array(
				'model'=>$itemSection,
				'attribute'=> 'humanStartTime',
				'options'=>array('dateFormat'=>'dd.mm.yy'),
				'htmlOptions'=>array(
					'style'=>'width:150px;',
					'id'=>'start_time_' . $itemSection->id,
				),
			));?>
		</div>
	</div>

	<div class="clearfix">
		<?php echo CHtml::label('Конец показов', 'reg')?>

		<div class="input">
			<?php $this->widget('application.components.widgets.AjaxDateTimePicker', array(
				'model'=>$itemSection,
				'attribute'=> 'humanEndTime',
				'options'=>array('dateFormat'=>'dd.mm.yy'),
				'htmlOptions'=>array(
					'style'=>'width:150px;',
					'id'=>'end_time_' . $itemSection->id,
				),
			));?>
		</div>
	</div>

	<div class="clearfix">
		<?php echo CHtml::label('Геортаргетинг', "geo"); ?>
		<div class="input">
			<?php echo CHtml::dropDownList('geo_type', '', array('city_id'=>'Город', 'country_id'=>'Страна'), array('style'=>'width:70px;', 'class'=>'geo_switcher', ))?>
			<?php $this->widget('application.components.widgets.CAjaxAutoComplete', array(
				'name'=>'city_id',
				'value'=> '',
				'sourceUrl'=>'/utility/autocompletecity',
				'options'=>array(
					'minLength'=>'2',
					'showAnim'=>'fold',
					'select'=>'js:function(event, ui) {assignGeo(' . $itemSection->id . ', "city_id", ui.item.id); $(this).val(""); return false;}',
				),
				'htmlOptions'=>array('id'=>'city_id_'.$itemSection->id, 'class'=>'city_id_selector', 'placeholder'=>'Название города')
			));?>
			<?php $this->widget('application.components.widgets.CAjaxAutoComplete', array(
				'name'=>'country_id',
				'value'=> '',
				'sourceUrl'=>'/utility/autocompletecountry',
				'options'=>array(
					'minLength'=>'2',
					'showAnim'=>'fold',
					'select'=>'js:function(event, ui) {assignGeo(' . $itemSection->id . ', "country_id", ui.item.id);$(this).val(""); return false;}',
				),
				'htmlOptions'=>array('id'=>'country_id_'.$itemSection->id, 'class'=>'country_id_selector', 'placeholder'=>'Название страны', 'style'=>'display:none;')
			));?>
			<ul id="itemSection_<?php echo $itemSection->id; ?>_Geos">
				<?php foreach($itemSection->getItemSectionGeos() as $geo) : ?>
					<?php $this->renderPartial('_geoForm', array('geo'=>$geo)); ?>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>

	<?php $itemSection->getErrors() ? $display = 'block' : $display = 'none'?>
	<div class="itemSectionErrors alert-message block-message error" style="display: <?php echo $display; ?>;">
		<?php foreach ($itemSection->getErrors() as $attr) : ?>
			<?php foreach ($attr as $err) : ?>
				<?php echo $err . '<br>'; ?>
			<?php endforeach; ?>
		<?php endforeach; ?>
	</div>
	<div class="itemSectionGeoErrors alert-message block-message error" style="display: none;"></div>

	<p style="padding-left: 45px; margin-bottom: 20px; font-size: 14px;">
		<?php echo CHtml::link('Удалить раздел', '#', array('class'=>'delete_section', 'item_section_id'=>$itemSection->id)); ?>
	</p>
</div>