<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'                   => 'index-product-tab-form',
	'enableAjaxValidation' => false,
	'focus'                => array($model, 'name')
)); ?>

	<p class="help-block">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	<?php echo $form->textFieldRow($model, 'name', array('class'=>'span5','maxlength'=>32)); ?>

	<?php echo $form->textFieldRow($model, 'url', array('class'=>'span5','maxlength'=>255)); ?>

	<?php echo $form->textFieldRow($model, 'position', array('class'=>'span5', 'hint' => 'от 1 до '.$model->getMaxPos())); ?>

	<div class="clearfix">
		<label><?php echo $model->getAttributeLabel('rubric'); ?> <span class="required">*</span></label>
		<div class="input">
			<?php
			$this->widget('application.components.widgets.EAutoComplete', array(
				'valueName'	=> '',
				'sourceUrl'	=> '/admin/utility/AcCatCategory2',
				'value'		=> '',
				'options'	=> array(
					'showAnim'  => 'fold',
					'minLength' => 2,
					'select'    => 'js:function(event, ui){


						if ( $(".rubric_list li[data-rub_id="+ui.item.id+"]").size() > 0 )
							alert("Уже добавлено!");
						else {
							if ($("#IndexProductTab_rubric").size() > 0) {
								var ids = $("#IndexProductTab_rubric").val();
								$("#IndexProductTab_rubric").val(ids + "," + ui.item.id);
							} else {
								var hidden = $("<input>")
									.attr("type", "hidden")
									.attr("name", "IndexProductTab[rubric]")
									.attr("id", "IndexProductTab_rubric")
									.attr("value", ui.item.id);

								$(".rubric_list")
									.after(hidden);
							}


							var li = $("<li>")
								.text(ui.item.label)
								.attr("data-rub_id", ui.item.id);

							$(".rubric_list")
								.append(li);

						}

						ui.item.value = "";
					}'
				),
				'htmlOptions'	=> array('id'=>'rubric', 'name'=>'Product[contractor]', 'class' => ''),
				'cssFile' => null,
			));
			?>
			<div style="margin-top: 10px;">
				<ul class="rubric_list">
					<?php // Выводим список добавленных ранее рубрик
					Yii::import('application.modules.catalog.models.Category');
					$rubrics = unserialize($model->rubric);
					if (is_array($rubrics)) {
						foreach($rubrics as $id) {
							echo CHtml::tag(
								'li',
								array('data-rub_id' => $id),
								Category::model()->findByPk($id)->name
							);
						}
					}
					?>
				</ul>
				<?php // Выводим список скрытых Input'ов для рубрик
				$rubrics = unserialize($model->rubric);
				if (is_array($rubrics)) {
					echo CHtml::hiddenField(
						'IndexProductTab[rubric]',
						implode(',', $rubrics),
						array('id' => 'IndexProductTab_rubric')
					);
				}
				?>

				<script>
					$(".rubric_list li").each(function(index, element){
						// Получаем ID рубрики
						var rubId = $(this).attr('data-rub_id');
						$(element)
							.prepend('<a href="#">[x]</a> ')
							.find('a')
								.click(function(){
									if (confirm('Удалить?')) {
										// Удаляем теги <li>
										$("[data-rub_id="+rubId+"]").remove();

										var strRubric = '';
										$('.rubric_list li').each(function(index, element){
											if (strRubric != '') {
												strRubric += ',';
											}
											strRubric += $(element).attr('data-rub_id');
										});
										$('#IndexProductTab_rubric').val(strRubric);

									}
									return false;
								});
					});
				</script>
			</div>
		</div>
	</div>



	<div class="actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>
