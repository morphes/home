<?php
$this->pageTitle = 'Магазинам — MyHome.ru';

Yii::import('application.modules.content.models.Content');

/**
 * @var $model StoreOffer
 */


/** @var $cs CustomClientScript */
$cs = Yii::app()->getClientScript();

$cs->registerScriptFile('/js-new/common.js');
$cs->registerScriptFile('/js-new/jquery.simplemodal.1.4.4.min.js');

$cs->registerScriptFile('/js/scroll.js');
$cs->registerScriptFile('/js/f.js');
$cs->registerScriptFile('/js/functions.js');
$cs->registerScriptFile('/js/CCatalog.js');
?>

<div class="-grid">
	<!-- breadcrumbs //-->
	<div class="-col-12 -gutter-bottom-null">
		<ul class="-menu-inline -breadcrumbs -gutter-top-null">
			<li><a>Главная</a></li>
		</ul>
	</div>
	<!-- eof breadcrumbs //-->

	<!-- title //-->
	<div class="-col-8">
		<h1 class="-gutter-bottom-hf">Магазинам</h1>
	</div>
	<!-- eof title //-->

	<!-- title right sidebar //-->
	<div class="-col-4 -text-align-right"></div>
	<!-- eof title right sidebar //-->
	<hr class="-col-12 -gutter-bottom-dbl">
</div>


<?php if ($goodSave) { ?>
	<?php
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
<?php } ?>


<div class="-grid">


	<div class="-col-8">
		<h2>Создайте свой магазин на MyHome</h2>
		<p>Ежедневно на MyHome в поисках товаров для дома заходит 20 000 пользователей. Это люди, которые уже делают ремонт или только планируют им заняться, все они — ваши потенциальные клиенты.</p>
		<p>Откройте на сайте страницу своего магазина, разместите как можно больше товаров, укажите их цены, и внимание аудитории будет вашим.</p>
		<hr class="-spacer -gutter-bottom-dbl">

		<?php /** @var $form CActiveForm */
		$form = $this->beginWidget('CActiveForm', array(
			'id'                     => 'storeoffer-form',
			'enableAjaxValidation'   => false,
			'enableClientValidation' => false,
			'htmlOptions'            => array(
				'class'   => '-form-block -tinygray-box offer-form',
				'enctype' => 'multipart/form-data',
				'method'  => 'POST'
			)
		)); ?>

			<h2 class="-gutter-top-null">Заявка на создание магазина</h2>

			<?php echo $form->errorSummary($model); ?>

			<div class="-field-group">
				<label>
					<strong>Компания / Город <i>*</i></strong>
					<?php echo $form->textField($model, 'company', array(
						'class'       => '-col-3 -small',
						'placeholder' => $model->getAttributeLabel('company')
					)); ?>
				</label>

				<?php // Город
				$htmlOptions = array();
				$htmlOptions['class'] = '-col-3 -small';
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
				<label>
					<strong>Контактные данные <i>*</i></strong>
					<?php echo $form->textField($model, 'company_phone', array(
						'class'       => '-col-3 -small',
						'placeholder' => $model->getAttributeLabel('company_phone')
					)); ?>
				</label>
				<?php echo $form->textField($model, 'email', array(
					'class'       => '-col-3 -small',
					'placeholder' => $model->getAttributeLabel('email')
				)); ?>
			</div>
			<div class="-field-group">
				<label>
					<strong>Контактное лицо <i>*</i></strong>
					<?php echo $form->textField($model, 'name', array(
						'class'       => '-col-3 -small',
						'placeholder' => $model->getAttributeLabel('name')
					)); ?>
				</label>
				<?php echo $form->textField($model, 'job', array(
					'class'       => '-col-3 -small',
					'placeholder' => $model->getAttributeLabel('job')
				)); ?>
			</div>
			<label>
				<strong>Адрес сайта</strong>
				<?php echo $form->textField($model, 'site', array(
					'class'       => '-col-6 -small',
					'placeholder' => 'Укажите ссылку на сайт вашей компании, если он существует'
				)); ?>
			</label>
			<label>
				<strong>Комментарий</strong>
				<?php echo $form->textArea($model, 'comment', array(
					'class'       => '-col-6',
					'rows'        => '6',
				)); ?>
			</label>
			<div class="-gutter-top-hf">
				<label class="-checkbox">
					<?php echo CHtml::checkBox(
						'StoreOffer[accept_rule]',
						$model->accept_rule,
						array(
							'class' => $model->getError('accept_rule') ? 'error' : ''
						)
					); ?>
					<span>Принимаю <span class="-acronym -red" id="toggleOfferPopup">условия</span><span class="-red"> *</span></span>
				</label>

			</div>
			<div class="-text-align-center -inset-top -inset-bottom">
				<button type="submit" class="-button -button-skyblue -large -semibold">Отправить</button>
			</div>

		<?php $this->endWidget(); ?>

		<script>
			$(function(){
				CCommon.offerPopup();
			})
		</script>
	</div>

	<div class="-col-3 -skip-1">

		<h4 class="-large -gutter-bottom-hf">Контакты</h4>
		<a class="-icon-mail -red" href="mailto:sales@myhome.ru">sales@myhome.ru</a>

		<hr class="-spacer -gutter-top">
		<h4 class="-large -gutter-bottom-hf -gutter-top-dbl">Презентации</h4>
		<a class="" href="http://www.myhome.ru/files/rate_universal.pdf">Тарифы</a>

	</div>
</div>

<div class="-block -gutter-top -gutter-bottom-dbl -inset-bottom-dbl"></div>


<?php
 /* ----------------------------------------------------------------------------
  *  Правила оказания рекламных услуг myhome.ru
  * ----------------------------------------------------------------------------
  */
?>
<div class="-hidden">
	<div class="popup popup-offer-agreement" id="popupOfferAgreement">
		<div class="popup-header">
			<div class="popup-header-wrapper">Правила оказания рекламных услуг myhome.ru</div>
		</div>
		<div class="popup-body">
			<div class="list-wrapper page-agreement">
				<div class="list-inner" id="offerBody">
					<div class="scrollbar"><div class="track"><div class="thumb"><div class="end"></div></div></div></div>
					<div class="viewport" style="height:500px">
						<div class="overview">
							<?php echo Content::getContentByAlias('advertising'); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

