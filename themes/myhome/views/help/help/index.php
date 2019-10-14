<?php $this->pageTitle = Help::$titleName[$baseId].' — MyHome.ru'?>

<?php
// ----- ОПТИМИЗАЦИЯ ДЛЯ СОЦСЕТЕЙ -----

Yii::app()->openGraph->title = 'Помощь по сайту';
Yii::app()->openGraph->description = 'Со всеми вопросами и предложениями по работе сайта вы можете обратиться в службу поддержки, используя форму обратной связи';
Yii::app()->openGraph->image = Yii::app()->homeUrl.'/img/logo-tb.png';
Yii::app()->openGraph->image = Yii::app()->homeUrl.'/img/tmp/help/way1.png';
Yii::app()->openGraph->image = Yii::app()->homeUrl.'/img/tmp/help/way2.png';
Yii::app()->openGraph->image = Yii::app()->homeUrl.'/img/tmp/help/way3.png';

Yii::app()->openGraph->renderTags();
?>

<div class="pathBar">
	<?php $this->widget('application.components.widgets.EBreadcrumbs', array(
		'links'=>array(),
	));?>
	<h1>Помощь по сайту</h1>

	<div class="spacer"></div>
</div>
<div class="help_page">
	<?php
	$this->widget('help.components.HelpBar.HelpBar', array(
		'baseId' => $baseId,
	));
	?>
	<div id="right_side" class="new_template">
		<div class="help_ways">
			<div class="item">
				<img src="/img/tmp/help/way1.png">
				<strong>Вы можете найти ответ самостоятельно</strong>
			</div>
			<div class="-feedback item feedback-handler">
				<img src="/img/tmp/help/way2.png">
				<strong><span>Написать</span><br> в службу поддержки</strong>
			</div>
			<div class="clear"></div>
		</div>
		<h2 class="block_head">Популярные вопросы</h2>
		<div class="popular_questions">
			<?php
			$count = round(count($faqs)/2);
			$cnt = 0;
			echo CHtml::openTag('ul');
			foreach ($faqs as $faq) {
				if ($cnt == $count) {
					echo CHtml::closeTag('ul');
					echo CHtml::openTag('ul');
				}
				$cnt++;
				$link = CHtml::link($faq->question, $faq->link);
				echo CHtml::tag('li', array(), $link);
			}
			echo CHtml::closeTag('ul');
			?>
			<div class="clear"></div>
		</div>
	</div>
	<div class="clear"></div>
</div>
<div class="spacer-18"></div>
