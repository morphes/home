<?php $this->pageTitle = CHtml::encode($query).' — MyHome.ru'?>
<?php Yii::app()->clientScript->registerCssFile('/css/search.css'); ?>


<div class="pathBar">
    <?php $this->widget('application.components.widgets.EBreadcrumbs', array(
        'links'=>array('Поиск'=>$this->createUrl('/search?q='.$query))
    ));?>
    <h1>Поиск товаров: «<?php echo $query; ?>»</h1>
    <div class="spacer"></div>
</div>


<div class="page_settings new">
    <div class="pages">
            <?php $this->widget('application.components.widgets.CustomPager2', array(
                    'pages'=>$dataProvider->getPagination()
            )); ?>
    </div>
    <div class="elements_on_page drop_down">
        Показать <span class="exp_current"><?php echo Config::$searchPageSizes[$pagesize]; ?><i></i></span>
        <ul>
                <?php foreach (Config::$searchPageSizes as $k => $v) echo CHtml::tag('li', array('data-value' => $k), $v); ?>
        </ul>
    </div>
    <div class="clear"></div>
</div>


<div class="search_list">
    <?php $i = $dataProvider->pagination->offset + 1; ?>
    <?php foreach($dataProvider->getData() as $data) : ?>
            <div class="item">
                <span class="cnt"><?php echo $i; ?>.</span>
                <a href="<?php echo Product::getLink($data->id, null, $data->category_id); ?>">
                        <?php echo CHtml::image('/'.$data->cover->getPreviewName(Product::$preview['resize_120']), '', array('width'=>120, 'height'=>120)); ?>
                </a>
                <div class="item_desc">
                        <?php echo CHtml::link(Amputate::selectQueryInText($data->name, $query), Product::getLink($data->id, null, $data->category_id), array('class'=>'item_head')); ?>
                        <?php if($data->average_price > 0) $price = number_format($data->average_price, 0, '.', ' ') . ' руб.'; else $price = 'Цена не указана'; ?>

                        <?php $this->widget('application.components.widgets.WStar', array(
                                'selectedStar' => $data->average_rating,
                                'addSpanClass' => 'rating-b',
                                'innerText' => CHtml::tag('span', array(), $price),
                        ));?>

                    <p><?php echo Amputate::getSearchedContext($data->desc, $query); ?></p>
                    <div class="item_path">
                            <?php echo CHtml::link($data->category->name, Category::getLink($data->category_id)); ?>
                            <span>&bull;</span>
                            <?php
                                echo isset($data->vendor) ? CHtml::link($data->vendor->name, Vendor::getLink($data->vendor_id)) : '';
                                echo isset($data->countryObj) ? (', ' . $data->countryObj->name) : '';
                            ?>
                    </div>
                </div>
                <div class="clear"></div>
            </div>
            <?php $i++; ?>
    <?php endforeach; ?>
</div>


<div class="page_settings new bottom">
    <div class="pages">
            <?php $this->widget('application.components.widgets.CustomPager2', array(
            'pages'=>$dataProvider->getPagination()
    )); ?>
    </div>
    <div class="elements_on_page drop_down">
        Показать <span class="exp_current"><?php echo Config::$searchPageSizes[$pagesize]; ?><i></i></span>
        <ul>
                <?php foreach (Config::$searchPageSizes as $k => $v) echo CHtml::tag('li', array('data-value' => $k), $v); ?>
        </ul>
    </div>
    <div class="clear"></div>
</div>

<?php echo CHtml::beginForm($this->createUrl('details', array('t'=>'product', 'q'=>$query)), 'get', array('id'=>'nav_form')); ?>
<?php echo CHtml::hiddenField('pagesize', $pagesize)?>
<?php echo CHtml::endForm(); ?>
<?php Yii::app()->clientScript->registerScript('search', '
        $(".elements_on_page ul li").click(function () {
                $("#nav_form input[name=pagesize]").val($(this).data("value"));
                $("#nav_form").submit();
        });
', CClientScript::POS_READY); ?>
