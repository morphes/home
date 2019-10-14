<?php
/**
 * @var $user User
 */
$this->breadcrumbs=array(
	'Управление пользователями' => array('userlist'),
	'Список пользователей' => array('userlist'),
	'Просмотр'
);
?>


<?php if(Yii::app()->user->hasFlash('user-create-success')):?>
	<div class="flash-success">
	<?php echo Yii::app()->user->getFlash('user-create-success'); ?>
	</div>
<?php endif; ?>


<h1>Пользователь #<?php echo $user->id;?> &mdash; <?php echo $user->name;?></h1>


<div class="well" style="padding-bottom: 0px;">
	<?php $this->widget('application.components.widgets.WUsergroup', array('user'=>$user));?>
</div>

<?php $this->rightbar = $this->renderPartial('application.modules.admin.views.user._systemMessage', array('user'=>$user, 'messages' => $messages), true); ?>


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

<div style="margin: 10px 0 10px;">
        <?php echo CHtml::image('/' . $user->getPreview(Config::$preview['resize_190']), 'Аватар пользователя')?>
</div>

<table class="detail-view">
	<tbody>
		<tr>
			<th>Город</th>
			<td><?php echo $user->getCityFull(); ?></td>
		</tr>
                
                <tr>
			<th>О себе</th>
			<td><?php echo $user->data->about; ?></td>
		</tr>

		<tr>
			<th>Пол</th>
			<td><?php echo $user->data->gender; ?></td>
		</tr>

		<tr>
			<th>Дата рождения</th>
			<td><?php echo $user->data->birthday; ?></td>
		</tr>

		<tr>
			<th>Сайт</th>
			<td><?php echo $user->data->site; ?></td>
		</tr>

		<tr>
			<th>Twitter</th>
			<td><?php echo $user->data->twitter; ?></td>
		</tr>

		<tr>
			<th>Vkontakte</th>
			<td><?php echo $user->data->vkontakte; ?></td>
		</tr>

		<tr>
			<th>Odnoklassniki</th>
			<td><?php echo $user->data->odnoklassniki; ?></td>
		</tr>

		<tr>
			<th>Facebook</th>
			<td><?php echo $user->data->facebook; ?></td>
		</tr>

		<tr>
			<th>Skype</th>
			<td><?php echo $user->data->skype; ?></td>
		</tr>

		<tr>
			<th>ICQ</th>
			<td><?php echo $user->data->icq; ?></td>
		</tr>
	</tbody>
</table>
	


<h2>Статус</h2>

<table class="detail-view">
	<tbody>
		<tr>
			<th>Статус</th>
			<td><span class="label success"><?php echo Config::$userStatus[$user->status];?></span></td>
		</tr>
	</tbody>
</table>
<?php if ($user->role == User::ROLE_STORES_ADMIN) : ?>
<h2>Магазины администратора</h2>
<table class="detail-view">
	<thead>
	<tr>
		<th>ID</th>
		<th>Магазин</th>
		<th>Город</th>
	</tr>
	</thead>
	<tbody>
	<?php /** @var $store Store */
		foreach ($stores as $store) : ?>
		<tr>
			<td><?php echo $store->id; ?></td>
			<td><?php echo $store->name.' ('.$store->address.')'; ?></td>
			<td><?php echo $store->city->name; ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<?php endif; ?>


<div class="actions">
	<?php echo CHtml::button(
		'Редактировать',
		array(
			'onclick' => 'document.location = \''.$this->createUrl($this->id.'/update', array('id'=>$user->id)).'\'',
			'class' => 'btn primary'
		)
	);?>
	
	<?php echo CHtml::button(
		'Отправить код активации',
		array(
			'onclick'=>'document.location = \''.$this->createUrl($this->id.'/resendcode', array('id'=>$user->id)).'\'',
			'class' => 'btn danger'
		)
	);?>
</div>