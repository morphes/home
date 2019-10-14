<?php
/**
 * @var $data StoreNews Экземпляр новости магазина
 */
?>
<div class="item">
	<div class="-col-2 -inset-top-hf">
		<?php
		if ($data->preview) {
			echo CHtml::image(
				'/' . $data->preview->getPreviewName(StoreNews::$preview['crop_140']),
				'',
				array('width' => 140, 'height' => 140, 'class' => '-quad-140')
			);
		} else {
			echo CHtml::image(
				'/' . UploadedFile::model()->getPreviewName(Config::$preview['crop_150']),
				'',
				array('width' => 140, 'height' => 140, 'class' => '-quad-140')
			);
		}
		?>
	</div>
	<div class="-col-6">
		<?php
		if (!$data->title) {
			echo 'Название не задано';
		} else {
			echo CHtml::link(
				$data->title,
				StoreNews::getLink($store, 'element', $data->id),
				array('class' => '-huge -strong')
			);
		}
		?>
		<p class="-gutter-top-hf -gutter-bottom-hf"><?php
			if (!$data->content) {
				echo 'Текст новости не указан';
			} else {
				echo nl2br(Amputate::getLimb($data->content, 400));
			}
		?></p>
		<span class="-small -gray"><?php echo CFormatterEx::formatDateToday($data->create_time);?></span>
	</div>
	<span class="controls" data-newsId="<?php echo $data->id;?>">
		<i class="-icon-pencil-xs"></i>
		<i class="-icon-cross-circle-xs"></i>
	</span>
</div>