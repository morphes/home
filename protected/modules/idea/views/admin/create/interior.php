<?php
/**
 * @var $interior      Interior
 * @var $interiorImage UploadedFile
 */

/** @var $cs CClientScript */
$cs = Yii::app()->clientScript;
$cs->registerScriptFile('/js/simple.lightbox.admin.js', CClientScript::POS_HEAD);
$cs->registerCoreScript('jquery.ui');
?>

<?php
$this->breadcrumbs = array(
	'Идеи'      => array('/idea/admin/interior/list'),
	'Интерьеры' => array('/idea/admin/interior/list'),
	'Редактирование интерьера'
);
?>

<script type="text/javascript">
	var color_counter = new Array();
</script>

<h1>Редактирование интерьера #<?php echo $interior->id; ?>
	<?php echo !empty($interior->name) ? ' - "' . $interior->name . '"'
		: ''; ?></h1>


<?php
if ($interior->author_id) {
	$author = CHtml::link($interior->author->login . ' (' . $interior->author->name . ')', $this->createUrl('/member/profile/user/', array('id' => $interior->author->id)));
} else $author = '';
$this->widget('ext.bootstrap.widgets.BootDetailView', array(
	'data'       => $interior,
	'attributes' => array(
		array(
			'label' => 'Автор',
			'type'  => 'html',
			'value' => $author,
		),
		array(
			'label' => 'Дата создания',
			'type'  => 'raw',
			'value' => date('d.m.Y H:i', $interior->create_time),
		),
		array(
			'label' => 'Статус',
			'type'  => 'html',
			'value' => "<span class='label success'>" . Interior::getStatusName($interior->status) . "</span>",
		),
	),
));
?>


<?php /** @var $form BootActiveForm */
$form = $this->beginWidget('ext.bootstrap.widgets.BootActiveForm', array(
	'id'                   => 'idea-making-form-step-2',
	'enableAjaxValidation' => false,
	'htmlOptions'          => array('class' => 'form-project-add'),
	'stacked'              => true,
)); ?>

<?php echo $form->errorSummary($interior); ?>

<div class="well" style="background-color: #F9F9F9;">
	<?php echo CHtml::tag('h2', array('style' => 'margin-bottom: 20px;'), 'Общие настройки', true); ?>

	<div class="clearfix">
		<?php echo CHtml::label('Автор', 'author'); ?>

		<div class="input">
			<?php $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
				'name'        => 'author',
				'sourceUrl'   => '/utility/autocompleteuser',
				'value'       => isset($interior->author->name)
					? $interior->author->name . " ({$interior->author->login})"
					: '',
				'options'     => array(
					'showAnim'  => 'fold',
					'delay'     => 0,
					'autoFocus' => true,
					'select'    => 'js:function(event, ui) {$("#Interior_author_id").val(ui.item.id); }',
				),
				'htmlOptions' => array('class' => 'span7')
			));?>

			<?php echo $form->hiddenField($interior, 'author_id', array('size' => 15)); ?>

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



	<?php echo $form->dropDownListRow($interior, 'object_id', array('' => 'Выберите объект') + CHtml::listData($objects, 'id', 'option_value'), array(
		'ajax' => array(
			'type'     => 'POST',
			'url'      => CController::createUrl($this->id . '/dynamicdropdowns'),
			'dataType' => 'json',
			'success'  => 'function(data){
                                                        $("#content_container .rooms").each(function(index){
                                                                $(this).html(data.rooms);
                                                        });
                                                        $("#content_container .styles").each(function(index){
                                                                $(this).html(data.styles);
                                                        });
                                                        $("#content_container .colors").each(function(index){
                                                                $(this).html(data.colors);
                                                        });
                                                }',
		)
	));
	?>

	<?php echo $form->textFieldRow($interior, 'name', array('class' => 'span12')); ?>

	<?php echo $form->textAreaRow($interior, 'desc', array('class' => 'span12', 'style' => 'height:150px;')); ?>

</div>

