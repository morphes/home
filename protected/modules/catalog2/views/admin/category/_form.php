<style>
        .error, .errorMessage {
                border-color: #EE5F5B;
                color: #B94A48;
        }
        .group_delete {
                cursor: pointer;
        }
</style>

<?php
        /**
         * Для автокомплита ключей
         */
        $cs = Yii::app()->clientScript;
        $cssCoreUrl = $cs->getCoreScriptUrl();
        $cs->registerCssFile($cssCoreUrl . '/jui/css/base/jquery-ui.css');
        $cs->registerCoreScript('jquery.ui');
?>

<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'category-form',
	'enableAjaxValidation'=>false,
)); ?>

        <div id="category-base-options">
                <div class="clearfix">
                        <label for="Parent_cat">Родительская категория</label>
                        <div class="input">
                                <input id="Parent_cat" class="span5" type="text" name="Parentcat" maxlength="255" value="<?php echo ($root->name == 'root') ? 'нет' : $root->name; ?>" disabled="disabled">
                        </div>
                </div>

                <?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>255)); ?>

                <?php echo $form->textFieldRow($model,'eng_name',array('class'=>'span5','maxlength'=>255)); ?>

                <?php echo $form->textFieldRow($model,'genitiveCase',array('class'=>'span5','maxlength'=>255)); ?>

                <?php echo $form->textFieldRow($model,'accusativeCase',array('class'=>'span5','maxlength'=>255)); ?>

                <?php echo $form->textAreaRow($model,'desc',array('class'=>'span5','maxlength'=>255)); ?>

		<?php echo $form->textAreaRow($model,'seo_top_desc',array('class'=>'span10', 'maxlength'=>3000,'style' => 'height:300px;')); ?>

		<?php echo $form->textAreaRow($model,'seo_bottom_desc',array('class'=>'span10', 'maxlength'=>3000,'style' => 'height:300px;')); ?>

		<?php echo $form->dropDownListRow($model,'status', Category::$statusLabels,array('class'=>'span5')); ?>

		<?php echo $form->dropDownListRow($model,'image_format', Category::$imageFormats, array('class'=>'span5')); ?>
        </div>

<?php if($model->isNewRecord) : ?>

        <div class="actions">
                <?php echo CHtml::submitButton('Создать', array('class'=>'btn primary')); ?>
        </div>

        <?php $this->endWidget(); ?>

