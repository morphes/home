<?php if (!isset($store_id)) $store_id = null; ?>
<div class="menu_block">
        <?php $seoText = CHtml::tag('span', array('class'=>'text_block'), $model->name . ':'); ?>
        <?php $this->widget('zii.widgets.CMenu',array(
                'activeCssClass' => 'current',
                'encodeLabel' => false,
                'items'=>array(
                        array(
                                'label'=>$seoText.'Описание',
                                'url'=>$this->createUrl('/product', array('id'=>$model->id, 'action'=>'index', 'store_id'=>$store_id)),
                                'active' => $this->id == 'product' && $this->action->id == 'index',
                        ),
                        array(
                                'label'=>$seoText.'Характеристики',
                                'url'=>$this->createUrl('/product', array('id'=>$model->id, 'action'=>'description', 'store_id'=>$store_id)),
                                'active' => $this->id == 'product' && $this->action->id == 'description',
                        ),
                        array(
                                'label'=>$seoText.'Где купить',
                                'url'=>$this->createUrl('/product', array('id'=>$model->id, 'action'=>'stores', 'store_id'=>$store_id)),
                                'active' => $this->id == 'product' && ($this->action->id == 'stores' || $this->action->id == 'storesInCity'),
                        ),
                        array(
                                'label'=>$seoText.'Отзывы',
                                'url'=>$this->createUrl('/product', array('id'=>$model->id, 'action'=>'feedback', 'store_id'=>$store_id)),
                                'template'=>"{menu} <span>(" . $model->count_feedback . ")</span>",
                                'active' => $this->id == 'product' && $this->action->id == 'feedback',
                        ),
                        array(
                                'label'=>$seoText.'Обсуждение',
                                'url'=>$this->createUrl('/product', array('id'=>$model->id, 'action'=>'comment', 'store_id'=>$store_id)),
                                'template'=>"{menu} <span>(" . $model->count_comment . ")</span>",
                                'active' => $this->id == 'product' && $this->action->id == 'comment',
                        ),
                )));
        ?>
        <div class="clear"></div>
</div>