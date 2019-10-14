<?php
$this->breadcrumbs=array(
	'Управление пользователями' => array('userlist'),
	'Список пользователей' => array('userlist'),
	'Редактирование'
);
?>
<?php Yii::app()->clientScript->registerCoreScript('jquery'); ?>
<?php Yii::app()->clientScript->registerScriptFile('/js/admin/userform.js'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/admin/userform.css'); ?>

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


<h1>Редактирвоание пользователя #<?php echo $user->id;?> &mdash; <br> <?php echo $user->name;?></h1>

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

</div>


<h2>Регистрационные данные</h2>

<table class="detail-view">
	<tbody>
	<tr>
		<th>Логин / Никнейм</th>
		<td><?php echo $user->login;?></td>
	</tr>
	<tr>
		<th>ФИО / Название компании</th>
		<td><?php echo $user->name;?></td>
	</tr>
	<tr>
		<th>E-mail</th>
		<td><?php echo $user->email;?></td>
	</tr>
	<tr>
		<th>Телефон</th>
		<td><?php echo $user->phone;?></td>
	</tr>
	<tr>
		<th>Кем приглашен</th>
		<td><?php echo !is_null($user->referrer) ? CHtml::link($user->referrer->login, Yii::app()->createUrl("/admin/user/view/", array("id"=>$user->referrer->id))) : "";?></td>
	</tr>
	</tbody>
</table>
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

<table class="detail-view">
	<tbody>
	<tr>
		<th>Город</th>
		<td><?php echo $user->getCityFull(); ?></td>
	</tr>

	<tr>
		<th>Пол</th>
		<td><?php echo $user->data->gender; ?></td>
	</tr>

	<tr>
		<th>Дата рождения</th>
		<td><?php echo $user->data->birthday; ?></td>
	</tr>

</table>



<?php
$form = $this->beginWidget('ext.bootstrap.widgets.BootActiveForm', array(
	'id' => 'user-create-form',
	'enableAjaxValidation' => false,
	'htmlOptions' => array('class' => 'form-stacke')
));
	echo $form->textAreaRow($user->data, 'about', array('class'=>'span7', 'rows' => 5));
?>

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


<hr>

<div class="actions">
	<?php echo CHtml::submitButton('Сохранить', array('class' => 'btn large primary'))?>
	<?php echo CHtml::Button('Отменить', array('onclick'=>'document.location = \''.Yii::app()->user->returnUrl.'\'', 'class' => 'btn large'))?>
</div>
<?php $this->endWidget(); ?>


