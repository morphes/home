<?php $this->pageTitle = 'Отзывы — ' . $user->name . ' — MyHome.ru' ?>
<?php Yii::app()->clientScript->registerScriptFile('/js-new/profile.js'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css-new/generated/profile.css'); ?>
<div class="reviews_page">
<div class="-col-9 -gutter-top -gutter-bottom-dbl">
<div class="-gutter-top-dbl reviews-list">
	<?php if (Yii::app()->getUser()->getIsGuest()) { ?>
		<div class="-border-all -border-radius-all -inset-all -gutter-bottom-dbl -large -semibold">
			Для того чтобы оставить отзыв, вам необходимо
			<a class="-login"
			   href="#">авторизоваться</a> или <a class="-red"
							      href="/site/registration">зарегистрироваться</a>
		</div>
	<?php
	} elseif ($user->id != Yii::app()->getUser()->getId()) {
		if (!Review::hasReview($user->id, Yii::app()->user->id)) :
			$authorId = Yii::app()->getUser()->getId();
			$authorModel = User::model()->findByPk($authorId);
			?>

			<div class="review-form -gutter-bottom-dbl">
				<div class="-col-wrap -gutter-right-qr">
					<a class="-block"
					   href="<?php echo $authorModel->getLinkProfile(); ?>">
						<?php echo CHtml::image('/' . $authorModel->getPreview(User::$preview['crop_23']), '', array('class' => '-quad-25')); ?>
					</a></div>
				<div class="-col-wrap">
					<a href="<?php echo $authorModel->getLinkProfile(); ?>"
					   class="-gray">
						<?php
						$authorId = Yii::app()->getUser()->getId();
						echo User::model()->findByPk($authorId)->name; ?>
					</a></div>
				<div class="review-quote-form">
					<i></i>

					<?php $form = $this->beginWidget('CActiveForm', array(
						'id'                     => 'storeoffer-form',
						'enableAjaxValidation'   => false,
						'enableClientValidation' => false,
						'htmlOptions'            => array(
							'class'   => '-form-inline',
							'enctype' => 'multipart/form-data',
							'method'  => 'POST'
						)
					)); ?>
					<?php echo $form->errorSummary($reviewModel); ?>

					<label class="-gutter-bottom-dbl">
						<strong class="-col-wrap">Оценка</strong>

						<div class="-col-wrap rating-stars"
						     id="rating-1">
							<i class="-icon-star-empty -icon-only"
							   data-rating='Не рекомендую'></i>
							<i class="-icon-star-empty -icon-only"
							   data-rating='Плохо'></i>
							<i class="-icon-star-empty -icon-only"
							   data-rating='Нормально'></i>
							<i class="-icon-star-empty -icon-only"
							   data-rating='Хорошо'></i>
							<i class="-icon-star-empty -icon-only"
							   data-rating='Рекомендую'></i>
							<span class="-gutter-left-hf -small -gray">&nbsp;</span>
							<?php echo CHtml::activeHiddenField($reviewModel, 'rating', array('value' => '')); ?>
							<?php echo CHtml::activeHiddenField($reviewModel, 'action', array('value' => 'create')); ?>

						</div>
						<script>

							CCommon.rating($('#rating-1'))
						</script>
					</label>

					<label>
						<strong class="-col-wrap -align-left">Ваш
										      отзыв</strong>
						<?php echo $form->textArea($reviewModel, 'message', array(
							'class' => 'textInput',
							'rows'  => 5,
						)); ?>
					</label>

					<div class="-gutter-top-hf files-block">
						<div class="-gutter-bottom-dbl MultiFile">
							<?php $this->widget('CMultiFileUpload',
								array(
									'model'       => $reviewModel,
									'attribute'   => 'attach',
									'accept'      => 'jpg|jpeg|png|bmp',
									'denied'      => 'Данный тип файла запрещен к загрузке',
									'max'         => 10,
									'remove'      => ' ',
									'duplicate'   => 'Уже выбран',
									'htmlOptions' => array('class' => '', 'id' => "review_files", 'size' => 61),
									'options'     => array(
										'afterFileAppend' => 'js:function (element, value, master_element) {
										var selector = master_element.list.selector;
										$(selector).appendTo("#fileslistReview");
									}',
									)
								)
							);?>

							<div class="MultiFile-select">
								<span class="-icon-attach -icon-red -pseudolink -red"><i>Прикрепить
															 файлы</i></span>
							</div>
							<div id="fileslistReview">

							</div>
							<input type="hidden"
							       name="userId"
							       value="<?php echo $user->id ?>">
						</div>
						<button class="-button -button-skyblue">
							Опубликовать
						</button>
					</div>

					<?php $this->endWidget(); ?>
				</div>
			</div>
		<?php endif; ?>
	<?php } ?>
	<?php for ($i = 0; $i < count($reviews); $i++) {
		$review = $reviews[$i];
		$author = $review->getAuthor();
		$imagesId = $review->getImagesId();

		?>
		<div class="review-item"
		     data-id="<?php echo $review->id ?>">

			<div class="-col-wrap -gutter-right-qr">
				<a class="-block"
				   href="<?php echo $author->getLinkProfile() ?>">
					<?php echo CHtml::image('/' . $author->getPreview(User::$preview['crop_23']), '', array('class' => '-quad-25')); ?>
				</a></div>


			<div class="-col-wrap -small">
				<?php echo CHtml::link($author->name, $author->getLinkProfile(), array('class' => 'gray')); ?>
				<span class="-gutter-left -gray"><?php echo Yii::app()->getDateFormatter()->format('d MMMM yyyy в HH:mm', $review->create_time) ?></span>
				<?php if ($review->author_id == Yii::app()->getUser()->getId()) : ?>
					<span class="-icon-cross-circle-xs -icon-only -icon-gray -gutter-left"></span>
				<?php endif ?>
			</div>

			<div class="-gutter-top -gutter-bottom rewiew-item-rating">
				<?php for ($r = 1; $r <= $review->rating; $r++) { ?>
					<i class="-icon-star-xs -icon-only -red"></i>
				<?php
				}
				$emptyRating = 6 - $r;

				for ($r = 1; $r <= $emptyRating; $r++) {
					?>
					<i class="-icon-star-empty-xs -icon-only -gray"></i>
				<?php } ?>

				<span class="-gutter-left-hf -small -gray"><?php echo $review->getNameRating(); ?></span>
			</div>
			<div class="rewiew-item-post">
				<?php echo $review->message; ?>
			</div>
			<div class="-gutter-top-dbl rewiew-item-photos">

				<?php if ($imagesId) : ?>
					<?php foreach ($imagesId as $idImage) : ?>
						<div class="-col-wrap -relative">
							<img class="-quad-60 -col-wrap -gutter-right-hf"
							     data-src="<?php echo '/' . $review->getPreview($idImage, Review::$preview['crop_520']) ?>"
							     src="<?php echo '/' . $review->getPreview($idImage, Review::$preview['crop_60']) ?>">
						</div>
					<?php endforeach; ?>
				<?php endif ?>
			</div>

			<?php if (isset($reviews[$i + 1]) && $reviews[$i + 1]->parent_id == $review->id) : // ответ на отклик
				$i++;
				$review = $reviews[$i];
				?>

				<div class="review-answer -tinygray-bg -inset-all -border-radius-all"
				     data-id="<?php echo $review->id; ?>">
					<div class="-col-wrap -gutter-right-qr">
						<a href = "<?php echo $user->getLinkProfile() ?>" class="-block"><?php echo CHtml::image('/' . $user->getPreview(User::$preview['crop_23']), '', array("-quad-25")); ?></a>
					</div>
					<div class="-col-wrap -small">
						<?php echo CHtml::link($user->name, $user->getLinkProfile(), array('class' => '-gray')); ?>
						<span class="-gutter-left -gray"><?php echo Yii::app()->getDateFormatter()->format('d MMMM yyyy в HH:mm', $review->create_time) ?></span>
					</div>
					<div class="answer-item-post -gutter-top-hf">
						<?php echo $review->message; ?>
					</div>
					<?php if ($review->author_id == Yii::app()->getUser()->getId()) : ?>
						<div class="answer-actions -gutter-top-hf">
							<a class="-icon-pencil-xs -gray -gutter-right -pseudolink"><i class="-acronym">Редактировать</i></a>
							<a class="-icon-cross-circle-xs -gray -pseudolink"><i class="-acronym">Удалить</i></a>
						</div>
					<?php endif; ?>
				</div>

			<?php elseif ($review->spec_id == Yii::app()->getUser()->getId()) : ?>
				<div class="review-answer -tinygray-bg -inset-all -border-radius-all">
					<span class="-red -acronym">Ответить</span>
				</div>
			<?php endif; ?>
		</div>
	<?php } ?>
</div>
<div class="-hidden"
     id="answer-form">
	<?php $form = $this->beginWidget('CActiveForm', array(
		'id'                     => 'storeoffer',
		'enableAjaxValidation'   => false,
		'enableClientValidation' => false,
		'htmlOptions'            => array(
			'class'   => '-gutter-top -gutter-bottom',
			'enctype' => 'multipart/form-data',
			'method'  => 'POST'
		)
	));

	echo $form->textArea($reviewModel, 'message', array(
		'class' => '-gutter-bottom-dbl',
		'rows'  => 5
	));
	echo CHtml::activeHiddenField($reviewModel, 'action', array('value' => 'answer'));
	?>

	<button class="-button -button-skyblue"> Ответить
	</button>
	<a class="-red -gutter-left"
	   href="#">Отмена</a>

	<?php $this->endWidget(); ?>
</div>

</div>

<script>
	profile.reviewImages();
	profile.reviewActions();
	profile.answerActions();
</script>