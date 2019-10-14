<?php
/** @var $cs CClientScript */
$cs = Yii::app()->getClientScript();
$cs->registerScriptFile('/js/jquery.Jcrop.min.js');
$cs->registerCssFile('/css/jquery.Jcrop.css');
$cs->registerCss('photo_tab', '
	#IndexSpecPhoto_blockIds label {
		float: none;
	}
');
?>


<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'index-spec-photo-form',
	'enableAjaxValidation'=>false,
)); ?>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<div class="span8 clearfix">
			<div class="clearfix">
				<?php echo CHtml::label('Логин / ФИО', 'author'); ?>

				<div class="input">
					<?php $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
						'name'        => 'author',
						'sourceUrl'   => '/utility/autocompleteuser',
						'value'       => isset($model->user->name)
							? $model->user->name . " ({$model->user->login})"
							: '',
						'options'     => array(
							'showAnim'  => 'fold',
							'delay'     => 0,
							'autoFocus' => true,
							'select'    => 'js:function(event, ui) {$("#origin").val(ui.item.id); }',
						),
						'htmlOptions' => array('size' => 15)
					));?>

					<?php echo $form->hiddenField($model,'model_id',array('size'=>15, 'id' => 'origin')); ?>

					<?php
					Yii::app()->clientScript->registerScript('loginType', '
						$("#author").keydown(function(event){
							if (
								event.keyCode != 27 && event.keyCode != 9 && event.keyCode != 123
								&& event.keyCode != 37 && event.keyCode != 38 && event.keyCode != 39 && event.keyCode != 40
								&& event.keyCode != 35 && event.keyCode != 36
							) {
								$("#IndexSpecPhoto_model_id").val("");
							}
						});
					', CClientScript::POS_READY);
					?>
				</div>
			</div>
		</div>
		<div class="span2">
			<span id="show"
			      class="btn primary small"
			      title="Если поля «Название» пустое, заполняются автоматически">показать</span>
		</div>
	</div>


	<div id="replace"
	     class="clearfix">
		<?php /* Сюда вставляется картинка
					 при клике на превью в списке фоток товара */
		?>
	</div>


	<div class="clearfix">
		<label>Пикча</label>
		<div class="input">
			<?php echo CHtml::image('', '', array('id'=>'big_photo')); ?>
			<div class="big_photo_info"></div>
			<div class="cut_photo_info"></div>

			<?php
			/** @var $image UploadedFile */
			if ($model->image) {
				echo CHtml::image($model->getImageFullPath());
			}
			?>
		</div>
	</div>

	<input type="hidden" id="x" name="img[x]">
	<input type="hidden" id="y" name="img[y]">
	<input type="hidden" id="w" name="img[w]">
	<input type="hidden" id="h" name="img[h]">
	<input type="hidden" id="photo_id" name="img[photo]" />


	<?php echo $form->dropDownListRow($model, 'status', array('' => '( выберите статус )') + IndexSpecPhoto::$statusName, array('class' => 'span5')); ?>

	<?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>255)); ?>

	<div class="clearfix">
		<label>Ссылки</label>
		<div class="input">
			<?php
			echo CHtml::activeCheckBoxList(
				$model,
				'blockIds',
				Chtml::listData(IndexSpecBlock::model()->findAll(),'id','name')
			);
			?>
		</div>
	</div>

	<div class="actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>



	<script type="text/javascript">
		$(function () {
			$('#origin').keydown(function (event) {
				if (event.keyCode == 13) {
					$('#show').trigger('click');
					return false;
				}
			});
			var jCrop;
			var imgWidth = 0;
			var imgHeight = 0;
			var originalWidth = 0;
			var originalHeight = 0;

			$('#big_photo').Jcrop({
				onSelect: updateCoords,
				aspectRatio: 95 / 95

			}, function () {
				jCrop = this;
			});

			function updateCoords(c) {
				// Проценты смещения левого верхнего угла
				var percentX = 0;
				var percentY = 0;
				// Проценты выделения от общего размера
				var percentW = 0;
				var percentH = 0;

				if (imgWidth !== 0) {
					percentX = c.x / imgWidth;
					percentW = c.w / imgWidth;
				}

				if (imgHeight !== 0) {
					percentY = c.y / imgHeight;
					percentH = c.h / imgHeight;
				}

				$('#x').val(percentX);
				$('#y').val(percentY);
				$('#w').val(percentW);
				$('#h').val(percentH);

				$('.cut_photo_info').text('Вырезаемый кусок: '
					+ parseInt(originalWidth * percentW)
					+ 'x'
					+ parseInt(originalHeight * percentH)
				);
			}

			$('#replace').on('click', '.photos img', function () {
				var url = $(this).data('url');
				$('#photo_id').val($(this).data('id'));

				var photo = $('#big_photo');
				photo.load(function () {
					$('#x').val(0);
					$('#y').val(0);
					$('#w').val(0);
					$('#h').val(0);
					jCrop.setImage(url);
					setTimeout(function () {
						setSelectCrop(jCrop);
					}, 200);
				});
				var origin = new Image();
				origin.onload = function () {
					imgWidth = this.width;
					imgHeight = this.height;
				};
				origin.src = url;
				photo.attr('src', url);

				// Показываем размеры фото-оригинала
				originalWidth = $(this).attr('data-original_width');
				originalHeight = $(this).attr('data-original_height');

				$('.big_photo_info').text(
					'Размер оригинала: '
						+ originalWidth
						+ 'x'
						+ originalHeight
				);

				return false;
			});

			$('#show').click(function () {

				var $origin = $('#origin');

				var modelId = parseInt($origin.val());
				if (isNaN(modelId)) {
					$('#origin').parents('.clearfix').addClass('error');
					return false;
				}

				$origin.parents('.clearfix').removeClass('error');

				$.ajax({
					url: '/admin/indexSpecPhoto/ajaxGetPhoto',
					dataType: 'json',
					type: 'post',
					data: {model_id: modelId},
					async: false,
					success: function (response) {
						if (response.success) {
							$('#replace').html(response.html);
							var photoName = $('#IndexSpecPhoto_name');
							if (photoName.val() == '') {
								photoName.val(response.name);
							}

							// Если Фотка всего одна, кликать в нее.
							if ($('.photos .media-grid li').size() == 1) {
								$('.photos img').click();
							}
						}
					},
					error: function(response) {

						if (response.statusText) {
							$('<div>' + response.responseText + '('+ response.statusText +')</div>').dialog({
								buttons: {
									"Закрыть": function() {
										$( this ).dialog( "close" );
									}
								},
								dialogClass: 'alert'
							});
						}
						return false;
					}

				});

				return false;
			});


			// Установление рамки выделения
			function setSelectCrop(jCrop) {
				jCrop.setOptions({setSelect: [0, 0, 95, 95]});
			}
		});
	</script>

<?php // Восстанавливаем значение после перезагрузки страницы ?>
<?php if ($model->model_id) : ?>
	<script>
		$(function () {
			$('#show').click();

			<?php if (isset($imgData['photo'])) : ?>
			setTimeout(function () {
				$('.photos').find('img[data-id="<?php echo $imgData['photo'];?>"]').click();
			}, 700);
			<?php endif; ?>
		});
	</script>
<?php endif; ?>