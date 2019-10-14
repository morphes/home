<div class="uploaded">
	<div class="input_row image_inp">
		<div class="input_conteiner">
			<div class="input_conteiner_img">
				<img style="width:131px;" src="/<?php echo $file->getPreviewName(Portfolio::$preview['crop_131']); ?>">
			</div>
			<textarea class="textInput img_descript" name="Portfolio[filedesc][<?php echo $file->id; ?>]"><?php echo $file->desc; ?></textarea>
		</div>
	</div>

	<div class="hint_conteiner">
		<div class="del_img page_fu">
			<span></span>
			<a id="" file_id="<?php echo $file->id; ?>" href="#">Удалить</a>
		</div>
	</div>
</div>