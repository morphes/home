<?php $this->pageTitle = empty($article->meta_title)?$article->title : $article->meta_title; ?>
<?php $this->pageTitle.= ' — MyHome.ru'; ?>
<?php $this->description = $article->meta_desc; ?>
<?php $this->keywords = $article->meta_keyword; ?>
<?php Yii::app()->clientScript->registerCssFile('/css/article.css'); ;?>


<div class="pathBar">
	<?php
	$this->widget('application.components.widgets.EBreadcrumbs', array(
		'links' => array(),
	));
	?>
	<h1 style="width: 700px"><?php echo $article->title; ?></h1>
	<?php 
	if ($article->sharebox) {
		$this->widget('ext.sharebox.EShareBox', array(
			// url to share, required.
			'url' => Yii::app()->request->hostInfo.Yii::app()->request->requestUri,

			// A title to describe your link, required.
			'title'=> $article->title,

			// A small message for post
			'message' => $article->desc,
			'exclude' => array('odkl','pinterest'),
		));
	}
	?>

	<div class="spacer"></div>
</div>

<div class="content-color">
<?php echo $article->content; ?>
</div>

<div class="spacer"></div>