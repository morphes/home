<?php
	$cs = Yii::app()->clientScript;
	$cs->registerCoreScript('jquery');
	$cs->registerScriptFile('/js/jquery-ui-1.8.18.custom.min.js');
	$cs->registerScriptFile('/js/admin.js', CClientScript::POS_HEAD);
	$cs->registerCssFile('/css/jquery-ui-1.8.18.custom.css');
?>

<?php
$this->breadcrumbs=array(
	'Заказы'=>array('/tenders/admin/tender/list'),
	'Редактирование заказа',
);
?>
<script type="text/javascript">
	function removeFile(self){
		if (confirm('Удалить файл?')) {
			var line = $(self).parents('.line');
			var id = line.attr('data-value');
			
			$.ajax({
				url:"/tenders/admin/tender/removefile",
				data: {'file_id':id},
				type: "post",
				dataType: "json",
				async: false,
				success: function(response) {
					if (response.success) {
						line.remove();
					}
					if (response.error){
						location.reload();
					}
				},
				error: function() {
					location.reload();
				}
			});
		}
	}
</script>

<h1>Редактирование заказа #<?php echo $tender->id; ?>
	<?php /** @var $tender Tender */
	echo ' - "'.$tender->name.'"';?></h1>


<?php
$this->widget('ext.bootstrap.widgets.BootDetailView', array(
    'data'=>$tender,
    'attributes'=>array(
        array(
            'label'=>'Автор',
            'type'=>'html',
            'value'=> is_null($tender->author_id) ? 'Гость' : CHtml::link($user->login.' ('.$user->name.')', $this->createUrl("/users/{$user->login}/")),
        ),
	array(
	    'label'=>'Email',
	    'type'=>'html',
	    'value'=>$tender->getAuthorEmail(),
	),
	array(
	    'label'=>'Город',
	    'type'=>'html',
	    'value'=>"<b>".$tender->getCityName()."</b>",
	),
	array(
	    'label'=>'Количество отзывов',
	    'type'=>'html',
	    'value'=>$tender->response_count,
	),		    
        array(
            'label'=>'Дата создания',
            'type'=>'raw',
            'value'=>date('d.m.Y H:i', $tender->create_time),
        ),
        array(
            'label'=>'Статус',
            'type'=>'html',
            'value'=>"<span class='label success'>".Tender::$statusNames[$tender->status]."</span>",
        ),
    ),
));
?>


