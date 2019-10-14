<?php
/**
 * @var $data StoreNews
 */
?>

<div class="-grid">
	<h2 class="-giant -inset-bottom-hf -inset-left-hf">Добавление публикации</h2>
	<form data-newsId="<?php echo $data->id;?>">

		<input type="hidden" name="newsId" value="<?php echo $data->id;?>">

		<div class="-col-2 -semibold">Изображение</div>
		<div class="-col-6 -gutter-bottom-dbl">
			<span class="pic_new" style="overflow: hidden;">
				<?php
				if ($data->preview) {
					echo CHtml::image(
						'/' . $data->preview->getPreviewName(StoreNews::$preview['crop_140']),
						'',
						array('width' => 140, 'height' => 140, 'class' => '-quad-140')
					);
				}
				?>
			</span>
			<a class="-file-input -button -button-skyblue -relative -gutter-bottom-hf">
				Выбрать файл
				<input type="file" class="picture_for_news">
			</a>
			<?php if ($data->preview) { ?>
				<div class="-inset-left">
					<a class="-inset-left -icon-cross-circle-xs -pseudolink -small -red delete_photo" data-photoId="<?php echo $data->image_id;?>">
						<i>Удалить</i>
					</a>
				</div>
			<?php } ?>

		</div>
		<div class="-col-2 -semibold">Заголовок</div>
		<div class="-col-6 -gutter-bottom-dbl">
			<?php echo CHtml::activeTextField(
				$data,
				'title',
				array(
					'class'       => '-large',
					'placeholder' => 'Заголовок акции должен быть кратким и емким'
				)
			); ?>
		</div>
		<div class="-col-2 -semibold">Текст</div>
		<div class="-col-6">
			<?php
			echo CHtml::activeTextArea(
				$data,
				'content',
				array(
					'class'       => '-large',
					'placeholder' => 'Содержание акции'
				)
			); ?>
		</div>
		<div class="-col-6 -skip-2 -gutter-top">
			<button class="-button -button-skyblue -huge -semibold -gutter-right">Опубликовать</button>
		</div>

		<div class="-error-list -col-6 -skip-2 error-send-news -hidden">
			<i class="-icon-alert"></i>
			<ol>
				<?php // Место для ошибок; ?>
			</ol>
		</div>

	</form>

</div>