<?php Yii::app()->getClientScript()->registerScriptFile('/js/scroll.js'); ?>

<script type="text/javascript">
	$(function(){
		forum.showRules();
	});
</script>
<div class="pathBar">
	<?php
	$this->widget('application.components.widgets.EBreadcrumbs', array(
		'links' => isset($breadCrumbs)
		           ? $breadCrumbs
			   : array(
				'Форум' => array('/forum')
			   ),
	));
	?>

	<?php // Выводим имя категории (нужен на странице топика)
	if (isset($categoryName))
		echo '<div class="category_name">'.$categoryName.'</div>';
	else
		echo '<h1>'.(isset($h1) ? $h1 : 'Форум').'</h1>';
	?>



	<span id="forum_rules">
		<i></i>
		<span class="forum_rules">Правила форума</span>
	</span>

	<div class="spacer"></div>
</div>
<div class="forum_index_page">
	<div class="shadow_block forum_top">
		<div class="forum_search">
			<form action="/forum/search">
				<input type="text" name="text" class="textInput" value="<?php echo CHtml::encode(Yii::app()->request->getParam('text'));?>"/>
				<input type="submit" value="&nbsp;"/>
			</form>
		</div>
		<ul class="forum_umenu">
			<?php
			// Класс для ссылок в случае, когда пользователь «Гость»
			$guestCls = (Yii::app()->user->isGuest) ? 'user_is_guest' : '';
			?>

			<?php
			// Класс текущего выделения
			$cls = ($this->action->id == 'mytopics') ? 'current' : '';
			$url = '/forum/mytopics';
			// Подсчитываем кол-во элементов
			$qnt = ForumTopic::model()->countByAttributes(
				array('author_id' => Yii::app()->user->id),
				'status = :st',
				array(':st' => ForumTopic::STATUS_PUBLIC)
			);
			?>
			<li class="<?php echo $cls;?>"><a href="<?php echo $url;?>" class="<?php echo $guestCls; ?>">Мои темы</a><span>(<?php echo $qnt;?>)</span></li>


			<?php
			$cls = ($this->action->id == 'myanswers') ? 'current' : '';
			$url = '/forum/myanswers';
			$qnt = ForumTopic::model()->countByAttributes(
				array('status' => ForumTopic::STATUS_PUBLIC),
				array(
					'join' => 'INNER JOIN (SELECT topic_id FROM forum_answer WHERE author_id = :aid GROUP BY topic_id) as tmp ON tmp.topic_id = t.id',
					'params'    => array(':aid' => Yii::app()->user->id)
				)
			);
			?>
			<li class="<?php echo $cls;?>"><a href="<?php echo $url;?>" class="<?php echo $guestCls; ?>">Мои ответы</a><span>(<?php echo $qnt;?>)</span></li>
		</ul>

		<?php /* // Временно коментим кнопку подписки
		<div class="forum_subscribe">
			<i></i>
			<span>Подписаться на новые темы</span>
		</div>
 		*/ ?>

		<div class="forum_new_topic">
			<div class="btn_conteiner">
				<?php echo CHtml::link('Создать тему', '/forum/create', array('class' => 'btn_grey'));?>
			</div>
		</div>
		<div class="clear"></div>
	</div>
	<div id="left_side" class="new_template">
		<div class="red_menu">
			<p>Разделы форума</p>
			<?php
			if ($sections) {
				echo CHtml::openTag('ul');
				foreach($sections as $section) {

					$liOptions = array();
					if (isset($activeSection) && $activeSection == $section->id)
						$liOptions['class'] = 'current';

					echo CHtml::openTag('li', $liOptions);
					echo CHtml::link($section->name, $section->getElementLink());
					echo CHtml::tag('span', array(), ForumTopic::model()->countByAttributes(array('section_id' => $section->id), 'status = :st', array(':st' => ForumTopic::STATUS_PUBLIC)));
					echo CHtml::closeTag('li');
				}
				echo CHtml::closeTag('ul');
			}
			?>
			<script type="text/javascript">
				forum.drowTriangle();
			</script>
		</div>
		<!--баннер-->
		<div class="-gutter-top-dbl -gutter-bottom-dbl -relative"><?php $this->widget('application.components.widgets.banner.BannerWidget', array(
				'section'=>Config::SECTION_FORUM,
				'type'=>2
		)); ?></div>

		<div class="-gutter-top-dbl -gutter-bottom-dbl">
			<?php
			// Яндекс директ
            Yii::app()->controller->renderPartial('//widget/profitpartner/profit_partner_vertical');
			?>

		</div>

	</div>
	<div id="right_side" class="new_template">

		<?php echo $content;?>

	</div>
	<div class="clear"></div>
