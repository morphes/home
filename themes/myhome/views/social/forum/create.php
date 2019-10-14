<?php $this->pageTitle = 'Создание темы — Форум — MyHome.ru'?>
<?php $this->description = ''; ?>
<?php $this->keywords = ''; ?>

<?php Yii::app()->clientScript->registerScriptFile('/js/scroll.js');?>

<script>
	$(document).ready(function(){
		forum.topicSearch();
		forum.hoverFileSelector();
	})
</script>

<?php if (Yii::app()->user->isGuest) : ?>
<div class="guest_hint">
	Уважаемый Гость, <a href="#" class="-login">Войдите</a> или <a href="/site/registration">Зарегистрируйтесь</a>, чтобы
	моментально публиковать темы. Без вашей регистрации, тема будет опубликована только после модерации, которая может занять несколько часов.
</div>
<?php endif; ?>

<div class="forum_topic_add_form">
	<?php
	$form = $this->beginWidget('CActiveForm', array(
		'id' => 'create-forum-topic',
		'htmlOptions' => array('enctype' => 'multipart/form-data'),
		'enableAjaxValidation' => true,
	)); ?>

	<?php echo $form->errorSummary($model); ?>


	<h3 class="subhead">Выберите раздел форума</h3>

	<div class="adding_block">
		<div class="forum_section_selector drop_down">
			<?php
			$htmlSections = '';
			$currentSection = '&nbsp;';
			foreach($sections as $section)
			{
				$htmlSections .= CHtml::tag('li', array('data-value' => $section->id), $section->name, true);

				if ($section->id == $model->section_id)
					$currentSection = $section->name;
			}
			?>
			<span class="exp_current <?php if ($model->getError('section_id')) echo 'error';?>"><?php echo $currentSection;?><i></i></span>
			<ul class="set_input">
				<?php echo $htmlSections; ?>
			</ul>
			<?php echo $form->hiddenField($model, 'section_id'); ?>
		</div>
	</div>



	<h3 class="subhead">Название темы</h3>

	<div class="adding_block">
		<?php echo $form->textField($model, 'name', array('class' => 'textInput', 'autocomplete' => 'off')); ?>

		<div class="similar_topics">
			<span class="disabled st_link">Похожие темы</span>
			<span class="loader"></span>

			<div class="similar_topics_container">
				<span class="opened">Похожие темы</span>
				<i class="close"></i>
				<div class="topic_wrapper" id="scrollbar">
					<div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>
					<div class="viewport">
						<div class="overview topics_list">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>



	<h3 class="subhead">Описание темы</h3>

	<div class="adding_block">
		<?php echo $form->textArea($model, 'description', array('class' => 'textInput')); ?>


		<div class="file_input_conteiner">
			<?php $this->widget('CMultiFileUpload',
				array(
					'model' 	=> $model,
					'attribute' 	=> 'files',
					'accept' 	=> 'jpg|jpeg|png|bmp|zip',
					'denied' 	=>'Данный тип файла запрещен к загрузке',
					'max' 		=> 5,
					'remove' 	=> '[x]',
					'duplicate' 	=> 'Уже выбран',
					'htmlOptions' 	=> array('class' => 'file_input', 'size' => 61),
					'options' 	=> array(
						'afterFileAppend' => 'js:function (element, value, master_element) {
							var selector = master_element.list.selector;
							$(selector).appendTo("#fileslist");
						}',
					)
				)
			);?>

			<div class="file_select">
				<i></i>
				<span>Прикрепить файл</span>
			</div>
			<div class="clear"></div>
			<div id="fileslist">

			</div>
		</div>

	</div>

	<?php if (Yii::app()->user->isGuest) : ?>
	<div class="user_fields">
		<div class="field">
			<label>Ваш e-mail <span class="required">*</span></label>
			<?php echo CHtml::activeTextField($model, 'guest_email', array('class' => 'textInput')); ?>
		</div>
		<div class="field">
			<label>Имя или название компании <span class="required">*</span></label>
			<?php echo CHtml::activeTextField($model, 'guest_name', array('class' => 'textInput')); ?>
		</div>

		<?php if(CCaptcha::checkRequirements()) {?>
		<div class="field captcha">
			<label>Введите код с картинки
				<span class="required">*</span></label>
			<?php $this->widget('CCaptcha', array(
				'captchaAction' => '/site/captcha', 'buttonType' => 'link',
				'buttonOptions' => array('class' => '-icon-refresh-s -icon-gray'),
				'buttonLabel'   => '',
				'imageOptions'  => array('width' => 90, 'height' => 50),
			))?>

			<?php echo CHtml::activeTextField($model, 'verifyCode', array('class' => 'required')) ?>
		</div>
		<?php } ?>
		<div class="clear"></div>
	</div>
	<?php endif; ?>


	<input type="submit" value="Создать" class="btn_grey add_topic"/>
	<a href="">Отменить</a>



	<?php $this->endWidget(); ?>
</div>