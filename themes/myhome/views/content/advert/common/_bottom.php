<div class="-col-4 -inset-top-dbl">

	<a href="mailto:sales@myhome.ru" class="-large -gray">sales@myhome.ru</a>
</div>
<div class="-col-4 -inset-top-dbl">
	<div class="-inset-top-hf -inset-bottom-hf"><a href="http://www.myhome.ru/files/MyHome.ru_Pravila_okazaniya_reklamnykh_uslug.docx" class="-icon -icon-doc -icon-skyblue -gray">Правила предоставления услуг</a></div>
	<div class="-inset-top-hf -inset-bottom-hf"><a href="http://www.myhome.ru/files/MyHome.ru_Tehnicheskie_trebovanija_dlja_reklamnyh_materialov.docx" class="-icon -icon-doc -icon-skyblue -gray">Требования к размещению</a></div>
</div>
<div class="-col-4 -inset-top-dbl">
</div>

<?php /** @var $form CActiveForm */
$form = $this->beginWidget('CActiveForm', array(
	'id'                     => 'storeoffer-form',
	'enableAjaxValidation'   => false,
	'enableClientValidation' => false,
	'htmlOptions'            => array(
		'class'   => '-col-9 -form-inline advert-form -hidden',
		'enctype' => 'multipart/form-data',
		'method'  => 'POST'
	)
)); ?>

<h2 class="-giant">Заказать услуги</h2>

<label class="-gutter-null">
	<?php echo $form->errorSummary($model);?>
	<strong class="-large -col-2">Выберите услуги</strong>
</label>

<div class="-col-6 -inset-top-hf -inset-left-hf">

	<label class="-checkbox -block">
		<?php echo CHtml::checkBox(
			'StoreOffer[banners]'
		); ?>
		<span>Баннеры</span>
	</label>

	<label class="-checkbox -block">
		<?php echo CHtml::checkBox(
			'StoreOffer[pr]'
		); ?>
		<span>PR-статьи</span>
	</label>

	<!--<label class="-checkbox -block">
		<?php /*echo CHtml::checkBox(
			'StoreOffer[click]'
		); */?>
		<span>Клики</span>
	</label>-->

</div>



<div class="-field-group">
	<label class="-gutter-null">
		<strong class = "-large -col-2">Компания / Город <i>*</i> </strong>
		<?php echo $form->textField($model, 'company', array(
			'class'       => '-col-3',
			'placeholder' => $model->getAttributeLabel('company')
		)); ?>
	</label>

	<?php // Город
	$htmlOptions = array();
	$htmlOptions['class'] = '-col-3';
	if ($model->getError('city_name')) {
		$htmlOptions['class'] .= ' error';
	}
	$htmlOptions['id'] = 'StoreOffer_city_name';
	$htmlOptions['placeholder'] = 'Город';

	$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
		'name'        => 'StoreOffer[city_name]',
		'sourceUrl'   => '/utility/autocompletecity',
		'value'       => $model->city_name,
		'options'     => array(
			'showAnim'  => 'fold',
			'minLength' => 3
		),
		'htmlOptions' => $htmlOptions
	));
	?>
</div>

<div class="-field-group">
	<label class="-gutter-null">
		<strong class = "-large -col-2">Контактные данные<i>*</i></strong>
		<?php echo $form->textField($model, 'company_phone', array(
			'class'       => '-col-3',
			'placeholder' => $model->getAttributeLabel('company_phone')
		)); ?>
	</label>
	<?php echo $form->textField($model, 'email', array(
		'class'       => '-col-3',
		'placeholder' => $model->getAttributeLabel('email')
	)); ?>
</div>
<div class="-field-group">
	<label class="-gutter-null">
		<strong class = "-large -col-2">Контактное лицо</strong>
		<?php echo $form->textField($model, 'name', array(
			'class'       => '-col-3',
			'placeholder' => $model->getAttributeLabel('name')
		)); ?>
	</label>
	<?php echo $form->textField($model, 'job', array(
		'class'       => '-col-3',
		'placeholder' => $model->getAttributeLabel('job')
	)); ?>
</div>
<label class="-gutter-null">
	<strong class = "-large -col-2">Адрес сайта</strong>
	<?php echo $form->textField($model, 'site', array(
		'class'       => '-col-6',
		'placeholder' => 'Укажите ссылку на сайт вашей компании, если он существует'
	)); ?>
</label>
<label class="-gutter-null">
	<strong class = "-large -col-2">Комментарий</strong>
	<?php echo $form->textArea($model, 'comment', array(
		'class'       => '-col-6',
		'rows'        => '6',
	)); ?>
</label>
<div class="-field-group">

    <label class="-gutter-null">Введите код с картинки
        <span class="required ">*</span>
    <?php $this->widget('CCaptcha', array(
        'captchaAction' => '/site/captchaWhite', 'buttonType' => 'link',
        'buttonOptions' => array('class' => '-icon-refresh-s -icon-gray'),
        'buttonLabel'   => '',
        'imageOptions'  => array('width' => 90, 'height' => 50, 'class' => '-col-2'),
    ))?>

    <?php echo CHtml::activeTextField($model, 'verifyCode', array('class' => 'required -col-3')) ?>
    </label>
</div>

<div class="-col-6 -skip-2">
	<span class="-block -gray">Нажав на кнопку, вы подтверждаете свое согласие с <a href="http://www.myhome.ru/files/MyHome.ru_Pravila_okazaniya_reklamnykh_uslug.docx" class="-red">условиями</a></span>
	<button type="submit" class="-button -button-skyblue -huge -semibold">Заказать</button>
</div>

<?php $this->endWidget(); ?>

<?php if($goodSave===false) : ?><script>adv.showForm()</script> <?php endif; ?>

<?php if($goodSave===true) :
	Yii::app()->clientScript->registerCssFile('/css/jquery.gritter.css');
	Yii::app()->clientScript->registerScriptFile('/js/jquery.gritter.min.js');
	?>
<script>
	$.gritter.add({
		title: 'Ваша заявка была успешно отправлена',
		text: 'Наш менеджер свяжется с вами в течении 2-ух рабочих дней',
		sticky: false,
		time: '4000',
		class_name: '',
		position: 'bottom-left'
	});
</script>
<?php endif; ?>
