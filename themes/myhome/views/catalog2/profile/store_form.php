<?php $this->pageTitle = 'Редактирование магазина — MyHome.ru'; ?>
<script type="text/javascript">
    $(document).ready(function(){
        store.initForm();
    })
</script>
<?php Yii::app()->clientScript->registerCoreScript('maskedinput'); ?>


<?php $this->renderPartial('_storeUpdateMenu', array('model'=>$model)); ?>

<div id="right_side">
        <?php $form=$this->beginWidget('CActiveForm',array(
                'id'=>'store-form',
                'htmlOptions'=>array(
                        'enctype'=>'multipart/form-data',
                ),
        )); ?>

        <?php if($model->getErrors()) : ?>
                <div class="error-title">
                        <?php foreach($model->getErrors() as $attributeErrors) : ?>
                                <?php foreach($attributeErrors as $error) : ?>
                                        <p><?php echo $error; ?></p>
                                <?php endforeach; ?>
                        <?php endforeach; ?>
                </div>
        <?php endif; ?>

	<div class="form">

	<?php echo CHtml::hiddenField('sid', isset($model->id) ? $model->id
		: $model->id); ?>

	<?php $requiredHtml = '<span class="required">*</span>'; ?>

	<div class="options_section">
		<div class="options_row">
			<div class="option_label">
				Логотип
			</div>
			<div class="option_value store_image">

				<?php if ($model->uploadedFile) : ?>
					<div class="photo_item uploaded_photo"
					     id="<?php echo $model->uploadedFile->id; ?>">
						<?php echo CHtml::image('/' . $model->uploadedFile->getPreviewName(Config::$preview['crop_78']), '', array('width' => 78)); ?>
						<div class="icons">
							<i></i>

							<div class="clear"></div>
                                        <span>
                                                <span>Удалить</span><br>
                                                <span>фото</span>
                                        </span>
						</div>
					</div>
				<?php endif; ?>


				<div class="clear"></div>
				<?php echo $form->fileField($model, 'logo', array('class' => 'simple_input')); ?>
			</div>
			<div class="clear"></div>
		</div>
	</div>
	<div class="options_section">
		<div class="options_row">
			<div class="option_label">
				<?php echo $form->label($model, 'name'); ?>
				<?php echo $model->isAttributeRequired('name')
					? $requiredHtml : ''; ?>
			</div>
			<div class="option_value">
				<?php echo $form->textField($model, 'name', array('class' => 'textInput')); ?>
			</div>
			<div class="clear"></div>
		</div>
		<div class="options_row">
			<div class="option_label">
				<?php echo $form->label($model, 'activity'); ?>
				<?php echo $model->isAttributeRequired('activity')
					? $requiredHtml : ''; ?>
			</div>
			<div class="option_value">
				<?php echo $form->textField($model, 'activity', array('class' => 'textInput')); ?>
			</div>
			<div class="clear"></div>
		</div>
		<div class="options_row">
			<div class="option_label">
				<?php echo $form->label($model, 'city_id'); ?>
				<?php echo $model->isAttributeRequired('city_id')
					? $requiredHtml : ''; ?>
			</div>
			<div class="option_value">
				<?php
				$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
					'name'        => 'City_id',
					'value'       => ($city instanceof City)
						         ? "{$city->name} ({$city->region->name}, {$city->country->name})"
						         : '',
					'sourceUrl'   => '/utility/autocompletecity',
					'options'     => array(
						'minLength' => '3',
						'showAnim'  => 'fold',
						'select'    => 'js:function(event, ui) {$("#StoreGeo_geo_id").val(ui.item.id).keyup();}',
						'change'    => 'js:function(event, ui) {if(ui.item === null) {$("#StoreGeo_geo_id").val("");}}',
					),
					'htmlOptions' => array('class' => 'textInput')
				));
				?>
				<?php echo CHtml::HiddenField('StoreGeo[geo_id]', ($city instanceof City) ? $city->id : '', array('id' => "StoreGeo_geo_id")); ?>
			</div>
			<div class="clear"></div>
		</div>
		<div class="options_row">
			<div class="option_label">
				<?php echo $form->label($model, 'address'); ?>
				<?php echo $model->isAttributeRequired('address')
					? $requiredHtml : ''; ?>
			</div>
			<div class="option_value">
				<?php echo $form->textField($model, 'address', array('class' => 'textInput')); ?>
			</div>
			<div class="clear"></div>
		</div>
		<div class="options_row">
			<div class="option_label">
				<?php echo $form->label($model, 'phone'); ?>
				<?php echo $model->isAttributeRequired('phone')
					? $requiredHtml : ''; ?>
			</div>
			<div class="option_value">
				<?php echo $form->textField($model, 'phone', array('class' => 'textInput')); ?>
				<span class="hint">Номера телефонов в формате (495) 299-00-00 через запятую</span>
				<!--<span class="add_row">Добавить еще телефон</span>-->
			</div>
			<div class="clear"></div>
		</div>
		<div class="options_row">
			<div class="option_label">
				<?php echo $form->label($model, 'email'); ?>
				<?php echo $model->isAttributeRequired('email')
					? $requiredHtml : ''; ?>
			</div>
			<div class="option_value">
				<?php echo $form->textField($model, 'email', array('class' => 'textInput short')); ?>
				<!--<span class="add_row">Добавить еще почту</span>-->
			</div>
			<div class="clear"></div>
		</div>
		<?php if ($model->tariff_id != Store::TARIF_FREE && $model->tariff_id != null) : ?>
			<div class="options_row">
				<div class="option_label">
					<?php echo $form->label($model, 'site'); ?>
					<?php echo $model->isAttributeRequired('site')
						? $requiredHtml : ''; ?>
				</div>
				<div class="option_value">
					<?php echo $form->textField($model, 'site', array('class' => 'textInput')); ?>
				</div>
				<div class="clear"></div>
			</div>
		<?php endif; ?>
		<div class="options_row">
			<div class="option_label">
				Время работы в будние дни
			</div>
			<div class="option_value">
				<div class="timetable_row">
					<span>c</span>
					<?php echo CHtml::textField('weekdays_work_from', $model->timeArray['weekdays']['work_from'], array('class' => 'textInput')); ?>
					<span>до</span>
					<?php echo CHtml::textField('weekdays_work_to', $model->timeArray['weekdays']['work_to'], array('class' => 'textInput')); ?>
					<label><?php echo CHtml::checkBox('weekdays_dinner_enabled', $model->timeArray['weekdays']['dinner_enabled']); ?>
						Обед</label>

					<div class="lunch_time <?php echo $model->timeArray['weekdays']['dinner_enabled']
						? '' : 'hide'; ?>">
						<span>c</span>
						<?php echo CHtml::textField('weekdays_dinner_from', $model->timeArray['weekdays']['dinner_from'], array('class' => 'textInput')); ?>
						<span>до</span>
						<?php echo CHtml::textField('weekdays_dinner_to', $model->timeArray['weekdays']['dinner_to'], array('class' => 'textInput')); ?>
					</div>
				</div>
			</div>
			<div class="clear"></div>
		</div>
		<div class="options_row">
			<div class="option_label">
				Суббота
			</div>
			<div class="option_value">
				<div class="timetable_row">
					<span>c</span>
					<?php echo CHtml::textField('saturday_work_from', $model->timeArray['saturday']['work_from'], array('class' => 'textInput')); ?>
					<span>до</span>
					<?php echo CHtml::textField('saturday_work_to', $model->timeArray['saturday']['work_to'], array('class' => 'textInput')); ?>
					<label><?php echo CHtml::checkBox('saturday_dinner_enabled', $model->timeArray['saturday']['dinner_enabled']); ?>
						Обед</label>

					<div class="lunch_time <?php echo $model->timeArray['saturday']['dinner_enabled']
						? '' : 'hide'; ?>">
						<span>c</span>
						<?php echo CHtml::textField('saturday_dinner_from', $model->timeArray['saturday']['dinner_from'], array('class' => 'textInput')); ?>
						<span>до</span>
						<?php echo CHtml::textField('saturday_dinner_to', $model->timeArray['saturday']['dinner_to'], array('class' => 'textInput')); ?>
					</div>
				</div>
			</div>
			<div class="clear"></div>
		</div>
		<div class="options_row">
			<div class="option_label">
				Воскресенье
			</div>
			<div class="option_value">
				<div class="timetable_row">
					<span>c</span>
					<?php echo CHtml::textField('sunday_work_from', $model->timeArray['sunday']['work_from'], array('class' => 'textInput')); ?>
					<span>до</span>
					<?php echo CHtml::textField('sunday_work_to', $model->timeArray['sunday']['work_to'], array('class' => 'textInput')); ?>
					<label><?php echo CHtml::checkBox('sunday_dinner_enabled', $model->timeArray['sunday']['dinner_enabled']); ?>
						Обед</label>

					<div class="lunch_time <?php echo $model->timeArray['sunday']['dinner_enabled']
						? '' : 'hide'; ?>">
						<span>c</span>
						<?php echo CHtml::textField('sunday_dinner_from', $model->timeArray['sunday']['dinner_from'], array('class' => 'textInput')); ?>
						<span>до</span>
						<?php echo CHtml::textField('sunday_dinner_to', $model->timeArray['sunday']['dinner_to'], array('class' => 'textInput')); ?>
					</div>
				</div>
			</div>
			<div class="clear"></div>
		</div>
		<?php if ($model->tariff_id != Store::TARIF_FREE && $model->tariff_id != null): ?>
			<div class="options_row">
				<div class="option_label">
					<?php echo $form->label($model, 'about'); ?>
					<?php echo $model->isAttributeRequired('about')
						? $requiredHtml : ''; ?>
				</div>
				<div class="option_value">
					<?php echo $form->textArea($model, 'about', array('class' => 'textInput')); ?>
				</div>
				<div class="clear"></div>
			</div>
		<?php endif; ?>
	</div>
	</div>
        <div class="buttons_block">
            <div class="btn_conteiner yellow">
                <?php echo CHtml::submitButton($model->isNewRecord ? 'Создать магазин' : 'Сохранить изменения', array('class'=>'btn_grey')); ?>
            </div>
        </div>
        <?php $this->endWidget(); ?>

</div>

<?php Yii::app()->clientScript->registerScript('form', '
        $("#store-form").find(".timetable_row input:text").mask("99:99");
', CClientScript::POS_READY); ?>