<?php $this->pageTitle = 'Изменение пароля — MyHome.ru'?>
<?php Yii::app()->clientScript->registerCssFile('/css/user.css'); ?>

<script type="text/javascript">
        $(document).ready(function(){
                $('.add_other_social').click(function(){
                        var input = $('p.inp_other_social:last').clone();
                        input.children('input').val('');
                        input.insertAfter('.other_social .inp_other_social:last');
                        return false;
                });
                $('.delete_other_social').live('click',function(){
                        $(this).parent().remove();
                        return false;
                });
        })
</script>

<div class="pathBar">
        <?php
        $this->widget('application.components.widgets.EBreadcrumbs', array(
                'links' => array(),
        ));
        ?>
        <h1><?php echo $user->name; ?></h1>
	<?php if(Yii::app()->user->id == $user->id) : ?>
		<a class="edit_info edit_profile" href="/member/profile/settings"><span></span>Редактировать профиль</a>
	<?php endif; ?>
        <div class="spacer"></div>
</div>


<div id="left_side">
        <?php $this->renderPartial('//member/profile/user/_settingsMenu', array('user' => $user));?>
</div>


<div id="right_side">
        <div class="content_block">

		<?php if (Yii::app()->request->isPostRequest) {
			if ($user->hasErrors()) { ?>
				<div class="error_conteiner">
					<div class="errorSummary">
						<?php $errors = $user->getErrors();
						foreach ($errors as $error) {
							if (isset($error[0]))
								echo CHtml::tag('p', array(), $error[0]);
						}
						?>
					</div>
				</div>
				<div class="spacer-20"></div>
			<?php } else { ?>
				<p class="good-title">Пароль успешно изменен</p>
			<?php }
		} ?>

                <div class="profile_info_conteiner">
                        <a class="back_to_profile" href="<?php echo $this->createUrl("/users/{$user->login}"); ?>">Вернуться в профиль</a>

                        <?php $form=$this->beginWidget('CActiveForm', array(
                                'id' => 'user-settings-form',
                                'enableAjaxValidation' => false,
                                'htmlOptions' => array('class' => 'form-password form-social'),
                        ));?>

                                <div class="shadow_block white profile_info">
                                        <h2>Изменение пароля</h2>
                                        <p>
                                                <?php echo $form->labelEx($user,'old_password'); ?><br>
                                                <?php echo $form->passwordField($user,'old_password', array('class' => 'textInput big_input')); ?>
                                        </p>

                                        <p class="fp-newpass">
                                                <?php echo $form->labelEx($user,'password'); ?><br>
                                                <?php echo $form->passwordField($user,'password', array('class' => 'textInput')); ?>
                                        </p>

                                        <p class="fp-newpass2">
                                                <?php echo $form->labelEx($user,'password2'); ?><br>
                                                <?php echo $form->passwordField($user,'password2', array('class' => 'textInput')); ?>
                                        </p>
                                        <div class="clear"></div>
                                </div>

				<div class="spacer-18"></div>

				<input type="submit" class="btn_grey" value="Сохранить изменения" />

                        <?php $this->endWidget(); ?>

                        <div class="clear"></div>
                </div>

        </div>
</div>
<div class="clear"></div>
<div class="spacer-30"></div>