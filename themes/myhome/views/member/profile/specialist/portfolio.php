<?php $this->pageTitle = 'Портфолио: ' . (!empty($currentService) ? $currentService->name .' — ' : '') . $user->name . ' — MyHome.ru'?>

<?php
if ($owner) {
	Yii::app()->getClientScript()->registerScript('sorting', 'user.sortPortfolioProjects('.$currentServiceId.');', CClientScript::POS_READY);
}

Yii::app()->clientScript->registerCssFile('/css/style.css');
Yii::app()->clientScript->registerScriptFile('/js/CUser.js');

// ----- ОПТИМИЗАЦИЯ ДЛЯ СОЦСЕТЕЙ -----

Yii::app()->openGraph->title = $user->name;
Yii::app()->openGraph->description = strip_tags($user->data->about);
Yii::app()->openGraph->image = Yii::app()->homeUrl.'/img/logo-tb.png';
Yii::app()->openGraph->image = Yii::app()->homeUrl.'/'.$user->getPreview(User::$preview['crop_180']);

foreach($projects as $data) {
	// TODO: убрать после перевода картинок
	if ($data instanceof Architecture) {
		Yii::app()->openGraph->image = $data->getPreview('crop_210');
		continue;
	}
	Yii::app()->openGraph->image = Yii::app()->homeUrl.'/'.$data->getPreview(Config::$preview['crop_210']);
}

Yii::app()->openGraph->renderTags();
?>


<?php $this->renderPartial('//idea/portfolio/_serviceNavigator', array('user' => $user, 'currentServiceId'=>$currentServiceId)); ?>

<?php if ($owner && !Yii::app()->getUser()->hasDbFlash('hide_portfolio_notice')) : ?>
	<div class="sort">
		<p>
			Теперь вы можете менять порядок работ по своему усмотрению.<br>
			Просто перетащите работу в нужное место зажав левую кнопку мыши.
		</p>
		<i class="close"></i>
	</div>
<?php endif; ?>

<?php $this->widget('application.components.widgets.ProjectList.ProjectListWidget',
	array(
		'projects'=>$projects,
		'renderCounters'=>true,
		'galleryAdditionalClass'=>'user_portfolio'
	)
); ?>