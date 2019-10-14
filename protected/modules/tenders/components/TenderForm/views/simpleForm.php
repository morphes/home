<?php // TODO: move to assets ?>
<script type="text/javascript">
	$(document).ready(function(){
		/**
		* загрузка файлов
		*/
		var cnt = 1;
		$('.img_input').live('change',function(evt){
			$(this).next('.img_mask').children('input').val($(this).val());
			var conteiner = $(this).parents('.img_input_conteiner');
			conteiner.find('.input_row.file_desc').removeClass('hide');
			conteiner.find('.input_row .hint_conteiner .del_img').removeClass('hide');

			input = $('#input_clone .img_input_conteiner').clone();
			input.appendTo('.image_to_upload');


			input.find('.input_row input').val("");
			input.find('.input_row input.img_input').attr("name","UploadedFile[file_"+cnt+"]");
			input.find('.input_row textarea').attr("name","UploadedFile["+cnt+"][desc]");
			input.find('.del_img').addClass("hide");
			input.find('.input_spacer').addClass("hide");
			cnt++;
		});

		$('.uploaded_files_del i').live('click',function(){
		        var id = $(this).data('value');
			var self = this;
			if (typeof(id) == 'undefined') { // не загружен
				delete filesList[this.id];
				$(self).parents('.uploaded_files').remove();
			} else {
				$.ajax({
					url:"/tenders/tender/removefile",
					data: {'file_id':id},
					type: "post",
					dataType: "json",
					async: false,
					success: function(response) {
						if (response.success) {
							$(self).parents('.uploaded_files').remove();
						}
					}
				});
			}
		});

		$('.del_img').live('click',function(){
			$(this).parents('.img_input_conteiner').remove();
			return false;
		});
		
	})
</script>

