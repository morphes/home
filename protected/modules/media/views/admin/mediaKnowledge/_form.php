<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'media-knowledge-form',
	'enableAjaxValidation'=>false,
	'stacked' => true
)); ?>


	<?php echo $form->errorSummary($model); ?>

	<div class="clearfix <?php if ($model->getError('themes')) echo 'error';?>">
		<label class="required <?php if ($model->getError('themes')) echo 'error';?>">Тематики <span class="required">*</span></label>
		<div class="input">
			<?php echo CHtml::activeCheckBoxList($model, 'themes', MediaTheme::getThemes(), array('class' => 'span7 theme_input')); ?>
                </div>
        </div>

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
				'select'	=> 'js:function(event, ui) {$("#MediaKnowledge_author_id").val(ui.item.id); }',
			),
			'htmlOptions' => array('class'=>'span7')
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
										$("#MediaKnowledge_author_id").val("");
									}
								});
							', CClientScript::POS_READY);
			?>
		</div>
	</div>

	<div class="alert-message warning block-message span7">

		<strong>Внешний автор:</strong>

		<div class="clearfix">
			<?php echo CHtml::activeLabel($model, 'author_name'); ?>
			<div class="input">
				<?php echo CHtml::activeTextField($model, 'author_name', array('class' => 'span6'));?>
			</div>

			<?php echo CHtml::activeLabel($model, 'author_url'); ?>
			<div class="input">
				<?php echo CHtml::activeTextField($model, 'author_url', array('class' => 'span6'));?>
			</div>

			<label>Фотография</label>
			<div class="input">
				<?php $this->widget('ext.FileUpload.FileUpload', array(
					'url'=> $this->createUrl('uploadAuthorPhoto', array('id'=>$model->id)),
					'postParams'=>array(),
					'config'=> array(
						'fileName' => 'MediaKnowledge[file]',
						'onSuccess'=>'js:function(response){ $("#img-container").html(response.html); }',
					),
					'htmlOptions'=>array('size'=>61, 'accept'=>'image', 'class'=>'img_input', 'multiple'=>false),
				)); ?>
			</div>
			<div id="img-container">
				<?php $this->renderPartial('_imageAuthorPhoto', array('model' =>$model, 'image' => $authorPhoto));?>
			</div>
		</div>
	</div>



	<?php echo $form->checkboxRow($model,'rss'); ?>

	<?php echo $form->textFieldRow($model,'title',array('class'=>'span7','maxlength'=>255)); ?>

	<?php echo $form->textAreaRow($model,'lead',array('class'=>'span7', 'rows' => 4, 'maxlength'=>500)); ?>

	<?php echo $form->textFieldRow($model,'meta_desc',array('class'=>'span7','maxlength'=>255)); ?>

	<?php echo $form->textFieldRow($model,'section_url',array('class'=>'span7','maxlength'=>255, 'placeholder' => 'напр. rubrica')); ?>

	<?php
	if ($model->image_id)
		$this->renderPartial('_imagePreview', array('model' => $model, 'image' => UploadedFile::model()->findByPk($model->image_id)));
	else
		echo $form->fileFieldRow($model,'image_id',array('class'=>'span7', 'id' => 'preview_image'));
	?>

	<script>
		$(document).ready(function(){

			$('#preview_image').live('change', function(){
				var file = this.files[0];
				var url = '/media/admin/mediaKnowledge/uploadPreview/id/<?php echo $model->id;?>';

				uploadFile(file, url, function(response){
					$('#preview_image').parents('.clearfix').replaceWith(response);

				});
			});


			function uploadFile(file, url, callback) {
				var xhr = new XMLHttpRequest();
				var formData = new FormData();
				// Событие, вызванное по итогу отправки очередного файла
				xhr.onreadystatechange = function(){
					if(this.readyState == 4) {
						if(this.status == 200) {
							// some handler
						}
						delete file;
						delete this;
						if(callback != undefined) callback(this.responseText);
					}
				}
				xhr.open("POST", url);
				formData.append('MediaKnowledge[upload]', file);
				xhr.send(formData);
			}


			$('.delete_preview').live('click', function(){
				var $this = $(this);
				$.post(
					'/media/admin/mediaKnowledge/deletePreview/',
					$this.data(),
					function(response){
						if (response == 'success') {
							$this.parents('.clearfix').remove();
						} else {
							alert('Ошибка при удалении Превью');
						}

					}
				);

				return false;
			})

		});
	</script>

	<div class="clearfix">
		<label for="MediaKnowledge_public_time"><?php echo $model->getAttributeLabel('content'); ?> <span class="require">*</span></label>
		<div class="input">
			<?php
			$this->widget('application.extensions.cleditor.ECLEditor', array(
				'model'=>$model,
				'attribute'=>'content', //Model attribute name. Nome do atributo do modelo.
				'options'=>array(
					'controls'	=> 'loadimage addgallery fpreviewtext style | bold italic underline strikethrough | removeformat | bullets numbering | undo redo | link unlink | pastetext | source',
					'width'		=> '730',
					'height'	=> 550,
					'useCSS'	=> true,
					'settings'	=> array(
						'addgallery' => array(
							// ID модели, который редактируем
							'model_id' => $model->id,
							// URL для получения номера галереи
							'urlGetNumber' => '/media/admin/mediaKnowledge/getNumberOfGallery',
							// URL для сохранения описаний фоток
							'urlSaveDesc' => '/media/admin/mediaKnowledge/saveImageDescription',
							// URL для получения всех фотографий галереи
							'urlGetImages' => '/media/admin/mediaKnowledge/getAllGalleryImages',
							// URL загрузки изображения
							'urlUploadImage' => '/media/admin/mediaKnowledge/uploadImageGallery/id/'.$model->id,
							//
							'urlDeleteImage' => '/media/admin/mediaKnowledge/deleteImageGallery',
							// URL Для получения списка всех галерей, загруженных ранее
							'urlGetListGallery' => '/media/admin/mediaKnowledge/getListGallery',
						),
						'selgallery' => array('model_id' => $model->id),
					)
				),
				'value'=>'<div></div>', //If you want pass a value for the widget. I think you will.
			));
			?>
		</div>
	</div>


