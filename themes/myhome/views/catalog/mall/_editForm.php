<?php
/**
 * @var $item MallPromo
 */
?>
<form class="-col-9 -form-inline banner-edit-form -hidden" enctype="multipart/form-data" method="post">
	<h2 class="-giant">Добавить баннер</h2>
	<div class="-col-2 -semibold -large -text-align-right -inset-right-hf">Изображение</div>
	<div class="-col-4 -gutter-bottom-dbl">
		<span class="show-image"><?php echo CHtml::image('/'.$item->getPreview(MallPromo::$preview['crop_330x140'])); ?></span>
		<a class="-file-input -button -button-skyblue -relative -gutter-bottom-hf">
			Выбрать файл
			<input type="file" onchange="changeInput(this);" class="picture_for_banner" name="MallPromo[file]">
		</a>
	</div>
	<div class="-col-2 -gray -small -inset-left">Рекомендуемый размер изображения 990&#215;420px</div>
	<div class="-col-2 -semibold -large -text-align-right -inset-right-hf">Название</div>
	<div class="-col-6 -gutter-bottom-dbl">
		<input class="-col-6" type="text" maxlength="255" name="MallPromo[name]" value="<?php echo $item->name; ?>">
	</div>
	<div class="-col-2 -semibold -large -text-align-right -inset-right-hf">Ссылка на альбом</div>
	<div class="-col-6 -gutter-bottom-dbl">
		<input class="-col-6" type="text" maxlength="255" name="MallPromo[url]" value="<?php echo $item->url; ?>">
		<label class="-checkbox -gutter-top-dbl -gutter-bottom">
			<?php echo CHtml::checkBox('active', $item->status==MallPromo::STATUS_ACTIVE); ?>
			<span>Активен</span>
		</label>
	</div>
	<input type="hidden" name="mall_id" value="<?php echo $item->mall_id; ?>">
	<input type="hidden" name="promo_id" value="<?php echo $item->id; ?>">
	<div class="-col-6 -skip-2">
		<button type="button" class="-button -button-skyblue -huge -semibold" onclick="submitForm(); return false;">Обновить</button>
		<a href="javascript:void(0);" class="-red -gutter-left">Отмена</a>
	</div>
</form>