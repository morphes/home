<?php echo CHtml::openTag('tr', array('id'=>'category-option-'.$model->id, 'class'=>!empty($model->parent_id) ? 'parentOption' : '')); ?>


        <td>
                <?php $this->widget('application.components.widgets.CAjaxAutoComplete', array(
                        'model'=>$model,
                        'attribute'=>"[$model->id]key",
                        'sourceUrl'=>array('parentKeys','cid'=>$model->category_id),
                        'htmlOptions'=>array('style'=>'width:110px;'),
                        'options'=>array(
                                'select'=>'js: function(event, ui) {
                                var option_id = ' . $model->id . ';
                                $.ajax({
                                        url: "/catalog/admin/category/useParentOption",
                                        data: {option_id: option_id, parent_option_id:ui.item["id"]},
                                        type: "get",
                                        dataType: "json",
                                        async: false,
                                        success: function(data) {
                                                if(data.result) {
                                                        $("#category-option-"+option_id).replaceWith(data.html);
                                                        $("#category-option-"+option_id).find("textarea, input:not(.category-option-delete), checkbox").attr("disabled", "disabled");
                                                }
                                        }
                                });
                        }',
                        ),
                ));
                ?>
                <?php echo CHtml::error($model, "[$model->id]key")?>
        </td>

        <td class="option-type_id-form">
                <?php echo CHtml::activeDropDownList($model, "[$model->id]type_id", Option::$types, array('class'=>'type-switch','option'=>$model->id,'style'=>'width:155px;', 'ignore_warn'=>''));?>
                <?php echo CHtml::error($model, "[$model->id]type_id")?>
        </td>

        <td>
                <?php echo CHtml::activeDropDownList($model, "[$model->id]group_id", $groups, array('style'=>'width:155px;'));?>
                <?php echo CHtml::error($model, "[$model->id]group_id")?>
        </td>

        <td>
                <?php echo CHtml::activeTextField($model,  "[$model->id]name", array('style'=>'width:155px;'));?>
                <?php echo CHtml::error($model, "[$model->id]name")?>
        </td>

        <td>
                <?php echo CHtml::openTag('div', array('id'=>'category-option-value-addform-'.$model->id, 'style'=>!$model->checkValueList() ? 'display:none;' : '',)); ?>
                        <?php echo CHtml::textField('category-option-value-input-' . $model->id, '', array('style'=>'width:105px;', 'class'=>'category-option-value-input', 'oid'=>$model->id));?>
                        <?php echo CHtml::button('+', array('style'=>'width:10px;', 'class'=>'btn values-add', 'id'=>'values-add-' . $model->id, 'option'=>$model->id));?>
                        <br>
                        <ul id="category-option-values-<?php echo $model->id?>">
                                <?php foreach($model->availableValues as $value) : ?>
                                        <?php isset($errors['Value'][$value->id]) ? $value->addErrors($errors['Value'][$value->id]) : false; ?>
                                        <?php $this->renderPartial('_optionValueRow', array('model'=>$value)); ?>
                                <?php endforeach; ?>
                        </ul>
                <?php echo CHtml::closeTag('div'); ?>
        </td>


        <td id="option_<?php echo $model->id; ?>_params_form">
                <?php echo $model->getParamsForm(); ?>
        </td>

        <?php /*
        <td>
                <?php echo CHtml::activeTextArea($model,  "[$model->id]desc", array('style'=>'width:150px;'));?>
                <?php echo CHtml::error($model, "[$model->id]desc");?>
        </td>
        */ ?>

        <td class="filterable-option" oid="<?php echo $model->id;?>">
                <?php echo CHtml::activeCheckBox($model, "[$model->id]forminimized");?>
                <?php echo 'В кратком фильтре'; ?>
                <?php echo CHtml::error($model, "[$model->id]forminimized");?>
                <br />
                <?php echo CHtml::activeCheckBox($model, "[$model->id]filterable");?>
                <?php echo 'В расширенном фильтре'; ?>
                <?php echo CHtml::error($model, "[$model->id]filterable");?>
                <br />
                <?php echo CHtml::activeCheckBox($model, "[$model->id]minicard");?>
                <?php echo 'В краткой карточке товара'; ?>
                <?php echo CHtml::error($model, "[$model->id]minicard");?>
                <br />
                <?php echo CHtml::activeCheckBox($model, "[$model->id]required");?>
                <?php echo 'Обязательное для заполнения'; ?>
                <?php echo CHtml::error($model, "[$model->id]required");?>
                <br />
                <?php echo CHtml::activeCheckBox($model, "[$model->id]miniform");?>
                <?php echo 'В краткой форме ЛК магазина'; ?>
                <?php echo CHtml::error($model, "[$model->id]miniform");?>
                <br />
                <?php echo CHtml::activeCheckBox($model, "[$model->id]hide");?>
                <?php echo 'Убрать из формы добавления'; ?>
                <?php echo CHtml::error($model, "[$model->id]hide");?>
        </td>
        <td>
                <?php echo CHtml::button('Удалить', array('class'=>'btn category-option-delete', 'option'=>$model->id, 'ignore_warn'=>'')); ?>
        </td>
<?php echo CHtml::closeTag('tr'); ?>