<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'tender-form',
	'enableAjaxValidation'=>false,
        'htmlOptions' => array('class' => 'form-project-add'),
        'stacked'=>true,
)); ?>

        <?php echo $form->errorSummary($tender); ?>

                <div class="well" style="background-color: #F9F9F9;">

                        <?php echo CHtml::tag('h2', array('style' => 'margin-bottom: 20px;'), 'Общие настройки', true); ?>
			
			<?php echo $form->textFieldRow($tender, 'name', array('class'=>'span12')); ?>

			<div class="clearfix">
				<label class="compare_offer">
					<?php echo CHtml::radioButton('Tender[cost_flag]', ($tender->cost_flag==Tender::COST_COMPARE), array('class'=>'textInput', 'value'=>Tender::COST_COMPARE)); ?>
					Не указывать</label>
				<label class="compare_offer">
					<?php echo CHtml::radioButton('Tender[cost_flag]', ($tender->cost_flag==Tender::COST_EXECT), array('class'=>'textInput', 'value'=>Tender::COST_EXECT)); ?>
					Указать</label>
				<label class="compare_offer num<?php if ($tender->cost_flag==Tender::COST_COMPARE) echo ' hide'; ?>"><span class="budget"><?php echo CHtml::textField('Tender[cost]', $tender->cost, array('class'=>'textInput', 'maxlength'=>12)); ?></span> рублей</label>
			</div>
			<script>
				$('.compare_offer input:radio').change(function(){
					if($(this).val() == 1){
						$('.compare_offer.num').show();
					}else{
						$('.compare_offer.num').hide();
					}
				})
			</script>
			
                        <?php echo $form->textAreaRow($tender, 'desc', array('class'=>'span12', 'style'=>'height:150px;')); ?>

			<div class="input_row">
				<div class="input_conteiner ">
					<label class="inline">Заявки принимаются до</label>
					<?php $class = $tender->hasErrors('expire') ? 'textInput error' : 'textInput'; ?>
					<?php echo CHtml::textField('', date('d.m.Y', $tender->expire), array('id'=>'tender_date', 'class'=>$class)); ?>
					<img class="ui-datepicker-trigger" src="/img/calendar_icon.png">
					<?php echo CHtml::hiddenField('Tender[expire]', $tender->expire); ?>
				</div>
				<div class="clear"></div>
			</div>
                </div>


		<?php /** Список услуг */ ?>
		<div class="well white services-list">
			<div class="">
			<?php
			if (!empty($services)) {
				$topService = 0;
				/** @var $service Service */
				foreach ($services as $service) {
					if ($service->parent_id == 0) {
						if ($topService != 0) {
							echo CHtml::closeTag('ul');
						} else {
							$topService++;
						}

						echo CHtml::tag('h5', array(), $service->name);
						echo CHtml::openTag('ul', array('class'=>'inputs-list'));

						continue;
					}
					echo CHtml::openTag('li');
					echo CHtml::openTag('label', array('style'=>'display: inline-block;'));

					echo CHtml::checkBox('Tender[service]['.$service->id.']', isset($checkedServices[$service->id]));
					echo CHtml::tag('span', array(), $service->name);

					echo CHtml::closeTag('label');
					echo CHtml::closeTag('label');
				}
			}
			?>
			</div>
		</div>

                <div class="well" style="background-color: #F9F9F9;">
                        <?php echo CHtml::tag('h2', array('style' => 'margin-bottom: 20px;'), 'Дополнительные материалы', true); ?>
			<table class="image_uploaded">
				<tbody>
				<?php foreach ($files as $file) { ?>
				
				<tr class="line" data-value="<?php echo $file['id']; ?>">
					<th><?php echo CHtml::link($file['name'].'.'.$file['ext'], Yii::app()->controller->createUrl('/download/tenderfile/', array('id'=>$file['id']))); ?></th>
					<td class="file_description">
						<?php echo CHtml::tag('span', array(), empty($file['desc']) ? 'Добавить описание' : $file['desc'] ); ?>
						<?php echo CHtml::textArea('File[desc]['.$file['id'].']', $file['desc'], array('class'=>'textInput hide', 'maxlength'=>255)); ?>
					</td>
					
					<th><?php echo CFormatterEx::formatFileSize($file['size']); ?></th>
					<th><?php echo CHtml::tag('span', array('onclick'=>'removeFile(this);'), 'Удалить'); ?></th>
				</tr>

				<?php } ?>
				</tbody>
			</table>
                </div>

		<div class="well" style="background-color: #F9F9F9;">
			<div class="input_conteiner">

				<label class=""></label>
				<?php $this->widget('ext.FileUpload.FileUpload', array(
					'url'=> $this->createUrl('upload', array('tid'=>$tender->id)),
					'postParams'=>array(),
					'config'=> array(
						'fileName' => 'UploadedFile[file]',
						'onSuccess'=>'js:function(response){ $(".image_uploaded tbody").append(response.html); }',
						'onStart' => 'js:function(data){ $("#load_img").show(); }',
						'onFinished' => 'js:function(data){ $("#load_img").hide(); }'
					),
					'htmlOptions'=>array('size'=>61, 'accept'=>'image', 'class'=>'img_input'),
				)); ?>
				<img src="/img/loaderT.gif" alt="" id="load_img" style="margin-top: 8px; margin-left: 6px; width: 16px; display: none;">
				<div class="clear"></div>
			</div>
			<div class="clear"></div>
		</div>

        <?php echo $form->dropDownListRow($tender, 'status', $tender->getAvailableStatusList()); ?>
	
	<?php echo CHtml::tag('hr', array('style' => 'height: 2px; background-color: black;')); ?>
	
        <div class="actions">
                <?php echo CHtml::submitButton('Сохранить', array('class'=>'btn primary large')); ?>
                <?php echo CHtml::button('Отмена', array('class'=>'btn default large','onclick' => "document.location = '".$this->createUrl('/tenders/admin/tender/view', array('id' => $tender->id))."'"));?>
        </div>

<?php $this->endWidget(); ?>