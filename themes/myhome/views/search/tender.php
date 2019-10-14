<?php $this->pageTitle = CHtml::encode($query).' — MyHome.ru'?>
<?php Yii::app()->clientScript->registerCssFile('/css/search.css'); ?>


<div class="pathBar">
    <?php $this->widget('application.components.widgets.EBreadcrumbs', array(
        'links'=>array('Поиск'=>$this->createUrl('/search?q='.$query))
    ));?>
    <h1>Поиск в заказах: «<?php echo $query; ?>»</h1>
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


<div class="search_list tenders">
    <?php $i = $dataProvider->pagination->offset + 1; ?>
    <?php foreach($dataProvider->getData() as $data) : ?>
            <div class="item">
                <span class="cnt"><?php echo $i; ?>.</span>
                <div class="item_desc">
                    <?php echo CHtml::link(Amputate::selectQueryInText($data->name, $query), $data->getLink(), array('class'=>'item_head')); ?>
                    <?php if($data->cost) $price = number_format($data->cost, 2, '.', ' ') . ' руб.'; else $price = 'Бюджет не указан'; ?>
                    <span class="rating rating-b">
                        <span><?php echo $price; ?></span>
                    </span>
                    <p><?php echo Amputate::getSearchedContext($data->desc, $query); ?></p>
                    <div class="block_item_info">
                        <span><?php echo CFormatterEx::formatDateToday($data->create_time); ?></span>
                        <div class="block_item_counters">
                            <span><?php echo $data->city->name; ?></span>
                            <a class="comments_quant"><i></i><?php echo $data->response_count?></a>
                        </div>
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

<?php echo CHtml::beginForm($this->createUrl('details', array('t'=>'tender', 'q'=>$query)), 'get', array('id'=>'nav_form')); ?>
<?php echo CHtml::hiddenField('pagesize', $pagesize)?>
<?php echo CHtml::endForm(); ?>
<?php Yii::app()->clientScript->registerScript('search', '
        $(".elements_on_page ul li").click(function () {
                $("#nav_form input[name=pagesize]").val($(this).data("value"));
                $("#nav_form").submit();
        });
', CClientScript::POS_READY); ?>
