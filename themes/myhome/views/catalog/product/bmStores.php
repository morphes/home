<?php $this->pageTitle = 'Где купить — ' . $model->name . ' — ТВК «Большая Медведица» — MyHome.ru'; ?>
<?php Yii::app()->clientScript->registerScriptFile('/js/CCatalog.js'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/catalog.css'); ?>
<?php Yii::app()->clientScript->registerScript('initial-run', '
        $(function(){
                cat.initBreadCrumbs();
        });
', CClientScript::POS_READY);?>


<?php $this->widget('catalog.components.widgets.CatBreadcrumbs', array(
	'category' => $model->category,
	'pageName' => $model->name,
	'mallCatalogClass' => true,
	'afterH1'  => $this->renderPartial('//widget/bmLogo', array(), true),
)); ?>

<div class="product_card">

        <?php $this->renderPartial('_bmMenuBlock', array('model'=>$model)); ?>

    <div class="chain_stores_list">

	<?php if (empty($sorted)) : ?>
	    <div class="no_result">В нашей базе пока что нет магазинов, предлагающих этот товар. Скоро мы их обязательно добавим, обещаем.</div>
	<?php endif; ?>


        <?php
            $items_qt = count($sorted); // общее кол-во букв для вывода
            $num_in_firstcolumn = ceil($items_qt / 4); // кол-во элементов в первой колонке
            $num_in_secondcolums = round($items_qt / 4); // кол-во элементов в остальных колонках
            $is_first_column = true; // флаг вывода первой колонки
            $current_num = 0; // кол-во элементов, выводимых в текущей колонке
            $count = 0; // номер выводимого элемента в текущей колонке
            $new_column = true; // флаг создания новой колонки
        ?>

        <?php foreach($sorted as $letter=>$cities) : ?>

                <?php // если установлен флаг вывода новой колонки, открывается тег для новой колонки ?>
                <?php if ($new_column) : ?>
                        <div class="stores_column">
                <?php endif; ?>

                <?php // устанавливается кол-во элементов, выводимых в данной колонке ?>
                <?php if($is_first_column) $current_num = $num_in_firstcolumn; else $current_num  = $num_in_secondcolums; ?>

                <?php // вывод городов для текущей буквы алфавита ?>
                <h2><?php echo $letter; ?></h2>
                <ul class="">
                        <?php foreach($cities['data'] as $city) : ?>
                                <li>
                                        <?php echo CHtml::link($city['name'], $this->createUrl('/product', array('id'=>$model->id, 'cid'=>$city['id'], 'action'=>'storesInCity'))); ?>
                                        <?php echo $city['qt'];?>
                                </li>
                        <?php endforeach; ?>
                </ul>

                <?php
                        $count++;
                        // если кол-во выведенных букв равно максимально допустимому для данной колонки
                        // то ставится флаг создания новой колонки, сбрасывается кол-во выведенных элементов в колонке
                        // и отмечается, что первая колонка выведена (если даже выводилась уже не первая)
                        if($count == $current_num) {
                                $new_column = true; // вывод новой колонки
                                $is_first_column = false;
                                $count = 0;
                        }
                        else {
                                // иначе продолжается вывод в текущей колонке
                                $new_column = false;
                        }
                ?>
                <?php // если установлен флаг вывода новой колонки, закрывается тег текущей колонки и новая создастся на
                       // следующей итерации. Так же тег закроется, если выведен весь массив. ?>
                <?php if ($new_column || $items_qt == $count-1) : ?>
                        </div>
                <?php endif; ?>

        <?php endforeach; ?>



        <div class="clear"></div>
    </div>

</div>

<div class="spacer-30"></div>
<div class="clear"></div>