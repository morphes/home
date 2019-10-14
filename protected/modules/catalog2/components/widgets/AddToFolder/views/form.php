<?php
$optionHtml = '';
Yii::app()->clientScript->registerScriptFile('/js-new/folders.js');

if (!empty($folders)) {
	foreach ($folders as $folder) {
		$params = array('value' => $folder['id']);
		$optionHtml .= CHtml::tag('option', $params, $folder['name'], true);
	}
}
?>

<?php $this->beginClip('addFolder'); ?>
<div id="popup-folder"
     class="-hidden -col-7 -white-bg -inset-all">
	<div class="-grid">
		<div class="-col-7">
			<h2>Добавить в альбом</h2>
		</div>
		<?php if (empty($folders)) {
			echo CHtml::openTag('div', array("class" => "-hidden"));
		} else {
			echo CHtml::openTag('div');
		}
		?>


		<label class="-col-2 -inset-top-hf ">
			<input type="radio"
			       name="listType"
			       class="textInput"
			       checked
			       value="1"/>
			Ваши альбомы
		</label>
		<select class="-col-4 -gutter-bottom-dbl">
			<?php echo $optionHtml ?>
		</select>
		<?php echo CHtml::closeTag('div') ?>
		<label class="-col-2 -inset-top-hf">
			<input type="radio"
			       name="listType"
			       class="textInput"
			       value="0"/>
			Новый альбом
		</label>
		<input class="-col-4  -gutter-bottom-dbl"
		       value=""
		       type="text"
		       name="new-list">

		<div class="-col-2">
			<a class="-button -button-expanded -button-skyblue"
			   href="#">Добавить</a>
		</div>

	</div>
</div>
<script>
	folders.addProductToFolder();
</script>
<?php $this->endClip(); ?>
