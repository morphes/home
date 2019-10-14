<div class="-grid">
	<h1 class="-col-9 -giant">Изменить обложку</h1>
	<div class="-col-9">
		<label class="-radio -gutter-right"><input type="radio" name="getPicture" value="1" checked="checked"><span>Загрузить с компьютера</span></label>
		<label class="-radio"><input type="radio" name="getPicture" value="2"><span>Выбрать из папки</span></label>
	</div>
	<div class="-col-9 area -inset-top -inset-bottom">
		<input type="file" id="image-upload">
	</div>
	<div class="-col-9 area -hidden list-wrapper">
		<div class="list-inner" id="cover-form">
			<div class="scrollbar"><div class="track"><div class="thumb"></div></div></div>
			<div class="viewport">
				<div class="-grid overview">
					<?php
					foreach ($photos as $photo) {
						echo CHtml::openTag('div', array('class'=>'-col-3', 'data-id'=>$photo->id));
						echo CHtml::image('/'.$photo->getPreviewName(Product::$preview['crop_200']), '', array('class'=>'-quad-200'));
						echo CHtml::closeTag('div');
					}
					?>
				</div>
			</div>
		</div>
	</div>
	<div class="-col-9 cover-form-submit">
		<button class="-bitton -button-skyblue">Сохранить</button>
		<a class="-red -gutter-left" href="javascript:void(0);">Отмена</a>
	</div>
</div>