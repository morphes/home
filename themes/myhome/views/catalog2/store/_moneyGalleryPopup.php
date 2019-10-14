<?php // --- Форма добавления фотогарфии --- ?>

<h2 class="-giant -inset-bottom-hf">Добавление фото</h2>
<div class="-grid">
	<form method="post" enctype="multipart/form-data">

		<?php if (!$data->isNewRecord) { ?>
			<input type="hidden" name="photoId" value="<?php echo $data->id;?>">
		<?php } ?>

		<div class="-col-2">
			<span class="photo-preview" style="overflow: hidden;">
				<!-- <img src="/img-new/tmp/" class="-quad-140"> -->
				<?php
				if ($data->preview) {
					echo CHtml::image(
						'/' . $data->preview->getPreviewName(StoreGallery::$preview['crop_140']),
						$data->name
					);
				}
				?>
			</span>
			<a class="-file-input -button -button-skyblue -gutter-bottom-hf">
				Выбрать файл
				<?php echo CHtml::activeFileField($data, 'image', array('class' => 'photo_for_gallery')); ?>
			</a>
			<div class="-text-align-center">
				<span class="-icon-cross-circle-xs -pseudolink -small -red delete_photo"><i>Удалить</i></span>
			</div>
		</div>
		<div class="-col-5">
			<?php echo CHtml::activeTextField($data, 'name', array('class' => '-gutter-bottom', 'placeholder' => 'Название'));?>
			<?php echo CHtml::activeTextArea($data, 'description', array('placeholder' => 'Описание'));?>
		</div>
		<div class="-col-5 -skip-2 -gutter-top">
			<button class="-button -button-skyblue -huge -semibold -gutter-right">Опубликовать</button>
			<a href="#" class="cancel -gray -large -gutter-left">Отмена</a>
		</div>

		<div class="-error-list -col-5 -skip-2 error-send-photo -hidden">
			<i class="-icon-alert"></i>
			<ol>
				<?php // Место для ошибок; ?>
			</ol>
		</div>

	</form>
</div>
