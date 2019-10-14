<?php Yii::app()->clientScript->registerScriptFile('/js/admin/bootstrap.affix.js'); ?>
<?php
$this->breadcrumbs=array(
	'Управление пользователями' => array('userlist'),
	'Список пользователей' => array('userlist'),
	'Редактирование'
);
?>
<?php Yii::app()->clientScript->registerScriptFile('/js/admin/userform.js'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/admin/userform.css'); ?>

<?php Yii::app()->clientScript->registerScript('search', "
$('#passwd-gen').click(function(){
        min = 8; max = 8;
	min *= 1; max *= 1; var res = '';
        chars =  '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        var len = max!=''&&max*1?(Math.floor(Math.random()*(max-min+1))+min)*1:min;
        for(var i=0;i<len;i++,res+=chars.substr(Math.floor(Math.random()*chars.length),1));
        $('#User_password').val(res);
});
");?>

<?php Yii::app()->clientScript->registerScript('ban-time', "
$('#User_status').change(function(){
        if($(this).val() == ".User::STATUS_BANNED."){
                $('.ban-time').attr('style', 'display:inline');
        } else {
                $('.ban-time').attr('style', 'display:none');
        }
});

if($('#User_status').val() == ".User::STATUS_BANNED."){
                $('.ban-time').attr('style', 'display:inline');
}
");?>

<script type="text/javascript">
	$(document).ready(function(){
		var userId = <?php echo $user->id; ?>;
		$('#user-image').change(function(){
			uploadFile({
				'url':'<?php echo $this->createUrl('uploadimage'); ?>',
				'data':{'User[image_file]':this.files[0], 'userId':userId},
				'success':function(response){
					if (response.success){
						$('#image-preview').attr('src', response.fileUrl);
						$('#user-image').val();
					}
				}
			});
			return false;
		});

		$('#delete-user-image').click(function(){
			$.ajax({
				'url':'<?php echo $this->createUrl('removeimage'); ?>',
				'data':{'userId':userId},
				'dataType':'json',
				'type':'post',
				'success':function(response){
					if (response.success){
						$('#image-preview').attr('src', response.fileUrl);
					}
				}
			});
			return false;
		});
	});

	function uploadFile(options) {
		var xhr = new XMLHttpRequest();
		var formData = new FormData();
		xhr.onreadystatechange = function(){
			if(this.readyState == 4) {
				if(this.status == 200) {
					delete this;
					if(options.success != undefined) options.success($.parseJSON(this.responseText));
				}
			}
		}
		xhr.open("POST", options.url);
		for (i in options.data){
			formData.append(i, options.data[i]);
		}
		xhr.send(formData);
	}
</script>

<?php if ( $user->isNewRecord ) : ?>
	<h1>Новый пользователь</h1>
<?php else : ?>
	<h1>Редактирвоание пользователя #<?php echo $user->id;?> &mdash; <br> <?php echo $user->name;?></h1>
<?php endif; ?>

<?php if ( ! $user->isNewRecord ) : ?>
	<div class="well" style="padding-bottom: 0; padding-top: 10px;">
		<div class="row">
			<div class="span6">
				<h4><?php echo $user->login ?></h4>
				роль: 
				<?php 
					if(array_key_exists($user->role, Config::$rolesUserReg))
					{
						echo Config::$rolesUserReg[$user->role];
					}
					elseif (array_key_exists($user->role, Config::$rolesAdmin))
					{
						echo Config::$rolesAdmin[$user->role];
					}
				?>
				<br>
				ID: <?php echo $user->id?>

				<br>
				Зарегистрирован: <?php echo date("d.m.Y", $user->create_time)?>
			</div>
		</div>
		
		<?php $this->widget('application.components.widgets.WUsergroup', array('user'=>$user));?>
	</div>
<?php endif ?>


<?php
/** @var $form CActiveForm */
$form = $this->beginWidget('ext.bootstrap.widgets.BootActiveForm', array(
	'id' => 'user-create-form',
	'enableAjaxValidation' => false,
	'htmlOptions' => array('class' => 'form-stacke')
	));
?>

<?php echo $form->errorSummary($user);?>
<?php echo $form->errorSummary($user->data);?>

<h2>Регистрационные данные</h2>

<div class="clearfix">
	<?php echo $form->label($user, 'role'); ?>
	<div class="input">
	<?php 
		if (array_key_exists($user->role, Config::$rolesUserReg))
		{
			echo $form->dropDownList($user, 'role', Config::$rolesUserReg);
		}
		elseif (array_key_exists($user->role, Config::$rolesAdmin))
		{
			echo $form->dropDownList($user, 'role', Config::$rolesAdmin);
		}
		else
		{
			if(Yii::app()->user->checkAccess(array(
				User::ROLE_ADMIN,
				User::ROLE_POWERADMIN
			)))
				echo $form->dropDownList($user, 'role', Config::$rolesUserReg+Config::$rolesAdmin);
			else
				echo $form->dropDownList($user, 'role', Config::$rolesUserReg);
		}
	?>
	</div>
	<?php echo $form->error($user, 'role'); ?>
</div>

<?php echo $form->textFieldRow($user, 'login') ?>


<div class="row">
	<div class="span7">
		<?php echo $form->textFieldRow($user, 'password') ?>
		
	</div>
	<div class="span3">
		<?php echo CHtml::button('случайный', array('id'=>'passwd-gen', 'class' => 'btn  info'))?>
	</div>
</div>
	

<?php echo $form->textFieldRow($user, 'email') ?>

<?php echo $form->textFieldRow($user,'firstname'); ?>

<?php echo $form->textFieldRow($user,'lastname'); ?>


<div class="row">
	<div class="span7">
		<?php echo $form->textFieldRow($user, 'phone') ?>
	</div>

	<div class="span5">
		<?php echo $form->checkBox($user->data, 'hide_phone')?>
		<?php echo $form->labelEx($user->data, 'hide_phone', array('style' => 'float: none;'))?>
	</div>
</div>

<h2>Экспертность</h2>
<div class="clearfix">
	<label><?php echo $user->getAttributeLabel('expert_type'); ?></label>
	<div class="input">
	<?php echo $form->dropDownList($user, 'expert_type', User::$expertNames); ?>
	</div>
</div>
<div class="clearfix">
	<label><?php echo $user->data->getAttributeLabel('expert_desc'); ?></label>
	<div class="input">
	<?php echo $form->textArea($user->data, 'expert_desc') ?>
	</div>
</div>
<div class="clearfix">
	<label>Услуги</label>
	<div class="input">
		<ul class="inputs-list">
		<?php
		if ( ! empty($checkedServices))
		foreach ($checkedServices as $serviceId => $val) {
			$srv = Service::model()->findByPk((int)$serviceId);
			?>
			<li>
				<label>
					<?php echo CHtml::checkBox('User[services]['.$serviceId.'][expert]', $val['expert']); ?>
					<span><?php echo $srv->name;?></span>
				</label>
			</li>
			<?php
		}
		?>
		</ul>
	</div>
</div>

<h2>Персональные данные</h2>

<?php if (!$user->getIsNewRecord()) : ?>
<?php $hasImage = !is_null($user->image_id); ?>
<div class="clearfix">
	<?php echo $user->getAttributeLabel('image'); ?>
	<div class="input">
		<?php echo CHtml::image( '/'.$user->getPreview( Config::$preview['crop_150'] ), '', array('id'=>'image-preview') ); ?><br />
		<?php echo CHtml::fileField('userImage', '', array('id'=>'user-image')); ?><br />
		<?php echo CHtml::link('Удалить фотографию','#', array('id'=>'delete-user-image')); ?>
	</div>
</div>
<?php endif; ?>

<div class="clearfix">
	<?php echo $form->labelEx($user,'city_id'); ?>
	<div class="input">
		<?php
		$htmlOptions = array('size'=>'20', 'onkeyup' => "if (this.value == '') $('#city_id').val('');");
		if ($user->city_id)
			$htmlOptions['readonly'] = 'readonly';

		$htmlOptions['id'] = 'User_city_id';
		$htmlOptions['class'] = 'span7';

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


<?php echo $form->radioButtonListRow($user->data, 'gender', array('M' => "Мужской", 'F' => 'Женский'), array('labelOptions' => array('style'=>'display:inline-block;')))?>


<div class="clearfix">
	<?php echo $form->labelEx($user->data, 'birthday')?>
	<div class="input">
		<?php $this->widget('zii.widgets.jui.CJuiDatePicker', array(
			'model'=>$user->data,
			'attribute' => 'birthday',
			'language'	=> 'ru',
			'options'=>array('dateFormat'	=> 'dd.mm.yy',),
			'htmlOptions'=>array(
			'style'=>'height:20px;'
			),
		));
		?>
		<?php echo $form->error($user->data,'birthday'); ?>
	</div>
</div>


<?php echo $form->textFieldRow($user->data, 'skype') ?>

<?php echo $form->textFieldRow($user->data, 'icq') ?>

<?php echo $form->textFieldRow($user->data, 'site') ?>

<?php echo $form->textFieldRow($user->data, 'twitter') ?>



<?php echo $form->textFieldRow($user->data, 'vkontakte') ?>

<?php echo $form->textFieldRow($user->data, 'odnoklassniki') ?>

<?php echo $form->textFieldRow($user->data, 'facebook') ?>



<?php echo $form->textAreaRow($user->data, 'about', array('class'=>'span7', 'rows' => 5)) ?>

<?php if (in_array($user->role, array(User::ROLE_SPEC_FIS, User::ROLE_SPEC_JUR)) && !empty($services) ) : ?>
	<h2>Специализация и услуги</h2>
	<table class="my_th">
		<tr>
			<td class="service_name">Услуга</td>
			<td class="price_range">Приоритетный ценовой сегмент</td>
			<td class="price_range">Дополнительный ценовой сегмент</td>
			<td class="service_exp  drop_down">Стаж</td>
		</tr>
	</table>

	<?php
		end($services);
		$last = key($services);

		reset($services);
		$first = key($services);
		?>
	<?php foreach ($services as $key => $service) : ?>

		<?php if ($service->parent_id == 0) : ?>

			<?php echo ($key != $first) ? '</table></div>' : ''; ?>

			<h5 class="myservices">
				<?php echo CHtml::checkBox('User[services][' . $service->id . '][id]', isset($checkedServices[$service->id]),array('class'=>'all_servise_check', 'id'=>'check_' . $service->id, 'value'=>$service->id)); ?>
				<a href="#"><?php echo $service->name; ?></a>
				<span class="list_status open"></span>
				<span class="act_service_count"></span>
			</h5>

			<?php echo '<div class="tbl_conteiner ' . (($key == $last) ? 'last' : ''). '"><table class="my_serv_list" id="list_' . $service->id . '">'; ?>

			<?php else : ?>
			<?php $experience = isset($checkedServices[$service->id]) ? $checkedServices[$service->id]['experience'] : '0'; ?>
			<?php $segment = isset($checkedServices[$service->id]) ? $checkedServices[$service->id]['segment'] : '0'; ?>
			<?php $segment_supp = isset($checkedServices[$service->id]) ? $checkedServices[$service->id]['segment_supp'] : '0'; ?>


			<tr <?php if (isset($checkedServices[$service->id])) echo 'class="active"'; ?> >
				<td class="service_name">
					<?php echo CHtml::checkBox('User[services][' . $service->id . '][id]',
					isset($checkedServices[$service->id]),
					array('class'=>'servise_check', 'value'=>$service->id)); ?>
					<?php echo $service->name; ?>
				</td>
				<?php // segment
				$class = isset($checkedServices[$service->id]['errorSegment']) ? 'validate_error' : '';
				?>
				<td class="service_exp  drop_down price_range required_field <?php echo $class; ?>">
					<span class="exp_current"><?php echo Config::$segmentName[ $segment ]; ?><i></i></span>
					<ul>
						<?php foreach (Config::$segmentName as $key_suka=>$value) :?>
						<li data-value="<?php echo $key_suka;?>"><?php echo $value;?></li>
						<?php endforeach; ?>
					</ul>
					<?php echo CHtml::hiddenField('User[services]['.$service->id.'][segment]', $segment, array('id'=>false)); ?>
				</td>
				<?php // segment_supp
				$class = isset($checkedServices[$service->id]['errorSegmentSupp']) ? 'required_field validate_error' : '';
				?>
				<td class="service_exp  drop_down price_range <?php echo $class; ?>">
					<span class="exp_current disabled"><?php echo Config::$segmentName[ $segment_supp ]; ?><i></i></span>
					<ul>
						<?php foreach (Config::$segmentName as $key_suka=>$value) :?>
						<li data-value="<?php echo $key_suka;?>"><?php echo $value;?></li>
						<?php endforeach; ?>
					</ul>
					<?php echo CHtml::hiddenField('User[services]['.$service->id.'][segment_supp]', $segment_supp, array('id'=>false)); ?>
				</td>
				<?php // experience
				$class = isset($checkedServices[$service->id]['errorExp']) ? 'validate_error' : '';
				?>
				<td class="service_exp drop_down required_field <?php echo $class; ?>">
					<span class="exp_current"><?php echo Config::$experienceType[ $experience ]; ?><i></i></span>

					<ul>
						<?php foreach (Config::$experienceType as $key_suka=>$value) :?>
						<li data-value="<?php echo $key_suka;?>"><?php echo $value;?></li>
						<?php endforeach; ?>
					</ul>
					<input type="hidden" value="<?php echo $experience; ?>" id = "servece_<?php echo $service->id; ?>" name='User[services][<?php echo $service->id; ?>][experience]'>
				</td>
			</tr>
			<?php endif; ?>

		<?php echo ($key == $last) ? '</table></div>' : ''; ?>


	<?php endforeach; ?>
<?php endif; ?>




<h2>Настройки аккаунта</h2>


<?php echo $form->checkBoxRow($user->data, 'notice_private_message')?>

<?php echo $form->checkBoxRow($user->data, 'ban_comment')?>




<h2>Изменение статуса</h2>

<div class="row">
	<div class="span7">
		<?php echo $form->dropDownListRow($user, 'status', Config::$userStatus) ?>
	</div>
	<div class="span4">
		<?php echo CHtml::dropDownList('ban-time', '', array(''=>'выберите время бана') + Config::$banTimes, array('style'=>'display:none', 'class'=>'ban-time')); ?>
		<span class="ban-time" style="display:none"><?php echo 'Будет разбанен: ' . date('d.m.Y H:i', $user->data->ban_end_time); ?></span>
	</div>
</div>

<div class="clearfix">
	<?php echo CHtml::label('Написать персональное собщение', 'msg_type') ?>
	<div class="input">
		<?php echo CHtml::dropDownList('msg_type', '1', array('1'=>'Через портал', '2'=>'По почте')) ?>
		<?php echo CHtml::tag('br')?>
		<?php echo CHtml::textArea('msg_body', '',array('class'=>'span7', 'rows' => 5))?>
		<?php echo $form->error($user, 'status') ?>
	</div>
</div>

<div class="clearfix">
	<?php echo CHtml::label('Написать внутренний комментарий', 'system_message') ?>
	<div class="input">
		<?php echo CHtml::textArea('system_message', '',array('class'=>'span7', 'rows' => 5))?>
	</div>
</div>

<hr>

<div class="actions" data-spy="affix" data-offset-top="550" style="bottom: -20px; width: 100%;">
	<?php echo CHtml::submitButton('Сохранить', array('class' => 'btn large primary'))?>
	<?php echo CHtml::submitButton('Применить', array('name' => 'save_stay', 'class' => 'btn primary', 'onclick' => 'var act = $(form).prop("action"); var sc = $(window).scrollTop(); $(form).prop("action", act+"#/"+"scroll/"+sc);  '))?>
	<?php echo CHtml::Button('Отменить', array('onclick'=>'document.location = \''.Yii::app()->user->returnUrl.'\'', 'class' => 'btn'))?>
</div>

<script type="text/javascript">
	$(function(){
		var hash = document.location.hash.split('/');
		if (hash[1] == 'scroll') {
			$(window).scrollTop(parseInt(hash[2]));
		}
	});
</script>


<?php $this->endWidget(); ?>
