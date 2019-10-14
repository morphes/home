<?php $this->pageTitle = $article->name .' — '.Help::$titleName[$section->base_path_id].' — MyHome.ru'?>

<div class="pathBar">
	<?php $this->widget('application.components.widgets.EBreadcrumbs', array(
		'links'=>array(
			'Помощь по сайту' => $this->createUrl('/help/help/index', array('baseId'=>$section->base_path_id)),
		),
	));?>
	<h1><?php echo $section->name; ?></h1>
	<div class="ask_question">
		Бесплатный телефон поддержки <span></span>
		<i></i><span class="-feedback feedback-handler">Задать вопрос</span>
	</div>
	<div class="spacer"></div>
</div>
<div class="help_page">
	<?php
	$this->widget('help.components.HelpBar.HelpBar', array(
		'baseId' => $section->base_path_id,
		'articleId' => $article->id,
	));
	?>
	<div id="right_side" class="new_template">

		<div class="section_descript">
			<?php echo CHtml::tag('h2', array('class'=>'block_head'), $article->name); ?>
			<?php echo CHtml::tag('p', array(), $article->data); ?>
			<?php
			if (!empty($chapters)) {
				echo CHtml::openTag('ul', array('class'=>'section_content'));
				foreach ($chapters as $chapter) {
					echo CHtml::openTag('li');
					echo CHtml::link($chapter->name, $chapter->getFrontLink() );
					echo CHtml::closeTag('li');
				}
				echo CHtml::closeTag('ul');
			}
			?>
		</div>
		<div class="articles_list">
			<?php foreach ($chapters as $chapter) : ?>
			<div class="item" id="<?php echo $chapter->id; ?>">
				<h2><?php echo $chapter->name; ?><a name="<?php echo $chapter->anchor; ?>" href="<?php echo $chapter->getFrontLink(); ?>">#</a></h2>
				<?php echo $chapter->data; ?>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
	<div class="clear"></div>
</div>
<div class="spacer-18"></div>