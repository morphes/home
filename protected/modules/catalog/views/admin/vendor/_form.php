<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'vendor-form',
	'enableAjaxValidation'=>false,
        'htmlOptions'=>array('enctype'=>'multipart/form-data'),
)); ?>

	<?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>255)); ?>

	<?php echo $form->textAreaRow($model,'desc',array('class'=>'span8','maxlength'=>3000, 'style'=>'height: 150px;')); ?>

        <div class="clearfix" id="city">
                <?php echo CHtml::label($model->getAttributeLabel('city_id'), 'City_id'); ?>
            <div class="input">
                    <?php
                    $city = City::model()->findByPk($model->city_id);
                    $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
                            'name'=>'City_id',
                            'value'=> !is_null($city) ? "{$city->name} ({$city->region->name}, {$city->country->name})" : '',
                            'sourceUrl'=>'/utility/autocompletecity',
                            'options'=>array(
                                    'minLength'=>'3',
                                    'showAnim'=>'fold',
                                    'select'=>'js:function(event, ui) {$("#Vendor_city_id").val(ui.item.id).keyup();$("#Vendor_country_id").val(ui.item.country_id);$("#Country_id").val("");}',
                                    'change'=>'js:function(event, ui) {if(ui.item === null) {$("#Vendor_city_id").val("");$("#Vendor_country_id").val("");$("#Country_id").val("");}}',
                            ),
                    ));
                    ?>
                    <?php echo CHtml::error($model, "city_id");?>
            </div>
        </div>

        <div class="clearfix" id="country">
            <?php echo CHtml::label($model->getAttributeLabel('country_id'), 'Country_id'); ?>
            <div class="input">
                    <?php
                    $country = is_null($city) ? Country::model()->findByPk($model->country_id) : null;
                    $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
                            'name'=>'Country_id',
                            'value'=> !is_null($country) ? "{$country->name}" : '',
                            'sourceUrl'=>$this->createUrl('autocompleteCountry'),
                            'options'=>array(
                                    'minLength'=>'1',
                                    'showAnim'=>'fold',
                                    'select'=>'js:function(event, ui) {$("#Vendor_country_id").val(ui.item.id).keyup();$("#Vendor_city_id").val("");$("#City_id").val("");}',
                                    'change'=>'js:function(event, ui) {if(ui.item === null) {$("#Vendor_country_id").val("");$("#Vendor_city_id").val("");$("#City_id").val("");}}',
                            ),
                    ));
                    ?>
                    <?php echo CHtml::error($model, "city_id");?>
            </div>
        </div>

        <?php echo CHtml::activeHiddenField($model,  "city_id");?>
        <?php echo CHtml::activeHiddenField($model,  "country_id");?>


        <?php echo $form->fileFieldRow($model, 'image',array('class'=>'span5'));?>

        <?php if($model->uploadedFile) :?>
                <div class="clearfix">
                        <div class="input">
                                <?php echo CHtml::image('/' . $model->uploadedFile->getPreviewName(Config::$preview['resize_190'])); ?>
                        </div>
                </div>
        <?php endif; ?>

        <?php echo $form->textFieldRow($model,'site',array('class'=>'span5','maxlength'=>255)); ?>

        <hr />

        <h3>Коллекции производителя</h3>

        <div class="clearfix">
                <label>Название</label>
                <div class="input">
                        <?php echo CHtml::textField('coll_name', '', array('id'=>'coll_name')); ?>
                        <?php echo CHtml::button('Добавить', array('id'=>'coll_button_create')); ?>

                        <ul id="coll_list" style="margin-top: 10px;">
                                <?php
				$colls = $model->getCollectionsArray();
				for ($i = 0, $ci = count($colls); $i < $ci; $i++)
				{
					$collection = $colls[$i];
					$this->renderPartial('_collectionRow', array(
						'coll_id'      => $collection['id'],
						'coll_name'    => $collection['name'],
					));
				}
                                ?>
                        </ul>
                </div>
        </div>


	<div class="actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>


<?php Yii::app()->clientScript->registerScript('collections', '

        var vid = "' . $model->id . '";
        var timer;

        disableUpDown = function (){
        	var $list = $("#coll_list");
        	$list.find(".coll_up, .coll_down").removeClass("disabled");

		$list.find("li:first .coll_up").addClass("disabled");
		$list.find("li:last .coll_down").addClass("disabled");
	};

	setLoader = function(objLi){
		objLi.css("list-style-image", "url(/img/load.gif)");
	}

	clearLoader = function(objLi){
		objLi.css("list-style-image", "");
	}

	// Отключаем крайние кнопки для сдвига
	disableUpDown();

        $("#coll_button_create").click(function(){
                $coll_name = $("#coll_name");
                var name = $coll_name.val();
                $coll_name.val("");
                $.post("/catalog/admin/vendor/createCollection", { vid: vid, coll_name: name }, function(response){
                        response = $.parseJSON(response);
                        if(response.success)
                                $("#coll_list").append(response.html);
                        else
                                alert(response.message);

                        disableUpDown();
                });

        });

        $(".coll_link_delete").live("click", function(){
        	if (confirm("Удалить?")) {
			var coll_id = $(this).attr("coll_id");
			$.post("/catalog/admin/vendor/deleteCollection", { vid: vid, coll_id: coll_id }, function(response){
				response = $.parseJSON(response);
				if(response.success)
					$("#coll_row_"+coll_id).remove();
				else
					alert(response.message);

				disableUpDown();
			});
        	}

        });

        $(".coll_input_name").live("keyup", function(){
        	var $this = $(this);
                var coll_id = $this.attr("coll_id");
                var name = $this.val();

                window.clearTimeout(timer);
                timer = window.setTimeout(function(){
                	setLoader($this.parents("li"));

                        $.post("/catalog/admin/vendor/updateCollection", { vid: vid, coll_id: coll_id, coll_name: name }, function(response){
                                response = $.parseJSON(response);
                                if(!response.success)
                                        alert(response.message);

        			clearLoader($this.parents("li"));
                        });
                },500);
        });

        $(".coll_up:not(.disabled)").live("click", function(){
        	var $li = $(this).parents("li");
        	var coll_id = $li.data("coll_id");

		setLoader($li);
		setLoader($li.prev("li"));

        	$.post("/catalog/admin/vendor/changePosCollection", {vid: vid, coll_id: coll_id, direct: "up"}, function(response){
        		if (response) {
				$li.prev("li").before($li);
				disableUpDown();
        		}

        		clearLoader($li);
        		clearLoader($li.next("li"));
        	}, "json");
        });

	$(".coll_down:not(.disabled)").live("click", function(){
        	var $li = $(this).parents("li");
        	var coll_id = $li.data("coll_id");

		setLoader($li);
		setLoader($li.next("li"));

        	$.post("/catalog/admin/vendor/changePosCollection", {vid: vid, coll_id: coll_id, direct: "down"}, function(response){
        		if (response) {
				$li.next("li").after($li);
				disableUpDown();
        		}

        		clearLoader($li);
        		clearLoader($li.prev("li"));
        	}, "json");
        });


', CClientScript::POS_READY); ?>
