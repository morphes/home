<?php $this->pageTitle = 'Регистрация — MyHome.ru'?>
<?php $this->description = ''; ?>
<?php $this->keywords = ''; ?>


<?php
$form=$this->beginWidget('CActiveForm', array(
	'id' => 'registration-form',
	'clientOptions' => array(
		'validateOnType'		=> true,
		'validationDelay'	=> 300,
		'validateOnChange'	=> true,
		'errorCssClass'		=> 'error-reg2',
		'successCssClass'	=> 'success-reg2',
		'validateOnSubmit'	=> true,
		'afterValidateAttribute'=> 'js:function(form, data, hasError){
			
			for(var error in hasError) {
				var $elem = $("#"+error);
				if ($elem.hasClass("currentInput") && ! $elem.val()) {
					$elem.parents("div").removeClass("error-reg2").find(".error-message").hide();
				}
			}
		}'
	),
	'htmlOptions' => array('class' => 'registration2', 'autocomplete'=>'off'),
	'enableAjaxValidation' => true,
	
));

CHtml::$requiredCss = '';
CHtml::$errorCss = 'textInput-error';
?>
	<div class="header cf">
		<h1>Регистрация</h1>
		<span class="quick-reg">
			Быстрая регистрация
			<?php echo CHtml::link(CHtml::image('/img/tw.png','Twitter', array('title' => 'Twitter', 'width' => '16', 'height' => '16')), '/oauth/twitter', array('onclick' => 'CCommon.oauth("/oauth/twitter", "Twitter"); return false;')); ?>
			<?php echo CHtml::link(CHtml::image('/img/fb.png','Facebook', array('title' => 'Facebook', 'width' => '16', 'height' => '16')), '/oauth/facebook', array('onclick' => 'CCommon.oauth("/oauth/facebook", "Facebook"); return false;')); ?>
			<?php echo CHtml::link(CHtml::image('/img/vk.png','ВКонтакте', array('title' => 'ВКонтакте', 'width' => '16', 'height' => '16')), '/oauth/vkontakte', array('onclick' => 'CCommon.oauth("/oauth/vkontakte", "Vkontakte"); return false;')); ?>
			<?php echo CHtml::link(CHtml::image('/img/ok.png','Одноклассники', array('title' => 'Одноклассники', 'width' => '16', 'height' => '16')), '/oauth/odnoklassniki', array('onclick' => 'CCommon.oauth("/oauth/odnoklassniki", "Odnoklassniki"); return false;')); ?>
		</span>
	</div>
	
	<?php // Вывод кастомизированных ошибок формы.
	$this->showErrors($model);
	?>
	
	<div class="input">
		<?php echo $form->labelEx($model, 'login');?>
		<span class="error-input"><?php
			echo $form->textField($model,'login');
		?></span>
		<?php echo $form->error($model, 'login', array('class' => 'error-message'));?>
		<div class="success-message">&nbsp;</div>
		<span class="hint-message">Используется для входа на сайт, допускаются только латинские буквы и цифры.<i></i></span>
	</div>
	
	
	<div class="reg-pass-group cf">
		<div class="input input-l">
			<?php echo $form->labelEx($model, 'password');?>
			<span class="error-input"><?php
				echo $form->passwordField($model,'password');
			?></span>
			<span class="hint-message hint-spec">Не менее четырех символов.<i></i></span>
			<?php echo $form->error($model, 'password', array('class' => 'error-message error-spec'));?>
		</div>
		
		<div class="input input-r">
			<?php echo $form->labelEx($model, 'password2');?>
			<span class="error-input"><?php
				echo $form->passwordField($model,'password2');
			?></span>
			<span class="hint-message">Введите пароль повторно.<i></i></span>
			<div class="success-message">&nbsp;</div>
			<?php echo $form->error($model, 'password2', array('class' => 'error-message', 'style' => 'background-color: #F4F4E8;'));?>
		</div>
		
	</div>
	<div class="input">
		<?php echo $form->labelEx($model, 'email');?>
		<span class="error-input"><?php
			echo $form->textField($model,'email');
		?></span>
		<?php echo $form->error($model, 'email', array('class' => 'error-message'));?>
		<div class="success-message">&nbsp;</div>
		<span class="hint-message">Будет использован для подтверждения регистрации.<i></i></span>
	</div>

	<div class="reg-type">
		<div class="input">
			<?php echo $form->labelEx($model, 'role');?>
			<span class="error-input"><?php
				echo $form->dropDownList($model, 'role', array('' => 'Выберите роль') + $available_roles, array('class' => 'sel-reg-type'));
			?></span>
			<?php echo $form->error($model, 'role', array('class' => 'error-message'));?>
			<div class="success-message">&nbsp;</div>
		</div>
		
		<div class="reg-type-info">
			
			
			<?php
			// *** ВЫВОД РАЗЛИЧАЮЩИХСЯ ПОЛЕЙ
			?>
			
			<div class="input <?php echo 'only-for-'.User::ROLE_USER;?> <?php echo 'only-for-'.User::ROLE_SPEC_FIS;?> <?php echo 'only-for-'.User::ROLE_SPEC_JUR;?>">
				<label class="" for="User_firstname"><span id="labelName">Имя</span> <span class="required">*</span></label>
				<span class="error-input"><?php
					echo $form->textField($model,'firstname');
				?></span>
				<?php echo $form->error($model, 'firstname', array('class' => 'error-message'));?>
				<div class="success-message">&nbsp;</div>
				<span class="hint-message"><span id="hintName">Пожалуйста, укажите свое настоящее имя.</span><i></i></span>
			</div>
			
			<div class="input <?php echo 'only-for-'.User::ROLE_USER;?> <?php echo 'only-for-'.User::ROLE_SPEC_FIS;?>">
                                <label class="" for="User_lastname"><span id="labelLastname">Фамилия</span> <span class="required">*</span></label>
				<span class="error-input"><?php
					echo $form->textField($model,'lastname');
				?></span>
				<?php echo $form->error($model, 'lastname', array('class' => 'error-message'));?>
				<div class="success-message">&nbsp;</div>
				<span class="hint-message">Пожалуйста, укажите свою настоящую фамилию.<i></i></span>
			</div>
			
			
			
			
			<div class="input <?php echo 'only-for-'.User::ROLE_SPEC_FIS;?> <?php echo 'only-for-'.User::ROLE_SPEC_JUR;?>">
				<label class="" for="User_city_id">Город <span class="required">*</span></label>
				
				<span class="error-input"><?php
					$htmlOptions = array('onkeyup' => "if (this.value == '') $('#city_id').val('');");

					$htmlOptions['id'] = 'User_city_id';

					$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
						'name'		=> 'city_name',
						'sourceUrl'	=> '/utility/autocompletecity',
						'value'		=> $city_name,
						'options'	=> array(
							    'showAnim'	=>'fold',
							    'select'	=>'js:function(event, ui) {
										$("#city_id").val(ui.item.id).keyup();
									}',
							    'focus'	=> 'js:function(event, ui){
										$("#city_id").val(ui.item.id).keyup();
									}',
							    'change'	=> 'js:function(event, ui){
										if (ui.item == null) {
											$(".input-clear").click();
										}
									}',
							    'create'	=> ( array_key_exists('city_id', $model->getErrors()) )
									   ? 'js:function(event, ui) {
										$("input[name=city_name]").autocomplete("search", "'.mb_substr($city_name, 0, 3, 'UTF-8').'");
									   }'
									   : '',
							    'minLength' => 3

						),
						'htmlOptions'	=> $htmlOptions
					));
				?></span>
				
				<?php echo CHtml::hiddenField('User[city_id]', $model->city_id, array('id' => 'city_id')); ?>
				<?php echo $form->error($model, 'city_id', array('class' => 'error-message'));?>
				<div class="success-message">&nbsp;</div>
				<span class="hint-message">Укажите город, в котором вы живете или работаете.<i></i></span>
				<span class="input-clear" title="Очистить"></span>
				
				<?php
				// Если кликаем на управляющие кнопки, то нужно стереть значение в city_id
				Yii::app()->clientScript->registerScript('cityType', '
					$("#User_city_id").keydown(function(event){
						if (
							event.keyCode != 27 && event.keyCode != 9 && event.keyCode != 13
							&& event.keyCode != 37 && event.keyCode != 38 && event.keyCode != 39 && event.keyCode != 40
							&& event.keyCode != 35 && event.keyCode != 36
						) {
							$("#city_id").val("");
						}
					});
				', CClientScript::POS_READY);
				?>
			</div>
			
			<div class="input <?php echo 'only-for-'.User::ROLE_SPEC_FIS;?> <?php echo 'only-for-'.User::ROLE_SPEC_JUR;?>">
				<label class="" for="User_phone">Телефон <span class="required">*</span></label>
				<span class="error-input"><?php
					echo $form->textField($model,'phone');
				?></span>
				<?php echo $form->error($model, 'phone', array('class' => 'error-message'));?>
				<div class="success-message">&nbsp;</div>
				<span class="hint-message">Не будет виден посетителям сайта.<i></i></span>
			</div>
			
			<div class="input <?php echo 'only-for-'.User::ROLE_SPEC_JUR;?>">
				<label class="" for="User_contact_face">Контактное лицо <span class="required">*</span></label>
				<span class="error-input"><?php
					echo $form->textField($model->data,'contact_face');
				?></span>
				<?php echo $form->error($model->data, 'contact_face', array('class' => 'error-message'));?>
				<div class="success-message">&nbsp;</div>
				<span class="hint-message">Контактное лицо<i></i></span>
			</div>

			<?php /* // Промоблок убран из регистрации по задачи #1078
			<div class="input <?php echo 'only-for-'.User::ROLE_SPEC_FIS;?> <?php echo 'only-for-'.User::ROLE_SPEC_JUR;?>">
				<?php echo $form->labelEx($model, 'promo_code');?>
				<span class="error-input"><?php
					echo $form->textField($model,'promo_code');
				?></span>
				<?php echo $form->error($model, 'promo_code', array('class' => 'error-message'));?>
				<div class="success-message">&nbsp;</div>
				<span class="hint-message">Если знаете, укажите для мгновенной регистрации без подтверждения.<i></i></span>
			</div>
 			*/ ?>
			
		</div>
	</div>
	<div class="input reg-agreement">
		<span class="error-input" style="height: 16px; margin: 0 0 0 -1px; padding: 1px 2px 0; width: 13px;">
			<?php echo CHtml::activeCheckBox($model, 'agreement', array('style' => 'width: 13px;')) ;?>
		</span>
		С <a href="#">соглашением об использовании сервиса</a> myhome.ru ознакомился и согласен
		<?php echo $form->error($model, 'agreement', array('class' => 'error-message', 'style' => 'margin-top: -20px;'));?>
<!--		<div style="margin-top: -20px;" class="error-message">Необходимо подтвердить ваше согласие с правилами использования сервиса myhome.ru</div>-->
	</div>


	<p class="submit">
		<button type="submit" class="button3" onclick="_gaq.push(['_trackEvent', 'Регистрация', 'Владелец квартиры', 'Создание заказа',, false]);">Зарегистрироваться</button>
		<span><span class="required">*</span> поля обязательные для заполнения</span>
	</p>
	<script>
		$(function(){
			(function(){
				var r = $('.registration2');
				r.find('.sel-reg-type').change(function(){
					var val = $(this).val();
					
					if (val == '<?php echo User::ROLE_SPEC_JUR;?>') {
						$("#labelName").text('Название компании');
						$("#hintName").text('Краткое, без указания формы собственности.');
					} else {
						$("#labelName").text('Имя');
						$("#hintName").text('Пожалуйста, укажите свое настоящее имя.');
					}
					
					if (val) {
						$specTags = r.find('.reg-type-info').show();
						
						// Показываем поля, соответсвующие роли
						$specTags.find('div.input').hide().find('input, select').attr('disabled', true);
						$specTags.find('.only-for-'+val).show().find('input, select').removeAttr('disabled');
						
						// Убираем инфу о валидации
						$specTags.find(".error-reg2").removeClass('error-reg2');
						$specTags.find(".error-message").hide();
						$specTags.find(".success-reg2").removeClass('success-reg2');
					} else {
						r.find('.reg-type-info').hide();
					}
				});
				r.find('.input-clear').each(function(){
					
					var $input = $(this).parents('div').find('.error-input input');
					
					
					//if ($input.val())
						$(this).fadeTo(100, 1);
					/*
					$input.keyup(function(){
						if ($(this).val() != '') {
							$(this).parents('div').find('.input-clear').stop().fadeTo(100, 1);
						} else {
							$(this).parents('div').find('.input-clear').stop().fadeTo(100, 0);
						};
					});
					*/
					$(this).click(function(){
						$(this).parent().find('input').val('').focus();
					});
				});
			})();
		});
	</script>
<?php $this->endWidget(); ?>


<?php
// Рендерим попап с пользовательским соглашением, для страницы регистрации
if ($this->getRoute() == 'site/registration')
	$this->renderPartial('//widget/popupAgreement');
?>



<?php
Yii::app()->clientScript->registerScript('anchor', '
        arr = [];
        arr = location.hash.split("/");
        $("#"+arr[1]).click();
	
	if (arr[1] == "designer") {
		$("select.sel-reg-type").val("'.USER::ROLE_SPEC_FIS.'").change();
	}
	
	$(".error-input input").on({
		"focus": function(){
			$(this).parents("div.input").find(".hint-message").addClass("active");
		},
		"blur": function(){
			$(this).parents("div.input").find(".hint-message").removeClass("active");
		}
	});
	
	$("input").focus(function(){
		$(this).addClass("currentInput");
	});
	$("p.submit").find("button").click(function(){
		$("input.currentInput").removeClass("currentInput");
	});
	
', CClientScript::POS_READY);

if ($model->role) {
	Yii::app()->clientScript->registerScript('reg', '
		$("select.sel-reg-type").val("'.$model->role.'").change();
	');
}
?>
