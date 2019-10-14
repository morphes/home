<?php
$this->breadcrumbs=array(
	'Управление пользователями'=>array('userlist'),
	'Приглашение пользователя'
);
?>

<?php
Yii::app()->clientScript->registerScript(
   'myHideEffect',
   '$(".flash-success").animate({opacity: 1.0}, 3000).fadeOut("slow");',
   CClientScript::POS_READY
);
?>

<?php if(Yii::app()->user->hasFlash('user-create-success')):?>
<div class="flash-success alert-message success">
	<strong><?php echo Yii::app()->user->getFlash('user-create-success'); ?></strong>
</div>
<?php endif; ?>


<h1>Приглашение пользователя (регистрация)</h1>

<?php
$form = $this->beginWidget('ext.bootstrap.widgets.BootActiveForm', array(
	'id' => 'user-invite-form',
	'enableAjaxValidation' => true,
	'clientOptions' => array(
		'validateOnType'		=> true,
		'validationDelay'	=> 300,
		'validateOnChange'	=> true,
	),
	'htmlOptions' => array('class' => '')
));
?>

<div class="clearfix">
	<?php echo $form->label($user, 'role'); ?>
	<div class="input">
		<?php echo $form->dropDownList($user, 'role', Config::$rolesUserReg);?>
	</div>
	<?php echo $form->error($user, 'role'); ?>
</div>

<?php echo $form->textFieldRow($user, 'login', array('class' => 'span6')) ?>

<?php echo $form->textFieldRow($user, 'password', array('class' => 'span6')) ?>

<?php echo $form->textFieldRow($user, 'email', array('class' => 'span6')) ?>

<?php echo $form->textFieldRow($user,'firstname', array('class' => 'span6')); ?>

<?php echo $form->textFieldRow($user,'lastname', array('class' => 'span6', 'hint' => 'Только для физического лица')); ?>

<div class="clearfix <?php if ($user->getError('city_id')) echo 'error';?>">
	<?php echo $form->labelEx($user,'city_id'); ?>
	<div class="input">
		<?php
		$htmlOptions = array('size'=>'20', 'onkeyup' => "if (this.value == '') $('#city_id').val('');");
		if ($user->city_id)
			$htmlOptions['readonly'] = 'readonly';

		$htmlOptions['id'] = 'User_city_id';
		$htmlOptions['class'] = 'span6';

		$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
			'name'		=> 'city_name',
			'sourceUrl'	=> '/utility/autocompletecity',
			'value'		=> $user->getCityFull(),
			'options'	=> array(
				'showAnim'		=>'fold',
				'select'		=>'js:function(event, ui) {
							$("#city_id").val(ui.item.id);
							$("input[name=city_name]").attr("readonly", "readonly");
						}',
				'create'	=> ( array_key_exists('city_id', $user->getErrors()) )
						? 'js:function(event, ui) {
						$("input[name=city_name]").autocomplete("search", "'.mb_substr($user->getCityFull(), 0, 3, 'UTF-8').'");
						}'
						: '',
				'minLength' => 3

			),
			'htmlOptions'	=> $htmlOptions
		));
		?>
		<?php echo CHtml::link('x', '#', array('alt' => 'очистить', 'onclick' => "$('input[name=city_name]').val('').removeAttr('readonly'); $('#city_id').val(''); return false;"));?>
		<?php echo $form->error($user,'city_id'); ?>
		<?php echo CHtml::hiddenField('User[city_id]', $user->city_id, array('id' => 'city_id')); ?>
	</div>
</div>

<?php echo $form->textFieldRow($user, 'phone', array('class' => 'span6')) ?>

<?php echo $form->textFieldRow($user->data, 'contact_face', array('class' => 'span6', 'hint' => 'Только для юридического лица')) ?>

<div class="actions">
	<?php echo CHtml::submitButton('Пригласить',array('class' => 'btn large danger'));?>
</div>


<?php $this->endWidget(); ?>


