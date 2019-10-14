<style type="text/css">
	#MallBuild_servicesIds label {
		float: none;
	}
	.floor a.close:hover{
		cursor: pointer;
	}


	.loading {
		color: black;
		display: inline-block;
		height: 100%;
		background-color: #777;
		border-radius: 3px;
		box-shadow: 0 1px 0 rgba(255, 255, 255, .5) inset;
		transition: width .4s ease-in-out;

		background-size: 30px 30px;
		background-image: -webkit-gradient(linear, left top, right bottom,
		color-stop(.25, rgba(255, 255, 255, .15)), color-stop(.25, transparent),
		color-stop(.5, transparent), color-stop(.5, rgba(255, 255, 255, .15)),
		color-stop(.75, rgba(255, 255, 255, .15)), color-stop(.75, transparent),
		to(transparent));
		background-image: -webkit-linear-gradient(135deg, rgba(255, 255, 255, .15) 25%, transparent 25%,
		transparent 50%, rgba(255, 255, 255, .15) 50%, rgba(255, 255, 255, .15) 75%,
		transparent 75%, transparent);
		background-image: -moz-linear-gradient(135deg, rgba(255, 255, 255, .15) 25%, transparent 25%,
		transparent 50%, rgba(255, 255, 255, .15) 50%, rgba(255, 255, 255, .15) 75%,
		transparent 75%, transparent);
		background-image: -ms-linear-gradient(135deg, rgba(255, 255, 255, .15) 25%, transparent 25%,
		transparent 50%, rgba(255, 255, 255, .15) 50%, rgba(255, 255, 255, .15) 75%,
		transparent 75%, transparent);
		background-image: -o-linear-gradient(135deg, rgba(255, 255, 255, .15) 25%, transparent 25%,
		transparent 50%, rgba(255, 255, 255, .15) 50%, rgba(255, 255, 255, .15) 75%,
		transparent 75%, transparent);
		background-image: linear-gradient(135deg, rgba(255, 255, 255, .15) 25%, transparent 25%,
		transparent 50%, rgba(255, 255, 255, .15) 50%, rgba(255, 255, 255, .15) 75%,
		transparent 75%, transparent);

		-webkit-animation: animate-stripes 3s linear infinite;
		-moz-animation: animate-stripes 3s linear infinite;
	}

	@-webkit-keyframes animate-stripes {
		0% {background-position: 0 0;} 100% {background-position: 60px 0;}
	}


	@-moz-keyframes animate-stripes {
		0% {background-position: 0 0;} 100% {background-position: 60px 0;}
	}
</style>

<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'                   => 'mall-build-form',
	'enableAjaxValidation' => false,
	'htmlOptions'          => array(
		'enctype'=>'multipart/form-data',
	),
)); ?>

	<?php echo $form->errorSummary($model); ?>



	<?php echo $form->fileFieldRow($model,'logo',array('class'=>'span5')); ?>

	<?php if($model->logoFile) :?>
	<div class="clearfix logo_image">
		<div class="input">
			<?php echo CHtml::image('/' . $model->logoFile->getPreviewName(Config::$preview['resize_190'])); ?>
			<br>
			<?php echo CHtml::button('удалить', array('id'=>'deleteLogo', 'class'=>'btn danger small', 'data-build_id' => $model->id)); ?>
		</div>
	</div>
	<?php endif; ?>

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


	<div class="clearfix">
		<?php echo CHtml::label($model->getAttributeLabel('city_id'), 'City_id'); ?>
		<div class="input">
			<?php
			$city = City::model()->findByPk($model->city_id);
			$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
				'name'      => 'City_id',
				'value'     => ! is_null($city) ? "{$city->name} ({$city->region->name}, {$city->country->name})" : '',
				'sourceUrl' => '/utility/autocompletecity',
				'options'   => array(
					'minLength' => '3',
					'showAnim'  => 'fold',
					'select'    => 'js:function(event, ui) {$("#MallBuild_city_id").val(ui.item.id).keyup();}',
					'change'    => 'js:function(event, ui) {if(ui.item === null) {$("#MallBuild_city_id").val("");}}',
				),
				'htmlOptions' => array(
					'class' => 'span5'
				)
			));
			?>
			<?php echo CHtml::activeHiddenField($model,  "city_id");?>
			<?php echo CHtml::error($model, "city_id");?>
			<span class="help-block">автокомплит</span>
		</div>
	</div>


	<?php echo $form->textFieldRow($model,'key',array('class'=>'span5','maxlength'=>20)); ?>

	<?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>255)); ?>

	<?php echo $form->textareaRow($model,'about',array('class'=>'span5','maxlength'=>3000, 'rows' => '5')); ?>

	<?php echo $form->textareaRow($model,'phone',array('class'=>'span5','maxlength'=>255)); ?>

	<?php echo $form->textFieldRow($model,'site',array('class'=>'span5','maxlength'=>255)); ?>

	<?php echo $form->textareaRow($model,'address',array('class'=>'span5','maxlength'=>1000, 'rows' => 5)); ?>

	<div class="clearfix">
		<label>Услуги</label>
		<div class="input">
			<?php
			echo CHtml::activeCheckBoxList(
				$model,
				'servicesIds',
				Chtml::listData(MallService::model()->findAll(),'id','name')
			);
			?>
		</div>
	</div>


	<?php $workTime->render('adminUpdate'); ?>



	<div class="row"><div class="span8 offset1 floors">
		<h4>Этажи</h4>

		<?php
		if ($model->isNewRecord) {
			echo 'Чтобы добавлять этажи, сохраните Торговый Центр';
		} else {
			$this->widget('ext.FileUpload.FileUpload', array('onlyScript' => true));

			foreach ($model->floors as $floor)
			{
				$this->renderPartial('_floorItem', array('floor' => $floor));
			}

			echo '<a class="btn info append_floor">+ добавить этаж</a>';

		}
		?>


	</div></div>



	<div class="actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>



