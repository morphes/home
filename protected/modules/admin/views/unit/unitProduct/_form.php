<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'id'=>'unit-product-form',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="help-block">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<div class="span8">
			<?php echo $form->textFieldRow($model,'product_id',array('class'=>'span5 product_id', 'autocomplete' => 'off')); ?>
		</div>
		<div class="span2">
			<span class="btn primary small show_prod_info">показать</span>
		</div>
	</div>

	<div class="clearfix prod_info hide">
		<label>Фото товара</label>
		<div class="input">
			<strong id="prod_category">Категория</strong>
			<br>
			<span id="prod_name">Имя товара</span>
			<br>

			<input type="hidden" id="x" name="x" />
			<input type="hidden" id="y" name="y" />
			<input type="hidden" id="w" name="w" />
			<input type="hidden" id="h" name="h" />
			<input type="hidden" id="percent" name="percent" />

			<img src="#" width="280" id="prod_image"/>

			<div>
				Будет вырезан кусок: <span id="crop_original">0x0</span>
			</div>

			<?php if ($model->image_path) : ?>
			<div>
				Сейчас показывается<br>
				<img src='<?php echo '/'.$model->image_path;?>' width="416" alt="" />
			</div>
			<?php endif; ?>
		</div>
	</div>






	<?php echo $form->dropDownListRow($model, 'status', array('' => '') + UnitProduct::$statusNames, array('class'=>'span5')); ?>

	<div class="actions">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Создать' : 'Сохранить',array('class'=>'btn primary')); ?>
	</div>

<?php $this->endWidget(); ?>


<script type="text/javascript">
	$('#UnitProduct_product_id').keydown(function(event){
		if (event.keyCode == 13) {
			$('.show_prod_info').trigger('click');

			return false;
		}
	});
	$('.show_prod_info').click(function(){
		var prod_id = parseInt( $('.product_id').val() );

		$.get(
			'/admin/unit/unitProduct/ajaxGetInfo/id/'+prod_id,
			function(data){
				if (data.success) {

					// Целевой размер области
					var target_w = 416;
					var target_h = 344;

					$('#prod_image').prop('src', data.product['image']);
					$('#prod_image_preview').prop('src', data.product['image']);
					$('#prod_category').text(data.product['category']);
					$('#prod_name').text(data.product['name']);

					var percent = 280 *100 / data.product.dimensions[0];
					$('#percent').val(percent);

					$('.prod_info').removeClass('hide');


					// Create variables (in this scope) to hold the API and image size
					var jcrop_api, boundx, boundy;

					// Минимальная ширина и высота выделяемой области
					var min_w = target_w * percent / 100;
					var min_h = target_h * percent / 100;

					$('#prod_image').Jcrop({
						//onChange: updatePreview,
						onSelect: updateCoords,
						aspectRatio: 1.2093023,
						minSize: [min_w, min_h],
						setSelect: [10,20, min_w, min_h]
					},function(){
						// Use the API to get the real image size
						var bounds = this.getBounds();
						boundx = bounds[0];
						boundy = bounds[1];
						// Store the API in the jcrop_api variable
						jcrop_api = this;
					});

					function updateCoords(c)
					{
						$('#x').val(c.x);
						$('#y').val(c.y);
						$('#w').val(c.w);
						$('#h').val(c.h);

						var w = Math.round(c.w*100/percent);
						var h = Math.round(c.h*100/percent);

						$('#crop_original').text(w+'×'+h);
					}

				} else {
					$('.prod_info').addClass('hide');
					alert(data.errorMsg);
				}
			}, 'json'
		);
	});
</script>