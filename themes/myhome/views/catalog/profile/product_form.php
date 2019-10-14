<?php
if($model->status == Product::STATUS_IN_PROGRESS) $this->pageTitle = 'Добавление товара — MyHome.ru';
else $this->pageTitle = 'Редактирование товара — MyHome.ru';
?>

<script type="text/javascript">
    $(document).ready(function(){
        store.initForm();
    })
</script>

<h2>Шаг 2 из 2. Заполните анкету товара</h2>

<div id="right_side">

        <?php echo CHtml::beginForm('', 'post', array('class'=>'product_adding_form', 'enctype' => 'multipart/form-data'))?>

                <?php if((isset($errors['Product']) && !empty($errors['Product']))  ||  (isset($errors['Value']) && !empty($errors['Value']))) : ?>
                        <div class="error-title">
                                Некоторые обязательные поля формы не заполнены или заполнены некорректно.
                        </div>
                <?php endif; ?>

                <div class="form">

                        <div class="options_section">
                                <div class="options_row form_variant">
                                    <div class="option_label">
                                        Анкета
                                    </div>
                                    <div class="option_value " id="form_layout">
                                        <span class="current short">Краткая анкета</span>
                                        <span>Расширенная анкета</span>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                                <div class="options_row">
                                    <div class="option_label">
                                        Выбранная категория
                                    </div>
                                    <div class="option_value">
                                        <?php echo $model->category->name; ?>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                        </div>

                        <?php echo CHtml::hiddenField('pid', $model->id); ?>

                        <div class="options_section">

                                <div class="options_row">
                                    <div class="option_label">
                                        Фото <span class="required">*</span>
                                    </div>
                                    <div class="option_value product_image">
					<div class="loader"></div>
                                        <div id="add-image">

                                                <?php foreach($model->getImages(true, true) as $img) : ?>
                                                    <?php $this->renderPartial('_product_form_image', array('file'=>$img)); ?>
                                                    <?php $hideHint = true; ?>
                                                <?php endforeach; ?>

                                        </div>

                                        <div class="photo_item uploaded_photo error" style="display: <?php if(isset($errors['Product']['image_id'])) echo 'block'; else echo 'none'; ?>;">
                                            :(
                                        </div>

                                        <div class="clear"></div>

                                        <?php if($fileApi) : ?>

                                                <div class="add_photo">
                                                        <?php $this->widget('ext.FileUpload.FileUpload', array(
                                                                'url'=> $this->createUrl('productUploadImage'),
                                                                'postParams'=>array('pid'=>$model->id),
                                                                'config'=> array(
                                                                        'fileName' => 'Product[file]',
                                                                        'onSuccess'=>'js:function(response){
                                                                                              if(response.success) {
                                                                                                  $("#add-image").append(response.html);
                                                                                                  $(".photo_hint").hide();
                                                                                              } else {
                                                                                                  $(".photo_item.uploaded_photo.error").show();
                                                                                              }
                                                                                          }',
                                                                        'onStart' => 'js:function(data){
                                                                                $(".photo_item.uploaded_photo.error").hide();
                                                                                $(".product_image").addClass("disabled");
                                                                        }',
                                                                        'onFinished' => 'js:function(data){
                                                                        	$(".product_image").removeClass("disabled");
                                                                        }'
                                                                ),
                                                                'htmlOptions'=>array('accept'=>'image', 'class'=>'photofile_input'),
                                                        )); ?>
                                                        <span>
                                                                <i></i>
                                                                <span>Загрузить фото</span>
                                                        </span>
                                                </div>

                                        <?php else : ?>
                                                <div class="input_row">
                                                        <?php echo CHtml::fileField('Product[file_0]', '', array('class'=>'simple_input')); ?>
                                                </div>
                                        <?php endif; ?>

                                        <div class="clear"></div>

                                        <?php if(!isset($hideHint)) : ?>
                                                <span class="photo_hint">
                                                        Первое загруженное фото является обложкой товара.<br>
                                                        Размер фото не менее 300x300px.
                                                </span>
                                        <?php endif?>

                                    </div>
                                    <div class="clear"></div>
                                </div>

                            <div class="options_row">
                                <div class="option_label">
                                        Производитель <span class="required">*</span>
                                </div>
                                <div class="option_value">
                                        <?php
                                        $class = '';
                                        if(isset($errors['Product']['vendor_id'])) $class = ' error';
                                        $vendor = Yii::app()->db->createCommand()->select('name')->from('cat_vendor')->where('id=:id', array(':id'=>(int)$model->vendor_id))->limit(1)->queryScalar();
                                        $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
                                                'name'=>'Value_vendor',
                                                'value'=>$vendor,
                                                'sourceUrl'=>'/catalog/admin/vendor/acVendor',
                                                'options'=>array(
                                                        'minLength'=>'2',
                                                        'showAnim'=>'fold',
                                                        'select'=>'js:function(event, ui) {
                                                                        $("#Product_vendor_id").val(ui.item.id).keyup();
                                                                        $("#Product_country").val(ui.item.country).keyup();
                                                                        $("#country_name").val(ui.item.country_name).keyup();
                                                                        $("#Product_collection_id").html(ui.item.collections);
                                                                        if(ui.item.collections_qt > 0)
                                                                                $("#Product_collection_id").prop("disabled", false);
                                                                        else
                                                                                $("#Product_collection_id").prop("disabled", true);
                                                                      }',
                                                        'change'=>'js:function(event, ui) {
                                                                        if(ui.item === null) {
                                                                                $("#Product_vendor_id").val("");
                                                                                $("#Product_country").val("");
                                                                                $("#country_name").val("");
                                                                        }}',
                                                ),
                                                'htmlOptions'=>array('class'=>'textInput' . $class),
                                        ));
                                        ?>
                                        <?php echo CHtml::activeHiddenField($model, "vendor_id");?>
                                </div>
                                <div class="clear"></div>
                            </div>

                            <div class="options_row">
                                <div class="option_label">Коллекция</div>
                                <div class="option_value">
                                        <?php if($model->collection_id) $disabled = false; else $disabled = true;?>
                                        <?php echo CHtml::activeDropDownList($model,  "collection_id", array(0=>'Не выбрано'), array('class'=>'textInput', 'disabled'=>$disabled));?>
                                </div>
                                <div class="clear"></div>
                            </div>

                            <div class="options_row">
                                <div class="option_label">Страна производства <span class="required">*</span></div>
                                <div class="option_value">
                                        <?php
                                        $class = '';
                                        if(isset($errors['Product']['country'])) $class = ' error';
                                        $country = Yii::app()->db->createCommand()->select('name')->from('country')->where('id=:id', array(':id'=>(int)$model->country))->limit(1)->queryScalar();
                                        $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
                                                'name'=>'Country',
                                                'value'=>$country,
                                                'sourceUrl'=>'/utility/autocompleteCountry',
                                                'options'=>array(
                                                        'minLength'=>'3',
                                                        'showAnim'=>'fold',
                                                        'select'=>'js:function(event, ui) {$("#Product_country").val(ui.item.id).keyup();}',
                                                        'change'=>'js:function(event, ui) {if(ui.item === null) {$("#Product_country").val("");}}',
                                                ),
                                                'htmlOptions'=>array('class'=>'textInput' . $class, 'id'=>'country_name'),
                                        ));
                                        ?>
                                        <?php echo CHtml::activeHiddenField($model, "country");?>
                                </div>
                                <div class="clear"></div>
                            </div>

                            <div class="options_row">
                                <div class="option_label">Название <span class="required">*</span></div>
                                <div class="option_value">
                                        <?php echo CHtml::activeTextField($model,  "name", array('class'=>'textInput'));?>
                                </div>
                                <div class="clear"></div>
                            </div>

                            <div class="options_row">
                                <div class="option_label">Описание</div>
                                <div class="option_value">
                                        <?php echo CHtml::activeTextArea($model,  "desc", array('class'=>'textInput'));?>
                                </div>
                                <div class="clear"></div>
                            </div>

                            <div class="options_row">
                                <div class="option_label">Артикул</div>
                                <div class="option_value">
                                        <?php echo CHtml::activeTextField($model,  "barcode", array('class'=>'textInput'));?>
                                </div>
                                <div class="clear"></div>
                            </div>

                            <div class="options_row">
                                <div class="option_label">Гарантия</div>
                                <div class="option_value">
                                        <?php echo CHtml::activeTextField($model,  "guaranty", array('class'=>'textInput'));?>
                                </div>
                                <div class="clear"></div>
                            </div>

                        </div>

                        <div class="options_section">
                                <?php $currentGroup = null;?>
                                <?php foreach($model->orderedValues as $value) : ?>

                                        <?php if($value->option->group_id === null) continue; ?>
                                        <?php if($currentGroup === null) $currentGroup = $value->option->group_id; ?>

                                        <?php if($currentGroup != $value->option->group_id) : ?>
                                                </div><div class="options_section">
                                                <?php $currentGroup = $value->option->group_id; ?>
                                        <?php endif; ?>

                                        <?php if(isset($errors['Value'][$value->id])) $value->addErrors($errors['Value'][$value->id]); ?>

                                        <?php $this->widget('catalog.components.widgets.productvalue.FrontProductValue', array('value'=>$value));?>

                                <?php endforeach; ?>
                        </div>

                        <?php echo CHtml::hiddenField('continue', 0, array('id'=>'continue')); ?>

                        <div class="options_section stores_list_container">
                            <h2 class="block_head">Добавить в магазины</h2>
                            <div class="shops_list_block">
                                <div class="shops_list_head">
                                    <label><input type="checkbox" name="" value=""/>Все магазины</label>
                                    <span>Установить во всех магазинах</span>
                                    <!--<div class="availability">
                                            <?php /*echo CHtml::dropDownList("product_available", '', StorePrice::$statuses, array('class'=>'textInput')); */?>
                                    </div>-->
                                    <div class="adding_product_price">
                                        <?php echo CHtml::dropDownList('price_type', StorePrice::PRICE_TYPE_MORE, StorePrice::$price_types, array('class'=>'textInput')); ?>
                                        <div class="adding_product_price">
                                            <input type="text" class="textInput" />
                                            <span class="currency">руб.</span>
                                        </div>
                                    </div>
                                    <div class="clear"></div>
                                </div>
                                <div class="shops_list_container">
                                    <ul class="">

                                        <?php $stores = Store::getStoresForOwner(Yii::app()->user->id); ?>
                                        <?php echo count($stores) == 0 ? 'Нет магазинов' : '';?>

                                        <?php foreach($stores as $store) : ?>

                                            <?php
                                                if(in_array($store->id, $model->getStoresIds()) || (isset($_POST['for_stores']) && in_array($store->id, $_POST['for_stores']))) $checked=true;
                                                else $checked=false;
                                            ?>

                                            <?php $store_price = $store->getProductPrice($model->id);?>

                                            <?php if(isset($_POST['for_stores']) && in_array($store->id, $_POST['for_stores'])) {
                                                    if(!$store_price) $store_price = new StorePrice();
                                                    $store_price->price = floatval(Yii::app()->request->getParam("Store_{$store->id}_product_price"));
                                                    $store_price->status = Yii::app()->request->getParam("Store_{$store->id}_product_status");
                                                    $store_price->price_type = Yii::app()->request->getParam("Store_{$store->id}_product_price_type");
                                            }?>

                                                <li class="<?php echo $checked ? 'checked' : ''; ?>">
                                                    <label><?php echo CHtml::checkBox('for_stores[]', $checked, array('value'=>$store->id)); ?><?php echo ($store->city) ? $store->city->name : ''; ?>, <?php echo $store->address; ?></label>
                                                        <!--<div class="availability">-->
                                                                <?php //echo CHtml::dropDownList("Store_{$store->id}_product_status", $store_price ? $store_price->status : StorePrice::STATUS_AVAILABLE, StorePrice::$statuses, array('class'=>'textInput')); ?>
                                                        <!--</div>-->
                                                    <div class="adding_product_price">
                                                        <?php echo CHtml::dropDownList("Store_{$store->id}_product_price_type", $store_price ? $store_price->price_type : StorePrice::PRICE_TYPE_MORE, StorePrice::$price_types, array('class'=>'textInput')); ?>
                                                        <div class="adding_product_price">
                                                            <?php if($store_price) $price = round($store_price->price); else $price = ''; ?>
                                                            <?php echo CHtml::textField("Store_{$store->id}_product_price", $price, array('class'=>'textInput')); ?>
                                                            <span class="currency">руб.</span>
                                                        </div>
                                                    </div>
                                                    <div class="clear"></div>
                                                </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                            <div class="clear"></div>
                        </div>
                </div>

                <div class="buttons_block">
                    <div class="btn_conteiner yellow">
                        <input type="submit" class="btn_grey" value="Сохранить">
                        <input type="button" class="btn_grey" id="save_and_continue" value="Сохранить и добавить еще один товар">
                    </div>
                </div>
        <?php echo CHtml::endForm(); ?>
</div>

<?php Yii::app()->clientScript->registerScript('submit', '
        $("#save_and_continue").click(function(){
                $("#continue").val(1);
                $(".product_adding_form").submit();
        });
', CClientScript::POS_READY); ?>
