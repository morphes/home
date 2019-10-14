<?php
/**
 * Формируем список элементов <li> представляющих группы избранного.
 * Также определяем имя дефолтной группы.
 */
$optionHtml = '';
$defaultGroupName = 'Общий список';
if ( ! empty($groups)) {
	foreach($groups as $group) {

		$params = array('value' => $group['id']);

		if ($group['id'] == $defaultGroupId)
			$params['selected'] = 'selected';

		$optionHtml .= CHtml::tag('option', $params, $group['name'], true);
	}
}
?>
<?php $this->beginClip('addFavorite'); ?>


<div id="popup-favorite" class="-hidden -col-7">
	<div class="-grid">
		<div class="-col-7">
			<span class="-giant -gutter-top -gutter-bottom-dbl -block">Добавить в избранное</span>
		</div>

		<label class="-col-2 -inset-top-hf">
			<input type="radio" name="listType" class="textInput" checked value="1"/>
			Ваши списки
		</label>
		<select class="-col-4 -gutter-bottom-dbl">
			<option value="0">Общий список</option>
			<?php echo $optionHtml; ?>
		</select>
		<label class="-col-2 -inset-top-hf">
			<input type="radio" name="listType" class="textInput" value="0"/>
			Новый список
		</label>
		<input class="-col-4  -gutter-bottom-dbl" value="" type="text" name="new-list">
		<div class="-col-2">
			<a class="-button -button-expanded -button-skyblue" href="#">Добавить</a>
		</div>

	</div>
</div>


<script type="text/javascript">
	CCommon.initFavorite();
</script>
<?php $this->endClip(); ?>