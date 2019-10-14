<?php 

        $rooms = CHtml::listData($rooms, 'id', 'option_value');
        $colors = CHtml::listData($colors, 'id', 'option_value');
        $styles = array('' => '') + CHtml::listData($styles, 'id', 'option_value');

?>

<div class="project_rooms" id="interior_content_id_<?php echo $content->id;?>" data-id="<?php echo $content->id;?>">

	<?php echo CHtml::activeHiddenField($content, "[{$content->id}]id"); ?>

	<div class="input_row">
		<div class="input_conteiner">
			<?php echo CHtml::activeLabel($content, "[{$content->id}]room_id"); ?>
			<div class="build_type drop_down">
				<span class="exp_current"><?php if (isset($rooms[$content->room_id])) echo CHtml::encode($rooms[$content->room_id]);?><i></i></span>
				<ul class="set_input">
					<?php
					// -- ПОМЕЩЕНИЯ --
					if ($rooms) {
						foreach($rooms as $id=>$name) {
							$class = ($id == $content->room_id) ? 'active' : '';
							echo CHtml::tag('li', array('data-value' => $id, 'class' => $class), $name, true);
						}
					}
					?>
				</ul>
				<?php echo CHtml::activeHiddenField($content, "[{$content->id}]room_id");?>
			</div>
		</div>
		<div class="hint_conteiner">
			<div class="del_img">
				<span></span><a class="del_room" href="#" onclick="remove_sc(<?php echo $content->id ?>); return false;">Удалить</a>
			</div>
		</div>
		<div class="clear"></div>
	</div>
	<div class="input_row">
		<div class="input_conteiner">
			<div class="">
				<?php echo CHtml::activeLabel($content, "[{$content->id}]style_id"); ?>
				<div class="drop_down <?php if ($content->getError("style_id")) echo 'error';?>">
					<span class="exp_current"><?php if (isset($styles[$content->style_id])) echo CHtml::encode($styles[$content->style_id]);?><i></i></span>
					<ul class="set_input">
						<?php
						// -- СТИЛИ --
						if ($styles) {
							foreach($styles as $id=>$name) {
								$class = ($id == $content->style_id) ? 'active' : '';
								echo CHtml::tag('li', array('data-value' => $id, 'class' => $class), $name, true);
							}
						}
						?>
					</ul>
					<?php echo CHtml::activeHiddenField($content, "[{$content->id}]style_id");?>
				</div>
			</div>
		</div>
		<div class="hint_conteiner">

		</div>
		<div class="clear"></div>
	</div>

	<div class="input_row">
		<div class="input_conteiner">
			<?php
			// -- ОСНОВНОЙ ЦВЕТ --
			$colorsLi = '';
			$currentColor = '';
			if ($colors) {
				foreach($colors as $id=>$name) {
					if ($id == $content->color_id) {
						$class = 'active';
						$currentColor = $name;
					} else {
						$class = '';
					}

					$colorsLi .= CHtml::tag('li', array('data-value' => $id, 'class' => $class), $name, true);
				}
			}
			?>
			<div class="colors">
				<?php echo CHtml::activeLabel($content, "[{$content->id}]color_id"); ?>
				<div class="drop_down <?php if ($content->getError("color_id")) echo 'error';?>">
					<span class="exp_current"><?php echo $currentColor;?><i></i></span>
					<ul class="set_input">
						<?php echo $colorsLi;?>
					</ul>

					<?php echo CHtml::activeHiddenField($content, "[{$content->id}]color_id");?>
				</div>
			</div>

			<div class="colors">
				<label <?php if ($additional_colors[0]->getError('color_id')) echo 'class="error"';?>>Дополнительный</label>
				<div class="drop_down <?php if ($additional_colors[0]->getError('color_id')) echo 'error';?>">
					<span class="exp_current"><?php if (isset($colors[$additional_colors[0]->color_id])) echo $colors[$additional_colors[0]->color_id];?><i></i></span>
					<ul class="set_input">
						<?php
						// -- ДОПОЛНИТЕЛЬНЫЙ ЦВЕТ 1 --
						if ($colors) {
							foreach($colors as $id=>$name) {
								$class = ($id == $additional_colors[0]->color_id) ? 'active' : '';
								echo CHtml::tag('li', array('data-value' => $id, 'class' => $class), $name, true);
							}
						}
						?>
					</ul>
					<?php echo CHtml::activeHiddenField($additional_colors[0], "[{$content->id}][0]color_id");?>
				</div>
			</div>

			<div class="colors last">
				<label <?php if ($additional_colors[1]->getError('color_id')) echo 'class="error"';?>>Дополнительный</label>
				<div class="drop_down <?php if ($additional_colors[1]->getError('color_id')) echo 'error';?>">
					<span class="exp_current"><?php if (isset($colors[$additional_colors[1]->color_id])) echo $colors[$additional_colors[1]->color_id];?><i></i></span>
					<ul class="set_input">
						<?php
						// -- ДОПОЛНИТЕЛЬНЫЙ ЦВЕТ 2 --
						if ($colors) {
							foreach($colors as $id=>$name) {
								$class = ($id == $additional_colors[1]->color_id) ? 'active' : '';
								echo CHtml::tag('li', array('data-value' => $id, 'class' => $class), $name, true);
							}
						}
						?>
					</ul>
					<?php echo CHtml::activeHiddenField($additional_colors[1], "[{$content->id}][1]color_id");?>
				</div>
			</div>
		</div>
		<div class="hint_conteiner">

		</div>
		<div class="clear"></div>
	</div>
	<div class="image_uploaded">
		<?php
		if ( ! empty($uploadedFiles)) {
			foreach ($uploadedFiles as $uf) { ?>
				<div class="uploaded">
					<div class="input_row image_inp">
						<div class="input_conteiner">
							<div class="input_conteiner_img"><img src="<?php echo '/'.$uf->getPreviewName(Config::$preview['crop_150'], 'interiorContent');?>" /></div>
							<label>Описание изображения</label>
							<?php echo CHtml::textArea('UploadImage[filedesc]['.$uf->id.']', $uf->desc, array('class'=>'textInput img_descript')); ?>
							<div class="clear"></div>
							<div class="del_cover"><i></i><a class="del_img" data-id="<?php echo $uf->id;?>" data-type="content" href="#">Удалить</a></div>
						</div>
					</div>
					<div class="hint_conteiner"></div>
				</div>
				<?php
			}
		}
		?>
	</div>
	<div class="clear"></div>
	<div class="image_to_upload">
		<div class="img_input_conteiner to_del">

			<div class="input_row">
				<div class="input_conteiner <?php if ($content->getError('image_id')) echo 'error';?>">
					<label <?php if ($content->getError('image_id')) echo 'class="error"';?>>Добавить изображение</label>
					<input  name="" type="file" class="img_input" size="61" data-type="content" data-content-id="<?php echo $content->id;?>" />
					<div class="img_mask">
						<input type="text" class="textInput img_input_text" />
					</div>
					<div class="clear"></div>
				</div>
				<div class="hint_conteiner">
					<div class="del_img hide">
						<span></span><a href="#">Удалить</a>
					</div>
				</div>
				<div class="clear"></div>
			</div>

                        <div class="input_row hide">
                                <div class="input_conteiner">
                                        <label>Описание изображения</label>
                                        <textarea  name="" class="textInput"></textarea>
                                </div>
                                <div class="hint_conteiner">

                                </div>
                                <div class="clear"></div>
                        </div>
			<div class="input_spacer hide"></div>
		</div>
	</div>
	<div class="black_border"></div>
</div>