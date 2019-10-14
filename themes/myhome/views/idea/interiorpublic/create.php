<?php
$cs = Yii::app()->clientScript;
$cssCoreUrl = $cs->getCoreScriptUrl();
$cs->registerCssFile($cssCoreUrl . '/jui/css/base/jquery-ui.css');
$cs->registerCssFile('/css/style.css');
$cs->registerCoreScript('jquery.ui');

/**
 * При загразуке страницы смотрим, что стоит в скритом поле input
 * Если value = 1, то закрываем форму от редактирования, позволяя
 * выбирать значения только из первого селекта.
 */
Yii::app()->getClientScript()->registerScript('disable_mask', "

	var formArch = $('form.arch');

	// Засеряем и болкируем всю форму, кроме выбора типа строения.
	if ($('.disabled_mask').length){
		$('.input_row:not(.building_type)').addClass('disabled');

		$('.proj_form_actions .btn_conteiner').addClass('disabled').find('input').attr('disabled', 'disabled');
		$('.disabled_mask').css('height', formArch.height()-130);
	}


	// Сабмит формы после выбора типа строения
	$('.project_add').on({
		click:function(){
			if ($(this).attr('data-alert-change')) {
				doAction({
					yes: function(){
						formArch.submit();
					},
					no: function(){

					}
				}, 'Сменить тип объекта?', 'При смене типа интерьера с «Общественного» на «Жилой» все введенные данные буду удалены! Продолжить?');
				return false;
			}
		}
	},'.building_type ul li');





", CClientScript::POS_READY);


?>
<script type="text/javascript">
	$(document).ready(function(){

		$('.save_later').click(function(){
			$('#architecture-form').append('<input type="hidden" name="later" value="yes">');

			uploadSubmit();

			return false;
		});

		$('.save_delete').click(function(){
			$.get(
				'/idea/interiorpublic/delete/id/<?php echo $model->id;?>',
				function(data){
					if (data.success) {
						document.location = '/users/<?php echo $user->login;?>/portfolio/service/<?php echo $model->service_id;?>';
					}
				}, 'json'
			);

			return false;
		});



		/**
		 * загрузка файлов
		 */
		var coverInput = $('.cover_input_clone.hide>div').clone();

		var filesList = [];
		var indexFile = 0;
		var totalLoaded = [];
		var totalSize = 0;
		var files;

		$('.img_input:not(.cover)').live('change',function(evt){

			files = evt.target.files;

			showFiles();
		});

		function showFiles(i)
		{
			if(typeof(i) == 'undefined')
				i = 0;

			if(i < files.length) {
				showFile(i, function(i){
					showFiles(i+1);
				});
			}
		}

		function showFile(i, callback)
		{
			var f = files[i];

			if (!f.type.match('image.*')){
				alert('Файл ' + f.name + ' не является изображением.');
				if(callback != undefined) callback(i);
				return;
			}

			var reader = new FileReader();
			reader.onload = (function(theFile)
			{
				return function(e)
				{
					var th = this;
					var img = new Image();
					img.onload = function()
					{
						var styleOrientation = (img.width > img.height)
								   ? 'height:150px;'
								   : 'width:150px;';

						$('<div class="uploaded"><div class="input_row image_inp"><div class="input_conteiner"><div class="input_conteiner_img"><img src="'+e.target.result +'" style="'+styleOrientation+'" /></div><label>Описание изображения</label><textarea maxlength="900"  name="UploadedImage[desc]['+ indexFile +']" id="UploadedImageDesc'+ indexFile +'"  class="textInput img_descript"></textarea><div class="clear"></div><div class="del_cover"><i></i><a class="del_img" id="'+indexFile+'" href="#">Удалить</a></div></div></div><div class="hint_conteiner"></div></div>').appendTo('.image_uploaded');

						filesList[indexFile] = theFile;
						indexFile++;
						delete files[i];
						delete th;
						if(callback != undefined) callback(i);
					};

					img.src = e.target.result;
				};
			})(f);
			reader.readAsDataURL(f);
		}


		/*
		 * Отправка формы
		 */
		$('#architecture-submit').click(function(){
			uploadSubmit();
		});

		/**
		 * Инициирует процесс загрузки фотографий и затем последющий
		 * сабмит формы.
		 */
		function uploadSubmit()
		{
			$('#inputFile').remove();

			//var h = $('.shadow_block.padding-18').height();
			//$('.progressbar').height(h+36);
			//$('.progressbar').removeClass('hide');

			$('.progressbar_center').removeClass('hide');
			$('.proj_form_actions').addClass('hide');

			totalSize = getFilesSize(filesList);

			if(filesList.length == 0) {
				finishUploadSubmit();
			} else {
				/*
				 * Проход по массиву файлов и их отправка
				 */
				sendFiles();
			}
		}

		/**
		 * Функция вызывается по завершению загрузки всех фоток аяксом.
		 * Помещает прогресс бар на 100% и сабмит основную форму.
		 */
		function finishUploadSubmit()
		{
			$('.progress').css('width', '100%');
			$('.progress > span').text('100%');

			$('#architecture-form').submit();
		}


		function sendFiles(i)
		{
			if(typeof(i) == 'undefined')
				i = 0;

			if(i < filesList.length) {
				sendFile(i, '<?php echo $this->createUrl('upload', array('id'=>$model->id)); ?>', function(i){
					sendFiles(i+1);
				});
			}
		}


		/*
		 * Размер массива файлов для загрузки
		 */
		function getFilesSize(files)
		{
			var total = 0;
			for(var i = 0; i < files.length; i++){
				if(typeof files[i] != "undefined")
					total+=files[i].size;
			}
			return total;
		}

		/*
		 * Отправка файла по протоколу XmlHttp
		 */
		function sendFile(cnt, url, callback)
		{

			if (typeof(filesList[cnt]) == 'object')
			{
				var xhr = new XMLHttpRequest();
				totalLoaded[cnt] = 0;

				/*
				 * Подключение обработчика события процесса загрузки (для прогрессбара)
				 */
				if ( xhr )
					xhr.upload.addEventListener("progress", function(e){ updateProgress(e, cnt) }, false);

				var file = new FormData();

				/*
				 * Событие, вызванное по итогу отправки очередного файла
				 */
				xhr.onreadystatechange = function(){
					if(this.readyState == 4) {
						if(this.status == 200) {
							/*
							 * Отправка всей формы в случае успешной отправки последнего файла в очереди
							 */
							if(cnt == filesList.length - 1) {
								finishUploadSubmit();
								return false;
							}
						}
						delete file;
						delete filesList[cnt];
						delete this;

						if (callback != undefined) callback(cnt);
					}
				};

				/*
				 * Отправка файла
				 */
				xhr.open("POST", url);

				file.append('Interiorpublic[image]', filesList[cnt]);
				file.append('Interiorpublic[desc]', $('#UploadedImageDesc'+ cnt).val());

				xhr.send(file);
			}
			else
			{
				if (cnt == filesList.length - 1) {
					finishUploadSubmit();
					return false;
				}

				delete filesList[cnt];
				delete this;

				if (callback != undefined) callback(cnt);
			}
		}

		/*
		 * Функция отрисовки прогрессбара
		 */
		function updateProgress(e, cnt){
			if (e.lengthComputable) {
				totalLoaded[cnt] = e.loaded;

				var loaded = 0;
				for(var i = 1; i < totalLoaded.length; i++){
					loaded+=totalLoaded[i];
				}

				var total = parseInt((loaded / totalSize) * 100);
				$('.progress').css('width', total+'%');
				$('.progress > span').text(total+'%');
			}
		}



		/* ----------
		 *  Обложка
		 * ----------
		 */
		$('.cover_image a').live('click',function(){
			$(this).parents('.cover_image').remove();
			$('.cover_conteiner').html(coverInput).show();

			$('#Interiorpublic_image_id').val('');
			return false;
		});

		$('.img_input.cover').live('change',function(evt){
			// start Showing image preview
			var files = evt.target.files[0];
			$this = $(this);
			var reader = new FileReader();
			reader.onload = (function(theFile) {
				return function(e) {
					$('.cover_conteiner').hide();
					$('<div class="cover_image"><div><img width=200 src="'+e.target.result+'"></div><br class="clear"><div class="del_cover"><i></i><a class="del" href="#">Удалить</a></div></div>').insertAfter('.cover_conteiner');
				}
			})(files);
			reader.readAsDataURL(files);

		});

		/* --------------------------
		 *  Удаление фотографий со страницы
		 * --------------------------
		 */
		$('.image_uploaded').on({
			click:function(){

				if ($(this).attr('id')) {
					delete filesList[this.id];
					$(this).parents('.uploaded').remove();
				} else {
					var img_id = parseInt($(this).attr('data-img_id'));
					if (img_id > 0) {
						$(this).parents('.uploaded').remove();

						$.get(
							'/idea/interiorpublic/deleteimage/id/'+img_id,
							function(response)
							{
								if (response == 'error')
									alert('Ошибка удаления изображения');
							}
						);
					}
				}

				return false;
			}
		}, '.del_img');

		/*-----/---*/





		$('.project_add').on({
			click:function(){
				var ul = $(this).parent();
				var val = $(this).attr('data-value');
				ul.next().val(val)
			}
		},'.drop_down ul li, .interior_selector ul li');

		$(document).ready(function(){
			$('.interior_selector').on({
				click:function(){
					var id = $(this).attr('data-value');
					var ul = $(this).parent();
					var imgConteiner = $('.interior_cover');

					ul.find('li').removeClass('current');
					$(this).addClass('current');

					var src = $(this).attr('data-img-src');

					if (id != 0) {
						imgConteiner.html('<img width="200" src="'+src+'">');
					} else {
						imgConteiner.html("");
					}

				}
			},'li');
		});


		/** -----------
		 *   Соавторы
		 *  -----------
		 */
		$('.add_coautor_link a').click(function(){
			/*var coautorInput = $('.coautor_clone.hide>div').clone();
			coautorInput.insertBefore('.add_coautor_link');*/

			$.ajax({
				url: "/idea/interiorpublic/addcoauthor",
				data: {"interiorpublicId":<?php echo $model->id; ?>},
				dataType: "json",
				type: "post",
				success: function(response)
				{
					if (response.success)
						$(response.data).insertBefore('.add_coautor_link');
					else
						alert(response.error);
				}
			});

			return false;
		});
		$('.project_add_coautor').on({
			click:function(){
				var $this = $(this);

				var coauthorId = $(this).attr('data-value');
				$.ajax({
					url:"/idea/interiorpublic/deletecoauthor",
					data:{"coauthorId":coauthorId},
					dataType:"json",
					type: "post",
					success: function(response){
						if (response.success) {
							$this.parents('.add_coautor').remove();
						}
					}
				});

				return false;
			}
		},'.del_coautor');
		/*-----/---*/

	})

</script>


<?php echo $this->renderPartial('//idea/portfolio/_serviceNavigator', array('user'=>$user,'currentServiceId'=>Interiorpublic::SERVICE_ID)); ?>

<?php echo CHtml::errorSummary($model, '<div class="error_conteiner service_error"><div class="error_content">', '</div></div> <div class="spacer-18"></div>');?>


<?php
$form = $this->beginWidget('CActiveForm', array(
	'id' 			=> 'architecture-form',
	'enableAjaxValidation' 	=> false,
	'htmlOptions' 		=> array(
		'class' => 'relative arch',
		'enctype' => 'multipart/form-data',
	),
));
?>

<?php
// Если тип строения не выбран, то запрещаем редактирование формы
if ($model->object_id == 0 && $model->building_type_id == 0)
	echo '<div class="disabled_mask"></div>';
?>


<div class="shadow_block padding-18 project_add">

	<h5 class="block_headline">Общая информация</h5>
	<div class="input_row building_type">
		<div class="input_conteiner">
			<label>Тип строения  <span class="required">*</span></label>
			<?php
			/**
			 * Формируем список пунктов "Тип строения" и определяем имя
			 * выбранного пункта из селекта.
			 */
			$buildingLi = '';
			$activeName = '';
			if ($buildingTypes)
			{
				foreach ($buildingTypes as $bType)
				{
					$options = array('data-value' => $bType->id);

					// помечаем выбранный элемент
					if ($bType->id == $model->building_type_id) {
						$options['class'] = 'active';
						$activeName = $bType->option_value;
					}
					// Если выводим тип строения, относящегося к Жилым
					if ($bType->parent_id == Interiorpublic::PROPERTY_ID_LIVE) {
						$options['data-alert-change'] = 'yes';
					}

					$buildingLi .= CHtml::tag('li', $options, $bType->option_value, true);
				}
			}
			?>
			<div class="build_type drop_down">
				<span class="exp_current"><?php echo $activeName; ?><i></i></span>
				<ul>
					<?php echo $buildingLi; ?>
				</ul>
				<?php echo $form->hiddenField($model, 'building_type_id'); ?>
			</div>
		</div>
		<div class="hint_conteiner">

		</div>
		<div class="clear"></div>
	</div>
	<div class="input_row">
		<div class="input_conteiner">
			<label><?php echo $model->getAttributeLabel('name'); ?> <span class="required">*</span></label>
			<?php
			$clsError = $model->getError('name') ? 'error' : '';
			echo $form->textField($model, 'name', array('class' => 'textInput '.$clsError));?>
		</div>
		<div class="hint_conteiner">

		</div>
		<div class="clear"></div>
	</div>
	<div class="input_row">
		<div class="input_conteiner">
			<?php echo $form->labelEx($model, 'desc'); ?>
			<?php echo $form->textArea($model, 'desc', array('class' => 'textInput', 'maxlength' => 1000)); ?>
		</div>
		<div class="hint_conteiner">

		</div>
		<div class="clear"></div>
	</div>
	<div class="input_row project_add_cover">
		<div class="input_conteiner">
			<label>Обложка идеи <span class="required">*</span></label>

			<div class="cover_conteiner <?php if ($model->getError('image_id')) echo 'error';?>" <?php if ($model->image) echo 'style="display:none;"';?>>
				<?php
				/**
				 * Запоминаем вид инпута для загрузки обложки, чтобы вставить его еще и в
				 * запасной инпут, которым заменается содержимое текущего cover_conteinera при
				 * удалении выбранной фотографии.
				 */
				$imageCoverInput = $form->fileField($model, 'image', array('class' => 'img_input cover', 'size' => 61));
				echo $imageCoverInput;
				?>
				<div class="img_mask">
					<input type="text" class="textInput img_input_text " />
				</div>
			</div>

			<?php if ($model->image) : ?>
				<div class="cover_image">
					<div>
						<img width="230" height="230" src="/<?php echo $model->image->getPreviewName(Config::$preview['crop_230']); ?>">

					</div>
					<br class="clear">
					<div class="del_cover"><i></i><a class="del" href="#">Удалить</a></div>
				</div>

			<?php endif; ?>

			<?php echo $form->hiddenField($model, 'image_id'); ?>

		</div>
		<div class="hint_conteiner">

		</div>
		<div class="clear"></div>
	</div>
	<div class="input_row project_add_coautor">


		<?php
		/* -----------
		 *  Соавторы
		 * -----------
		 */
		Yii::app()->getClientScript()->registerCoreScript('jquery-ui');
		foreach ($coauthors as $coauthor) {
			$coauthorError = empty($coauthorErrors[$coauthor->id]) ? array() : $coauthorErrors[$coauthor->id];
			$this->renderPartial('//idea/interiorpublic/_addCoauthor', array('coauthor' => $coauthor, 'errors' => $coauthorError));
		}
		?>

		<div class="add_coautor_link">
			<i></i>
			<a href="#">Добавить соавтора проекта</a>
		</div>
		<div class="clear"></div>
	</div>

</div>
<div class="spacer-18"></div>



<div class="shadow_block padding-18 project_add">
	<h5 class="block_headline">Характеристики</h5>
	<div class="input_row">
		<div class="input_conteiner">
			<?php
			// ФОрмируем строку для стилей и определяем текущий элемент
			$styleLi = '';
			$styleCurrentName = '';
			if ($styles) {
				foreach($styles as $style) {
					$styleLi .= CHtml::tag('li', array('data-value' => $style->id), $style->option_value, true);
					if ($style->id == $model->style_id)
						$styleCurrentName = $style->option_value;
				}
			}
			?>

			<label>Стиль объекта <span class="required">*</span></label>
			<div class="build_type drop_down <?php if ($model->getError('style_id')) echo 'error';?>">
				<span class="exp_current"><?php echo $styleCurrentName;?><i></i></span>
				<ul>
					<?php echo $styleLi; ?>
				</ul>
				<?php echo CHtml::activeHiddenField($model, 'style_id'); ?>
			</div>
		</div>
		<div class="hint_conteiner">

		</div>
		<div class="clear"></div>
	</div>


	<div class="input_row">
		<div class="input_conteiner">
			<div class="colors">
				<?php
				// Формируем строку для стилей и определяем текущий элемент
				$colorLi = '';
				$colorCurrentName = '';
				if ($colors) {
					foreach($colors as $color) {
						$colorLi .= CHtml::tag('li', array('data-value' => $color->id), $color->option_value, true);
						if ($color->id == $model->color_id)
							$colorCurrentName = $color->option_value;
					}
				}
				?>

				<label>Основной цвет <span class="required">*</span></label>
				<div class="build_type drop_down <?php if ($model->getError('color_id')) echo 'error';?>">
					<span class="exp_current"><?php echo $colorCurrentName;?><i></i></span>
					<ul>
						<?php echo $colorLi;?>
					</ul>
					<?php echo CHtml::activeHiddenField($model, 'color_id'); ?>
				</div>
			</div>

			<div class="colors">
				<?php
				// Формируем строку для стилей и определяем текущий элемент
				$colorLi = '';
				$colorCurrentName = '';
				if ($colors) {
					foreach($colors as $color) {
						$colorLi .= CHtml::tag('li', array('data-value' => $color->id), $color->option_value, true);
						if ($color->id == $addColors[0]->color_id)
							$colorCurrentName = $color->option_value;
					}
				}
				?>

				<label>Дополнительный</label>
				<div class="build_type drop_down <?php if (isset($errorsSaveColors[$model->id][0]['color_id'])) echo 'error';?>">
					<span class="exp_current"><?php echo $colorCurrentName;?><i></i></span>
					<ul>
						<li value="">&nbsp;</li>
						<?php echo $colorLi; ?>
					</ul>
					<?php echo CHtml::activeHiddenField($addColors[0], "[$model->id][0]color_id"); ?>
				</div>
			</div>

			<div class="colors last">
				<?php
				// Формируем строку для стилей и определяем текущий элемент
				$colorLi = '';
				$colorCurrentName = '';
				if ($colors) {
					foreach($colors as $color) {
						$colorLi .= CHtml::tag('li', array('data-value' => $color->id), $color->option_value, true);
						if ($color->id == $addColors[1]->color_id)
							$colorCurrentName = $color->option_value;
					}
				}
				?>

				<label>Дополнительный</label>
				<div class="build_type drop_down <?php if (isset($errorsSaveColors[$model->id][1]['color_id'])) echo 'error';?>">
					<span class="exp_current"><?php echo $colorCurrentName;?><i></i></span>
					<ul>
						<li value="">&nbsp;</li>
						<?php echo $colorLi; ?>
					</ul>
					<?php echo CHtml::activeHiddenField($addColors[1], "[$model->id][1]color_id"); ?>
				</div>
			</div>
		</div>
		<div class="hint_conteiner">

		</div>
		<div class="clear"></div>
	</div>
</div>
<div class="spacer-18"></div>



<div class="shadow_block padding-18 img_inputs project_add">
	<h5 class="block_headline">Изображения</h5>
	<div class="image_uploaded">
		<?php
		/* -------------------------------------------
		 *  Вывод уже имеющихся фотографий у проекта
		 * -------------------------------------------
		 */
		foreach($model->images as $img) : ?>
			<div class="uploaded">
				<div class="input_row image_inp">
					<div class="input_conteiner">
						<div class="input_conteiner_img"><img
							src="/<?php echo $img->uploadedFile->getPreviewName(Config::$preview['crop_150']); ?>"
							style="width:150px;"></div>

						<label>Описание изображения</label>
						<textarea maxlength="900"
							  name="UploadedImage[desc][<?php echo $img->uploadedFile->id;?>]"
							  class="textInput img_descript"><?php echo $img->uploadedFile->desc;?></textarea>

						<div class="clear"></div>
						<div class="del_cover"><i></i><a class="del_img" data-img_id="<?php echo $img->uploadedFile->id;?>" href="#">Удалить</a></div>
					</div>
				</div>
				<div class="hint_conteiner"></div>
			</div>
		<?php endforeach; ?>
	</div>
	<div class="clear"></div>
	<div class="image_to_upload">
		<div class="img_input_conteiner to_del">
			<div class="input_row">
				<div class="input_conteiner <?php if ($model->getError('imagesCount')) echo 'error';?>">
					<input  name="" type="file" class="img_input" size="61" multiple="multiple" />
					<div class="img_mask">
						<input type="text" class="textInput img_input_text" />
					</div>
					<div class="clear"></div>
				</div>
				<div class="hint_conteiner">
					<div class="del_img hide">
						<span></span><a href="#">Удалить</a>
					</div>
				</div>
				<div class="clear"></div>
			</div>
		</div>
	</div>
</div>
<div class="spacer-18"></div>
<?php /* // КОМЕНТИМ ПОЛЕ "ТЕГИ" ДЛЯ ПОЛЬЗОВАТЕЛЙ


<div class="shadow_block padding-18 project_add">
	<h5 class="block_headline">Теги</h5>
	<div class="input_row">
		<div class="input_conteiner">
			<?php echo $form->textField($model, 'tag', array('class' => 'textInput')); ?>
		</div>
		<div class="hint_conteiner">
		</div>
		<div class="clear"></div>
	</div>
</div>
<div class="spacer-18"></div>
*/?>

<div class="shadow_block padding-18 project_add">
	<h5 class="block_headline">Добавить архитектуру этого объекта (если есть)</h5>
	<div class="input_row">
		<label>Выбрать из моего портфолио</label>
		<div class="input_conteiner interior_selector textInput">
			<?php
			// Выводим список интерьеров автора
			$architectureLi = '';
			$currentImg = '';
			foreach($architectures as $arch) {
				$htmlOptions = array(
					'data-value' => $arch->id,
					'data-img-src' => '/'.$arch->getPreview('crop_230'),
				);
				if ($arch->id == $model->architecture_id) {
					$htmlOptions['class'] = 'current';
					$currentImg = '<img width="200" src="'.$htmlOptions['data-img-src'].'">';
				}

				$architectureLi .= CHtml::tag('li', $htmlOptions, $arch->name, true);
			}
			?>
			<ul>
				<li data-value="" <?php if (!$model->architecture_id) echo 'class="current"';?>>Не выбран</li>
				<?php echo $architectureLi; ?>
			</ul>
			<?php echo $form->hiddenField($model, 'architecture_id');?>
		</div>
		<div class="hint_conteiner">
			<div class="shadow_block white interior_cover">
				<?php echo $currentImg; ?>
			</div>

		</div>
		<div class="clear"></div>
	</div>
</div>
<div class="spacer-18"></div>
<div class="proj_form_actions">
	<div class="btn_conteiner small ">
		<?php echo CHtml::button('Опубликовать', array('class'=>'btn_grey', 'id'=>'architecture-submit')); ?>
	</div>
	<a href="#" class="save_later">Сохранить и продолжить позже</a>
	<a href="#" class="save_delete">Удалить</a>
</div>


<div class="progressbar_center hide">
	<div class="bar_conteiner">
		<div class="progress"><span>0%</span></div>
	</div>
	<span class="waiting">Подождите, идет загрузка</span>
</div>


<?php $this->endWidget(); ?>

<div class="hide cover_input_clone">
	<div>
		<?php echo $imageCoverInput; ?>
		<div class="img_mask">
			<input type="text" class="textInput img_input_text " />
		</div>
	</div>
</div>
