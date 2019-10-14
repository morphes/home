<style>
        #store-working-time .input label {float: none;}
        #store-working-time .input input {width: 70px;}
	.deleteGeo {color: blue; cursor: pointer;}
</style>

<?php
/**
 * Для автокомплитов
 */
$cs = Yii::app()->clientScript;
$cssCoreUrl = $cs->getCoreScriptUrl();
$cs->registerCssFile($cssCoreUrl . '/jui/css/base/jquery-ui.css');
$cs->registerCoreScript('jquery.ui');
?>

<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'store-form',
	'enableAjaxValidation'=>false,
        'htmlOptions'=>array(
                'enctype'=>'multipart/form-data',
        ),
)); ?>

	<?php echo $form->errorSummary($model); ?>

	<?php echo $form->dropDownListRow($model, 'type', Store::$types, array('class'=>'span5')); ?>

        <?php echo $form->fileFieldRow($model,'logo',array('class'=>'span5')); ?>

        <?php echo $form->hiddenField($model, 'id', array('id'=>'model_id')); ?>

        <?php if($model->uploadedFile) :?>
        <div class="clearfix">
                <div class="input">
                        <?php echo CHtml::image('/' . $model->uploadedFile->getPreviewName(Config::$preview['resize_190']), '',array('class'=>'storeImage')); ?>
                        <br>
                        <?php echo CHtml::button('удалить', array('id'=>'deleteImage', 'class'=>'storeImage')); ?>
                </div>
        </div>
        <?php endif; ?>

        <?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>255)); ?>

        <?php echo $form->dropDownListRow($model, 'tariff_id', Store::$tariffs, array('class'=>'span5')); ?>


	<?php echo $form->dropDownListRow($model, 'tariff_id_new', array('' => '')+Store::$tariffs, array('class'=>'span5')); ?>

	<div class="clearfix">
		<?php echo CHtml::activeLabel($model, 'tariff_enable');?>
		<div class="input">
			<?php $this->widget('zii.widgets.jui.CJuiDatePicker',array(
				'model'=>$model,
				'attribute'=>'tariff_enable',
				'options'=>array('dateFormat'=>'dd.mm.yy'),
				'language'=>'ru',
				'htmlOptions'=>array(
					'style'=>'height:20px;'
				),
			));?>
		</div>
	</div>

	<div class="clearfix">
            <?php echo CHtml::activeLabel($model, 'tariff_expire');?>
                <div class="input">
                        <?php $this->widget('zii.widgets.jui.CJuiDatePicker',array(
                                'model'=>$model,
                                'attribute'=>'tariff_expire',
                                'options'=>array('dateFormat'=>'dd.mm.yy'),
                                'language'=>'ru',
                                'htmlOptions'=>array(
                                        'style'=>'height:20px;'
                                ),
                        ));?>
                </div>
        </div>

<div class="well">

	<h3>Параметры для тарифа «Минисайт»</h3>

	<?php echo $form->textFieldRow(
		$model->subdomain,
		'domain',
		array(
			'class' => 'span5',
			'hint'  => 'Сохраняется только при тарифе «Минисайт»'
		)
	); ?>

	<div class="clearfix">
		<label><?php echo Store::model()->getAttributeLabel('head_image_id');?></label>
		<div class="input">
			<?php $this->widget('ext.FileUpload.FileUpload', array(
				'url'         => $this->createUrl('headImageUpload'),
				'postParams'  => array('sid' => $model->id),
				'config'      => array(
					'fileName'   => 'Store[headImage]',
					'onSuccess'  => 'js:function(response){
							if (response.success) {
								$("#add-image").html(response.html);
								$(".photo_hint").hide();
							} else {

								$(".uploaded_image_error").html(response.message).show();
							}
						}',
					'onStart'    => 'js:function(data){
							$(".uploaded_image_error").hide();
							$(".product_image").addClass("disabled");
						}',
					'onFinished' => 'js:function(data){
							$(".product_image").removeClass("disabled");
					}'
				),
				'htmlOptions' => array('accept' => 'image', 'class' => 'photofile_input'),
			)); ?>

			<div id="add-image">
				<?php
				$file = UploadedFile::model()->findByPk($model->head_image_id);
				if ($file) {
					echo $this->renderPartial('_head_image', array('file' => $file));
				}
				?>
			</div>
			<span class="uploaded_image_error hide label important"></span>

			<script>
				// Удаление загруженной фотографии для шапки Минисайта
				$('#add-image').on('click', '.head_image_delete', function(){
					$.ajax({
						url: '/catalog2/admin/store/headImageDelete',
						data: {
							sid: '<?php echo $model->id;?>',
							fid: $('.uploaded_photo').attr('id')
						},
						dataType: 'json',
						method: 'POST',
						success: function(data){
							if (!data.success) {
								alert(data.message);
							} else {
								$('#add-image').empty();
							}
						},
						error: function(data){
							alert(data.responseText + '('+data.responseStatus+')');
						}

					});

					return false;
				});
			</script>

		</div>
	</div>

