<?php $i = 0; ?>
<?php foreach ($stores as $st) : ?>

    <!--Проверяем на mall-->
    <?php $mall = MallBuild::model()->findByPk($st->mall_build_id) ?>
    <div class="-col-wrap address">
        <span class="-huge -gray"><?php echo ++$i.'.'?></span>
        <?php echo CHtml::link($st->name, $st::getLink($st->id), array('class' => '-skyblue -large')) ?>

        <?php echo CHtml::openTag('span', array('class' => '-block -gray -small -underline', 'lat' => $st->getCoordinates('lat'), 'lng' => $st->getCoordinates('lng'), 'hint' => $st->getBaloonContent())) ?>
        <?php echo $st->address ?>
        <br>
        <?php if (isset($mall) && $mall instanceof MallBuild) : ?>
            <?php echo CHtml::link($mall->name, Yii::app()->params->bmHomeUrl . '/about', array('class' => '-red -small')) ?>
            <?php echo Chtml::closeTag('span') ?>
        <?php endif ?>
    </div>

    <div class="-col-wrap price">
        <?php

        $storePrice = StorePrice::model()->findByAttributes(array(
            'store_id'   => $st->id,
            'product_id' => $model->id
        ));


        if ($storePrice && $storePrice->price > 0) {
            $ot = ($storePrice->price_type == $storePrice::PRICE_TYPE_MORE)
                ? 'от '
                : '';
            echo CHtml::tag('span', array('class' => '-huge -semibold'), $ot . number_format($storePrice->price, 0, '.', ' ') . ' руб.');
        } else {
            echo CHtml::tag('span', array('class' => '-large -gray'), 'Цена не указана');
        }
        unset($storePrice);
        ?>
    </div>
<?php endforeach ?>