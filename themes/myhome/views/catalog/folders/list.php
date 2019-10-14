<?php Yii::app()->clientScript->registerScriptFile('/js-new/folders.js'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css-new/generated/profile.css'); ?>

<div class="-grid-wrapper page-content">


	<?php
	$this->widget('catalog.components.widgets.CatBreadcrumbs',
		array(
		'category' => Category::model()->getRoot(),
		'afterH1'  => '',// $this->renderPartial('//widget/bmLogo', array(), true),
		'homeLink' => '<a href="'.Yii::app()->params->bmHomeUrl.'">Каталог ТВК «Большая Медведица»</a>',
		'pageName' => 'Спецпредложения',
		)
	);?>


	<div class="-grid">
	<?php  if($models) :

	$this->widget('application.components.widgets.FoldersList.FoldersListWidget',array(
		'items'=>$models,
		'view' => '//widget/folders/bigItems',
	));
	endif;
	?>
	</div>
</div>

<script>
	folders.initFoldersActions();
	folders.addFolder();
</script>