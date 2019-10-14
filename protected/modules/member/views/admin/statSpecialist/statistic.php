<?php
$this->breadcrumbs = array(
	'Магазины' => array('index'),
	'Статистика',
);
?>

<h1>Статистика специалиста "<?php echo $model->name; ?>"</h1>



<?php
//Формируем массив по которым
//Возможна сортировка
$listCity = array();
$listCity = StatUserService::model()->getListCity($model->id);

$this->renderPartial('_search', array(
	'model'    => $model,
	'timeTo'   => $timeTo,
	'timeFrom' => $timeFrom,
	'listCity' => $listCity,
	'city'     => $city
)); ?>

<br>
<br>
<br>

<h3>Общие данные</h3>

Просмотр профиля: <?php echo $statSpecialistModel[StatSpecialist::TYPE_HIT_PROFILE]; ?>
<br>
Просмотр стр. "Контакты": <?php echo $statSpecialistModel[StatSpecialist::TYPE_HIT_CONTACTS]; ?>
<br>
Просмотр проектов: <?php echo $statProjectModel[StatProject::TYPE_PROJECT_VIEW]; ?>
<br>
Добавлений в избранное <?php echo $statProjectModel[StatProject::TYPE_PROJECT_TO_FAVORITES]; ?>
<br>
Кликов связаться со мной <?php echo $statSpecialistModel[StatSpecialist::TYPE_CLICK_CONTACT_ME]; ?>
<br>
Отправлено сообщений специалистам <?php echo $statSpecialistModel[StatSpecialist::TYPE_SEND_MESSAGE_TO_SPECIALIST]?>

<h3>Список специалистов</h3>

Показов профиля: <?php echo $statSpecialistModel[StatSpecialist::TYPE_SHOW_PROFILE_IN_LIST]; ?>
<br>
Кликов по профилю: <?php echo $statSpecialistModel[StatSpecialist::TYPE_CLICK_PROFILE_IN_LIST]; ?>
<br>
CTR: <?php
if($statSpecialistModel[StatSpecialist::TYPE_SHOW_PROFILE_IN_LIST] > 0)
{
	echo round(($statSpecialistModel[StatSpecialist::TYPE_CLICK_PROFILE_IN_LIST]/$statSpecialistModel[StatSpecialist::TYPE_SHOW_PROFILE_IN_LIST])*100, 2) .' %';
}
else echo '0 %'?>


<br>
<br>
<h3>Список проектов в разделе идеи</h3>
Показов проектов: <?php echo $statProjectModel[StatProject::TYPE_SHOW_PROJECT_IN_LIST]; ?>
<br>
Кликов на проект: <?php echo $statProjectModel[StatProject::TYPE_CLICK_PROJECT_IN_LIST]; ?>
<br>
CTR: <?php
if($statProjectModel[StatProject::TYPE_SHOW_PROJECT_IN_LIST] > 0)
{
	echo round(($statProjectModel[StatProject::TYPE_CLICK_PROJECT_IN_LIST]/$statProjectModel[StatProject::TYPE_SHOW_PROJECT_IN_LIST])*100, 2) .' %';
}
else echo '0 %';
?>
<br>
<br>
<?php




$this->widget('ext.bootstrap.widgets.BootGridView',array(
	'id'=>'stat-service-grid',
	'dataProvider'=>$statUserService,
	'columns'=> array(
		array('name'=>'Услуга',
		      'value'=>'Service::model()->findByPk($data->service_id)->name'
	          	),
		array('name'=>'Показов',
		      'value'=>'$data->getViewData('.StatUserService::TYPE_SHOW_PROFILE_SERVICE.')',
		),
		array('name'=>'Кликов',
		      'value'=>'$data->getViewData('.StatUserService::TYPE_CLICK_PROFILE_SERVICE.')',
		),
		array('name'=>'CTR %',
		      'value'=>'($data->getViewData('.StatUserService::TYPE_SHOW_PROFILE_SERVICE.')>0) ?
			      round($data->getViewData('.StatUserService::TYPE_CLICK_PROFILE_SERVICE.')/$data->getViewData('.StatUserService::TYPE_SHOW_PROFILE_SERVICE.')*100, 2)
			      : 0',
		)
	)
));



$this->widget('ext.bootstrap.widgets.BootGridView',array(
'id'=>'stat-specialist-grid',
'dataProvider'=>$statProjectModelDp,
'columns'=> array(
	array('name'=>'id',
	      'type'=>'raw',
	      'value' => 'CHtml::link(CHtml::encode($data->model_id), $data->getLink(), array(\'target\'=>\'_blank\'))'),
	array('name'=>'В идеях?',
	      'value' => '($data->getProjectStatus()) ? \'+\' : \'-\' '),
	array('name'=>'Модель',
	      'value' => '$data->model'),
	array(
		'name'=>'Просмотров',
		'value'=>'$data->getViewData('.StatProject::TYPE_PROJECT_VIEW.')',
	),
	array(
		'name'=>'Показов',
		'value'=>'$data->getViewData('.StatProject::TYPE_SHOW_PROJECT_IN_LIST.')',
	),
	array(
		'name'=>'Добавлений в избранное',
		'value'=>'$data->getViewData('.StatProject::TYPE_PROJECT_TO_FAVORITES.')',
	),
	array(
		'name'=>'Кликов',
		'value'=>'$data->getViewData('.StatProject::TYPE_CLICK_PROJECT_IN_LIST.')',
	),
	array(
		'name'=>'CTR кликов %',
		'value'=>'($data->getViewData('.StatProject::TYPE_SHOW_PROJECT_IN_LIST.')>0) ?
		 round($data->getViewData('.StatProject::TYPE_CLICK_PROJECT_IN_LIST.')/$data->getViewData('.StatProject::TYPE_SHOW_PROJECT_IN_LIST.')*100, 2)
		 : 0',
	),
	array(
		'name'=>'CTR добавлений в избранное',
		'value'=>'($data->getViewData('.StatProject::TYPE_SHOW_PROJECT_IN_LIST.')>0) ?
		 round($data->getViewData('.StatProject::TYPE_PROJECT_TO_FAVORITES.')/$data->getViewData('.StatProject::TYPE_SHOW_PROJECT_IN_LIST.')*100, 2)
		 : 0',
	),
	)));

?>














