<?php $this->pageTitle = 'Персональные данные — MyHome.ru'?>
<?php Yii::app()->clientScript->registerCssFile('/css/user.css'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/style.css'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css-new/generated/profile.css'); ?>

<script type="text/javascript">
        $(document).ready(function(){
                $('.del_avatar').click(function(){
                        $.post("<?php echo $this->createUrl('deleteavatar'); ?>", {},
                                function(data) {
                                        data = jQuery.parseJSON(data);
                                        if(data.result == true) {
                                                $('#avatar_container').remove();
                                                return false;
                                        }
                                });
                })
        });
</script>


<div class="-grid-wrapper page-title">
	<div class="-grid">
		<div class="-col-12">
			<ul class="-menu-inline -breadcrumbs">
				<li><a href="<?php echo Yii::app()->homeUrl;?>">Главная</a></li>
				<li><a href="/specialist">Специалисты</a></li>
			</ul>
		</div>
		<div class="-col-12"><h1 class="-inline"><?php echo $user->name; ?></h1><a href="/member/profile/settings" onclick="Common.hideBlock();" class="-icon-pencil-xs -gutter-left -gray -small">Редактировать профиль</a></div>
	</div>
</div>

<div class="-grid-wrapper page-content">
<div class="-grid">

<div class="-col-3 profile-sidebar">
	<h3>Редактирование</h3>

	<?php $this->renderPartial('//member/profile/specialist/_settingsMenu', array('user' => $user)); ?>
</div>