<?php echo CHtml::form('', 'post', array('id'=>'tender-form', 'enctype'=>'multipart/form-data')); ?>
<div class="tender_add_form">
	<div class="tander_name">
		<h3 class="subhead">Наименование <span class="required">*</span></h3>
		<div class="adding_block">
			<?php $class = $tender->hasErrors('name') ? 'textInput textInput-placeholder error' : 'textInput'; ?>
			<?php echo CHtml::activeTextField($tender, 'name', array('id'=>'', 'class'=>$class, 'placeholder'=>'Наименование')); ?>
		</div>
	</div>

	<div class="tender_city">
		<h3 class="subhead">Город</h3>
		<div class="adding_block">
			<div class="dropdown_input">
				<?php $class = $tender->hasErrors('city_id') ? 'textInput city error' : 'textInput city'; ?>
				<?php
				$this->widget('application.components.widgets.EAutoComplete', array(
					'valueName'	=> $cityName,
					'sourceUrl'	=> '/utility/autocompletecity',
					'value'		=> $tender->city_id,
					'options'	=> array(
						'showAnim'  => 'fold',
						'open'      => 'js:function( event, ui ) { $(".ui-autocomplete").width(194); }',
						'minLength' => 3
					),
					'htmlOptions'	=> array('id'=>'city_id', 'name'=>'Tender[city_id]', 'class'=>$class, 'placeholder'=>'Начните вводить название города'),
					'cssFile' => null,
				));
				?>
			</div>
		</div>
	</div>

	<div class="clear"></div>

	<h3 class="subhead">Подробное описание работ <span class="required">*</span></h3>
	<div class="adding_block tender_text">
		<span class="char_stay">Осталось <span>3000</span> знаков</span>
		<?php echo CHtml::activeTextArea($tender, 'desc', array('id'=>'tender_descript', 'class'=>'textInput', 'maxlength'=>'3000', 'placeholder'=>'Опишите подробно ваш заказ')); ?>

		<div class="image_uploaded">
			<?php foreach ($files as $file) { ?>
			<div class="uploaded_files">
				<div class="uploaded_files_name image">
					<i></i>
					<?php echo CHtml::link($file['name'].'.'.$file['ext'], Yii::app()->controller->createUrl('/download/tenderfile/', array('id'=>$file['id']))); ?>
				</div>
				<div class="uploaded_files_description">
					<?php echo CHtml::tag('span', array(), empty($file['desc']) ? 'Добавить описание' : $file['desc'] ); ?>
					<?php echo CHtml::textArea('File[desc]['.$file['id'].']', $file['desc'], array('class'=>'textInput hide', 'maxlength'=>255)); ?>
				</div>
				<div class="uploaded_files_filesize">
					<?php echo CFormatterEx::formatFileSize($file['size']); ?>
				</div>
				<div class="uploaded_files_del">
					<?php echo CHtml::tag('i', array('data-value'=>$file['id']), ''); ?>
				</div>

				<div class="clear"></div>
			</div>
			<?php } ?>
		</div>

		<div class="image_to_upload">
			<div class="img_input_conteiner to_del">
				<div class="input_row">
					<div class="input_conteiner">
						<label>Загрузить файл</label>
						<input  name="UploadedFile[file_0]" type="file" multiple='multiple' class="img_input" size="60" />
						<div class="img_mask">
							<input type="text" class="textInput img_input_text" />
						</div>
						<div class="clear"></div>
					</div>
					<div class="hint_conteiner">
						<div class="del_img hide">
							<span></span><a href="#">Удалить</a>
						</div>
						<div class="tender_hint">
							<p>Просим не размещать файлы рекламного характера и вредоносные программы.</p>
						</div>
					</div>
					<div class="clear"></div>
				</div>
				<div class="input_row file_desc hide">
					<div class="input_conteiner">
						<label>Описание изображения</label>
						<textarea  name="UploadedFile[0][desc]" class="textInput"></textarea>
					</div>
					<div class="hint_conteiner">

					</div>
					<div class="clear"></div>
				</div>
			</div>
		</div>

	</div>

	<h3 class="subhead">Бюджет заказа</h3>
	<div class="adding_block">
		<label class="compare_offer">
			<?php echo CHtml::radioButton('Tender[cost_flag]', ($tender->cost_flag==Tender::COST_COMPARE), array('class'=>'textInput', 'value'=>Tender::COST_COMPARE)); ?>
			Не указывать</label>
		<label class="compare_offer">
			<?php echo CHtml::radioButton('Tender[cost_flag]', ($tender->cost_flag==Tender::COST_EXECT), array('class'=>'textInput', 'value'=>Tender::COST_EXECT)); ?>
			Указать</label>
		<label class="compare_offer <?php if ($tender->cost_flag==Tender::COST_COMPARE) echo 'hide'; ?>"><span class="budget"><?php
			$class = $tender->hasErrors('cost') ? 'textInput error' : 'textInput';
			echo CHtml::textField('Tender[cost]', $tender->cost, array('class'=>$class, 'maxlength'=>10));
		?></span> рублей</label>
	</div>

	<h3 class="subhead">Закрыть заказ через</h3>
	<div class="adding_block tender_close drop_down">
		<?php if ($tender->status == Tender::STATUS_MAKING) : ?>
		<span class="exp_current"><?php echo $expireLabel; ?><i></i></span>
		<ul class="set_input">
			<li class="current" data-value="3">3 дня</li>
			<li data-value="7">7 дней</li>
			<li data-value="14">14 дней</li>
			<li data-value="30">30 дней</li>
		</ul>
		<input type="hidden" value="3"  name="time">

		<?php else : ?>

		<span class="exp_current disabled"><?php echo $expireLabel; ?><i></i></span>
		<ul class="set_input">
			<li data-value="3">3 дня</li>
			<li data-value="7">7 дней</li>
			<li data-value="14">14 дней</li>
			<li data-value="30">30 дней</li>
		</ul>
		<input type="hidden" value=""  name="time">
		<a href="#">Изменить дату</a>
		<script type="text/javascript">
			tender.changeTenderDate();
			tender.removeErrorClass();
		</script>

		<?php endif; ?>
	</div>

	<?php if (Yii::app()->getUser()->getIsGuest()) : ?>
	<div class="user_fields">
		<div class="field">
			<label>Ваш e-mail <span class="required">*</span></label>
			<?php $class = $tender->hasErrors('email') ? 'textInput error' : 'textInput'; ?>
			<?php echo CHtml::textField('Tender[email]', $tender->email, array('class'=>$class)); ?>
		</div>
		<div class="field">
			<label>Имя или название компании</label>
			<?php $class = $tender->hasErrors('author_name') ? 'textInput error' : 'textInput'; ?>
			<?php echo CHtml::textField('Tender[author_name]', $tender->author_name, array('class'=>$class)); ?>
		</div>
		<div class="clear"></div>
	</div>
	<?php endif; ?>
	<input type="submit" value="Разместить заказ" class="btn_grey add_topic"/>
	<img src="/img/load.gif"  id="photo_loader"/>
</div>
<?php echo CHtml::endForm(); ?>

	
<div class="hide" id="input_clone">
        <div class="img_input_conteiner to_del">
                <div class="input_row">
                        <div class="input_conteiner">
                                <label>Изображение</label>
                                <input  name="UploadedFile[0]" type="file" class="img_input" size="61" />
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
                <div class="input_row">
                        <div class="input_conteiner">
                                <label>Описание изображения</label>
                                <textarea  name="UploadedFile[0][desc]" class="textInput"></textarea>
                        </div>
                        <div class="hint_conteiner">

                        </div>
                        <div class="clear"></div>
                </div>
                <div class="input_spacer hide"></div>
        </div>
</div>