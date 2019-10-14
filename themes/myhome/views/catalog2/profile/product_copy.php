<?php
        $this->pageTitle = 'Копирование товара — MyHome.ru';
?>

<script type="text/javascript">
    $(document).ready(function(){
        store.initCopyForm();
    })
</script>


<div id="right_side">
    <?php echo CHtml::beginForm($this->createUrl('productCopy'), 'get', array('class'=>'products_copy_form')); ?>

        <?php if(!empty($errors)): ?>
                <div class="error-title">
                    <?php foreach($errors as $error): ?>
                        <?php echo $error; ?> <br />
                    <?php endforeach; ?>
                </div>
        <?php endif; ?>

        <div class="form">

            <div class="options_section">
                <div class="options_row">
                    <span class="label">из</span>
                    <select class="textInput stores" name="from_store">
                        <?php foreach($stores as $store) : ?>
                                <?php if($selected_store &&  $selected_store->id == $store->id) $selected = 'selected'; else $selected = ''; ?>
                                <option data-products="<?php echo $store->productQt; ?>" value="<?php echo $store->id; ?>" <?php echo $selected; ?>><?php echo $store->fullName; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <span class="store_procts_quant"><?php echo $selected_store ? CFormatterEx::formatNumeral($selected_store->productQt, array('товар', 'товара', 'товаров')) : '';?></span>
                    <div class="clear"></div>
                </div>
            </div>
            <div class="options_section">
                <div class="options_row">
                    <span class="label">в</span>
                    <input id="store_search" type="text" class="textInput" placeholder="Начните вводить адрес магазина">
                    <div class="stores_list_block">
                        <ul class="">
                            <li id="not_store">
                                <label>
                                    <input type="checkbox" name="" value=""/>Выбрать все магазины
                                    <span class="finded_stores">(<?php echo count($stores); ?>)</span>
                                </label>
                                <span>Выбрано (<span class="checked_cnt">0</span>)</span>
                            </li>

                            <?php foreach($stores as $store) : ?>
                                <?php if ($selected_store && $selected_store->id == $store->id) $class = 'disabled'; else $class = ''; ?>
                                <?php if (is_array($to_stores) && in_array($store->id, $to_stores)) $checked=true; else $checked = false; ?>
                                <li id="store_<?php echo $store->id; ?>" class='<?php echo $class; ?>'><label><?php echo CHtml::checkBox('to_stores[]', $checked, array('value'=>$store->id))?> <?php echo $store->fullName;?></label></li>
                            <?php endforeach; ?>

                            <li class="no_results hide">Ничего не найдено</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
        <div class="buttons_block">
            <?php echo CHtml::radioButtonList('copy_type', 1, $copy_types, array('class'=>'textInput', 'template'=>'<label>{input} {label}<labell>')); ?><br />
            <div class="btn_conteiner yellow">
                <?php echo CHtml::submitButton('Копировать товары', array('class'=>'btn_grey')); ?>
            </div>
        </div>
    <?php echo CHtml::endForm(); ?>

</div>