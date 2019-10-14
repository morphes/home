<?php $this->pageTitle = 'Редактирование проекта интерьера — MyHome.ru'?>

<?php
	$cs = Yii::app()->clientScript;
	//$cs->registerCoreScript('jquery');
	$cs->registerScriptFile('/js/fileApiLoader.js', CClientScript::POS_HEAD);
?>

<script type="text/javascript">

	$(document).ready(function(){


		var coverInput = $('.cover_input_clone.hide>div').clone();

		/* ------------------------------------------
		 *  Выбор фотографии
		 * ------------------------------------------
		 */
		var cnt = 0,input="";
		$('.img_input:not(.cover)').live('change',function(){

			var dataType = $(this).attr('data-type');
			var dataContentId = 0;
			if (dataType == 'content') {
				dataContentId = $(this).attr('data-content-id');
			}

			// Получаем объект контейнера, в котором лежит Input
			// длы выбора изображения
			var thisInput = $(this).parents('.img_input_conteiner');

			if (dataType == "layout")
			{
				thisInput.find('.input_row input.img_input').attr("name","Interior[file_"+cnt+"]");
				thisInput.find('.input_row textarea').attr("name","UploadFile[layout]["+cnt+"][desc]");
			}
			else if (dataType == 'content')
			{
				thisInput.find('.input_row input.img_input').attr("name","InteriorContent[file_"+cnt+"]");
				thisInput.find('.input_row textarea').attr("name","UploadFile[content]["+cnt+"][desc]");
				$('#idea-making-form-step-2').append('<input type="hidden" name="UploadFile[content_id]['+cnt+']" value="'+dataContentId+'">');
			}

			// Помечаем ссылку удаления еще не загруженных на сервер изображений
			thisInput.find('.del_img a').attr('new-img', 'new-img');

			$(this).next('.img_mask').children('input').val($(this).val());

			var img_conteiner = $(this).parents('.image_to_upload');

			img_conteiner.find('.input_row.hide').removeClass('hide');
			img_conteiner.find('.del_img').removeClass("hide");
			img_conteiner.find('.input_spacer').removeClass("hide");

			input = $('#image_input_clone .img_input_conteiner').clone();
			input.appendTo(img_conteiner);
			input.find('.input_row input').val("");

			// Присваиваем атрибуты типа загружаемого изображения
			input.find('.img_input').attr('data-type', dataType);
			if (dataType == 'content') {
				input.find('.img_input').attr('data-content-id', dataContentId);
			}

			cnt++;
		});


		/*обработка событий для обложки*/

		$('.img_input.cover').live('change',function(evt){
			$(this).next('.img_mask').children('input').val($(this).val());
		});


		/**
		 * Удаление изображений
		 */
		$('.uploaded .del_img').click(function(){

			var img_id = parseInt($(this).attr('data-id'));
			var img_type = $(this).attr('data-type');

			if (img_id > 0) {
				$(this).parents('.uploaded').remove();

				$.get(
					'/idea/create/imagedelete/id/'+img_id+'/interior_id/<?php echo $interior->id;?>/type/'+img_type,
					function(response)
					{
						if (response == 'error')
							alert('Ошибка удаления изображения');
					}
				);
			}

			return false;
		});

		$('.cover_image .del').click(function(){
			var img_id = parseInt($(this).attr('data-id'));
			var img_type = $(this).attr('data-type');

			var cover = $(this).parents('.cover_image');
			var input = cover.prev('div');

			if (img_id > 0) {
				$(this).parents('.cover_image').remove();
				input.show();

				$.get(
					'/idea/create/imagedelete/id/'+img_id+'/interior_id/<?php echo $interior->id;?>/type/'+img_type,
					function(response)
					{
						if (response == 'error')
							alert('Ошибка удаления изображения');
					}
				);
			}

			return false;
		});


		/*удаление загруженных фоток*/
		$('.del_img a').live('click', function(){
			if ($(this).attr('new-img')){
				$(this).parents('.img_input_conteiner').remove();
			}
			return false;
		});



		// Сохранить и продолжить позже
		$('.fpa-later').click(function(){
			$('#idea-making-form-step-2').append('<input type="hidden" name="later" value="yes">');
			$('#architecture-submit').trigger('click');

			return false;
		});
	});

	/*-----/---*/

	$(document).ready(function(){
		/**
		 * Выбор архитектуры этого проекта
		 */
		$('.interior_selector').on({
			click:function(){
				var id = $(this).attr('data-value');
				var ul = $(this).parent();
				var imgConteiner = $('.interior_cover');

				ul.next().val(id);

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


		// Сабмит формы после выбора типа строения
		$('.project_add .building_type ul').on({
			click:function(){
				//$('#architecture-form').append('<input type=\"hidden\" name=\"change_build_type\" value=\"true\">');

				if ($(this).attr('data-alert-change')) {
					var id = $(this).attr('data-value');
					doAction({
						yes: function(){
							$('#build_type').val(id);
							$('#idea-making-form-step-2').submit();
						},
						no: function(){

						}
					}, 'Сменить тип объекта?', 'При смене типа интерьера с «Жилого» на «Общественный» все введенные данные будут удалены! Продолжить?');
					return false;
				}
			}
		},'li');
	});




</script>


	<?php echo $this->renderPartial('//idea/portfolio/_serviceNavigator', array('user'=>$user,'currentServiceId'=>Interior::SERVICE_ID)); ?>

<!--	<div class="form">-->


	<?php // ******* НОВАЯ ФОРМА >>>>>> ?>

	<?php echo CHtml::errorSummary($interior, '<div class="error_conteiner service_error"><div class="error_content">', '</div></div> <div class="spacer-18"></div>');?>

	<?php
	$form = $this->beginWidget('CActiveForm', array(
		'id' => 'idea-making-form-step-2',
		'enableAjaxValidation' => false,
		'htmlOptions' => array('class' => 'relative', 'enctype' => 'multipart/form-data'),

	)); ?>


	<div class="shadow_block padding-18 project_add">

		<h5 class="block_headline">Общая информация</h5>

		<div class="input_row building_type">
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
					if ($bType->parent_id == $interior->object_id) {
						$options['class'] = 'active';
						$activeName = $bType->option_value;
					}

					// Если выводим тип строения, относящегося к Общественным
					if ( ! $interior->getIsNewRecord() && $bType->parent_id == Interiorpublic::PROPERTY_ID_PUBLIC) {
						$options['data-alert-change'] = 'yes';
					}

					$buildingLi .= CHtml::tag('li', $options, $bType->option_value, true);
				}
			}
			?>
			<div class="input_conteiner">
				<?php echo $form->labelEx($interior, 'object_id'); ?>
				<div class="build_type drop_down">
					<span class="exp_current"><?php echo $activeName; ?><i></i></span>
					<ul class="set_input <?php if ($interior->getIsNewRecord()) echo 'need_submit' ;?>">
						<?php echo $buildingLi; ?>
					</ul>
					<input id="build_type" type="hidden" name="building_type" value=""/>
				</div>
			</div>
			<div class="hint_conteiner">

			</div>
			<div class="clear"></div>
		</div>


		<div class="input_row">
			<div class="input_conteiner">
				<?php echo $form->labelEx($interior, 'name'); ?>
				<?php echo $form->textField($interior, 'name', array('class' => 'textInput')); ?>
			</div>
			<div class="hint_conteiner">

			</div>
			<div class="clear"></div>
		</div>
		<div class="input_row">
			<div class="input_conteiner">
				<?php echo $form->labelEx($interior, 'desc'); ?>
				<?php echo $form->textArea($interior, 'desc', array('class' => 'textInput')); ?>
			</div>
			<div class="hint_conteiner">

			</div>
			<div class="clear"></div>
		</div>
		<div class="input_row project_add_cover">
			<div class="input_conteiner">
				<?php echo $form->labelEx($interior, 'image_id'); ?>
				<div class="cover_conteiner <?php if ($interior->getError('image_id')) echo 'error';?>" <?php if ($interior->image_id) echo 'style="display: none;"';?>>
					<input  name="Interior[cover]" type="file" class="img_input cover" data-type="cover" size="61" />
					<div class="img_mask">
						<input type="text" class="textInput img_input_text " />
					</div>
				</div>

				<?php if ($interior->image_id) { ?>
				<div class="cover_image">
					<div>
						<img width="150" height="150" src="/<?php echo $interior->getPreview(Config::$preview['crop_150']);?>">
					</div>
					<br class="clear">
					<div class="del_cover"><i></i><a class="del" data-id="<?php echo $interior->image_id;?>" data-type="cover" href="#">Удалить</a></div>
				</div>
				<?php } ?>
			</div>

			<div class="hint_conteiner">

			</div>
			<div class="clear"></div>
		</div>

		<div class="input_row project_add_coautor">

			<?php foreach ($coauthors as $coauthor) {
				$coauthorError = empty($coauthorErrors[$coauthor->id]) ? array() : $coauthorErrors[$coauthor->id];
				$this->renderPartial('_getCoAuthor', array('coauthor' => $coauthor, 'errors' => $coauthorError));
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


	<div class="shadow_block padding-18 room_planing project_add">
		<h5 class="block_headline">Планировки</h5>
		<div class="image_uploaded">
			<?php
			if ($layouts) {
				foreach($layouts as $layout) {
					?>
					<div class="uploaded">
						<div class="input_row image_inp">
							<div class="input_conteiner">
								<div class="input_conteiner_img">
									<img src="/<?php echo $layout->getPreviewName(Config::$preview['crop_150']);?>" />
								</div>
								<label>Описание изображения</label>
								<textarea name="UploadImage[desc][<?php echo $layout->id;?>]"  class="textInput img_descript"><?php echo CHtml::value($layout, 'desc');?></textarea>
								<div class="clear"></div>

								<div class="del_cover"><i></i><a class="del_img" data-id="<?php echo $layout->id;?>" data-type="layout" href="#">Удалить</a></div>
							</div>
						</div>
						<div class="hint_conteiner"></div>
					</div>
					<?php
				}
			}
			?>

		</div>
		<div class="clear"></div>
		<div class="image_to_upload">
			<div class="img_input_conteiner to_del">
				<div class="input_row">
					<div class="input_conteiner">
						<label>Изображение</label>
						<input  name=""  type="file" class="img_input" size="61" data-type="layout" />
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
				<div class="input_row hide">
					<div class="input_conteiner">
						<label>Описание изображения</label>
						<textarea name="" class="textInput"></textarea>
					</div>
					<div class="hint_conteiner">

					</div>
					<div class="clear"></div>
				</div>
				<div class="input_spacer hide"></div>
			</div>
		</div>
	</div>
	<div class="spacer-18"></div>


	<div class="shadow_block padding-18 project_add">
		<h5 class="block_headline">Помещения (стили, цвета, изображения)</h5>
		<div class="input_row">

			<?php $this->widget('application.components.widgets.InteriorContentForms', array('interior' => $interior, 'errors'=>$errors)); ?>

			<div class="build_type drop_down  <?php echo ($interior->getError('InteriorContentCount')) ? 'error' : 'room_selector'; ?>">
				<label <?php if ($interior->getError('InteriorContentCount')) echo 'class="error"';?>>Чтобы добавить помещение, выберите его из списка</label>
				<span class="exp_current"><i></i></span>
				<ul class="set_input" data-callback="roomSelect">
					<?php
					if ($rooms) {
						foreach($rooms as $id=>$name) {
							echo CHtml::tag('li', array('data-value' => $id), $name, true);
						}
					}
					?>
				</ul>
				<input type="hidden" name="room" value="">

				<?php
				/**
				 * Функция которая, вызывается при выбре нового помещения.
				 * Вызов функции происходит, как callback к Элементу ul
				 */
				Yii::app()->getClientScript()->registerScript('room','

					function roomSelect(li)
					{
						$.post(
							"/idea/create/interior/id/' . $interior->id . '",
							$("#idea-making-form-step-2").serialize(),
							function(response){
								if (response.success) {
									var text = li.text();
									var o = $(response.data);
									o.find(".build_type .exp_current").html(text+"<i></i>");
									o.insertBefore(".room_selector");
								}
								if (response.error)
									alert(response.error);
							}, "json"
						);
					}
				', CClientScript::POS_HEAD);
				?>
			</div>

			<div class="hint_conteiner">

			</div>
			<div class="clear"></div>

			<input type="hidden" name="content_counter" id="content_counter" value="0">
		</div>
	</div>
	<div class="spacer-18"></div>


	<div class="shadow_block padding-18 project_add">
		<h5 class="block_headline">Добавить интерьер этого объекта (если есть)</h5>
		<div class="input_row">
			<label>Выбрать из моего портфолио</label>
			<div class="input_conteiner interior_selector textInput">
				<?php
				// Выводим список интерьеров автора
				$architectureLi = '';
				$currentImg = '';
				foreach($architectures as $architecture) {
					$htmlOptions = array(
						'data-value' => $architecture->id,
						'data-img-src' => '/'.$architecture->getPreview('crop_230'),
					);
					if ($architecture->id == $interior->architecture_id) {
						$htmlOptions['class'] = 'current';
						$currentImg = '<img width="200" src="'.$htmlOptions['data-img-src'].'">';
					}

					$architectureLi .= CHtml::tag('li', $htmlOptions, $architecture->name, true);
				}
				?>
				<ul>
					<li data-value="" <?php if (!$interior->architecture_id) echo 'class="current"';?>>Не выбран</li>
					<?php echo $architectureLi; ?>
				</ul>
				<?php echo $form->hiddenField($interior, 'architecture_id');?>
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
		<div class="btn_conteiner small">
			<?php echo CHtml::submitButton('Опубликовать', array('class'=>'btn_grey', 'id'=>'architecture-submit')); ?>
		</div>
		<a href="#" class="fpa-later">Сохранить и продолжить позже</a>
		<?php echo CHtml::link('Удалить', $this->createUrl('/idea/create/delete', array('id' => $interior->id)));?>
	</div>

	<div class="progressbar_center hide">
		<div class="bar_conteiner">
			<div class="progress"><span>0%</span></div>
		</div>
		<span class="waiting">Подождите, идет загрузка</span>
	</div>


	<?php $this->endWidget(); ?>

	<?php // <<<<< НОВАЯ ФОРМА ******* ?>

<div class="hide" id="image_input_clone">
	<div class="img_input_conteiner to_del">
		<div class="input_row">
			<div class="input_conteiner">
				<label>Изображение</label>
				<input  name="" type="file" class="img_input" size="61" />
				<div class="img_mask">
					<input type="text" class="textInput img_input_text" />
				</div>
				<div class="clear"></div>
			</div>
			<div class="hint_conteiner">
				<div class="del_img hide">
					<span></span><a  class="del_image" href="#">Удалить</a>
				</div>
			</div>
			<div class="clear"></div>
		</div>
		<div class="input_row hide">
			<div class="input_conteiner">
				<label>Описание изображения</label>
				<textarea  name="" class="textInput"></textarea>
			</div>
			<div class="hint_conteiner">

			</div>
			<div class="clear"></div>
		</div>
		<div class="input_spacer hide"></div>
	</div>
</div>

<script type="text/javascript">

        function remove_sc(id) {
                $.ajax({
                        url: '/<?php echo $this->module->id.'/'.$this->id?>/delete_sc/id/'+id,
                        async: false,
                        success: function(data){
                                if(data == 'ok'){
                                        $('#interior_content_id_'+id).remove();
                                } else {
                                        alert(data);
                                }
                        }
                });
        }

	// Добавление соавтора
	$('.add_coautor_link a').click(function(){
		$.ajax({
			url: "<?php echo '/'.$this->module->id.'/'.$this->id.'/getcoauthor'; ?>",
			data: {"interiorId":<?php echo $interior->id; ?>},
			dataType: "json",
			type: "post",
			success: function(response){
				if (response.success) {
					$(response.data).insertBefore('.add_coautor_link');
				}
				if (response.error) {
					alert(response.error);
				}
			}
		});
		return false;
	});

	function removeCoauthor(self, coauthorId){
		$.ajax({
			url:"<?php echo '/'.$this->module->id.'/'.$this->id.'/removecoauthor'; ?>",
			data:{"coauthorId":coauthorId},
			dataType:"json",
			type: "post",
			success: function(response){
				if (response.success) {
					$(self).parents(".add_coautor").remove();
				}
			}
		});
		return false;
	}
</script>