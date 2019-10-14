<div class="menu_block">
        <?php $seoText = CHtml::tag('span', array('class'=>'text_block'), $model->name . ':'); ?>
        <?php $this->widget('zii.widgets.CMenu',array(
                'activeCssClass' => 'current',
                'encodeLabel' => false,
                'items'=>array(
                        array(
                                'label'=>$seoText.'Описание',
                                'url'=>$this->createUrl('/catalog2/vendor', array('id'=>$model->id, 'action'=>'index')),
                                'active' => $this->id == 'vendor' && $this->action->id == 'index',
                        ),
                        array(
                                'label'=>$seoText.'Товары',
                                'url'=>$this->createUrl('/catalog2/vendor', array('id'=>$model->id, 'action'=>'products')),
                                'active' => $this->id == 'vendor' && $this->action->id == 'products',
                        ),
                        array(
                                'label'=>$seoText.'Магазины',
                                'url'=>$this->createUrl('/catalog2/vendor', array('id'=>$model->id, 'action'=>'stores')),
                                'active' => $this->id == 'vendor' && ($this->action->id == 'stores' || $this->action->id == 'storesInCity'),
                        ),
                )));
        ?>
        <div class="clear"></div>
</div>