<?php $this->pageTitle = 'Новости проекта — MyHome.ru'?>
<?php $this->description = ''; ?>
<?php $this->keywords = ''; ?>

<div class="content news-page"> 

	<div class="pathBar">
		<?php $this->widget('application.components.widgets.EBreadcrumbs', array(
			'links'=>array(),
		));?>
		<h1>Новости проекта <a href="/content/feed/rss2"><img src="/img/rss.png" width="16" height="16" alt="RSS"></a></h1>

		<div class="spacer"></div>
	</div>

	<div class="article">
		<?php
		$this->widget('zii.widgets.CListView', array(
		    'dataProvider' => $dataProvider,
		    'itemView' => '_news',
		    'template'=> "{sorter}\n{items}",
		));
		?>
		<div class="pages">
		<?php 
		$this->widget('application.components.widgets.CustomPager2', array(
		    'pages' => $dataProvider->getPagination(),
		));
		?>
		</div>
	</div>
	
	<div class="side side_right">

		<p class="side_right_image"><img src="/img/tv.png" width="143" height="142" alt=""></p>

		<div class="side_right_info">
			<p>Как вы уже заметили, на MyHome есть масса всего интересного.
				Вот здесь, например, мы рассказываем о развитии портала, о том, чем он живет.</p>
			<p>И мы приглашаем вас к обсуждению новостей MyHome!
				Нам важно каждое ваше мнение. Ведь этот проект &mdash; для вас.</p>
		</div>
	</div>



	<div class="spacer"></div>

</div>

