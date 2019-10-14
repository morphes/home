<?php if (!$model->isNewRecord && $model->tariff_id != Store::TARIF_FREE) : ?>
        <div id="left_side" class="new_template">
                <?php $this->widget('zii.widgets.CMenu', array(
                        'items'=>array(
                                array('label'=>'Общая информация', 'url'=>array('storeUpdate', 'id'=>$model->id), 'active'=>$this->action->id=='storeUpdate'),
                                array('label'=>'Витрина товаров', 'url'=>array('storeShowcase', 'id'=>$model->id), 'active'=>$this->action->id=='storeShowcase'),
                                array(
					'label'       => 'Фотогалерея',
					'url'         => ($model->tariff_id == Store::TARIF_MINI_SITE && $model->subdomain)
						         ? Yii::app()->createAbsoluteUrl('catalog2/store/moneyGallery', array('sub' => $model->subdomain->domain))
						         : '#',
					'itemOptions' => ($model->tariff_id == Store::TARIF_MINI_SITE && $model->subdomain)
		                                         ? array()
						         : array('class' => 'disabled')
				),
                                array(
					'label'       => 'Акции и новости',
					'url'         => ($model->tariff_id == Store::TARIF_MINI_SITE && $model->subdomain)
						         ? StoreNews::getLink($model, 'list')
						         : '#',
					'itemOptions' => ($model->tariff_id == Store::TARIF_MINI_SITE && $model->subdomain)
						         ? array()
						         : array('class' => 'disabled')
				),

                        ),
                        'htmlOptions'=>array('class'=>'store_menu'),
                        'activeCssClass'=>'current',
                ));?>
        </div>
<?php endif; ?>