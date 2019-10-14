<?php $this->pageTitle = CHtml::encode($query).' — MyHome.ru'?>
<?php Yii::app()->clientScript->registerCssFile('/css/search.css'); ?>


<div class="pathBar">
    <?php $this->widget('application.components.widgets.EBreadcrumbs', array(
        'links'=>array('Поиск'=>$this->createUrl('/search?q='.$query))
    ));?>
    <h1>Поиск топиков в форуме: «<?php echo $query; ?>»</h1>
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


<div class="search_list forum">
    <?php $i = $dataProvider->pagination->offset + 1; ?>
    <?php foreach($dataProvider->getData() as $data) : ?>
            <div class="item">
                <span class="cnt"><?php echo $i; ?>.</span>
                <div class="item_desc">
                    <?php echo CHtml::link(Amputate::selectQueryInText($data->name, $query), $data->getElementLink(), array('class'=>'item_head')); ?>
                    <p><?php echo Amputate::getSearchedContext($data->description, $query); ?></p>
                    <div class="block_item_info">
                        <span><?php echo CFormatterEx::formatDateToday($data->create_time); ?></span>
                        <div class="block_item_counters">
                            <?php if($data->author) : ?>
                                <?php echo CHtml::link($data->author->name, $data->author->getLinkProfile()); ?>
                            <?php else : ?>
                                Гость
                            <?php endif; ?>
                            <span>&rarr;</span>
                            <?php echo CHtml::link($data->section->name, $data->section->getElementLink()); ?>
                            <span class="comments_quant"><a href="<?php echo $this->createUrl($data->getElementLink())?>"><i></i><?php echo $data->count_answer; ?></a></span>
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

<?php echo CHtml::beginForm($this->createUrl('details', array('t'=>'forum', 'q'=>$query)), 'get', array('id'=>'nav_form')); ?>
<?php echo CHtml::hiddenField('pagesize', $pagesize)?>
<?php echo CHtml::endForm(); ?>
<?php Yii::app()->clientScript->registerScript('search', '
        $(".elements_on_page ul li").click(function () {
                $("#nav_form input[name=pagesize]").val($(this).data("value"));
                $("#nav_form").submit();
        });
', CClientScript::POS_READY); ?>