<div class="clearfix span9">
	<label>Категории</label>
	<div class="input">
		<?php
		Yii::import('application.modules.catalog.models.Tapestore');
		Yii::import('application.modules.catalog.models.Category');
		$this->widget('ext.NestedDynaTree.NestedDynaTree', array(
			'modelClass' => 'Category',
			'manipulationEnabled' => false,
			'dndEnabled' => false,
			'options' => array(
				'initAjax'=>null,
				'checkbox'=>true,
				'persist' => false,
				'selectMode'=>2,
				'debugLevel'=>0,
				'children'=>MediaKnowledge::getTree($sCategory),
				'onSelect' => 'js:function(flag, node){
					node.data.select = true;
					$(node.span).find("input:checkbox").attr("checked", flag);
				}',
				'onRender' => 'js:function(node, nodeSpan){
					if (node.data.key=="_statusNode")
						return;
					var input=$("<input type=\'checkbox\' class=\'hide\'>").attr({"checked":node.data.select, name:"MediaKnowledgeCategory["+node.data.key+"]"});
					$(nodeSpan).append(input);
				}'
			),

		));
		?>
	</div>
</div>




	<?php echo $form->dropDownListRow($model,'genre', array(''=>'')+MediaKnowledge::$genreNames, array('class'=>'span7')); ?>

	<?php echo $form->dropDownListRow($model, 'whom_interest', array(
		'' => '',
		MediaKnowledge::WHOM_SPEC => 'Специалистам',
		MediaKnowledge::WHOM_USER => 'Владельцам квартир',
		MediaKnowledge::WHOM_SPEC_USER => 'Специалистам + владельцам квартир'
	), array('class' => 'span7'));?>

	<?php echo $form->textFieldRow($model,'cat_category_name',array('class'=>'span7','maxlength'=>255, 'disabled' => 'disabled')); ?>

	<div class="clearfix">
		<label for="MediaKnowledge_public_time"><?php echo $model->getAttributeLabel('public_time'); ?></label>
		<div class="input">
			<?php
			$this->widget('application.extensions.timepicker.EJuiDateTimePicker', array(
				'model' => $model,
				'attribute' => 'public_time',
				'options' => array(
					'autoLanguage' => false,
					'dateFormat' => 'dd.mm.yy',
					'timeFormat' => 'hh:mm',
					'changeMonth' => true,
					'changeYear' => false,
					'timeOnlyTitle' => 'Выберите время',
					'timeText' => 'Время',
					'hourText' => 'Часы',
					'minuteText' => 'Минуты',
					'secondText' => 'Секунды',
					'closeText' => 'Закрыть'
				),
			));
			?>
		</div>
	</div>

	<?php echo $form->dropDownListRow($model, 'status', MediaKnowledge::$statusNames); ?>

	<br>
	<label for="MediaKnowledge_read_more"><?php echo $model->getAttributeLabel('read_more'); ?></label>
	<br>

	<?php echo $form->textFieldRow($model,'article_first'); ?>
	<?php echo $form->dropDownListRow($model, 'model_first', MediaNew::$readMoreModel); ?>
	<?php echo $form->textFieldRow($model,'article_second'); ?>
	<?php echo $form->dropDownListRow($model, 'model_second', MediaNew::$readMoreModel); ?>
	<?php echo $form->textFieldRow($model,'article_third'); ?>
	<?php echo $form->dropDownListRow($model, 'model_third', MediaNew::$readMoreModel); ?>

	<div class="actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить',array('class'=>'btn primary')); ?>
	</div>




<?php $this->endWidget(); ?>