<div class="well" style="background-color: #F9F9F9;">

	<?php echo CHtml::tag('h2', array('style' => 'margin-bottom: 20px;'), 'Обложка интерьера', true); ?>
	<div class="clearfix fpa-space form-stacked">
		<?php echo $form->labelEx($interior, 'image_id', array('id' => 'main-label')); ?>


		<?php if (!is_null($interiorImage)) : ?>
			<div class="row" id="main-image">
				<span class="span3">
					<a href="<?php echo '/' . $interiorImage->getPreviewName(Interior::$preview['resize_710x475']); ?>"
					   class="preview"
					   title="<?php echo $interiorImage->getOriginalImageSize(); ?>">
						<?php echo CHtml::image('/' . $interiorImage->getPreviewName(Interior::$preview['crop_150']), '', array('width' => 150, 'height' => 150)); ?>
					</a>
				</span>

				<div class="clearfix span12">
					<label>Описание<br><textarea maxlength="1000"
								     rows="6"
								     data-id="<?php echo $interiorImage->id; ?>"
								     class="span12 img_desc"><?php echo $interiorImage->desc; ?></textarea></label>
				</div>
				<div style="clear: both"></div>
			</div>
		<?php endif; ?>


		<?php $this->widget('ext.FileUpload.FileUpload', array(
			'url'         => '/' . $this->module->id . '/' . $this->id . '/upload',
			'postParams'  => array('id' => $interior->id, 'type' => 'interior'),
			'config'      => array(
				'maxConnections' => 1,
				'fileName'       => 'Interior[file]',
				'onSuccess'      => 'js:function(response){ if (response.success) {$("#main-image").remove(); $("#main-label").after(response.html); } }',
				'onStart'        => 'js:function(data){ }',
				'onFinished'     => 'js:function(data){ }'
			),
			'htmlOptions' => array('size' => 61, 'accept' => 'image', 'class' => 'img_input', 'multiple' => false),
		)); ?>
	</div>
</div>

<div class="well"
     style="background-color: #F9F9F9;">
	<?php echo CHtml::tag('h2', array('style' => 'margin-bottom: 20px;'), 'Соавторы', true); ?>
	<?php $this->renderPartial('_getCoAuthor', array('coauthors' => $coauthors, 'coauthorErrors' => $coauthorErrors)); ?>
</div>

<div class="well"
     style="background-color: #F9F9F9;">
	<?php echo CHtml::tag('h2', array('style' => 'margin-bottom: 20px;'), 'Источники', true); ?>
	<?php $this->renderPartial('_source', array('sources' => $sources, 'coauthorErrors' => $coauthorErrors, 'interior' => $interior, 'form' => $form)); ?>
</div>

<? // *** ПЛАНИРОВКИ *** ?>

<div class="well" style="background-color: #F9F9F9;">
	<?php echo CHtml::tag('h2', array('style' => 'margin-bottom: 20px;'), 'Планировки', true); ?>


	<div id="layout-container" class="fpa-space clearfix form-stacked">

		<?php /** @var $layout UploadedFile */
		foreach ($layouts as $layout) : ?>
			<div class="row">
				<span class="span3">
					<a href="<?php echo '/' . $layout->getPreviewName(Interior::$preview['resize_710x475']); ?>"
					   class="preview"
					   title="<?php echo $layout->getOriginalImageSize(); ?>">
						<?php echo CHtml::image('/' . $layout->getPreviewName(Interior::$preview['crop_150']), '', array('width' => 150, 'height' => 150)); ?>
					</a>
				</span>

				<div class="clearfix span12">
					<label>Описание<br><textarea maxlength="1000"
								     data-id="<?php echo $layout->id; ?>"
								     rows="6"
								     class="span12 img_desc"><?php echo $layout->desc; ?></textarea></label>
				</div>

				<div class="span3">
					<a class="img_del"
					   data-parent="<?php echo $interior->id; ?>"
					   data-type="layout"
					   data-id="<?php echo $layout->id; ?>"
					   href="#">Удалить</a>
				</div>
				<div style="clear: both"></div>
			</div>
			<div class="clearfix"></div>
		<?php endforeach; ?>


	</div>
	<div class="fpa-space clearfix form-stacked">
		<?php $this->widget('ext.FileUpload.FileUpload', array(
			'url'         => '/' . $this->module->id . '/' . $this->id . '/upload',
			'postParams'  => array('id' => $interior->id, 'type' => 'layout'),
			'config'      => array(
				'maxConnections' => 1,
				'fileName'       => 'Interior[file]',
				'onSuccess'      => 'js:function(response){ if (response.success) { $("#layout-container").append(response.html); } }',
				'onStart'        => 'js:function(data){ }',
				'onFinished'     => 'js:function(data){ }'
			),
			'htmlOptions' => array('size' => 61, 'accept' => 'image', 'class' => 'img_input'),
		)); ?>
	</div>

