<style type="text/css">
	.wrapper {
		width: 550px;
	}
	.image_place img {
		box-shadow: 2px 2px 6px;
	}
	.btn_crop:hover, .btn_close:hover {
		cursor: pointer;
	}
	.btn_crop {
		float:left;
		font-size: 20px;
		color: white;
		border: 1px solid gray;
		border-radius: 5px;
		padding: 5px 15px;
		background: #a90329; /* Old browsers */
		background: -moz-linear-gradient(top, #a90329 0%, #8f0222 44%, #6d0019 100%); /* FF3.6+ */
		background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#a90329), color-stop(44%,#8f0222), color-stop(100%,#6d0019)); /* Chrome,Safari4+ */
		background: -webkit-linear-gradient(top, #a90329 0%,#8f0222 44%,#6d0019 100%); /* Chrome10+,Safari5.1+ */
		background: -o-linear-gradient(top, #a90329 0%,#8f0222 44%,#6d0019 100%); /* Opera 11.10+ */
		background: -ms-linear-gradient(top, #a90329 0%,#8f0222 44%,#6d0019 100%); /* IE10+ */
		background: linear-gradient(to bottom, #a90329 0%,#8f0222 44%,#6d0019 100%); /* W3C */
		filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#a90329', endColorstr='#6d0019',GradientType=0 ); /* IE6-9 */
	}
	.btn_close {
		float:right;
		font-size: 20px;
		color: white;
		border: 1px solid gray;
		border-radius: 5px;
		padding: 5px 15px;
		background: #6db3f2; /* Old browsers */
		background: -moz-linear-gradient(top, #6db3f2 0%, #54a3ee 50%, #3690f0 51%, #1e69de 100%); /* FF3.6+ */
		background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#6db3f2), color-stop(50%,#54a3ee), color-stop(51%,#3690f0), color-stop(100%,#1e69de)); /* Chrome,Safari4+ */
		background: -webkit-linear-gradient(top, #6db3f2 0%,#54a3ee 50%,#3690f0 51%,#1e69de 100%); /* Chrome10+,Safari5.1+ */
		background: -o-linear-gradient(top, #6db3f2 0%,#54a3ee 50%,#3690f0 51%,#1e69de 100%); /* Opera 11.10+ */
		background: -ms-linear-gradient(top, #6db3f2 0%,#54a3ee 50%,#3690f0 51%,#1e69de 100%); /* IE10+ */
		background: linear-gradient(to bottom, #6db3f2 0%,#54a3ee 50%,#3690f0 51%,#1e69de 100%); /* W3C */
		filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#6db3f2', endColorstr='#1e69de',GradientType=0 ); /* IE6-9 */
	}
</style>


<?php Yii::app()->clientScript->registerCoreScript('jquery');?>
<?php Yii::app()->clientScript->registerScriptFile('/js/jquery.Jcrop.min.js');?>
<?php Yii::app()->clientScript->registerCssFile('/css/jquery.Jcrop.css');?>
<?php Yii::app()->clientScript->registerMetaTag('', null, null, array('charset' => 'utf-8')); ?>



<div>
	<form action="" method="post">
		Оригинальный размер: <?php echo $orgWidth.'×'.$orgHeight;?>
		<div class="image_place">
			<input type="hidden" id="x" name="x" />
			<input type="hidden" id="y" name="y" />
			<input type="hidden" id="w" name="w" />
			<input type="hidden" id="h" name="h" />
			<input type="hidden" id="percent" name="percent" />
			<?php
			$htmlOptions = array();
			$htmlOptions['id'] = 'cover_image';
			if ($orgWidth > 500)
				$htmlOptions['width'] = '500';

			$htmlOptions['data-orgWidth'] = $orgWidth;
			$htmlOptions['data-orgHeight'] = $orgHeight;

			echo CHtml::image('/download/productImgOriginal/file_id/'.$uFile->id, '', $htmlOptions);
			echo CHtml::hiddenField('fileid', $uFile->id);
			?>
		</div>
		<div>Итоговый размер: <span id="crop_original">0x0</span></div>

		<button type="submit" name="action" value="crop" class="btn_crop">Обрезать</button>
		<button class="close btn_close">Закрыть</button>
	</form>
</div>


<script type="text/javascript">
	$(function(){
		$('button.close').click(function(){
			window.close();
		});
	});

	$(function(){
		// Процентное соотношение выводимого размера и реального размера фотки.
		var percent = 1;
		// Оригинальные размеры фотографии
		var orgWidth = parseInt('<?php echo $orgWidth;?>');
		var orgHeight = parseInt('<?php echo $orgHeight;?>');

		var jcrop_api;

		$('#cover_image').Jcrop({
			onChange: updateCoords,
			onSelect: updateCoords
		},function(){
			// Use the API to get the real image size
			var bounds = this.getBounds();
			boundx = bounds[0];
			boundy = bounds[1];
			// Store the API in the jcrop_api variable
			jcrop_api = this;

			percent = boundx * 100 / orgWidth;
			$('#percent').val(percent);
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

		$('.btn_crop').click(function(){
			var select = jcrop_api.tellSelect();

			if (select.w == 0 || select.h == 0) {
				alert("Вы забыли выделить область на фотографии!");
				return false;
			}
		});
	});
</script>
<?php if ($isCroped) : ?>
	<script type="text/javascript">
		var parent = window.opener;

		<?php if ($type == 'cover') : ?>
		$.get(
			'/catalog2/admin/product/getProductRow/pid/<?php echo $pid;?>',
			function(data){
				var $coverLi = $(data.html).find('#cover-preview-<?php echo $pid;?>').find('li');
				parent.$('#cover-preview-<?php echo $pid;?>').html( $('<div>').append($coverLi).html() );
				//window.close();
			}, 'json'
		);
		<?php endif; ?>


		<?php if ($type == 'image') : ?>
		$.get(
			'/catalog2/admin/product/getProductRow/pid/<?php echo $pid;?>',
			function(data){
				var $coverLi = $(data.html).find('#images-preview-<?php echo $pid;?>').find('li');
				parent.$('#images-preview-<?php echo $pid;?>').html( $('<div>').append($coverLi).html() );
				//window.close();
			}, 'json'
		);
		<?php endif; ?>
	</script>
<?php endif; ?>



