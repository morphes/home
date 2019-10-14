<?php $this->pageTitle = 'Товары — ' . $model->name . ' — Производители — MyHome.ru'; ?>
<?php Yii::app()->clientScript->registerScriptFile('/js/CCatalog.js'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/catalog.css'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/style.css'); ?>
<?php Yii::app()->clientScript->registerScript('initial-run', '
        $(function(){
                cat.menuTriangle();
                cat.menuTabs();
                cat.toggleDesc();
        });
', CClientScript::POS_READY);?>

<div class="-grid-wrapper page-content">
<?php echo CHtml::beginForm('', 'post', array('id'=>'refresh-form')) . CHtml::endForm();?>

<div class="pathBar">
        <?php $this->widget('application.components.widgets.EBreadcrumbs', array(
                'encodeLabel'=>false,
		'homeLink' => '<a href="'.Yii::app()->params->bmHomeUrl.'">Каталог ТВК «Большая Медведица»</a>',
                'links'=>array(
                        '<a class="no_link">Производители</a>'
                ),
        ));?>
        <h1><?php echo CHtml::value($model, 'name'); ?></h1>
    <div class="spacer"></div>
</div>

<div class="shop_items">

        <?php $this->renderPartial('_menuBlock', array('model'=>$model)); ?>

        <div id="right_side" class="new_template">

                <div class="page_settings new">

                     <?php $this->widget('catalog.components.widgets.CatFilterSort', array(
                        'cookieName'=>'product_vendor_sort',
                        'formSelector'=>'#refresh-form',
                        'items'=>array(
                                array('name'=>'date', 'text'=>'дате'),
                        ),
                     )); ?>

                    <div class="elements_on_page drop_down">
                        На странице <span class="exp_current"><?php echo $pagesize; ?><i></i></span>
                        <ul class="need_submit">
                                <?php foreach(Config::$productFilterPageSizes as $key=>$item) : ?>
                                <?php echo CHtml::tag('li', array('data-value'=>$key), $item); ?>
                                <?php endforeach; ?>
                        </ul>
                    </div>

                    <div class="clear"></div>
                </div>


                <div class="catalog_items_list">

                        <?php $this->widget('zii.widgets.CListView', array(
                                'dataProvider'=>$dataProvider,
                                'itemView'=>'//catalog/category/_productItem',
                                'template'=>'{items}'
                        ));?>

                    <div class="clear"></div>
                </div>

                <div class="page_settings bottom new">

                    <div class="pages">
                            <?php $this->widget('application.components.widgets.CustomPager2', array(
                            'pages' => $dataProvider->pagination,
                            'maxButtonCount' => 5,
                    )); ?>
                    </div>

                    <div class="elements_on_page drop_down">
                        На странице <span class="exp_current"><?php echo $pagesize; ?><i></i></span>
                        <ul class="need_submit">
                                <?php foreach(Config::$productFilterPageSizes as $key=>$item) : ?>
                                <?php echo CHtml::tag('li', array('data-value'=>$key), $item); ?>
                                <?php endforeach; ?>
                        </ul>
                    </div>


                    <div class="clear"></div>
                </div>
        </div>

        <div id="left_side" class="new_template">
                <?php echo CHtml::hiddenField('menu_tabs_update_url', $this->createUrl('updateNavList', array('id'=>$model->id)))?>
                <ul class="menu_tabs">
                    <?php if($nav_type == 'category') $class = 'current'; else $class = ''; ?>
                        <li class="<?php echo $class; ?>" data-content="categories"><span>Категории</span></li>

                    <?php if($nav_type == 'collections') $class = 'current'; else $class = ''; ?>
                        <li class="<?php echo $class; ?>" data-content="collections"><span>Коллекции</span></li>
                </ul>
                <div class="clear"></div>
                <div class="red_menu">
                    <ul>
                            <?php echo $nav_list; ?>
                    </ul>
                    <script type="text/javascript">
                        cat.drowTriangle();
                    </script>
                </div>
        </div>

</div>




<div class="spacer-30"></div>
<div class="clear"></div>

<?php Yii::app()->clientScript->registerScript('feedback-form', '
        $(".drop_down ul li").click(function(){
                CCommon.setCookie("vendor_product_filter_pagesize", $(this).attr("data-value"), {expires:31*24*60*60, path:"/"});
                $("#refresh-form").submit();
        });

', CClientScript::POS_END);?>
</div>