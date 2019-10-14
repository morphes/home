<?php $this->pageTitle = $model->name . ' — Сети магазинов — MyHome.ru'; ?>
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
                        '<a class="no_link">Сети магазинов</a>'
                ),
        ));?>

    <h1><?php echo CHtml::value($model, 'name'); ?></h1>

    <div class="spacer"></div>
</div>

<div class="shop_card chain">

    <?php $this->renderPartial('_menuBlock', array('model'=>$model)); ?>

    <div class="shop_left chain">
        <div class="shop_info">
            <?php if($model->uploadedFile) :?>
                <?php echo CHtml::image('/' . $model->uploadedFile->getPreviewName(Config::$preview['resize_140'])); ?>
            <?php endif; ?>
            <div class="shop_contacts">
                <div class="chain_phone">
                    <?php echo $model->phone; ?>
                </div>
                <div class="chain_mail">
                    <p><?php echo CHtml::link($model->email, 'mailto:'.$model->email); ?></p>
                </div>
            </div>
            <div class="clear"></div>
        </div>
        <div class="shop_description">
                <p><?php echo $model->about; ?></p>
        </div>
        <div class="spacer-30"></div>
    </div>
    <div class="product_icons">
        <div class="stores_list">
            <h3 class="headline">Магазины в сети</h3>
            <ul class="">

                <?php foreach($cities as $city) : ?>
                        <li>
                            <?php echo CHtml::link($city['name'], array('id'=>$model->id)) . '(' . $city['qt'] . ')'; ?>
                        </li>
                <?php endforeach; ?>
            </ul>

            <span class="all_elements_link" style="display: inline;">
                <?php echo CHtml::link('Все города', $this->createUrl('stores', array('id'=>$model->id))); ?>
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
                    'message' => Amputate::getLimb($model->about, 500, '...'),
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