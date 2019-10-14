<?php $this->pageTitle = 'MyHome.ru — портал для поиска товаров, идей и специалистов в области ремонта и благоустройства дома'?>
<?php $this->description = 'Ваш интернет-помощник на всех этапах создания домашнего уюта — от формирования образа дома до воплощения задуманного'; ?>
<?php $this->keywords = ''; ?>

<?php
// ----- ОПТИМИЗАЦИЯ ДЛЯ СОЦСЕТЕЙ -----

Yii::app()->openGraph->title = 'MyHome.ru';
Yii::app()->openGraph->description = 'Ваш путеводитель в мире благоустройства и ремонта дома';
Yii::app()->openGraph->image = Yii::app()->homeUrl.'/img/logo-tb.png';

Yii::app()->openGraph->renderTags();
?>

<div class="spacer-18"></div>
<?php
Yii::import('application.modules.catalog.models.Product');
Yii::import('application.modules.catalog.models.Store');
Yii::import('application.modules.idea.models.Idea');
Yii::import('application.modules.catalog.models.Vendor');
Yii::import('application.modules.admin.models.UnitProduct');
?>

<div class="slogan_wrapper">
	<span>Ваш путеводитель в мире благоустройства и ремонта дома</span>
	<p>
		<?php echo CHtml::link(
		CFormatterEx::formatNumeral(Product::countAll(), array('товар', 'товара', 'товаров')),
		Yii::app()->createUrl('/catalog')
		);?>
		от
		<?php echo CFormatterEx::formatNumeral(Store::countAll(), array('магазина', 'магазинов', 'магазинов'));?>,

		<?php echo CHtml::link(
		CFormatterEx::formatNumeral(User::getSpecialistsQuantity(), array('специалист', 'специалиста', 'специалистов')),
		'/specialist'
		);?> по ремонту,

		<?php echo CHtml::link(
		CFormatterEx::formatNumeral(Idea::getIdeasPhotoQuantity(), array('фотография идей', 'фотографии идей', 'фотографий идей')),
		'/idea'
		);?> интерьеров,

		интересные <a href="/journal/knowledge">статьи</a> и <a href="/journal/news">новости</a>
	</p>
</div>



<?php
// Виджет КАТАЛОГа
echo $this->renderPartial('//widget/unit/catalog'); ?>


<div class="wrapper">

	<div class="main_left">
		<div class="left_block">
			<?php $this->renderPartial('//widget/unit/journal'); ?>
		</div>
		<div class="left_block">
			<?php $this->renderPartial('//widget/unit/forum'); ?>
		</div>
	</div>
	<div class="main_right">
		<div class="right_block ">
			<?php $this->widget('application.components.widgets.units.UDesigner', array('pageNumber' => $pageNumber)); ?>
		</div>
		<div class="right_block">
			<?php $this->widget('application.components.widgets.units.UIdea', array('pageNumber' => $pageNumber)); ?>
		</div>
	</div>

	<div class="clear"></div>
	<div class="spacer-30"></div>
</div>