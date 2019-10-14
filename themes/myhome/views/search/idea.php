<?php $this->pageTitle = CHtml::encode($query).' — MyHome.ru'?>
<?php Yii::app()->clientScript->registerCssFile('/css/search.css'); ?>
<?php Yii::app()->clientScript->registerScript('toogler', 'js.serviceToggler()', CClientScript::POS_READY);?>


<div class="pathBar">
    <?php $this->widget('application.components.widgets.EBreadcrumbs', array(
        'links'=>array('Поиск'=>$this->createUrl('/search?q='.$query))
    ));?>
    <h1>Поиск в идеях: «<?php echo $query; ?>»</h1>
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
                        <a href="<?php echo $data->object->getIdeaLink(); ?>">
                            <?php if($data->object instanceof Architecture) {echo CHtml::image($data->object->getPreview('crop_60')); } else echo CHtml::image('/'.$data->object->getPreview(Config::$preview['crop_60'], 'default', true), '', array('width'=>60, 'height'=>60)); ?>
                        </a>
                        <div class="item_desc">
                            <a href="<?php echo $data->object->getIdeaLink(); ?>" class="item_head">
                                    <?php echo Amputate::selectQueryInText($data->name, $query); ?>
                            </a>
                            <?php $this->widget('application.components.widgets.WStar', array(
                                    'selectedStar' => $data->average_rating,
                                    'addSpanClass' => 'rating-b',
                            ));?>
                            <p><?php echo Amputate::getSearchedContext($data->desc, $query); ?></p>
                            <div class="item_path">
                                    <?php if(array_key_exists($data->object->author->role, Config::$rolesAdmin)) : ?>
                                    <?php echo CHtml::tag('span', array(), 'Редакция MyHome')?>
                                    <?php else : ?>
                                    <?php echo CHtml::link($data->object->author->name, $data->object->author->getLinkProfile()); ?>
                                    <?php endif; ?>

                                <span>&rarr;</span>
                                <?php echo CHtml::link($data->getTypeLabel(), $data->object->getFilterLink()); ?>
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

<?php echo CHtml::beginForm($this->createUrl('details', array('t'=>'idea', 'q'=>$query)), 'get', array('id'=>'nav_form')); ?>
<?php echo CHtml::hiddenField('pagesize', $pagesize)?>
<?php echo CHtml::endForm(); ?>
<?php Yii::app()->clientScript->registerScript('search', '
        $(".elements_on_page ul li").click(function () {
                $("#nav_form input[name=pagesize]").val($(this).data("value"));
                $("#nav_form").submit();
        });
', CClientScript::POS_READY); ?>