</div>

<? // *** ПОМЕЩЕНИЯ *** ?>


<div class="well" style="background-color: #F9F9F9;">
	<?php echo CHtml::tag('h2', array('style' => 'margin-bottom: 20px;'), 'Помещения', true); ?>


	<input type="hidden"
	       name="content_counter"
	       id="content_counter"
	       value="0">

	<div class="clearfix">
		<?php echo CHtml::ajaxSubmitButton('Добавить помещение', '', array('update' => '.tab-content', 'beforeSend' => 'js:function(html){ }', 'success' => 'js:function(html){data = $.parseJSON(html); $(".tab-content").append(data.html); $(".tabs").append(data.tab); $(".tabs").tabs().find("a:last").click(); }'), array('class' => 'primary btn', 'id' => 'ajax-interior-form-request')); ?>
	</div>

	<div id="content_container"
	     class="clearfix">

		<?php $this->widget('application.components.widgets.InteriorContentFormsAdmin', array('interior' => $interior, 'errors' => $errors)); ?>

	</div>
</div>


<?php echo $form->dropDownListRow($interior, 'status', Interior::getStatusName(), array(
	'class' => 'interior_status'
)); ?>


<?php
/* -----------------------------------------------------------------------------
 *  Виджет выбора шаблона сообщения для статуса "Отклонен"
 * -----------------------------------------------------------------------------
 */
$this->widget('application.modules.idea.components.InteriorRejectedMessage.InteriorRejectedMessage', array(
	'selectorForStatus'    => '.interior_status',
	'selectorMessageField' => '#user_message',
	'authorName'           => $interior->author->name,
	'projectName'          => $interior->name
));?>


<div class="clearfix">
	<label>Сообщение пользователю</label>

	<div class="input">
		<?php echo CHtml::textArea('user_message', '', array('class' => 'span13', 'rows' => 15)); ?>
	</div>
</div>

<?php echo CHtml::tag('hr', array('style' => 'height: 2px; background-color: black;')); ?>

<div class="actions">
	<?php echo CHtml::submitButton('Сохранить', array('class' => 'btn primary large')); ?>
	<?php echo CHtml::button('Отмена', array('class' => 'btn default large', 'onclick' => "document.location = '" . $this->createUrl('/' . $this->module->id . '/admin/interior/view', array('interior_id' => $interior->id)) . "'")); ?>
</div>

<?php $this->endWidget(); ?>



