<?php Yii::app()->clientScript->registerCoreScript('jquery') ?>

<script type="text/javascript">
	$(document).ready(function(){
		$('.del_img a').live('click',function(){
			$(this).parents('.to_del').remove();
			$('<input type="hidden" name="Portfolio[delete][]" value="' + $(this).attr('file_id') + '">').appendTo('#portfolio-form');
			return false;
		});
	})
</script>

<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'			=> 'portfolio-form',
	'enableAjaxValidation'	=> false,
	'stacked'		=> true,
	'htmlOptions' => array(
		'enctype' => 'multipart/form-data'
	),
)); ?>

	<p class="help-block">Поля, отмеченные звездочкой, обязательны.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="well" style="background-color: #F9F9F9;">

		<?php echo CHtml::tag('h2', array('style' => 'margin-bottom: 20px;'), 'Общие параметры', true); ?>

		<?php
		// Форимируем вложенный массив списка сервисов, сгруппированных по родителю
		// В $arr[0] лежат все родительские сервисы
		$arr = CHtml::listData( Service::model()->findAll(''), 'id', 'name', 'parent_id') ;
		foreach($arr[0] as $parent_id=>$parent_name) {
			$arr[ $parent_name ] = $arr[ $parent_id ];
			unset( $arr[$parent_id] );
		}
		unset($arr[0]);
		echo $form->dropDownListRow($model, 'service_id', array('0' => 'Все')+$arr, array('class' => 'span9'));
		?>

                <div class="clearfix">
                        <?php echo CHtml::label('Автор', 'author'); ?>

                        <div class="input">
                                <?php $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
                                'name'		=> 'author',
                                'sourceUrl'	=> '/utility/autocompleteuser',
                                'value'        => isset($model->author->name) ? $model->author->name . " ({$model->author->login})" : '',
                                'options'	=> array(
                                        'showAnim'	=> 'fold',
                                        'delay'		=> 0,
                                        'autoFocus'	=> true,
                                        'select'	=> 'js:function(event, ui) {$("#Portfolio_author_id").val(ui.item.id); }',


                                ),
                                'htmlOptions' => array('size'=>15)
                        ));?>

                                <?php echo $form->hiddenField($model,'author_id',array('size'=>15)); ?>

                                <?php
                                Yii::app()->clientScript->registerScript('loginType', '
                                                        $("#author").keydown(function(event){
                                                                if (
                                                                        event.keyCode != 27 && event.keyCode != 9 && event.keyCode != 13
                                                                        && event.keyCode != 37 && event.keyCode != 38 && event.keyCode != 39 && event.keyCode != 40
                                                                        && event.keyCode != 35 && event.keyCode != 36
                                                                ) {
                                                                        $("#Interior_author_id").val("");
                                                                }
                                                        });
                                                ', CClientScript::POS_READY);
                                ?>
                        </div>
                </div>

		<?php echo $form->textFieldRow($model,'name',array('class'=>'span9','maxlength'=>45)); ?>

		<?php echo $form->textAreaRow($model,'desc',array('class'=>'span9', 'rows' => 4, 'maxlength'=>2000)); ?>

	</div>

	<div class="well">

		<?php echo CHtml::tag('h2', array('style' => 'margin-bottom: 20px;'), 'Фотографии', true); ?>

		<?php
		if ($model->images)
		foreach($model->images as $img) : ?>
		<div class="row to_del" style="margin-bottom: 20px;">
			<div class="span3">
				<img style="width:131px;" src="/<?php echo $img->file->getPreviewName(array('131', '131', 'crop', 100)); ?>">

			</div>
			<div class="span6">
				<?php echo CHtml::textArea('Portfolio[filedesc]['.$img->file->id.']', $img->file->desc, array('class' => 'span6', 'rows' => 5)); ?>
			</div>
			<div class="span3 del_img">
				<a id="" file_id="<?php echo $img->file->id; ?>" href="#">Удалить</a>
			</div>
		</div>
		<?php endforeach; ?>


		<div id="portfolio_photos">

		</div>


		<?php echo CHtml::button('Добавить фото', array(
			'class' => 'btn primary',
			'onclick' => "
				var original = $('#one_image_original').clone();
				var num_file = parseInt(original.attr('data-num-file'));

				original.removeAttr('id');
				original.addClass('one_image');
				original.find('input').attr('name', 'Portfolio[file_'+num_file+']');
				original.find('textarea').attr('name', 'Portfolio[new]['+num_file+'][filedesc]');
				original.show();

				$('#portfolio_photos').append(original);

				$('#one_image_original').attr('data-num-file', num_file+1);
			"
		));?>
	</div>

	<?php echo $form->dropDownListRow($model, 'status', Portfolio::$statusNames); ?>

	<?php echo CHtml::tag('hr', array('style' => 'height: 2px; background-color: black;')); ?>

	<div class="actions">
		<?php echo CHtml::submitButton('Сохранить', array('class'=>'btn primary large')); ?>
		<?php echo CHtml::button('Отмена', array('class'=>'btn default large','onclick' => "document.location = '".$this->createUrl('/'.$this->module->id.'/admin/portfolio/view', array('id' => $model->id))."'"));?>
	</div>

<?php $this->endWidget(); ?>


<div class="row" id="one_image_original" data-num-file="0" style="margin-bottom: 20px; display: none;">
	<div class="span3">
		<div style="background-color: rgb(238, 238, 238); width: 131px; height: 131px; border: 1px solid rgb(170, 170, 170);">
			<div style="margin-left: 40px; margin-top: 13px; font-size: 96px; color: white;">x</div>
		</div>
	</div>
	<div class="span6">
		<input  name="Portfolio[file_0]" type="file" style="display: block; margin-bottom: 5px;" />
		<textarea  name="Portfolio[new][0][filedesc]" class="span6" rows="4"></textarea>
	</div>
	<div class="span3">
		<a href="#" onclick="$(this).parents('.one_image').remove(); return false;">Удалить</a>
	</div>
</div>
