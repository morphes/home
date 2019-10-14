<?php Yii::app()->clientScript->registerScriptFile('/js-new/folders.js'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css-new/generated/profile.css'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css-new/generated/goods.css'); ?>
<script>
	$(function(){

	});
</script>
<div class="-grid">
	<?php $this->widget('application.components.widgets.FoldersList.FoldersListWidget',array(
		'items'=>$items,
	)); ?>
	<div class="-col-3 folder folder-template">
			<span class="folder-picture">
				<strong class="-skyblue -pseudolink" id="toggleAlbumForm"><i>Создать альбом</i></strong>
			</span>
		<div class="-gutter-top-dbl -hidden">
			<input type="text" class="-col-3 -gutter-left-null -gutter-right-null">
			<button class="-button -button-skyblue -small">Создать</button><span class="-red -pseudolink -gutter-left -small" id="toggleFormBack"><i>Отмена</i></span>
		</div>
	</div>
</div>

<script>
	folders.initFoldersActions();
	folders.addFolder();
</script>