<script type="text/javascript">

	function append_color(id) {

		if (color_counter[id] == undefined) {
			color_counter[id] = 0;
		}

		$.ajax({
			url: '/<?php echo $this->module->id.'/'.$this->id?>/append_color/id/' + id + '/counter/' + color_counter[id],
			async: false,
			success: function (data) {
				$('#additional-color-' + id).append(data);
				color_counter[id]++;
			}
		});
	}

	function remove_scc(sc_id, pos) {
		$.ajax({
			url: '/<?php echo $this->module->id.'/'.$this->id?>/delete_scc/sc_id/' + sc_id + '/pos/' + pos,
			async: false,
			success: function (data) {
				if (data == 'ok') {
					$('#additional-color-' + sc_id + '-' + pos).remove();
				} else {
					alert(data);
				}
			}
		});
	}

	function remove_sc(id) {
		var answer = confirm('Вы действительно хотите удалить помещение?');
		if (answer) {
			$.ajax({
				url: '/<?php echo $this->module->id.'/'.$this->id?>/delete_sc/id/' + id,
				async: false,
				success: function (data) {
					if (data == 'ok') {
						$('#interior_content_id_' + id).remove();
						$('#tab_' + id).remove();
					} else {
						alert(data);
					}
				}
			});
		}
	}

	$("#coauthor-create-button").click(function () {
		$.ajax({
			url: "<?php echo '/'.$this->module->id.'/'.$this->id.'/getcoauthor'; ?>",
			data: {"interiorId":<?php echo $interior->id; ?>},
			dataType: "json",
			type: "post",
			success: function (response) {
				if (response.success) {
					$(".coauthor-container").append(response.data);
				}
			}
		});
	});


	function removeCoauthor(self, coauthorId) {
		$.ajax({
			url: "<?php echo '/'.$this->module->id.'/'.$this->id.'/removecoauthor'; ?>",
			data: {"coauthorId": coauthorId},
			dataType: "json",
			type: "post",
			success: function (response) {
				if (response.success) {
					$(self).parents("tr").remove();
				}
			}
		});
		return false;
	}

	$("#source-create-button").click(function () {
		$.post(
			"/content/admin/source/createmultiple",
			{ model_id: "<?php echo $interior->id?>" },
			function (response) {
				if (response.success) {
					$(".source-container").append(response.html);
				}
			},
			"json"
		);
	});

	function removeSource(self, sourceId) {
		$.post(
			'/content/admin/source/deletemultiple/id/' + sourceId,
			function (response) {
				if (response.success) {
					$(self).parents('tr').remove();
				} else {
					alert('Ошибка удаления');
				}
			}, 'json'
		);
	}

	$('.tag-area').live('keydown', function (e) {
		var code = e.keyCode || e.which;
		var value = $(this).val().replace(/^\s+|\s+$/g, "");
		if (code == '9' && value.length > 0 && value.charAt(value.length - 1) != ',') {
			$(this).val(value + ', ');
			return false;
		} else if (code == '9') {
			return false;
		}
	});

	$('.rooms').live('change', function () {
		var data_id = $(this).attr('data-id');
		var text = $('#InteriorContent_' + data_id + '_room_id').find("option:selected").text();
		$('#tab_' + data_id + ' > a').html(text);
	});

	var admIdea = new AdmIdea();
	function AdmIdea() {
		var self = this;

		this.init = function () {


			/** Remove image */
			$('#idea-making-form-step-2').on('click', '.row a.img_del', function () {
				var item = $(this);
				$.ajax({
					url: "<?php echo '/'.$this->module->id.'/'.$this->id.'/removeimage'; ?>",
					data: {"type": item.data('type'), 'id': item.data('id'), 'parentId': item.data('parent')},
					dataType: "json",
					type: "post",
					success: function (response) {
						if (response.success) {
							item.parents('.row').remove();
						}
						if (response.error) {
							alert(error.message);
						}
					},
					error: function (error) {
						alert(error);
					}
				});


				return false;
			});

			/** Обновление описания страницы */
			$('#idea-making-form-step-2').on('focusout', '.row textarea.img_desc', function () {
				var item = $(this);
				$.ajax({
					url: "<?php echo '/'.$this->module->id.'/'.$this->id.'/imagedesc'; ?>",
					data: {'id': item.data('id'), 'html': item.val()},
					dataType: "json",
					type: "post",
					success: function (response) {
						if (response.success) {
							item.parent().parent().removeClass('error');
						}
						if (response.error) {
							item.parent().parent().addClass('error');
						}
					},
					error: function (error) {
						item.parent().parent().addClass('error');
					}
				});


				return false;
			});
		}

		this.init();


	}
</script>


<?php $this->rightbar = $this->renderPartial('_journal', array('journal' => $journal, 'interior' => $interior), true); ?>


