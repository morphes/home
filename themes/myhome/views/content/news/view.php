<?php $this->pageTitle = $model->title.' — Новости проекта — MyHome.ru';?>
<?php $this->description = ''; ?>
<?php $this->keywords = ''; ?>

<div class="content news-page">
	<div class="pathBar">
		<?php $this->widget('application.components.widgets.EBreadcrumbs', array(
			'links'=>array(
			    'Новости проекта' => array('/content/news'),
			),
		));?>
		<?php echo CHtml::tag('h1', array(), $model->title, true); ?>
		<p class="date"><?php echo Yii::app()->getDateFormatter()->format('d MMMM yyyy', $model->public_time); ?></p>
	<div class="spacer"></div></div>

	<div class="article">
		<?php
			$patern="#<[\s]*myhomecut[\s]*>([^<]*)<[\s]*/myhomecut[\s]*>#i";
			$content = preg_replace($patern, "<a name='myhomecut'></a>", $model->content);
		?>

		<?php echo CHtml::tag('p', array(), $content, true) ?>
		<div class="news-nav cf">
			<?php $prevNews = $model->getPrevNews(); 
			$nextNews = $model->getNextNews(); 
			if (!is_null($prevNews)) : ?>
			<div class="news-nav-prev">
				<p>&larr; Предыдущая новость</p>
				<p><?php echo CHtml::link($prevNews->title, $this->createUrl('/content/news/view/id/'.$prevNews->id) ); ?></p>
				<?php echo CHtml::tag('p', array('class' => 'date'), Yii::app()->getDateFormatter()->format('d MMMM yyyy', $prevNews->public_time) ); ?>
			</div>
			<?php endif; 
			if (!is_null($nextNews)) : ?>
			<div class="news-nav-next">
				<p>Следующая новость &rarr;</p>
				<p><?php echo CHtml::link($nextNews->title, $this->createUrl('/content/news/view/id/'.$nextNews->id) ); ?></p>
				<?php echo CHtml::tag('p', array('class' => 'date'), Yii::app()->getDateFormatter()->format('d MMMM yyyy', $nextNews->public_time) ); ?>
			</div>
			<?php endif; ?>
		</div>
		
		<?php $this->widget('application.components.widgets.WComment', array(
		    'model' => $model,
		    'showRating' => false,
		    'showCnt' => 50,
		)); ?>
	</div>
	<div class="side">
		<?php $this->widget('ext.sharebox.EShareBox', array(
			'view' => 'news',
			// url to share, required.
			'url' => Yii::app()->request->hostInfo.Yii::app()->request->requestUri,

			// A title to describe your link, required.
			'title'=> $model->title,

			// A small message for post
			'message' => Amputate::getLimb($content, 500, '...'),
			'classDefinitions' => array(
				'livejournal' => 'ns-lj',
				'vkontakte' => 'ns-vk',
				'twitter' => 'ns-tw',
				'facebook' => 'ns-fb',
				'google+' => 'ns-gp',	    
			),
			'exclude' => array('odkl','pinterest'),
			'htmlOptions' => array('class' => 'social'),
		));?>

		<script>
			$(function(){
				var s = $('.side .social'),
					sOffset = s.offset().top,
					f = false;
				function scroller() {
					var offsetTop = $(document).scrollTop() + 20;
					if (!f && sOffset <= offsetTop) {
						s.addClass('social-fixed');
						f = true;
					} else if (f && sOffset > offsetTop) {
						s.removeClass('social-fixed');
						f = false;
					};
				};
				$(window).scroll(scroller);
			});
		</script>

	</div>
		
	<div class="spacer"></div>

</div>
<div class="spacer"></div>
