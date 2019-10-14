<?php $this->pageTitle = 'Форум — MyHome.ru'?>
<?php $this->description = ''; ?>
<?php $this->keywords = ''; ?>

<?php
// ----- ОПТИМИЗАЦИЯ ДЛЯ СОЦСЕТЕЙ -----

Yii::app()->openGraph->title = 'Форум MyHome';
Yii::app()->openGraph->description = 'Делитесь своим опытом, советуйтесь со специалистами, задавайте вопросы экспертам MyHome';
Yii::app()->openGraph->image = Yii::app()->homeUrl.'/img/logo-tb.png';

Yii::app()->openGraph->renderTags();
?>


<script>
	$(document).ready(function () {
		forum.showSubscribe();
		forum.tabsInit();
		//forum.slideInit();
	});
</script>


<!--<div class="forum_description">

	<div class="desc_text">
		<p>Дорогие специалисты и владельцы квартир в этом разделе нашего сайта вы сможете найти актуальные
		   ответы на интересующие вас вопросы.</p>

		<p>Общайтесь, обсуждайте, высказывайте мнения, но для начала обязательно прочитайте
			<span class="forum_rules">правила форума</span>. Ваше активное участие в обсуждениях очень
		   важно! Так как чем ваша активность больше, тем быстрее вы станете экспертом и будете обладать
		   дополнительными привилегиями.</p>

		<p>Приятного вам общения!</p>
	</div>
	<div class="desc_image">
		<img src="/img/tmp/forum/icon.png"/>
	</div>
	<div class="clear"></div>
</div>-->
<div class="f_middle">
	<ul class="forum_tabs">
		<li data-id="1" class="current"><span>Новые темы</span></li>
		<li data-id="2"><span>Популярные темы</span></li>
	</ul>
	<div class="clear"></div>
	<div class="main_topics_list_container">
		<div class="prev_topic"><i></i></div>
		<div class="topics_list main" id="tab_1">
			<?php
			/* --------------------------------
			 *  Темы — самые последние по дате
			 * --------------------------------
			 */
			foreach ($freshTopics as $topic) {
				?>
				<div class="item">
					<a class="item_head" href="<?php echo $topic->getElementLink();?>"><?php echo $topic->name;?></a>

					<div class="item_info">
						<?php
						if ($topic->author_id) {
							echo CHtml::link($topic->author->name, $topic->author->getLinkProfile(), array('class' => 'author'));
						} else {
							echo CHtml::tag('span', array(), 'Гость');
						}
						?>

						<span><?php echo CFormatterEx::formatDateToday($topic->create_time);?></span>

						<div class="block_item_counters">
						<span class="comments_quant"
						      title="комментарии">
							<a href="<?php echo $topic->getElementLink();?>">
								<i></i>
								<?php echo CFormatterEx::formatNumeral($topic->count_answer, array('ответ', 'ответа', 'ответов'));?>
							</a>
						</span>
						</div>
						<div class="clear"></div>
					</div>
				</div>
				<?php
			}
			?>
		</div>

		<div class="topics_list main hide" id="tab_2">
			<?php
			/* --------------------------------
			 *  Темы — самые популярные
			 * --------------------------------
			 */
			foreach ($popularTopics as $topic) {
				?>
				<div class="item">
					<a class="item_head" href="<?php echo $topic->getElementLink();?>"><?php echo $topic->name;?></a>

					<div class="item_info">
						<?php
						if ($topic->author_id) {
							echo CHtml::link($topic->author->name, $topic->author->getLinkProfile(), array('class' => 'author'));
						} else {
							echo CHtml::tag('span', array(), 'Гость');
						}
						?>

						<span><?php echo CFormatterEx::formatDateToday($topic->create_time);?></span>

						<div class="block_item_counters">
						<span class="comments_quant"
						      title="комментарии">
							<a href="<?php echo $topic->getElementLink();?>">
								<i></i>
								<?php echo CFormatterEx::formatNumeral($topic->count_answer, array('ответ', 'ответа', 'ответов'));?>
							</a>
						</span>
						</div>
						<div class="clear"></div>
					</div>
				</div>
				<?php
			}
			?>
		</div>
		<div class="next_topic"><i></i></div>
	</div>

	<?php
	// Яндекс директ
    Yii::app()->controller->renderPartial('//widget/profitpartner/profit_partner_list_under');
	?>

</div>
<div class="f_right">

	<?php if (count($mostActiveExperts)) { ?>
	<div class="user_pics">
		<h2 class="block_head">Эксперты</h2>
		<span class="all_elements_link">
			<a href="/experts"><?php echo User::model()->countByAttributes(array('status' => User::STATUS_ACTIVE), 'expert_type <> :et', array(':et' => User::EXPERT_NONE));?></a>
		</span>
		<ul class="user_photos expert">
			<?php
			foreach($mostActiveExperts as $user) {
				echo CHtml::openTag('li');
				$img = CHtml::image($user->getPreview(User::$preview['crop_45']));
				echo CHtml::link($img, $user->getLinkProfile(), array('title' => $user->name));
				echo CHtml::closeTag('li');
			}
			?>
		</ul>
		<div class="clear"></div>
	</div>
	<?php } ?>

	<div class="user_pics">
		<h2 class="block_head">Самые активные</h2>
		<ul class="user_photos">
			<?php
			$htmlUsers = '';
			foreach($mostActiveUsers as $user) {
				if ($user) {
					$htmlUsers .= CHtml::openTag('li');
					$htmlUsers .= CHtml::openTag('a', array('href' => $user->getLinkProfile(), 'title' => $user->name));
					$htmlUsers .= CHtml::image('/'.$user->getPreview(Config::$preview['crop_45']));
					$htmlUsers .= CHtml::closeTag('a');
					$htmlUsers .= CHtml::closeTag('li');
				}
			}
			echo $htmlUsers;
			?>
		</ul>
		<div class="clear"></div>
	</div>

	<div class="-gutter-top-dbl">

	</div>
</div>


