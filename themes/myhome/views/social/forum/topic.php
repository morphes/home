<?php $this->pageTitle = strip_tags($topic->name).' — Форум — MyHome.ru'?>
<?php $this->description = ''; ?>
<?php $this->keywords = ''; ?>
<?php Yii::app()->clientScript->registerScriptFile('/js/scroll.js');?>

<?php
// ----- ОПТИМИЗАЦИЯ ДЛЯ СОЦСЕТЕЙ -----

Yii::app()->openGraph->title = $topic->name;
Yii::app()->openGraph->description = $topic->description;
Yii::app()->openGraph->image = Yii::app()->homeUrl.'/img/logo-tb.png';

Yii::app()->openGraph->renderTags();
?>


<script type="text/javascript">
	$(document).ready(function(){
		forum.initComments();
		forum.hoverFileSelector();
	})
</script>

<div class="forum_topic_side">

	<div class="forum_head">
		<h1><?php echo $topic->name;?></h1>
		<span class="all_elements_link">
			<span>&larr;</span> <a href="<?php echo ForumSection::model()->findByPk($topic->section_id)->getElementLink();?>">Перейти к списку тем</a>
		</span>
	</div>
	<div class="forum_theme">
		<div class="author">
			<?php if ($topic->author_id) { ?>
				<a title='<?php echo $topic->author->name;?>' href="<?php echo $topic->author->getLinkProfile();?>"><img src="/<?php echo $topic->author->getPreview(Config::$preview['crop_45']);?>" width="45" height="45"
				                                                                                                         alt="<?php echo $topic->author->name;?>"></a>

				<div class="author_info">
					<a title='<?php echo $topic->author->name;?>' href="<?php echo $topic->author->getLinkProfile();?>"><?php echo $topic->author->name;?> </a>
					<span class="topic_date"><?php echo date('d.m.Y', $topic->create_time);?></span>
				</div>
			<?php } else { ?>
				<img src="/<?php echo User::model()->getPreview(Config::$preview['crop_45']);?>" width="45" height="45" alt="Гость">

				<div class="author_info">
					<div>Гость</div>
					<span class="topic_date"><?php echo date('d.m.Y', $topic->create_time);?></span>
				</div>
			<?php } ?>


			<div class="topic_text">
				<p><?php echo nl2br($topic->description);?></p>

				<?php
				$files = $topic->files;
				if ($files) {
					echo CHtml::openTag('ul', array('class' => 'item_files'));
					foreach($files as $file) {
						echo CHtml::openTag('li');
						echo CHtml::tag('i', array('class' => 'fileicon '.ForumTopic::getClassIcon($file['ext'])), '', true);
						echo CHtml::link(($file['original_name']) ? $file['original_name'] : $file['name'], $file['full_path'], array('target'=>'_blank'));
						echo CHtml::encode(CFormatterEx::formatFileSize($file['size']));
						echo CHtml::closeTag('li');
					}
					echo CHtml::closeTag('ul');
				}
				?>
			</div>
			<span>Ответить</span>
			<i></i>
		</div>
		<div class="forum_comments">
			<!--Если нет комментов-->
			<!--<p class="no_comments">В этой теме пока нет ответов. Ваш ответ может быть первым!</p>-->

			<h2 class="block_head">Ответы</h2>

			<span class="block_head_cnt">(<?php echo count($answers);?>)</span>


			<?php /* Временно коментим
 				<span class="expert_answers" href="#">Показать ответы экспертов</span>
 			*/?>

			<?php
			/* --------------------
			 *  ОТВЕТЫ
			 * --------------------
			 */
			foreach ($answers as $i=>$answer) {

				if ($answer->status == ForumAnswer::STATUS_PUBLIC)
					$this->renderPartial('//social/forum/_answer', array(
						'data' => $answer,
						'i' => $i
					));
				elseif ($answer->status == ForumAnswer::STATUS_DELETED_SOFT)
					$this->renderPartial('//social/forum/_answerDel', array(
						'data' => $answer,
						'i' => $i
					));
			}
			?>
		</div>

        <div class="topic_answer_form">
			<div class="user_photo">

				<?php if (Yii::app()->user->isGuest) : ?>
					<img src="/<?php echo User::model()->getPreview(Config::$preview['crop_23']);?>" width="23" height="23"/>
					<a>Гость </a>
					<span class="guest_form">Чтобы ответить от своего имени,  <a href="#" class="-login">Войдите</a> или <a href="#">Зарегистрируйтесь</a></span>
				<?php else: ?>
					<img src="/<?php echo Yii::app()->user->model->getPreview(Config::$preview['crop_23']);?>" width="23" height="23"/>
					<a href="#"><?php echo Yii::app()->user->model->name;?> </a>
				<?php endif;?>

			</div>

			<a name="answer"></a>

			<div class="forum_answer_container">
				<i></i>

				<?php if (Yii::app()->user->isGuest) : ?>
				`	<div class="guest_hint">
						Уважаемый Гость, оставленный вами ответ будет опубликован в течение нескольких часов.<br>
						<a href="#" class="-login">Войдите</a> или <a href="/site/registration">Зарегистрируйтесь</a>, чтобы моментально оставлять ответы.
					</div>
					<br>
				<?php endif; ?>

				<form action="#answer" method="post" enctype="multipart/form-data">
				<?php echo CHtml::activeTextArea($modelAnswer, 'answer', array('class' => 'textInput')); ?>




				<div class="file_input_conteiner">

					<?php $this->widget('CMultiFileUpload',
						array(
							'model' 	=> $modelAnswer,
							'attribute' 	=> 'files',
							'accept' 	=> 'zip|7z|rar|jpg|jpeg|png|txt|doc|docx|xls|xlsx|rtf|pdf',
							'denied' 	=>'Данный тип файла запрещен к загрузке',
							'max' 		=> 5,
							'remove' 	=> '[x]',
							'duplicate' 	=> 'Уже выбран',
							'htmlOptions' 	=> array('class' => 'file_input', 'size' => 61),
							'options' 	=> array(
								'afterFileAppend' => 'js:function (element, value, master_element) {
									var selector = master_element.list.selector;
									$(selector).appendTo("#fileslist");
								}',
							)
						)
					);?>

					<div class="file_select">
						<i></i>
						<span>Прикрепить файл</span>
					</div>
					<div id="fileslist">

					</div>
				</div>
					<?php if (Yii::app()->user->isGuest) : ?>
					<div class="user_fields">
						<div class="field captcha">
							<label>Введите код с
							       картинки
								<span class="required">*</span></label>
							<?php $this->widget('CCaptcha', array(
								'captchaAction' => '/site/captchaWhite',
								'buttonOptions' => array('class' => '-icon-refresh-s -icon-gray'),
								'buttonLabel'   => '',
								'imageOptions'  => array('width' => 90, 'height' => 50),

							))?>

							<?php echo CHtml::activeTextField($modelAnswer, 'verifyCode', array('class' => 'required')) ?>
						</div>
						<div class="clear"></div>
					</div>
					<?php endif; ?>
				<?php echo CHtml::errorSummary($modelAnswer); ?>

				<input type="hidden" name="action" value="create">

				<input type="submit" value="Ответить" class="btn_grey add_topic"/>
				<a class="cancel_edit hide" href="#">Отменить</a>
				</form>
			</div>


		</div>
	</div>
</div>


<div class="forum_form_clone hide">

	<div class="forum_answer_container">
		<form action="" method="post" enctype="multipart/form-data">
			<i></i>
			<textarea class="textInput" name="ForumAnswer[answer]" value=""></textarea>
			<div class="file_input_conteiner">
				<?php $this->widget('CMultiFileUpload',
					array(
						'model' 	=> new ForumAnswer,
						'attribute' 	=> 'files',
						'accept' 	=> 'zip|7z|rar|jpg|jpeg|png|txt|doc|docx|xls|xlsx|rtf|pdf',
						'denied' 	=>'Данный тип файла запрещен к загрузке',
						'max' 		=> 5,
						'remove' 	=> '[x]',
						'duplicate' 	=> 'Уже выбран',
						'htmlOptions' 	=> array('class' => 'file_input', 'size' => 61),
						'options' 	=> array(
							'afterFileAppend' => 'js:function (element, value, master_element) {
									var selector = master_element.list.selector;
									$(selector).appendTo("#fileslist");
								}',
						)
					)
				);?>
				<div class="file_select">
					<i></i>
					<span>Прикрепить файл</span>
				</div>
				<div class="fileslist">

				</div>
			</div>

			<input type="hidden" name="action" value="update">

			<input type="submit" value="Ответить" class="btn_grey add_topic"/>
			<a class="cancel_edit hide" href="#">Отменить</a>
		</form>
	</div>

</div>