<?php $this->pageTitle = CHtml::encode($query).' — MyHome.ru'?>
<?php Yii::app()->clientScript->registerCssFile('/css/search.css'); ?>
<?php Yii::app()->clientScript->registerScript('toogler', 'js.serviceToggler()', CClientScript::POS_READY);?>


<div class="pathBar">
        <?php $this->widget('application.components.widgets.EBreadcrumbs', array(
                'links'=>array('Поиск'=>$this->createUrl('/search?q='.$query))
        ));?>
    <h1>Поиск специалистов: «<?php echo $query; ?>»</h1>
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
                <a href="<?php echo $data->getLinkProfile(); ?>">
                        <?php echo CHtml::image('/' .$data->getPreview(User::$preview['crop_120']), '', array('width'=>120, 'height'=>120)); ?>
                </a>
                <div class="item_desc">
                    <?php echo CHtml::link(Amputate::selectQueryInText($data->name, $query), $data->getLinkProfile(), array('class'=>'item_head')); ?>
                    <p><?php echo Amputate::getSearchedContext($data->data->about, $query); ?></p>
                    <div class="item_path">
                        <?php $s_links = array(); ?>
                        <?php foreach($data->getServiceListLite() as $s) $s_links[] = CHtml::link($s['service_name'], $this->createUrl('/specialist/'.$s['url'])); ?>
                        <?php echo implode(', ', $s_links); ?>
                    </div>
                    <div class="block_item_info">
                        <?php echo $data->city->name; ?>
                        <div class="block_item_counters">
                            <span class="projects_quant"><a href="<?php echo $this->createUrl('/users/'.$data->login.'/portfolio'); ?>"><i></i><?php echo $data->data->project_quantity; ?></a></span>
                            <span class="views_quant"><i></i><?php echo $data->getProfileViews(); ?></span>
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

<?php echo CHtml::beginForm($this->createUrl('details', array('t'=>'specialist', 'q'=>$query)), 'get', array('id'=>'nav_form')); ?>
<?php echo CHtml::hiddenField('pagesize', $pagesize)?>
<?php echo CHtml::endForm(); ?>
<?php Yii::app()->clientScript->registerScript('search', '
        $(".elements_on_page ul li").click(function () {
                $("#nav_form input[name=pagesize]").val($(this).data("value"));
                $("#nav_form").submit();
        });
', CClientScript::POS_READY); ?>
