<?php
/**
 * @var $arrModels array
 */
$data_author = ($authorMode) ? 'data-author="1"' : '';
?>
<?php if ($authorMode) { ?>
<script>
	function findImageGoogle()
	{
		alert('aaa');
	}
</script>
<?php } ?>
<div id="player" <?php echo $data_author;?>>
	<?php
	if ( ! empty($arrModels))
		/** @var $imgComp ImageComponent */
		$imgComp = Yii::app()->img;
		foreach($arrModels as $item) {
			if ($item instanceof UploadedFile) {
				$image = array(
					'small' => '/'.$item->getPreviewName(Config::$preview['crop_80']),
					'big'	=> $item->getPreviewName(Config::$preview['resize_710x475']),
					'full'	=> $item->getPreviewName(Config::$preview['resize_1920x1080']),
					'title'	=> $item->desc,
				);

				$attributes = array(
					'href' => '/'.$image['full'], // Это для огромных фоток 1920*1080
					'data-preview' => '/'.$image['big'],
					'title' => ($image['title']) ? $image['title'] : '',
					'target' => '_blank'
				);
				if ($authorMode) {
					$url = Yii::app()->homeUrl.'/'.$image['big'];
					$attributes['data-copy']= 'http://www.tineye.com/search/?url='.$url;
				}

			} else { //($model instanceof Architecture) { // TODO: заплатка
				$image = array(
					'small' => $imgComp->getPreview($item, 'crop_80'),
					'big'	=> $imgComp->getPreview($item, 'resize_710x475'),
					'full'	=> $imgComp->getPreview($item, 'resize_1920x1080'),
					'title'	=> $imgComp->getDesc($item),
				);

				$attributes = array(
					'href' => $image['full'], // Это для огромных фоток 1920*1080
					'data-preview' => $image['big'],
					'title' => ($image['title']) ? $image['title'] : '',
					'target' => '_blank'
				);
				if ($authorMode) {
					$url = $image['big'];
					$attributes['data-copy']= 'http://www.tineye.com/search/?url='.$url;
				}
			}

			echo CHtml::openTag('a', $attributes);
                        echo !empty($image['title']) ? '<span class="has_descript"></span>' : '';
			echo CHtml::image($image['small'], '', array('width'=>45, 'height'=>45));
			echo CHtml::closeTag('a');
		}
	?>
</div>