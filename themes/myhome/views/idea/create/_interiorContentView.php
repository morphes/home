<?php 

        $rooms = array(''=>'Выберите помещение') + CHtml::listData($rooms, 'id', 'option_value');
        $colors = array('' => 'Выберите цвет') + CHtml::listData($colors, 'id', 'option_value');
        $styles = array('' => 'Выберите стиль') + CHtml::listData($styles, 'id', 'option_value');

?>

<div>
        <div class="row">      
                <?php echo CHtml::tag('h3', array(), $rooms[ $content->room_id ], true);?>      
        </div>
	
	<div class="row">
		<?php
		echo CHtml::label('Обложка помещения', '');
		$this->renderPartial('idea.views.admin.interior._image', array(
			'uploadedImage' => $mainImage
		));
		?>
	</div>
	
	<div class="row">
                <?php
		echo CHtml::label('Метки от автора', '');
		echo $content->tag;
		?>
        </div>

        <div class="row">     
                <?php 
		echo CHtml::label('Основной стиль', '');
		if ($content->style_id) echo $styles[ $content->style_id ];
                ?>
        </div>

        <div class="row">
		<table><tr>
		<td>
			<?php 
			echo CHtml::label('Основной цвет', '');
			if ($content->color_id) echo $colors[ $content->color_id ];
			?>
		</td>
        
		<td>
			<?php 
			echo CHtml::label('Дополнительный цвет', '');
			if(!empty($additional_colors)) {
				foreach($additional_colors as $color){
					echo $colors[ $color->color_id ].' ';
				}
			}
			?>
		</td>
		</tr></table>
        </div>        
        
	
	<div class="row">
		<?php 
		echo CHtml::label('Изображения помещения','');
		foreach ($uploadedFiles as $uploadedFile) {
			$this->renderPartial('idea.views.admin.interior._image', array(
				'uploadedImage' => $uploadedFile
			));
		}
		?>
	</div>
	<hr style="height: 2px;" />
</div>