<?php else : ?>

        <?php echo CHtml::hiddenField('cid', $model->id, array('id'=>'cid'))?>

        <div class="category-groups">
                <div class="clearfix">
                        <label for="groups">Группы</label>
                        <div class="input">
                                <?php echo CHtml::textField('group_name', 'Новая группа', array('id'=>'group_name')); ?>
                                <?php echo CHtml::button('Добавить', array('class'=>'btn group-add')); ?>
                                <?php echo CHtml::button('Применить настройки групп', array('class'=>'btn primary refresh', 'style'=>'margin-bottom:10px;')); ?>
                                <ul id="group-list">
                                        <?php if(!empty($model->groups)) : ?>
                                                <?php foreach(unserialize($model->groups) as $gid=>$gname) : ?>
                                                        <li>
                                                                <?php echo CHtml::textField("[$gid]group", $gname, array('disabled'=>true)); ?>
                                                                <?php echo CHtml::tag('span', array("class"=>"group_delete", "gid"=>$gid), 'удал.'); ?>
                                                        </li>
                                                <?php endforeach; ?>
                                        <?php endif; ?>
                                </ul>
                        </div>
                </div>
        </div>

        <div id="category-ext-options" class="grid-view">
                <div>
                        <?php echo CHtml::button('Новая опция',array('class'=>'btn', 'id'=>'category-option-add', 'style'=>'float:right;')); ?>
                </div>
                <div style="clear:both;"></div>

                <table class="condensed-table" style="margin-top: 10px;">
                        <thead>
                        <tr>
                                <th class="header" id="category-option-key" width="115px">Ключ</th>
                                <th class="header" id="category-option-type" width="155px">Тип</th>
                                <th class="header" id="category-option-group" width="155px">Группа</th>
                                <th class="header" id="category-option-name" width="155px">Наименование</th>
                                <th class="header" id="category-option-values" width="250px">Значения</th>
                                <th class="header" id="category-option-params" width="160px">Параметры</th>
                                <th class="header" id="category-option-desc" width="250px">Отображение</th>
                               <!-- <th class="header" id="category-option-filterconfig" width="140px">Фильтр</th>
                                <th class="header" id="category-option-minicard" width="100px">В краткой карте</th>
                                <th class="header" id="category-option-required" width="100px">Обязательное</th>
                                <th class="header" id="category-option-miniform" width="100px">В краткой форме ЛК</th>-->
                                <th class="header" id="category-option-delete" width="100px"></th>
                        </tr>
                        </thead>
                        <tbody>
                                <?php foreach($model->options as $option) :?>
                                        <?php isset($errors['Option'][$option->id]) ? $option->addErrors($errors['Option'][$option->id]) : false; ?>
                                        <?php $this->renderPartial('_optionRow', array('model'=>$option, 'errors'=>$errors, 'groups'=>$model->groupsArray)); ?>
                                <?php endforeach; ?>
                        </tbody>
                </table>
        </div>




        <div class="actions">
                <?php echo CHtml::button('Сохранить', array('class'=>'btn primary', 'onclick'=>'$("#category-form").submit(); return false;')); ?>
                <?php echo CHtml::button('К списку категорий', array('class'=>'btn','onclick'=>'document.location = \''.$this->createUrl('index', array('cid'=>$root->id)).'\''))?>
        </div>

        <?php $this->endWidget(); ?>

        <?php Yii::app()->clientScript->registerScript('option-add', '
                $("#category-option-add").live("click", function(){
                        $.ajax({
                                url: "' . $this->createUrl('optionCreate', array('category_id'=>$model->id)) . '",
                                dataType: "json",
                                async: false,
                                success: function(data) {
                                        if(data.result) {
                                                $(".condensed-table tbody").append(data.html);
                                        }
                                }
                        });
                });

                $(".values-add").live("click", function(){
                        var option_id = $(this).attr("option");
                        var value = $("#category-option-value-input-" + option_id).val();
                        $.ajax({
                                url: "/catalog2/admin/category/optionValueCreate",
                                data: {option_id: option_id, val: value},
                                type: "get",
                                dataType: "json",
                                async: false,
                                success: function(data) {
                                        if(data.result) {
                                                $("#category-option-values-"+option_id).append(data.html);
                                                $("#category-option-value-input-"+ option_id).val("");
                                        }
                                }
                        });
                });

                $(".type-switch").live("change", function(){
                        $this = $(this);
                        var option_id = $this.attr("option");
                        var ignore_warn = $this.attr("ignore_warn");
                        $.ajax({
                                url: "/catalog2/admin/category/getTypeParams",
                                data: {type: $(this).val(), option_id: option_id, ignore_warn: ignore_warn},
                                type: "get",
                                dataType: "json",
                                async: false,
                                success: function(data) {
                                        if(data.result) {
                                                if(!data.params.valueList) {
                                                        $("#category-option-value-addform-"+option_id).css("display", "none");
                                                } else {
                                                        $("#category-option-value-addform-"+option_id).css("display", "block");
                                                }
                                                $("#category-option-values-"+option_id).html("");
                                                $("#option_"+option_id+"_params_form").html(data.paramsFormHtml);
                                        } else {
                                                if (confirm(data.message)) {
                                                        $this.attr("ignore_warn", "true");
                                                        $this.change();
                                                } else {
                                                        $("#Option_"+option_id+"_type_id").val(data.old_value);
                                                }
                                        }
                                }
                        });
                });

                $(".category-option-delete").live("click", function(){
                        $this = $(this);
                        var option_id = $this.attr("option");
                        var ignore_warn = $this.attr("ignore_warn");
                        $.ajax({
                                url: "/catalog2/admin/category/optionDelete",
                                data: {option_id: option_id, ignore_warn: ignore_warn},
                                type: "get",
                                dataType: "json",
                                async: false,
                                success: function(data) {
                                        if(data.result) {
                                                $("#category-option-"+option_id).remove();
                                        } else {
                                                if (confirm(data.message)) {
                                                        $this.attr("ignore_warn", "true");
                                                        $this.click();
                                                }
                                        }
                                }
                        });
                });

                $(".category-value-delete").live("click", function(){
                        $this = $(this);
                        var value_id = $this.attr("value");
                        $.ajax({
                                url: "/catalog2/admin/category/valueDelete",
                                data: {value_id: value_id},
                                type: "get",
                                dataType: "json",
                                async: false,
                                success: function(data) {
                                        if(data.result) {
                                                $("#category-option-value-"+value_id).remove();
                                        }
                                }
                        });
                });

                $(".category-option-value-input").live("keyup", function(e) {
                        if(e.keyCode == 13){
                                var oid = $(this).attr("oid");
                                $("#values-add-"+oid).click();
                        }
                });

                $(".group-add").click(function(){
                        var cid = $("#cid").val();
                        var gname = $("#group_name").val();
                        $.ajax({
                                url: "/catalog2/admin/category/groupCreate",
                                data: {category_id: cid, group_name:gname},
                                dataType: "json",
                                success: function(data) {
                                        if(data.success)
                                                $("#group-list").append(data.html);
                                }
                        });
                });

                $(".group_delete").live("click", function(){
                        var cid = $("#cid").val();
                        var gid = $(this).attr("gid");
                        var $this = $(this);
                        $.ajax({
                                url: "/catalog2/admin/category/groupDelete",
                                data: {category_id: cid, gid:gid},
                                dataType: "json",
                                success: function(data) {
                                        if(data.success)
                                                $this.parent().remove();
                                }
                        });
                });

                $(".filterable-option input:checkbox").live("change", function(){

                        var $this = $(this);
                        var td = $this.parent("td");
                        var type_id = td.siblings(".option-type_id-form").find(".type-switch option:selected").val();

                        // only for SizeOption
                        if(type_id == 9) {
                                var cid = $("#cid").val();
                                var oid = td.attr("oid");
                                $.ajax({
                                        url: "/catalog2/admin/category/sizeOptionFilterCheck",
                                        data: {cid: cid, oid:oid},
                                        dataType: "json",
                                        success: function(data) {
                                                if(!data.success) {
                                                        $this.attr("checked", false);
                                                        alert(data.message);
                                                }
                                        }
                                });
                        }
                });

                $(".refresh").click(function(){
                        if (confirm("Применение изменений в списке групп приведет к потере несохраненных настроек опций. Продолжить?"))
                                location.reload();
                });

        ', CClientScript::POS_READY); ?>

        <?php Yii::app()->clientScript->registerScript('disableChildOptions', '
                $(".parentOption").find("textarea, input:not(.category-option-delete), checkbox").attr("disabled", "disabled");
        ', CClientScript::POS_LOAD)?>

<?php endif; ?>