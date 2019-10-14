<?php
        $seoText = CHtml::tag('span', array('class'=>'text_block'), $model->name . ':');

        // базовое меню для магазинов с любым тарифом
        $items = array(
                array(
                        'label'=>$seoText.'Описание',
                        'url'=>$this->createUrl('index', array('id'=>$model->id)),
                        'active' => $this->id == 'store' && $this->action->id == 'index',
                ),
        );

        // расширение меню в зависимости от тарифа
        if ($model->tariff_id != Store::TARIF_FREE) {
                $items[] = array(
                        'label'=>$seoText.'Товары',
                        'url'=>$this->createUrl('products', array('id'=>$model->id)),
                        'active' => $this->id == 'store' && $this->action->id == 'products',
                );
        }

        $items[] = array(
                'label'=>$seoText.'Отзывы',
                'template'=>"{menu} <span>(" . $model->feedbackQt . ")</span>",
                'url'=>$this->createUrl('feedback', array('id'=>$model->id)),
                'active' => $this->id == 'store' && $this->action->id == 'feedback',
        );
?>

<div class="menu_block">
        <?php $this->widget('zii.widgets.CMenu',array(
                'activeCssClass' => 'current',
                'encodeLabel' => false,
                'items'=>$items,
                ));
        ?>
        <div class="clear"></div>
</div>