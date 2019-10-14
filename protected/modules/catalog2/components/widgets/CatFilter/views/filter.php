<?php
$urlParams = array('eng_name'=>$category->eng_name);
if ($city instanceof City)
	$urlParams['city_name'] = $city->eng_name;
echo CHtml::beginForm(Yii::app()->createUrl('/catalog2/category/list', $urlParams), 'get', array(
        'id'=>'filter_form'
)); ?>

<div class="shadow_block padding-18 catalog_filter">

    <div class="filter_hint">
        <i></i>Показать <a id="show_result"><span>0</span> вариантов</a>
    </div>

        <?php echo CHtml::hiddenField('country_id', $category->id); ?>

    <div class="drop_down filter_item">
        <div class="filter_name">Помещения</div>
            <?php echo CHtml::dropDownList('rooms', isset($selected['rooms']) ? $selected['rooms'] : '-1', array('-1'=>'Все помещения') + MainRoom::getAllRooms(), array('class'=>'textInput'))?>
    </div>

    <div class="filter_item price_filter">
		<div class="filter_name">Цена</div>
		<input type="text" id="price_from" name="price_from" class="textInput" value="<?php echo $selected['price_from'];?>"/> — <input type="text" id="price_to" name="price_to" class="textInput" value="<?php echo $selected['price_to'];?>"/><span>р.</span>
		<input type="hidden" id="min_price" value="0">
		<input type="hidden" id="max_price" value="<?php echo round($category->getMaxPrice());?>">
	    	<?php echo CHtml::hiddenField('view_type', $viewType, array('id'=>'viewtype')); ?>
	    <div id="range"></div>
    </div>

    <div class="manufacture_list filter_item">
        <div class="filter_name">Производители</div>
        <div class="drop_down filter_item filter_item_chained">
                <?php echo CHtml::dropDownList('vendor_country', isset($selected['vendor_country']) ? $selected['vendor_country'] : '', array(''=>'Все страны')+CHtml::listData(Vendor::getCountries($category->id), 'id', 'name'), array('class'=>'textInput')); ?>
        </div>
        <div id="vendors-list">
		<?php
		$listData = CHtml::listData(Vendor::getVendorsByCountry(isset($selected['vendor_country']) ? $selected['vendor_country'] : '',$category->id), 'id', 'name');
		$vendorVisible = array_slice($listData, 0, 5, true);
		$vendorHidden = array_slice($listData, 5, count($listData), true);

		if ( ! $selected['vendors'])
			$selected['vendors'] = array();
		?>
            	<ul class="visible_types">
                    <?php // Список производителей, видимых сразу
		    $htmlVisibleVendors = '';
		    foreach($vendorVisible as $v_id => $v_name) {
			    $htmlVisibleVendors .= CHtml::openTag('li');
			    $htmlVisibleVendors .= CHtml::checkBox('vendors[]', in_array($v_id, $selected['vendors']), array('id' => 'vendor_'.$v_id, 'value' => $v_id));
			    $htmlVisibleVendors .= CHtml::label($v_name, 'vendor_'.$v_id);
			    $htmlVisibleVendors .= CHtml::closeTag('li');
		    }
		    echo $htmlVisibleVendors;
		    ?>
            	</ul>
		<?php if ($vendorHidden) : ?>
		<ul class="hide_types hide">
			<?php // Список производителей, скрытых за кнопкой "показать все"
			$htmlHiddenVendors = '';
			foreach($vendorHidden as $v_id => $v_name) {
				$htmlHiddenVendors .= CHtml::openTag('li');
				$htmlHiddenVendors .= CHtml::checkBox('vendors[]', in_array($v_id, $selected['vendors']), array('id' => 'vendor_'.$v_id, 'value' => $v_id));
				$htmlHiddenVendors .= CHtml::label($v_name, 'vendor_'.$v_id);
				$htmlHiddenVendors .= CHtml::closeTag('li');
			}
			echo $htmlHiddenVendors;
			?>
		</ul>
		<span class="show_all">Показать все</span>
		<?php endif; ?>
        </div>
    </div>

        <?php
        /**
         * Проход по опциям сокращенной карточки товара и вывод
         */
        foreach($options as $option)
        {
                /**
                 * Пропуск опции без названий, типов или без значения
                 */
                if(empty($option['name']) || empty($option['type_id']))
                        continue;

                /**
                 * Контейнер опции
                 */
                echo CHtml::openTag('div', array('class'=>'filter_item'));

                /**
                 * Заголовок опции
                 */
                echo CHtml::openTag('div', array('class'=>'filter_name'));
                echo  '<i></i>' . CHtml::tag('span', array(), $option['name']);
                echo CHtml::closeTag('div');

                echo CHtml::openTag('div', array('class'=>'slide_container'));

                /**
                 * Вывод названия и значения опции в зависимости от типа опции
                 */
                switch($option['type_id']) {
                        case Option::TYPE_INPUT :
                                echo CHtml::textField($option['key'], isset($selected[$option['key']]) ? $selected[$option['key']] : '', array('class'=>'textInput'));
                                echo CHtml::hiddenField('has_value', 1);
                                break;

                        case Option::TYPE_TEXTAREA :
                                break;

                        case Option::TYPE_SELECT :
                                $values = Yii::app()->dbcatalog2->createCommand()->from('cat_value')->order('position')
                                        ->where('option_id=:oid and product_id is null', array(':oid'=>$option['id']))->queryAll();
                                $dropdownValues = array();
                                foreach($values as $val) $dropdownValues[$val['id']]=$val['value'];
                                echo CHtml::dropDownList($option['key'], isset($selected[$option['key']]) ? $selected[$option['key']] : '', array(''=>'Не важно')+$dropdownValues, array('class'=>'textInput'));
                                echo CHtml::hiddenField('has_value', 1);
                                break;

                        case Option::TYPE_CHECKBOX:
                                echo CHtml::radioButtonList($option['key'], isset($selected[$option['key']]) ? $selected[$option['key']] : '', array(''=>'Не важно', 1=>'Да', 0=>'Нет'));
                                echo CHtml::hiddenField('has_value', 1);
                                break;

                        case Option::TYPE_SELECTMULTIPLE :
                                $values = Yii::app()->dbcatalog2->createCommand()->from('cat_value')->order('position')
                                        ->where('option_id=:oid and product_id is null', array(':oid'=>$option['id']))->queryAll();
                                $checkboxes = array();
                                foreach($values as $val) $checkboxes[$val['id']]=$val['value'];
                                echo CHtml::openTag('ul');
                                echo CHtml::checkBoxList($option['key'], isset($selected[$option['key']]) ? $selected[$option['key']] : '', $checkboxes, array('template'=>'<li>{input} {label}</li>', 'separator'=>''));
                                echo CHtml::closeTag('ul');
                                echo CHtml::hiddenField('has_value', 1);
                                break;

                        case Option::TYPE_COLOR :
                                $values = Yii::app()->dbcatalog2->createCommand()->from('cat_color')->queryAll();
                                echo CHtml::openTag('div', array('class'=>'room_color'));
                                echo CHtml::openTag('ul', array('class'=>'colors_list'));
                                foreach($values as $val) {
                                        echo CHtml::openTag('li', array('class'=>$val['param'], 'id'=>$val['id'], 'title' => $val['name']));
                                        echo CHtml::checkBox($option['key'].'[]', false, array('value'=>$val['id'], 'class'=>'hide'));
                                        echo '<p class="hide">' . $val['name'] . '</p><div></div>';
                                        echo CHtml::closeTag('li');
                                }
                                echo CHtml::closeTag('ul');
                                echo CHtml::hiddenField($option['key'].'_selected', implode(', ', (isset($selected[$option['key']]) && is_array($selected[$option['key']])) ? $selected[$option['key']] : array()));
                                echo '<div class="clear"></div><div class="checked_color"></div>';
                                echo CHtml::closeTag('div');
                                echo CHtml::hiddenField('has_value', 1);
                                break;

                        case Option::TYPE_STYLE :
                                $values = Yii::app()->dbcatalog2->createCommand()->from('cat_style')->queryAll();
                                $dropdownValues = array();
                                foreach($values as $val) $dropdownValues[$val['id']]=$val['name'];
                                echo CHtml::dropDownList($option['key'], isset($selected[$option['key']]) ? $selected[$option['key']] : '', array(''=>'Не важно')+$dropdownValues, array('class'=>'textInput'));
                                echo CHtml::hiddenField('has_value', 1);
                                break;

                        case Option::TYPE_IMAGE :
                                break;

                        case Option::TYPE_SIZE :

                                $catParams = $category->getParamsArray();
                                if(isset($catParams['filterable_'.$option['type_id']]) && in_array($option['id'], $catParams['filterable_'.$option['type_id']])) {
                                        echo 'от ' . CHtml::textField($option['key'].'[from]', isset($selected[$option['key']]['from']) ? $selected[$option['key']]['from'] : '', array('class'=>'textInput', 'style'=>'width:50px;'));
                                        echo ' до ' . CHtml::textField($option['key'].'[to]', isset($selected[$option['key']]['to']) ? $selected[$option['key']]['to'] : '', array('class'=>'textInput', 'style'=>'width:50px;'));
                                        echo CHtml::hiddenField('has_value', ( (isset($selected[$option['key']]['from']) && ! empty($selected[$option['key']]['from'])) || (isset($selected[$option['key']]['to']) && ! empty($selected[$option['key']]['to']) )) ? 1 : 0);
                                } else {
                                        echo CHtml::textField($option['key'], isset($selected[$option['key']]) ? $selected[$option['key']] : '', array('class'=>'textInput'));
                                        echo CHtml::hiddenField('has_value', isset($selected[$option['key']]) ? 1 : 0);
                                }

                                $params = $option['param'];
                                if(empty($params{0}))
                                        break;
                                $params = unserialize($params);
                                if(isset($params['size_unit']) && isset(Option::$units[$params['size_unit']]))
                                        echo ' ' . Option::$units[$params['size_unit']];
                                break;
                }

                echo CHtml::closeTag('div');
                echo CHtml::closeTag('div');
        }
        ?>

    <div class="btn_conteiner yellow">
        <a class="btn_grey" id="filter_action">Показать</a>
    </div>
</div>
<?php echo CHtml::endForm(); ?>


<?php Yii::app()->clientScript->registerScript('filter', '
        $("#vendor_country").change(function(){
                var cid = $(this).val();
                var category_id = $("#country_id").val();
                $("#vendors-list").find("input").prop("checked", false);
                $.post("/catalog2/category/ajaxVendorsList", {country_id: cid, category_id:category_id}, function(response){
                        response = $.parseJSON(response);
                        if(response.success)
                                $("#vendors-list").html(response.html);
                        else
                                alert(response.message);
                });
        });

        $("#filter_action").click(function(){
                $("#filter_form").submit();
        });

        $("#show_result").live("click", function(){
                $("#filter_form").submit();
        });
', CClientScript::POS_READY);?>