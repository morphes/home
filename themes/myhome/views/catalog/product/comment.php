<?php $this->pageTitle = 'Обсуждение — ' . $model->name . ' — ' . $model->category->name . ' — MyHome.ru'; ?>
<?php Yii::app()->clientScript->registerScriptFile('/js/CCatalog.js'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/catalog.css'); ?>
<?php Yii::app()->clientScript->registerScript('initial-run', '
        $(function(){
                cat.initBreadCrumbs();
                cat.ComentPartToggler();
        });
', CClientScript::POS_READY);?>


<?php $this->widget('catalog.components.widgets.CatBreadcrumbs', array('category'=>$model->category, 'pageName'=>$model->name)); ?>

<div class="product_card">

        <?php $this->renderPartial('_menuBlock', array('model' => $model, 'store_id'=>$store_id)); ?>

        <?php $this->widget('application.components.widgets.WComment', array(
                'model' => $model,
                'view' => '//widget/comment/product/main',
                'hideComments' => !$model->getCommentsVisibility(),
                'showCnt' => 0,
        ));?>

</div>

<div class="spacer-30"></div>
<div class="clear"></div>