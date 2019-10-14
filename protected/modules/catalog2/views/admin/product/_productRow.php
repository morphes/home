<?php Yii::app()->clientScript->registerScriptFile('/js/admin/jquery.maskMoney.js');?>

<?php echo CHtml::openTag('tr', array('id'=>'product-'.$model->id, 'pid'=>$model->id, 'class'=>'product ' . $class)); ?>

        <td>
		<p><?php echo CHtml::button('Цены', array('class'=>'btn small price', 'data-pid'=>$model->id, 'style'=>'width: 60px;')); ?></p>
                <p><?php echo CHtml::button('Клон', array('class'=>'btn small success clone', 'style'=>'width: 60px;', 'pid'=>$model->id)); ?></p>
                <p><?php echo CHtml::button('Удалить', array('class'=>'btn small danger delete', 'pid'=>$model->id, 'style'=>'width: 60px;')); ?></p>
		<?php if ($model->status == Product::STATUS_ACTIVE) : ?>
			<p><?php echo CHtml::button('Посмотр.', array('class'=>'btn small preview',  'style'=>'width: 60px;', 'data-url'=>Product::getLink($model->id, null, $model->category_id))); ?></p>
        	<?php endif; ?>
	</td>

        <td>
                <?php echo CHtml::activeTextField($model,  "[$model->id]name", array('style'=>'width:170px;'));?>
                <?php echo CHtml::error($model, "[$model->id]name");?>
        </td>

        <td>
                <?php echo CHtml::activeTextArea($model,  "[$model->id]desc", array('style'=>'width:270px; height:100px;'));?>
                <?php echo CHtml::error($model, "[$model->id]desc");?>
        </td>

        <!--<td>
                <?php /*echo CHtml::activeTextArea($model,  "[$model->id]tags");*/?>
                <?php /*echo CHtml::error($model, "[$model->id]tags");*/?>
        </td>-->

        <td>
                <?php echo CHtml::activeTextField($model,  "[$model->id]barcode");?>
                <?php echo CHtml::error($model, "[$model->id]barcode");?>
        </td>

	<!--<td>
		<?php /*echo CHtml::activeTextField($model,  "[$model->id]price");*/?>
		<?php /*echo CHtml::error($model, "[$model->id]price");*/?>
	</td>-->
	<script type="text/javascript">$('#Product_<?php echo $model->id;?>_price').maskMoney({thousands:''});</script>


        <td>
                <?php
                $country = Yii::app()->dbcatalog2->createCommand()->select('name')->from('country')->where('id=:id', array(':id'=>$model->country))->limit(1)->queryScalar();
                $this->widget('application.components.widgets.CAjaxAutoComplete', array(
                        'name'=>'Product_country_' . $model->id,
                        'value'=>$country,
                        'sourceUrl'=>'/catalog2/admin/product/country',
                        'options'=>array(
                                'minLength'=>'1',
                                'showAnim'=>'fold',
                                'select'=>'js:function(event, ui) {$("#Product_' . $model->id . '_country").val(ui.item.id).keyup();}',
                                'change'=>'js:function(event, ui) {if(ui.item === null) {$("#Product_' . $model->id . '_country").val("");}}',
                        ),
                        'htmlOptions'=>array('style'=>'width: 130px;'),
                ));
                ?>
                <?php echo CHtml::activeHiddenField($model,  "[$model->id]country");?>
                <?php echo CHtml::error($model, "[$model->id]country");?>
        </td>

        <td>
                <?php echo CHtml::activeTextField($model,  "[$model->id]guaranty");?>
                <?php echo CHtml::error($model, "[$model->id]guaranty");?>
        </td>

        <td>
                <?php echo CHtml::activeDropDownList($model,  "[$model->id]usageplace", MainRoom::getAllRooms(), array('multiple'=>true));?>
                <?php echo CHtml::error($model, "[$model->id]usageplace");?>
        </td>

        <td>
                <?php echo CHtml::textField($model->id . '_similar-text-id', '', array('style'=>'width:44px;'));?>
                <?php echo CHtml::button('+', array('style'=>'width:8px;', 'class'=>'btn similar-button-add', 'pid'=>$model->id));?>
                <br>
                <ul id="similar-product-<?php echo $model->id?>-list">
                        <?php foreach($model->getSimilar(false, 100) as $spid) : ?>
                                <?php $this->renderPartial('_similarRow', array('pid'=>$model->id, 'spid'=>$spid['id'])); ?>
                        <?php endforeach; ?>
                </ul>
        </td>

        <td>
                <?php echo CHtml::activeTextField($model,  "[$model->id]related_product");?>
                <?php echo CHtml::error($model, "[$model->id]related_product");?>
        </td>

        <td>
                <?php echo CHtml::activeCheckBox($model,  "[$model->id]eco");?>
                <?php echo CHtml::error($model, "[$model->id]eco");?>
        </td>

        <td>
		</div>
                	<?php echo CHtml::fileField('coverFile', '', array("size"=>1, "multiple"=>false, "class"=>"coverFile", 'pid'=>$model->id, 'style'=>'margin-bottom:10px;')); ?>
		</div>
		<div>
			<?php echo CHtml::textField('cover', '', array('data-pid'=>$model->id, 'placeholder'=>'url изображения'))?>
			<?php echo CHtml::button('загр.', array('class'=>'btn url-image-upload', 'style'=>'width:60px;')); ?>
			<?php echo CHtml::error($model, "[$model->id]image_id");?>
		</div>
                <p><a style="cursor: pointer" onclick="showHideSelector($(this), 'cover-preview-<?php echo $model->id; ?>')">скрыть</a></p>
                <ul class="thumbnails" id="cover-preview-<?php echo $model->id; ?>">
                        <?php if($model->cover && $model->cover->id) $this->renderPartial('_image', array('file'=>$model->cover, 'product'=>$model, 'type'=>'cover')); ?>
                </ul>
        </td>

        <td>
		<div>
                	<?php echo CHtml::fileField('imageFiles', '', array("size"=>1, "multiple"=>true, "class"=>"imageFiles", 'pid'=>$model->id, 'style'=>'margin-bottom:10px;')); ?>
		</div>
		<div>
			<?php echo CHtml::textField('image', '', array('data-pid'=>$model->id, 'placeholder'=>'url изображения'))?>
			<?php echo CHtml::button('загр.', array('class'=>'btn url-image-upload', 'style'=>'width:60px;')); ?>
		</div>
		<p><a style="cursor: pointer" onclick="showHideSelector($(this), 'images-preview-<?php echo $model->id; ?>')">скрыть</a></p>
                <ul class="thumbnails" id="images-preview-<?php echo $model->id; ?>">
                        <?php foreach($model->getImages(true) as $image) : ?>
                                <?php $this->renderPartial('_image', array('file'=>$image, 'product'=>$model, 'type'=>'image')); ?>
                        <?php endforeach; ?>
                </ul>
        </td>

        <td>
                <?php
                $vendor = Yii::app()->dbcatalog2->createCommand()->select('name')->from('cat_vendor')->where('id=:id', array(':id'=>(int)$model->vendor_id))->limit(1)->queryScalar();
                $this->widget('application.components.widgets.CAjaxAutoComplete', array(
                        'name'=>'Value_vendor_' . $model->id,
                        'value'=>$vendor,
                        'sourceUrl'=>'/catalog2/admin/vendor/acVendor',
                        'options'=>array(
                                'minLength'=>'1',
                                'showAnim'=>'fold',
                                'select'=>'js:function(event, ui) {$("#Product_' . $model->id . '_vendor_id").val(ui.item.id).keyup();$("#Product_' . $model->id . '_collection_id").html(ui.item.collections)}',
                                'change'=>'js:function(event, ui) {if(ui.item === null) {$("#Product_' . $model->id . '_vendor_id").val("");}}',
                        ),
                ));
                ?>
                <?php echo CHtml::activeHiddenField($model,  "[$model->id]vendor_id");?>
                <?php echo CHtml::error($model, "[$model->id]vendor_id");?>
        </td>

        <td>
                <?php
		if ($model->vendor)
			$collections = $model->vendor->getCollectionsArray();
		else
			$collections = array();
		$collsDropDown = array();
		foreach ($collections as $item) {
			$collsDropDown[$item['id']] = $item['name'];
		}
		?>
                <?php echo CHtml::activeDropDownList($model,  "[$model->id]collection_id", array(0=>'Не выбрано') + $collsDropDown);?>
                <?php echo CHtml::error($model, "[$model->id]collection_id");?>
        </td>

        <?php $availableOptions = $model->category->availableOptions; ?>

        <?php foreach($model->values as $value) : ?>

                <?php if(!isset($availableOptions[$value->option_id])) continue; ?>

                <?php if(isset($errors['Value'][$value->id])) $value->addErrors($errors['Value'][$value->id]); ?>

                <td>
                        <?php $this->widget('application.components.widgets.productvalue.ProductValue', array('value'=>$value, 'ARForm'=>true));?>
                        <?php echo CHtml::error($value,  "[$value->id]value");?>
                </td>

        <?php endforeach; ?>

        <td>
                <?php echo CHtml::activeTextArea($model,  "[$model->id]admin_comment");?>
                <?php echo CHtml::error($model, "[$model->id]admin_comment");?>
        </td>

        <td>
                <?php echo CHtml::activeDropDownList($model,  "[$model->id]status", Product::$statuses);?>
                <?php echo CHtml::error($model, "[$model->id]status");?>
        </td>

<?php echo CHtml::closeTag('tr'); ?>
