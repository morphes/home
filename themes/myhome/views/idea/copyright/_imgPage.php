<p class="signature"><img src="img/tmp/signature.png" /></p>

<div class="top_space_img"></div>
<div id="wrapper">
	<div class="project_image">
		<p style="font-size: 16px; font-weight: bold;"><?php echo $projName;?><br>Изображение <?php echo $curCntImg;?> из <?php echo $allCntImg;?></p>
		<?php echo CHtml::image($photo->getPreviewName(Config::$preview['resize_710x475']),'') ;?>
	</div>
	<div id="image_info">
		<table class="table">
			<tr>
				<td class="l-col">Расположение изображения</td>
				<td class="bold">
					<?php
					$link = Yii::app()->homeUrl.'/'.$photo->getPreviewName(Config::$preview['resize_710x475']);
					$originalLink = $link;
					// Разрезаем длинную ссылку на два кусочка, чтобы 
					// организовать перенос на две строки
					$strLink1 = mb_substr($link, 0, 65, 'UTF-8');
					$strLink2 = mb_substr($link, 65, mb_strlen($link)-1, 'UTF-8');
					echo CHtml::link($strLink1.' '.$strLink2, $originalLink);
					?>
				</td>
			</tr>
			<tr>
				<td>ID изображения</td>
				<td class="bold"><?php echo $photo->id;?></td>
			</tr>
			<tr>
				<td>Дата добавления изображения</td>
				<td class="bold">(MSK, UTC+4) <?php echo date('d/m/Y в H:i:s', $photo->create_time);?></td>
			</tr>
		</table>
	</div>
</div>