<div>register</div>
<?php if (Yii::app()->user->hasFlash('login')): ?>
	<div class="flash-notice">
		<?php echo Yii::app()->user->getFlash('login'); ?>
	</div>
<?php endif; ?>		

<div class="form">
	<table>
		<tr>
			<td style="vertical-align: top;">
				<?php
				
				$form = $this->beginWidget('CActiveForm', array(
						'id' => 'auth-form',
					));
				?> 
				<?php echo CHtml::hiddenField('authorize', true); ?>
				<div class="authorize">
					<div class="row">
						<?php echo $form->labelEx($userAuth, 'login'); ?>
						<?php echo $form->textField($userAuth, 'login'); ?>
						<?php echo $form->error($userAuth, 'login'); ?>
					</div>

					<div class="row">
						<?php echo $form->labelEx($userAuth, 'password'); ?>
						<?php echo $form->passwordField($userAuth, 'password'); ?>
						<?php echo $form->error($userAuth, 'password'); ?>
					</div>

					<div class="row rememberMe">
						<?php echo $form->checkBox($userAuth, 'rememberMe'); ?>
						<?php echo $form->label($userAuth, 'rememberMe'); ?>
						<?php echo $form->error($userAuth, 'rememberMe'); ?>
					</div>

					<div class="row buttons">
						<?php echo CHtml::submitButton('Вход'); ?>
					</div>
				</div>
				<?php $this->endWidget(); ?>
			</td>
			<td>

				<?php
				$form = $this->beginWidget('CActiveForm', array(
						'id' => 'registration-form',
					));
				?>
				<?php echo CHtml::hiddenField('register', TRUE); ?>
				<div class="register">

					<p class="note">Поля со <span class="required">*</span> обязательны.</p>

					<?php echo $form->errorSummary($userReg); ?>

					<div class="row">
						<?php echo $form->labelEx($userReg, 'login'); ?>
						<?php echo $form->textField($userReg, 'login'); ?>
						<?php echo $form->error($userReg, 'login'); ?>
					</div>

					<div class="row">
						<?php echo $form->labelEx($userReg, 'password'); ?>
						<?php echo $form->passwordField($userReg, 'password'); ?>
						<?php echo $form->error($userReg, 'password'); ?>
					</div>

					<div class="row">
						<?php echo $form->labelEx($userReg, 'password2'); ?>
						<?php echo $form->passwordField($userReg, 'password2'); ?>
						<?php echo $form->error($userReg, 'password2'); ?>
					</div>

					<div class="row">
						<?php echo $form->labelEx($userReg, 'email'); ?>
						<?php echo $form->textField($userReg, 'email'); ?>
						<?php echo $form->error($userReg, 'email'); ?>
					</div>

					<div class="row">
						<?php echo $form->labelEx($userReg, 'name'); ?>
						<?php echo $form->textField($userReg, 'name'); ?>
						<?php echo $form->error($userReg, 'name'); ?>
					</div>

					<div class="buttons">
						<?php echo CHtml::submitButton('Зарегистрироваться'); ?>
					</div>

				</div>

				<?php $this->endWidget(); ?>
			</td>
		</tr>
	</table>
</div><!-- form -->
