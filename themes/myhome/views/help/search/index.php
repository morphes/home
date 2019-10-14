<?php $this->pageTitle = 'Поиск — '.Help::$titleName[$baseId].' — MyHome.ru'?>

<div class="pathBar">
	<?php $this->widget('application.components.widgets.EBreadcrumbs', array(
		'links'=>array(
			'Помощь по сайту' => $this->createUrl('/help/help/index', array('baseId'=>$baseId)),
		),
	));?>
	<h1>Поиск по разделу</h1>
	<div class="ask_question">

		<i></i><span class="-feedback feedback-handler">Задать вопрос</span>
	</div>
	<div class="spacer"></div>
</div>
<div class="help_page">
	<?php
	$this->widget('help.components.HelpBar.HelpBar', array(
		'baseId' => $baseId,
		'query' => $query,
	));
	?>


	<div id="right_side" class="new_template">

		<div class="search_result">
			<?php $resultCount = $dataProvider->getItemCount(); ?>
			<h2 class="block_head"><?php echo CFormatterEx::formatNumeral($resultCount, array('Найдена', 'Найдены', 'Найдено'), true) .' '. CFormatterEx::formatNumeral($resultCount, array('статья', 'статьи', 'статей')); ?> по запросу «<?php echo $query; ?>»</h2>
			<?php $data = $dataProvider->getData();
			$replaceText = CHtml::tag('span', array('class'=>'search_word'), $query);
			foreach ($data as $article) : ?>
				<div class="item">
					<?php $text = str_replace($query, $replaceText, $article->name); ?>
					<?php echo CHtml::link( $text, $article->getFrontLink() ); ?>
					<p><?php echo $article->data; ?></p>
				</div>
			<?php endforeach; ?>
		</div>

	</div>
	<div class="clear"></div>
</div>
<div class="spacer-18"></div>