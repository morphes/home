<?php
/** @var $cs CClientScript */
$cs = Yii::app()->getClientScript();
$cs->registerScriptFile('/js/jquery.Jcrop.min.js');
$cs->registerCssFile('/css/jquery.Jcrop.css');
$cs->registerCss('photo_tab', '
	#IndexProductPhoto_tabIds label {
		float: none;
	}
');
?>

<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'index-product-photo-form',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="help-block">Поля, отмеченные <span class="required">*</span>, обязательны.</p>

	<?php echo $form->errorSummary($model); ?>

	<?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>50)); ?>

	<?php echo $form->textFieldRow($model,'price',array('class'=>'span5')); ?>

    <?php echo $form->textFieldRow($model,'url',array('class'=>'span5', 'maxlength'=>255)); ?>

	<div class="row">
		<div class="span8 clearfix">
			<?php echo $form->textFieldRow($model, 'product_id',array('class'=>'span5 product_id', 'autocomplete' => 'off', 'id'=>'origin')); ?>
		</div>
		<div class="span2">
			<span id="show" class="btn primary small" title="Если поля «Название» и «Цена» пустые — заполняются автоматически">показать</span>
		</div>
	</div>

	<div id="replace" class="clearfix">
		<?php /* Сюда вставляется картинка
		         при клике на превью в списке фоток товара */ ?>
	</div>


	<?php echo $form->dropDownListRow($model, 'type', array(
		IndexProductPhoto::TYPE_PROMO => 'Промо',
		IndexProductPhoto::TYPE_PREVIEW => 'Превью'
	));?>

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




	<?php echo $form->dropDownListRow($model,'status', array('' => '( выберите статус )')+IndexProductPhoto::$statusName, array('class'=>'span5')); ?>

	<div class="clearfix">
		<label>Вкладки</label>
		<div class="input">
			<?php
			echo CHtml::activeCheckBoxList(
				$model,
				'tabIds',
				Chtml::listData(IndexProductTab::model()->findAll(),'id','name')
			);
			?>
		</div>
	</div>

	<div class="actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>



<script type="text/javascript">
	$(function(){
		$('#origin').keydown(function(event){
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
			aspectRatio: '<?php echo ($model->type == IndexProductPhoto::TYPE_PREVIEW) ? IndexProductPhoto::RATIO_PREVIEW : IndexProductPhoto::RATIO_PROMO;  ?>'

		}, function(){
			jCrop=this;
		});

		function updateCoords(c)
		{
			// Проценты смещения левого верхнего угла
			var percentX = 0;
			var percentY = 0;
			// Проценты выделения от общего размера
			var percentW = 0;
			var percentH = 0;

			if (imgWidth !== 0) {
				percentX = c.x/imgWidth;
				percentW = c.w/imgWidth;
			}

			if (imgHeight !== 0) {
				percentY = c.y/imgHeight;
				percentH = c.h/imgHeight;
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

		$('#replace').on('click', '.photos img', function(){
			var url = $(this).data('url');
			$('#photo_id').val( $(this).data('id') );

			var photo = $('#big_photo');
			photo.load(function(){
				$('#x').val(0);
				$('#y').val(0);
				$('#w').val(0);
				$('#h').val(0);
				jCrop.setImage(url);
				setTimeout(function(){
					setSelectCrop(jCrop);
				}, 200);
			});
			var origin=new Image();
			origin.onload = function(){
				imgWidth=this.width;
				imgHeight=this.height;
			};
			origin.src=url;
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

		$('#show').click(function(){
			var productId = parseInt( $('#origin').val() );
			if (isNaN(productId)){
				$('#origin').parents('.clearfix').addClass('error');
				return false;
			}

			$('#origin').parents('.clearfix').removeClass('error');

			$.ajax({
				url: 	'/admin/indexProductPhoto/ajaxGetPhotos',
				dataType: 'json',
				type: 'post',
				data: {product_id: productId},
				async: 	false,
				success:function (response) {
					if (response.success){
						$('#replace').html( response.html );

						if ($('#IndexProductPhoto_name').val() == '') {
							$('#IndexProductPhoto_name').val(response.name);
						}
						if ($('#IndexProductPhoto_price').val() == '') {
							$('#IndexProductPhoto_price').val(response.price);
						}

						// Если Фотка всего одна, кликать в нее.
						if ($('.photos .media-grid li').size() == 1) {
							$('.photos img').click();
						}
					}
				},
				error: function(response){
					if (response.statusText){
						alert(response.statusText);
					} else {
						window.location.reload();
					}
				}

			});

			return false;
		});

		// Смена типа фотографии (Промоблок / Превью)
		$('#IndexProductPhoto_type').change(function(){
			var type = $(this).val();

			switch (type) {
				case '<?php echo IndexProductPhoto::TYPE_PREVIEW;?>':
					jCrop.setOptions({aspectRatio: <?php echo IndexProductPhoto::RATIO_PREVIEW;?>});
					setSelectCrop(jCrop);
					break;
				default:
					jCrop.setOptions({aspectRatio: <?php echo IndexProductPhoto::RATIO_PROMO;?>});
					setSelectCrop(jCrop);
					break;
			}
		});

		// Установление рамки выделения
		function setSelectCrop(jCrop)
		{
			if ($('#IndexProductPhoto_type') == '<?php echo IndexProductPhoto::TYPE_PREVIEW;?>') {
				jCrop.setOptions({setSelect: [0, 0, 277, 200]});
			} else {
				jCrop.setOptions({setSelect: [0, 0, 238, 200]});
			}
		}
	});
</script>

<?php // Восстанавливаем значение после перезагрузки страницы ?>
<?php if ($model->product_id) : ?>
<script>
	$(function(){
		$('#show').click();

		<?php if (isset($imgData['photo'])) : ?>
		setTimeout(function(){
			$('.photos').find('img[data-id="<?php echo $imgData['photo'];?>"]').click();
		}, 700);
		<?php endif; ?>
	});
</script>
<?php endif; ?>