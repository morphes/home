<div class="article_photo">
	<div class="content_player">
		<div id="player_<?php echo $numGallery;?>">
			<?php
			$html = '';
			foreach ($models as $model) {
				$html .= CHtml::openTag('a', array(
					'href'             => '#', // Большая фотка на всплывашке
					'data-preview'     => '/'.$model->image->getPreviewName($model::$preview['crop_700x450']),
					'target'           => '_blank',
					'title'            => $model->image->desc, // Короткое описание
					'data-descript'    => $model->description
				));
				$html .= CHtml::image('/'.$model->image->getPreviewName($model::$preview['crop_60']), '', array('width' => 60));
				$html .= CHtml::closeTag('a');
			}
			echo $html;
			?>
		</div>
	</div>
</div>