</div>

<div class="spacer-18"></div>


<?php // ***----- ПОПАПЫ -----*** ?>


<?php
// Рендерим попап с правилами форума.
// Выводится в //layouts/main
$rules = Content::model()->findByAttributes(array('alias' => 'forum_rules'));
if ($rules) {
	?>
	<div class="-hidden">
		<div class="popup popup-agreement" id="popup-agreement">
			<div class="popup-header">
				<div class="popup-header-wrapper">
					<?php echo $rules->title; ?>
				</div>
			</div>
			<div class="popup-body">
				<div class="list-wrapper page-agreement"><div class="list-inner" id="forum-rules">
					<div class="scrollbar"><div class="track"><div class="thumb"></div></div></div>
					<div class="viewport">
						<div class="overview topics_list">
							<?php echo $rules->content; ?>
						</div>
					</div>
				</div></div>
			</div>
		</div>
	</div>
	<?php
}
?>

<?php if (Yii::app()->user->isGuest) : ?>
	<?php
	// Если пользователь не авторизованный — выводим попап который предлагает авторизацию.
	// Нужен при клике на «Мои темы» и «Мои ответы»
	?>
	<div class="-hidden">
		<div id="popup-message-guest" class="popup popup-message-guest">
			<div class="popup-header">
				<div class="popup-header-wrapper">
					Форум
				</div>
			</div>
			<div class="popup-body">
				Просмотр истории создания тем и ответов на форуме доступен только зарегистрированным пользователям. Чтобы получить доступ к страницам «Мои темы» и «Мои ответы»,
				<a class="-login" href="#">авторизуйтесь</a>
				или
				<a href="/site/registration">зарегистрируйтесь</a>
				.
			</div>
		</div>
	</div>
<?php endif; ?>


<div class="-hidden">
	<div class="popup popup-subscribe" id="popup-subscribe">
		<div class="popup-header">
			<div class="popup-header-wrapper">
				Подписка на новые темы форума
			</div>
		</div>
		<div class="popup-body">
			<form action="#">
				<p>Выберите разделы форума, на обновление которых хотите подписаться</p>
				<ul class="">
					<li><label><input type="checkbox"/>Дизайн интерьера</label><span>(23)</span></li>
					<li><label><input type="checkbox"/>Ремонт и отделка </label><span>(23)</span></li>
					<li><label><input type="checkbox"/>Архитектура </label><span>(23)</span></li>
					<li><label><input type="checkbox"/>Ландшафтный дизайн </label><span>(23)</span></li>
					<li><label><input type="checkbox"/>Загородный дом и строительство</label><span>(23)</span></li>
					<li><label><input type="checkbox"/>Мебель</label><span>(23)</span></li>
					<li><label><input type="checkbox"/>Дизайн интерьера</label><span>(23)</span></li>
					<li><label><input type="checkbox"/>Ремонт и отделка </label><span>(23)</span></li>
					<li><label><input type="checkbox"/>Архитектура </label><span>(23)</span></li>
					<li><label><input type="checkbox"/>Ландшафтный дизайн </label><span>(23)</span></li>
					<li><label><input type="checkbox"/>Загородный дом и строительство</label><span>(23)</span></li>
					<li><label><input type="checkbox"/>Мебель</label><span>(23)</span></li>
				</ul>
				<input type="submit" class="btn_grey" value="Сохранить">
				<a class="cancel_link" href="#">Отменить</a>
			</form>
		</div>
	</div>
</div>