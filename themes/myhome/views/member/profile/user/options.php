<?php $this->pageTitle = 'Настройка уведомлений — MyHome.ru'?>
<?php Yii::app()->clientScript->registerCssFile('/css/user.css'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/style.css'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css-new/generated/profile.css'); ?>


<div class="-grid-wrapper page-title">
	<div class="-grid">
		<div class="-col-12">
			<ul class="-menu-inline -breadcrumbs">
				<li><a href="<?php echo Yii::app()->homeUrl;?>">Главная</a></li>
			</ul>
		</div>
		<div class="-col-12">
			<h1 class="-inline"><?php echo (!empty($user->firstname) || !empty($user->lasname)) ? $user->name : $user->login; ?></h1>
			<?php if(Yii::app()->user->id == $user->id) : ?>
				<a href="/member/profile/settings" class="-icon-pencil-xs -gutter-left -gray -small">Редактировать профиль</a>
			<?php endif; ?>
		</div>
	</div>
</div>


<div class="-grid-wrapper page-content">
	<div class="-grid">

		<div class="-col-3 profile-sidebar">
			<h3>Редактирование</h3>

			<?php $this->renderPartial('//member/profile/user/_settingsMenu', array('user' => $user)); ?>
		</div>


		<div class="-col-9">
			<?php
			/* -------------------------------------------------------------
			 *  Навигация
			 * -------------------------------------------------------------
			 */
			?>
			<?php $this->renderPartial('//member/profile/user/_menu', array('user' => $user)); ?>

			<div class="content_block">

				<div class="profile_info_conteiner">
					<a class="back_to_profile" href="<?php echo $this->createUrl("/users/{$user->login}"); ?>">Вернуться в профиль</a>

					<?php $form=$this->beginWidget('CActiveForm', array(
						'id'=>'user-options-form',
						'enableAjaxValidation'=>false,
						'htmlOptions'=>array(
							'class' => 'form-social form-profile'
						)
					));?>
					<div class="shadow_block white profile_info">
						<h2>Настройка уведомлений</h2>
						<div class="inner">
							<p class="light">
								Выберите события, о которых необходимо уведомление
								<br>
								по электронной почте
							</p>
							<ul class="checkbox-list">
								<li>
									<label for="fn-new-message">
										<?php echo $form->checkBox($user->data, 'notice_private_message', array('id' => 'fn-new-message'));?>
										<?php echo $user->data->getAttributeLabel('notice_private_message');?>
									</label>
								</li>
								<li>
									<label for="fn-tender-response">
										<?php echo $form->checkBox($user->data, 'tender_response_notify', array('id' => 'fn-tender-response'));?>
										<?php echo $user->data->getAttributeLabel('tender_response_notify');?>
									</label>
								</li>
								<li>
									<label for="fn-portal-notice">
										<?php echo $form->checkBox($user->data, 'portal_notice', array('id' => 'fn-portal-notice'));?>
										<?php echo $user->data->getAttributeLabel('portal_notice');?>
									</label>
								</li>
							</ul>
						</div>
					</div>

					<div class="spacer-18"></div>
					<input type="submit" class="btn_grey" value="Сохранить изменения" />
					<?php $this->endWidget(); ?>
					<div class="clear"></div>
				</div>
			</div>

		</div>
	</div>
</div>