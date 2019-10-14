<?php $this->pageTitle = $model->name . ' — Производители — MyHome.ru'; ?>
<?php Yii::app()->clientScript->registerScriptFile('/js/CCatalog.js'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/catalog.css'); ?>
<?php Yii::app()->clientScript->registerScript('initial-run', '
        $(function(){
                cat.initBreadCrumbs();
        });
', CClientScript::POS_READY);?>


<div class="pathBar">
        <?php $this->widget('application.components.widgets.EBreadcrumbs', array(
                'encodeLabel'=>false,
                'links'=>array(
                        '<a class="no_link">Производители</a>'
                ),
        ));?>

    <h1><?php echo CHtml::value($model, 'name'); ?></h1>

    <div class="spacer"></div>
</div>

<div class="shop_card chain">

    <?php $this->renderPartial('_menuBlock', array('model'=>$model)); ?>

    <div class="shop_left">

        <div class="shop_info">
            <?php if($model->uploadedFile) :?>
                <?php echo CHtml::image('/' . $model->uploadedFile->getPreviewName(Config::$preview['resize_140'])); ?>
            <?php endif; ?>
            <div class="shop_contacts">
                <p><?php echo isset($model->country) ? $model->country->name : ''; echo isset($model->city) ? ', ' . $model->city->name : ''; ?></p>
                <div class="chain_mail">
                    <p><noindex><?php echo CHtml::link($model->site, $model->site, array('rel' => 'nofollow'))?></noindex></p>
                </div>
            </div>
            <div class="shop_rubrics">
                <div class="catalog_sections">
                    <?php
                        $cats = array();
                        foreach($model->categories as $category)
                                $cats[] = CHtml::link($category->name, $this->createUrl('/catalog/vendor', array('id'=>$model->id, 'action'=>'products', 'category_id'=>$category->id)));
                        echo implode(', ', $cats);
                    ?>
                </div>
            </div>
            <div class="clear"></div>
        </div>


        <div class="shop_description">
                <p><?php echo nl2br($model->desc); ?></p>
        </div>

        <div class="other_models">
            <h3 class="headline">Товары</h3>
                <span class="all_elements_link" style="display: inline;">
                        <?php echo CHtml::link('Все', $this->createUrl('/catalog/vendor', array('id'=>$model->id, 'action'=>'products'))); ?>
                        <span>&rarr;</span>
                </span>
            <div class="catalog_items_list_small">
                <?php foreach($products as $prod) : ?>
                        <div class="item">
                            <?php echo CHtml::openTag('a', array('href'=>$this->createUrl('/product', array('id'=>$prod->id, 'action'=>'index')))); ?>
                                <?php echo CHtml::image('/'.$prod->cover->getPreviewName(Product::$preview['resize_120']), '', array('style'=>'max-width:120px; max-height:120px;')); ?>
                            <?php echo CHtml::closeTag('a'); ?>
                            <h2><?php echo CHtml::link($prod->name, $this->createUrl('/product', array('id'=>$prod->id, 'action'=>'index'))); ?></h2>
                        </div>
                <?php endforeach; ?>
                <div class="clear"></div>
            </div>
        </div>

        <div class="spacer-30"></div>
    </div>

    <div class="product_icons">
        <div class="stores_list">
            <h3 class="headline">Магазины</h3>
            <ul class="">
                <?php $i = 0; ?>
                <?php foreach($cities as $city) : ?>
                        <?php $i++;?>
                        <li>
                            <?php echo CHtml::link($city['name'], $this->createUrl('/catalog/vendor', array('id'=>$model->id, 'action'=>'storesInCity', 'cid'=>$city['id']))) . '(' . $city['qt'] . ')'; ?>
                        </li>
                        <?php if($i == 10) break; ?>
                <?php endforeach; ?>
            </ul>

            <span class="all_elements_link" style="display: inline;">
                <?php echo CHtml::link('Все города', $this->createUrl('/catalog/vendor', array('id'=>$model->id, 'action'=>'stores'))); ?>
                <span>&rarr;</span>
            </span>

            <div class="clear"></div>
        </div>


        <div class="social_links">
            <div class="social_links">

                    <?php $this->widget('ext.sharebox.EShareBox', array(
                    'view' => 'product',
                    // url to share, required.
                    'url' => Yii::app()->request->hostInfo.Yii::app()->request->requestUri,

                    // A title to describe your link, required.
                    'title'=> $model->name,

                    // A small message for post
                    'message' => Amputate::getLimb($model->desc, 500, '...'),
                    'classDefinitions' => array(
                            'livejournal' => 'ns-lj',
                            'vkontakte' => 'ns-vk',
                            'twitter' => 'ns-tw',
                            'facebook' => 'ns-fb',
                            'google+' => 'ns-gp',
                    ),
		    'exclude' => array('odkl','pinterest'),
                    'htmlOptions' => array('class' => 'social'),
            ));?>

            </div>
        </div>
    </div>

</div>

<div class="spacer-30"></div>
<div class="clear"></div>