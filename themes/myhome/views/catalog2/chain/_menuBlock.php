<div class="menu_block">
        <?php $seoText = CHtml::tag('span', array('class'=>'text_block'), $model->name . ':'); ?>
        <?php $this->widget('zii.widgets.CMenu',array(
                'activeCssClass' => 'current',
                'encodeLabel' => false,
                'items'=>array(
                        array(
                                'label'=>$seoText.'Описание',
                                'url'=>$this->createUrl('index', array('id'=>$model->id)),
                                'active' => $this->id == 'chain' && $this->action->id == 'index',
                        ),
                        array(
                                'label'=>$seoText.'Магазины',
                                'url'=>$this->createUrl('stores', array('id'=>$model->id)),
                                'active' => $this->id == 'chain' && ($this->action->id == 'stores' || $this->action->id == 'storesInCity'),
                        ),
                )));
        ?>
        <div class="clear"></div>
</div>