<?php Yii::app()->clientScript->registerScript('mall_build', '

	/* -------------------
	 *  Удаление логотипа
	 * -------------------
	 */
	$("#deleteLogo").click(function(){
		var bid = $(this).data("build_id");

		if(confirm("Вы хотите удалить изображение?")) {
			$.ajax({
				url: "/catalog/admin/mallBuild/ajaxDeleteLogo",
				type: "POST",
				data: {build_id : bid},
				dataType: "JSON",
				success: function(response) {
					if(response.success)
						$(".logo_image").remove();
					else
						alert("Ошибка удаления");
				},
				error: function(data){
					alert(data.responseText);
				}
			});
		}
	});

	/* -------------------
	 *  Добавление этажа
	 * -------------------
	 */
	$("a.append_floor").click(function(){
		$.ajax({
			url: "/catalog/admin/mallBuild/ajaxAppendFloor/bid/'.$model->id.'",
			type: "POST",
			dataType: "JSON",
			success: function(data){
				$("div.floors").find(".append_floor").before(data.html);
				$("div.floors:last").find("input[type=text]").focus();
			},
			error: function(data){
				alert(data.responseText);
			}
		});

		return false;
	});

	/* -------------------
	 *  Удаление этажа
	 * -------------------
	 */
	$("div.floors").on("click", ".close", function(){
		if (confirm("Удалить этаж?")) {

			var floor_id = $(this).parents(".floor").attr("data-floor_id");

			$.ajax({
				url: "/catalog/admin/mallBuild/ajaxDeleteFloor/fid/"+floor_id,
				type: "POST",
				dataType: "JSON",
				success: function(data){
					$("#floor_"+floor_id).remove();
				},
				error: function(data){
					alert(data.responseText);
				}
			});
		}
	});

	/* -------------------
	 *  Удаление этажа
	 * -------------------
	 */
	var timer;
	$("div.floors").on("keyup", "input.floor_name", function(){
		var input = $(this);

		clearTimeout(timer);

		timer = setTimeout(function(){
			input.addClass("loading");

			var floor_id = input.parents(".floor").attr("data-floor_id");

			$.ajax({
				url: "/catalog/admin/mallBuild/ajaxUpadeNameFloor/fid/"+floor_id,
				type: "POST",
				data: {name: input.val()},
				dataType: "JSON",
				success: function(data){
					input.removeClass("loading");

					if ( ! data.success)
						alert(data.errorMsg);

				},
				error: function(data){
					alert(data.responseText);
					input.removeClass("loading");
				}
			});
		}, 600);
	});



', CClientScript::POS_READY);?>