</div>

        <div class="clearfix">
                <?php echo CHtml::label('ID администратора', 'admin_id'); ?>
                <div class="input">
                        <?php echo CHtml::activeTextField($model, 'admin_id'); ?>
                        <span id='admin-name'>
                            <?php
                                if($model->admin)
                                        echo "{$model->admin->name}, {$model->admin->login}";
                            ?>
                        </span>
                </div>
        </div>

	<?php echo $form->textFieldRow($model,'email',array('class'=>'span5','maxlength'=>255)); ?>

    <?php echo $form->textFieldRow($model,'anchor',array('class'=>'span5','maxlength'=>1000)); ?>

	<?php echo $form->textFieldRow($model,'site',array('class'=>'span5','maxlength'=>255)); ?>

	<?php echo $form->textAreaRow($model,'phone',array('class'=>'span5','maxlength'=>255)); ?>

	<?php echo $form->textAreaRow($model,'about',array('class'=>'span5','maxlength'=>3000)); ?>

    <?php echo $form->textFieldRow($model,'xml_url',array('class'=>'span5','maxlength'=>1000, 'placeholder' => 'http://')); ?>

	<?php echo $form->dropDownListRow($model, 'xml_parser_id', ['' => ''] + XmlParser::$parsers, array('class'=>'span5')); ?>

    <?php if ($model->type == Store::TYPE_ONLINE) : ?>
		<div class="clearfix">
			<?php echo CHtml::label('География охвата', "online_store_geo"); ?>
			<div class="input">
				<?php echo CHtml::dropDownList('store_geo_type', null, StoreGeo::$types+array('cng'=>'СНГ'), array('class'=>'span2')); ?>
				<?php echo CHtml::hiddenField('store_geo_id', null); ?>

				<?php // Автокомлит по городу ?>
				<?php $this->widget('application.components.widgets.CAjaxAutoComplete', array(
					'name'=>'geo_city_id',
					'value'=> '',
					'sourceUrl'=>'/utility/autocompletecity',
					'options'=>array(
						'minLength'=>'2',
						'showAnim'=>'fold',
						'select'=>'js:function(event, ui) {$("#store_geo_id").val(ui.item.id).trigger("change"); $(this).val(""); return false;}',
					),
					'htmlOptions'=>array('id'=>'geo_autocomplete_' . StoreGeo::TYPE_CITY, 'class'=>'geo_autocomplete', 'placeholder'=>'Название города')
				));?>

				<?php // Автокомлит по стране ?>
				<?php $this->widget('application.components.widgets.CAjaxAutoComplete', array(
					'name'=>'geo_region_id',
					'value'=> '',
					'sourceUrl'=>'/utility/autocompleteregion',
					'options'=>array(
						'minLength'=>'2',
						'showAnim'=>'fold',
						'select'=>'js:function(event, ui) {$("#store_geo_id").val(ui.item.id).trigger("change"); $(this).val(""); return false;}',
					),
					'htmlOptions'=>array('id'=>'geo_autocomplete_' . StoreGeo::TYPE_REGION, 'class'=>'geo_autocomplete', 'placeholder'=>'Название региона', 'style'=>'display:none;')
				));?>

				<?php // Автокомлит по стране ?>
				<?php $this->widget('application.components.widgets.CAjaxAutoComplete', array(
					'name'=>'geo_country_id',
					'value'=> '',
					'sourceUrl'=>'/utility/autocompletecountry',
					'options'=>array(
						'minLength'=>'2',
						'showAnim'=>'fold',
						'select'=>'js:function(event, ui) {$("#store_geo_id").val(ui.item.id).trigger("change"); $(this).val(""); return false;}',
					),
					'htmlOptions'=>array('id'=>'geo_autocomplete_' . StoreGeo::TYPE_COUNTRY, 'class'=>'geo_autocomplete', 'placeholder'=>'Название страны', 'style'=>'display:none;')
				));?>

				<ul id="online_store_geo_saved">
					<?php foreach($model->getGeos() as $geo) : ?>
						<?php $this->renderPartial('_geoForm', array('geo'=>$geo)); ?>
					<?php endforeach; ?>
				</ul>

				<?php Yii::app()->getClientScript()->registerScript('geo', '
					// выбор типа добавляемой географии охвата
					$("#store_geo_type").change(function(){
						var selected_geo_type = $(this).val();
						if (selected_geo_type=="cng") {
							if (confirm("Добавить все страны СНГ?")) {
								$.ajax({
									url: "/catalog2/admin/store/ajaxSetCountries",
									type: "POST",
									async: false,
									dataType: "json",
									data: {store_id : $("#model_id").val()},
									success: function(response){
										if (!response.success) {
											alert(response.error);
											return false;
										}
										$("#online_store_geo_saved").append(response.html);
									},
									error: function(response){alert("Неизвестная ошибка");},
								});
							}
						} else {
							$(".geo_autocomplete").hide();
							$("#geo_autocomplete_" + selected_geo_type).show();
						}
						return false;
					});

					$("#store_geo_id").change(function(){

						var data = {
							geo_type : $("#store_geo_type").val(),
							geo_id : $("#store_geo_id").val(),
							store_id : $("#model_id").val(),
						};

						$.ajax({
							dataType: "json",
							data: data,
							type: "post",
							url: "/catalog2/admin/store/ajaxCreateStoreGeo",
							success: function(response){
								if (!response.success) {
									alert(response.error);
									return false;
								}

								$("#online_store_geo_saved").append(response.html);
							},
							error: function(response){alert("Неизвестная ошибка");},
						});

						return false;
					});

					$("#online_store_geo_saved").on("click", ".deleteGeo", function(){
						var $this = $(this);
						var data = {
							geo_type : $this.data("geo_type"),
							geo_id : $this.data("geo_id"),
							store_id : $("#model_id").val(),
						};
						$.ajax({
							dataType: "json",
							data: data,
							type: "post",
							url: "/catalog2/admin/store/ajaxDeleteStoreGeo",
							success: function(response){
								if (!response.success) {
									alert(response.error);
									return false;
								}

								$this.parent().remove();
							},
							error: function(response){alert("Неизвестная ошибка");},
						});
						return false;
					});
				', CClientScript::POS_READY); ?>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( $model->type == Store::TYPE_OFFLINE ) : ?>

		<?php $errorCls = ($model->getError('city_id')) ? 'error' : '';?>
		<div class="clearfix <?php echo $errorCls;?>">
			<?php echo CHtml::label($model->getAttributeLabel('city_id').' <span class="required">*</span>', 'City_id'); ?>
			<div class="input">
				<?php

				$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
					'name'        => 'City_id',
					'value'       => ($city instanceof City)
						         ? "{$city->name} ({$city->region->name}, {$city->country->name})"
						         : '',
					'sourceUrl'   => '/utility/autocompletecity',
					'options'     => array(
						'minLength' => '3',
						'showAnim'  => 'fold',
						'select'    => 'js:function(event, ui) {$("#StoreGeo_geo_id").val(ui.item.id).keyup();}',
						'change'    => 'js:function(event, ui) {if(ui.item === null) {$("#StoreGeo_geo_id").val("");}}',
					),
					'htmlOptions' => array('class' => $errorCls . ' span5')
				));
				?>
				<?php echo CHtml::hiddenField('StoreGeo[geo_id]', ($city instanceof City) ? $city->id : '',  array('id' => "StoreGeo_geo_id"));?>
				<?php echo ($model->getError('city_id')) ? CHtml::tag('span', array('class' => 'help-inline'), $model->getError('city_id')) : '';?>
			</div>
		</div>

		<?php echo $form->textFieldRow($model,'address',array('class'=>'span5','maxlength'=>1000)); ?>

		<div class="clearfix" id="store-working-time">

			<label>Время работы в будние дни</label>

			<div class="input">
				<?php echo CHtml::label('С', 'weekdays_work_from'); ?>
				<?php $this->widget('CMaskedTextField', array('mask' => '99:99','placeholder' => '_','completed' => 'function(){$("#weekdays_work_to").focus();}',
					'name' => 'weekdays_work_from','value' => $model->timeArray['weekdays']['work_from']));
				?>

				<?php echo CHtml::label('До', 'weekdays_work_to'); ?>
				<?php $this->widget('CMaskedTextField', array('mask' => '99:99','placeholder' => '_','completed' => 'function(){$("#weekdays_dinner_enabled").focus();}',
					'name' => 'weekdays_work_to','value' => $model->timeArray['weekdays']['work_to'],));
				?>

				<?php echo CHtml::label('&nbsp;Обед', 'weekdays_dinner_enabled'); ?>
				<?php echo CHtml::checkBox('weekdays_dinner_enabled', $model->timeArray['weekdays']['dinner_enabled']);?>

				<span id="weekdays_dinner" style="display: <?php echo $model->timeArray['weekdays']['dinner_enabled'] == true ? 'inline' : 'none'; ?>">
					<?php echo CHtml::label('С', 'weekdays_dinner_from'); ?>
					<?php $this->widget('CMaskedTextField', array('mask' => '99:99','placeholder' => '_','completed' => 'function(){$("#weekdays_dinner_to").focus();}',
						'name' => 'weekdays_dinner_from','value' => $model->timeArray['weekdays']['dinner_from']));
					?>

					<?php echo CHtml::label('По', 'weekdays_dinner_to'); ?>
					<?php $this->widget('CMaskedTextField', array('mask' => '99:99','placeholder' => '_','completed' => 'function(){$("#weekend_work_from").focus();}',
						'name' => 'weekdays_dinner_to','value' => $model->timeArray['weekdays']['dinner_to']));
					?>
				</span>
			</div>

			<br /><br />

			<label>Время работы в субботу</label>

			<div class="input">
				<?php echo CHtml::label('С', 'weekdays_work_from'); ?>
				<?php $this->widget('CMaskedTextField', array('mask' => '99:99','placeholder' => '_','completed' => 'function(){$("#saturday_work_to").focus();}',
					'name' => 'saturday_work_from','value' => $model->timeArray['saturday']['work_from']));
				?>

				<?php echo CHtml::label('До', 'weekdays_work_to'); ?>
				<?php $this->widget('CMaskedTextField', array('mask' => '99:99','placeholder' => '_','completed' => 'function(){$("#saturday_dinner_enabled").focus();}',
					'name' => 'saturday_work_to','value' => $model->timeArray['saturday']['work_to']));
				?>

				<?php echo CHtml::label('&nbsp;Обед', 'saturday_dinner_enabled'); ?>
				<?php echo CHtml::checkBox('saturday_dinner_enabled', $model->timeArray['saturday']['dinner_enabled']);?>

				<span id="saturday_dinner" style="display: <?php echo $model->timeArray['saturday']['dinner_enabled'] == true ? 'inline' : 'none'; ?>">
					<?php echo CHtml::label('С', 'weekdays_dinner_from'); ?>
					<?php $this->widget('CMaskedTextField', array('mask' => '99:99','placeholder' => '_','completed' => 'function(){$("#saturday_dinner_to").focus();}',
						'name' => 'saturday_dinner_from','value' => $model->timeArray['saturday']['dinner_from']));
					?>

					<?php echo CHtml::label('По', 'weekdays_dinner_to'); ?>
					<?php $this->widget('CMaskedTextField', array('mask' => '99:99','placeholder' => '_','completed' => 'function(){$("#Store_phone").focus();}',
						'name' => 'saturday_dinner_to','value' => $model->timeArray['saturday']['dinner_to']));
					?>
				</span>
			</div>

			<br /><br />

			<label>Время работы в воскресенье</label>

			<div class="input">
				<?php echo CHtml::label('С', 'weekdays_work_from'); ?>
				<?php $this->widget('CMaskedTextField', array('mask' => '99:99','placeholder' => '_','completed' => 'function(){$("#sunday_work_to").focus();}',
					'name' => 'sunday_work_from','value' => $model->timeArray['sunday']['work_from']));
				?>

				<?php echo CHtml::label('До', 'weekdays_work_to'); ?>
				<?php $this->widget('CMaskedTextField', array('mask' => '99:99','placeholder' => '_','completed' => 'function(){$("#sunday_dinner_enabled").focus();}',
					'name' => 'sunday_work_to','value' => $model->timeArray['sunday']['work_to']));
				?>

				<?php echo CHtml::label('&nbsp;Обед', 'sunday_dinner_enabled'); ?>
				<?php echo CHtml::checkBox('sunday_dinner_enabled', $model->timeArray['sunday']['dinner_enabled']);?>

				<span id="sunday_dinner" style="display: <?php echo $model->timeArray['sunday']['dinner_enabled'] == true ? 'inline' : 'none'; ?>">
					<?php echo CHtml::label('С', 'weekdays_dinner_from'); ?>
					<?php $this->widget('CMaskedTextField', array('mask' => '99:99','placeholder' => '_','completed' => 'function(){$("#sunday_dinner_to").focus();}',
						'name' => 'sunday_dinner_from','value' => $model->timeArray['sunday']['dinner_from']));
					?>

					<?php echo CHtml::label('По', 'weekdays_dinner_to'); ?>
					<?php $this->widget('CMaskedTextField', array('mask' => '99:99','placeholder' => '_','completed' => 'function(){$("#Store_phone").focus();}',
						'name' => 'sunday_dinner_to','value' => $model->timeArray['sunday']['dinner_to']));
					?>
				</span>
			</div>
		</div>

		<h3>Торговые центры</h3>

		<?php echo $form->checkboxListRow($model, 'mall_build_id',  CHtml::listData(MallBuild::model()->findAll(), 'id', 'name'), array('class' => 'span1 select_mall')); ?>
		<?php echo $form->dropDownListRow($model, 'floor_id', CHtml::listData(MallFloor::model()->findAllByAttributes(array('mall_build_id' => $model->mall_build_id)), 'id', 'name'), array('disabled' => 'disabled', 'class' => 'floors')); ?>
		<?php echo $form->textfieldRow($model, 'sect_name', array('disabled' => 'disabled', 'class' => 'sect_name')); ?>

		<script type="text/javascript">

			if ($('.select_mall').is(':checked')) {
				$('.floors').removeAttr('disabled');
				$('.sect_name').removeAttr('disabled');
			}


			$('.select_mall').click(function(){
				// ID Торгового Центра
				var build_id = $(this).val();

				if ($(this).is(':checked')) {
					// Если выставляем галочку, то нужно получить список этажей
					$.ajax({
						url: '/catalog2/admin/mallBuild/ajaxGetFloorsById/id/'+build_id,
						type: 'POST',
						dataType: 'JSON',
						success: function(data){
							$('.floors').html($(data.html).html()).removeAttr('disabled');
							$('.sect_name').removeAttr('disabled');
						},
						error: function(data){
							alert(data.responseText);
						}
					});
				} else {
					// Если снимаем галочку с ТЦ
					$('.floors').html($('<option>')).attr('disabled', 'disabled');
					$('.sect_name').val('').attr('disabled', 'disabled');
				}
			});
		</script>

	<?php endif; ?>

	<div class="actions">
		<?php echo CHtml::button($model->isNewRecord ? 'Создать' : 'Сохранить',array('class'=>'btn primary', 'id'=>'submit-form')); ?>
                <?php echo CHtml::submitButton($model->isNewRecord ? 'Создать принудительно' : 'Сохранить принудительно',array('class'=>'btn primary', 'id'=>'submit-form-force')); ?>

                <?php
		// Временно коментим удаление магазина. Чтобы позже реализовать
		// удаление из связных таблиц.

		/* if (!$model->isNewRecord) : ?>
		<?php echo CHtml::button('Удалить', array('class'=>'btn danger', 'id'=>'delete')); ?>
		<?php endif;*/ ?>
	</div>

        <?php Yii::app()->clientScript->registerScript('submit-form', '


        	$("#Store_type").change(function(){
        		if (confirm("Вы действительно хотите изменить тип магазина? Все не сохраненные данные формы будут потеряны!"))
        			document.location.href = "' . $changeTypeUrl . '" + "/type/" + $(this).val();
        		else
        			return false;
        	});

                $("#submit-form").click(function(){

                        var data = {
                                name: $("#Store_name").val(),
                                city_id: $("#Store_city_id").val(),
                                address: $("#Store_address").val(),
                                model_id: $("#model_id").val(),
				mall_select: $("input.select_mall").is(":checked")
                        };

                        $.post("/catalog2/admin/store/findDublicates", data, function(response){
                                if(response.success && response.dublicate_id) {
                                        alert("Найден дубликат добавляемого магазина. Id дубликата "+response.dublicate_id);
                                        return false;
                                } else {
                                        $("#store-form").submit();
                                }
                        }, "json");

                });
        ', CClientScript::POS_READY);?>



        <?php if(!$model->isNewRecord) : ?>

                <h3>Производители в магазине</h3>

                <?php echo CHtml::hiddenField('store_id', $model->id); ?>

                <div class="clearfix" style="margin-top: 10px;">
                        <?php echo CHtml::label('Производитель', 'vendor_name'); ?>
                        <div class="input">
                                <?php
                                $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
                                        'name'=>'vendor_name',
                                        'value'=> '',
                                        'sourceUrl'=>'/admin/utility/acVendor',
                                        'options'=>array(
                                                'minLength'=>'1',
                                                'showAnim'=>'fold',
                                                'select'=>'js:function(event, ui) {$("#vendor_id").val(ui.item.id).keyup();}',
                                                'change'=>'js:function(event, ui) {if(ui.item === null) {$("#vendor_id").val("");}}',
                                        ),
                                ));
                                ?>
                                <?php echo CHtml::hiddenField('vendor_id');?>
                                <?php echo CHtml::button('Добавить', array('class'=>'btn', 'id'=>'button-add-vendor')); ?>
                        </div>
                </div>

                <?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
                                'id'=>'vendors-grid',
                                'template'=>'{items}',
                                'dataProvider'=>$model->getVendors(true, false, true),
                                'htmlOptions'=>array('style'=>'padding-top:0px;'),
                                'columns'=>array(
                                        'id',
                                        'name',
                                        array(
                                                'class'=>'CButtonColumn',
                                                'template'=>'{update}{delete}',
                                                'updateButtonUrl'=>'"/catalog2/admin/vendor/update/id/".$data->id',
                                                'deleteButtonUrl'=>'"/catalog2/admin/store/deleteVendor/store_id/'.$model->id.'/vendor_id/".$data->id'
                                        ),
                                ),
                        )); ?>

                <?php Yii::app()->clientScript->registerScript('vendor', '

                        $("#delete").click(function(){
                                if(confirm("Вы хотите удалить магазин?")) {
                                        window.location = "'.$this->createUrl("delete", array("id"=>$model->id)).'"
                                }
                        });

                        $("#deleteImage").click(function(){
                                var sid = $("#store_id").val();
                                if(confirm("Вы хотите удалить изображение?")) {
                                        $.post("/catalog2/admin/store/deleteImage", {store_id: sid}, function(response) {
                                        if(response.success)
                                                $(".storeImage").remove();
                                        else
                                                alert("Ошибка удаления");
                                }, "json");
                                }
                        });

                        $("#button-add-vendor").click(function(){

                                var sid = $("#store_id").val();
                                var vid = $("#vendor_id").val();

                                $.post("/catalog2/admin/store/addVendor", {store_id: sid, vendor_id: vid}, function(response) {
                                        if(response.success)
                                                $.fn.yiiGridView.update("vendors-grid");
                                        else
                                                alert(response.message);
                                }, "json");


                                $("#vendor_id").val("");
                                $("#vendor_name").val("");
                        });


                ', CClientScript::POS_READY);?>

        <?php endif; ?>

        <?php if(!$model->isNewRecord) : ?>

                <h3>Модераторы магазина</h3>

                <div class="clearfix" style="margin-top: 10px;">
                    <div class="clearfix">
                        <?php echo CHtml::label('ID модератора', 'moderator_id'); ?>
                        <div class="input">
                            <?php echo CHtml::textField('moderator_id', ''); ?>
                            <?php echo CHtml::button('Добавить', array('class'=>'btn', 'id'=>'button-add-moderator')); ?>
                        </div>
                    </div>
                </div>

                <?php $this->widget('ext.bootstrap.widgets.BootGridView',array(
                        'id'=>'moderators-grid',
                        'template'=>'{items}',
                        'dataProvider'=>$model->moderators,
                        'htmlOptions'=>array('style'=>'padding-top:0px;'),
                        'columns'=>array(
                                'id',
                                'name',
                                array(
                                        'class'=>'CButtonColumn',
                                        'template'=>'{update}{delete}',
                                        'updateButtonUrl'=>'"/admin/user/update/id/".$data->id',
                                        'deleteButtonUrl'=>'"/catalog2/admin/store/deleteModerator/store_id/'.$model->id.'/moderator_id/".$data->id'
                                ),
                        ),
                )); ?>

                <?php Yii::app()->clientScript->registerScript('moderator', '
                        $("#button-add-moderator").click(function(){
                                var sid = $("#store_id").val();
                                var mid = $("#moderator_id").val();
                                $.post("/catalog2/admin/store/addModerator", {store_id: sid, moderator_id: mid}, function(response) {
                                        if(response.success)
                                                $.fn.yiiGridView.update("moderators-grid");
                                        else
                                                alert(response.message);
                                }, "json");
                                $("#moderator_id").val("");
                        });
                ', CClientScript::POS_READY);?>

        <?php endif; ?>
<?php $this->endWidget(); ?>


<?php Yii::app()->clientScript->registerScript('working-time', '
        $("#weekdays_dinner_enabled").click(function(){
                if ($(this).is(":checked"))
                        $("#weekdays_dinner").show();
                else
                        $("#weekdays_dinner").hide();
        });
        $("#saturday_dinner_enabled").click(function(){
                if ($(this).is(":checked"))
                        $("#saturday_dinner").show();
                else
                        $("#saturday_dinner").hide();
        });
        $("#sunday_dinner_enabled").click(function(){
                if ($(this).is(":checked"))
                        $("#sunday_dinner").show();
                else
                        $("#sunday_dinner").hide();
        });

         $("#Store_admin_id").change(function(){
                 var $this = $(this);

                 if($this.val() == "") {
                        $this.val("");
                        $("#admin-name").html("");
                        return false;
                 }

                 $.post("/catalog2/admin/store/checkAdmin", {admin_id: $this.val()}, function(response) {
                        if(response.success) {
                                $("#admin-name").html(response.html);
                        } else {
                                $this.val("");
                                $("#admin-name").html("");
                                alert(response.message);
                        }
                }, "json");
        });
', CClientScript::POS_READY);?>