<?php $this->pageTitle = 'Обсуждение — ' . $model->name . ' — ТВК «Большая Медведица» — MyHome.ru'; ?>
<?php Yii::app()->clientScript->registerScriptFile('/js/CCatalog.js'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/catalog.css'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/style.css'); ?>
<?php Yii::app()->clientScript->registerScript('initial-run', '
        $(function(){
                cat.initBreadCrumbs();
                cat.ComentPartToggler();
        });
', CClientScript::POS_READY);?>

<div class="-grid-wrapper page-content">
<?php $this->widget('catalog2.components.widgets.CatBreadcrumbs', array(
	'category' => $model->category,
	'homeLink' => '<a href="'.Yii::app()->params->bmHomeUrl.'">Каталог ТВК «Большая Медведица»</a>',
	'pageName' => $model->name,
	'mallCatalogClass' => true,
	'afterH1'  => '',//$this->renderPartial('//widget/bmLogo', array(), true),
)); ?>

<div class="product_card">

        <?php $this->renderPartial('_bmMenuBlock', array('model'=>$model)); ?>

        <?php $this->widget('application.components.widgets.WComment', array(
                'model' => $model,
                'view' => '//widget/comment/product/main',
                'hideComments' => !$model->getCommentsVisibility(),
                'showCnt' => 0,
        ));?>

</div>

<div class="spacer-30"></div>
<div class="clear"></div>
</div>