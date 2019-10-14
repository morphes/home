<?php $this->pageTitle = 'Характеристики — ' . $model->name . ' — ' . $model->category->name . ' — MyHome.ru'; ?>
<?php Yii::app()->clientScript->registerScriptFile('/js/CCatalog.js'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/catalog.css'); ?>
<?php Yii::app()->clientScript->registerScript('initial-run', '
        $(function(){
                cat.initBreadCrumbs();
        });
', CClientScript::POS_READY);?>


<?php $this->widget('catalog2.components.widgets.CatBreadcrumbs', array( 'category'=>$model->category, 'pageName'=>$model->name)); ?>

<div class="product_card">

        <?php $this->renderPartial('_menuBlock', array('model' => $model, 'store_id'=>$store_id)); ?>

        <div class="product_detail_desc">
                <?php echo nl2br($model->desc); ?>
        </div>

        <div class="product_behaviour">
                <ul class="item_params">

                        <li class="parent">
                                <span>Общие</span>
                        </li>

                        <li>
                                <span><b>Производитель</b></span>
                                <span class="param_value">
                                        <?php echo CHtml::link($model->vendor->name, Vendor::getLink($model->vendor_id)); ?>
                                </span>
                        </li>

                        <?php if($model->countryObj) : ?>
                                <li>
                                        <span><b>Страна</b></span>
                                        <span class="param_value"><?php echo $model->countryObj->name; ?></span>
                                </li>
                        <?php endif; ?>

                        <?php if($model->barcode) : ?>
                                <li>
                                        <span><b>Артикул</b></span>
                                        <span class="param_value"><?php echo $model->barcode; ?></span>
                                </li>
                        <?php endif; ?>

                        <?php if($model->collectionName) : ?>
                                <li>
                                        <span><b>Коллекция</b></span>
                                        <span class="param_value"><?php echo $model->collectionName; ?></span>
                                </li>
                        <?php endif; ?>

                        <?php if($model->guaranty) : ?>
                                <li>
                                        <span><b>Гарантия</b></span>
                                        <span class="param_value"><?php echo $model->guaranty; ?></span>
                                </li>
                        <?php endif; ?>

                        <?php if($model->eco) : ?>
                                <li>
                                        <span><b>Экологичность</b></span>
                                        <span class="param_value">Да</span>
                                </li>
                        <?php endif; ?>


                        <?php $this->widget('catalog.components.widgets.CatFullcardOptions', array('model'=>$model)); ?>

                </ul>

        </div>
        <div class="clear"></div>
</div>

<div class="spacer-30"></div>
<div class="clear"></div>