<div class="-col-9">
	<?php
	/* -------------------------------------------------------------
	 *  Навигация
	 * -------------------------------------------------------------
	 */
	?>
	<?php $this->renderPartial('//member/profile/specialist/_menu', array('user' => $user)); ?>


	<?php
	/* -------------------------------------------------------------
	 *  Форма редактирование личных данных
	 * -------------------------------------------------------------
	 */
	?>

	<?php $form = $this->beginWidget('CActiveForm', array(
		'id'                   => 'user-settings-form',
		'enableAjaxValidation' => false,
		'htmlOptions'          => array(
			'enctype' => 'multipart/form-data',
			'class'   => 'form-social form-profile'
		)
	));?>

	<?php if ($user->hasErrors()) : ?>
		<div class="error_conteiner">
			<?php echo $form->errorSummary($user); ?>
		</div>
		<div class="spacer-20"></div>
	<?php endif; ?>

	<div class="profile_info_conteiner">
		<a class="back_to_profile"
		   href="<?php echo $this->createUrl("/users/{$user->login}"); ?>">Вернуться
										   в
										   профиль</a>


		<div class="shadow_block white profile_info">
			<h2>Персональные данные</h2>

			<div class="inner">

				<?php if (!is_null($user->image_id)) : ?>
					<div id="avatar_container">
						<span class="project_tools_del del_avatar"><i></i><a href="#">Удалить</a></span>

						<div class="top_block"></div>
						<div class="avatar">
							<img src="/<?php echo $user->getPreview(Config::$preview['crop_180']) ?>">
						</div>
					</div>
				<?php endif; ?>

				<p>
					<label class=""
					       for="fp-name">Изменить
							     фотокарточку</label><br>

				<div class="avatar_input_conteiner">
					<?php echo $form->fileField($user, 'image_file', array('class' => 'avatar_input', 'size' => 50, 'accept' => 'image')); ?>
					<div class="avatar_img_mask">
						<input type="text"
						       class="textInput avatar_input_text"/>
					</div>
					<div class="clear"></div>
				</div>
				</p>
				<p>
					<?php echo $form->labelEx($user, 'firstname'); ?>
					<br>
					<?php echo $form->textField($user, 'firstname', array('class' => 'textInput')); ?>
				</p>

				<?php if ($user->role != User::ROLE_SPEC_JUR) : ?>
					<p>
						<?php echo $form->labelEx($user, 'lastname'); ?>
						<br>
						<?php echo $form->textField($user, 'lastname', array('class' => 'textInput')); ?>
					</p>
				<?php endif; ?>

				<p>
					<?php echo $form->labelEx($user, 'email'); ?>
					<br>
					<?php echo $form->textField($user, 'email', array('class' => 'textInput')); ?>
				</p>

				<p class="fp-sex">
					<strong><?php echo $user->data->getAttributeLabel('gender'); ?></strong><br>
					<label for="fp-sex-male"><?php echo CHtml::radioButton('UserData[gender]', $user->data->gender == 'M', array('value' => 'M', 'id' => 'fp-sex-male')); ?>
						Мужской</label>
					<label for="fp-sex-female"><?php echo CHtml::radioButton('UserData[gender]', $user->data->gender == 'F', array('value' => 'F', 'id' => 'fp-sex-female')); ?>
						Женский</label>
				</p>

				<p class="fp-bd">
					<?php echo $form->labelEx($user->data, 'birthday'); ?>
					<br>

					<?php $this->widget('application.components.widgets.WDateSelector', array(
						'name'  => 'UserData[birthday]',
						'value' => $user->data->birthday,
					));?>

				</p>

				<div class="clear"></div>
				<p>
					<?php echo $form->labelEx($user->data, 'about'); ?>
					<br>
					<?php
					$this->widget('application.extensions.tinymce.ETinyMce', array(
						'model'     => $user->data,
						'attribute' => 'about',
						'options'   => array(
							'theme'                           => 'advanced',
							'theme_advanced_buttons1'         => "link, unlink",
							'theme_advanced_buttons2'         => "",
							'theme_advanced_buttons3'         => "",
							'forced_root_block'               => false,
							'force_br_newlines'               => true,
							'force_p_newlines'                => false,
							'height'                          => '150px',
							'theme_advanced_toolbar_location' => 'top',
							'theme_advanced_toolbar_align'    => "left",
							'language'                        => 'ru',
						),
					));
					?>
				</p>

				<div class="spacer"></div>
			</div>
		</div>

		<div class="spacer-18"></div>
		<div class="shadow_block white profile_info">
			<h2>Контактные данные</h2>

			<div class="inner">

				<p class="input">
					<?php echo $form->labelEx($user, 'city_id'); ?>
					<?php
					$htmlOptions = array('class' => 'textInput', 'id' => 'User_city_id');

					$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
						'name'        => 'city_name',
						'sourceUrl'   => '/utility/autocompletecity',
						'value'       => $user->getCityFull(),
						'options'     => array(
							'showAnim'  => 'fold',
							'select'    => 'js:function(event, ui) {
									$("#city_id").val(ui.item.id);
								}',
							'create'    => (array_key_exists('city_id', $user->getErrors()))
								? 'js:function(event, ui) {
											$("input[name=city_name]").autocomplete("search", "' . mb_substr($user->getCityFull(), 0, 3, 'UTF-8') . '");
										     }'
								: '',
							'minLength' => 3
						),
						'htmlOptions' => $htmlOptions
					));
					?>
					<?php echo CHtml::hiddenField('User[city_id]', $user->city_id, array('id' => 'city_id')); ?>
					<span class="input-clear"
					      title="Очистить"
					      onclick="$('#city_id, #User_city_id').val(''); $('#User_city_id').focus(); return false;"></span>

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
				</p>

                <p>
                    <?php echo $form->labelEx($user, 'address'); ?>
                    <br>
                    <?php echo $form->textField($user, 'address', array('class' => 'textInput')); ?>
                </p>

				<p>
					<?php echo $form->labelEx($user, 'phone'); ?>
					<br>
					<?php echo $form->textField($user, 'phone', array('class' => 'textInput', 'id' => 'fp-contacts-phone')); ?>
					<span class="hide_phone"><?php echo $form->checkBox($user->data, 'hide_phone'); ?>
						<span>Скрыть телефон</span></span>
				</p>

				<p>
					<?php echo $form->labelEx($user->data, 'skype'); ?>
					<br>
					<?php echo $form->textField($user->data, 'skype', array('class' => 'textInput')); ?>
				</p>

				<p>
					<?php echo $form->labelEx($user->data, 'icq'); ?>
					<br>
					<?php echo $form->textField($user->data, 'icq', array('class' => 'textInput')); ?>
				</p>

				<p>
					<?php echo $form->labelEx($user->data, 'site'); ?>
					<br>
					<?php echo $form->textField($user->data, 'site', array('class' => 'textInput')); ?>
				</p>


			</div>
		</div>
		<div class="spacer-18"></div>
		<input type="submit"
		       class="btn_grey"
		       value="Сохранить изменения"/>

		<?php $this->endWidget(); ?>
		<div class="clear"></div>
	</div>

</div>

</div>
</div>