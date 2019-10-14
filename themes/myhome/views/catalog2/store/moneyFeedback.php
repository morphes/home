<?php $this->pageTitle = 'Отзывы — ' . $store->name; ?>

<div class="-grid-wrapper page-content">
	<div class="-grid">
		<div class="-col-8">

			<h2>Отзывы<span class="-gutter-left-hf -normal -huge -gray"><?php echo $store->feedbackQt;?></span><a href="#product_comment_form" class="-block -gutter-top -push-right -pseudolink -icon-bubble-s -normal -large -red"><i>Оставить отзыв</i></a></h2>
			<div class="-grid -gutter-bottom-null">
				<!-- pager -->
				<div class="-col-8 -text-align-right -inset-bottom -gutter-bottom-dbl -border-dotted-bottom">

					<form action="" method="post" id="update-form">
						<span class="-push-left -small">
							<?php
							/* -----------------------------
							 *  Сортировка
							 * -----------------------------
							 */
							?>
							<?php $this->widget('catalog.components.widgets.CatFilterSortGrid', array(
								'cookieName'   => 'store_feedback_sort',
								'formSelector' => '#update-form',
								'items'        => array(
									array('name' => 'date', 'text' => 'дате'),
									array('name' => 'mark', 'text' => 'оценке'),
								),
							)); ?>
						</span>

						<script>
							//записываем в куки поле и порядок сортировки
							$('.sorting .-sort a').click(function(){
								var data = $(this).data();
								CCommon.setCookie("store_feedback_sort", data.sort,{"expires":1800,"path":"\/"});
								$('#update-form').submit();
								return false;
							});
						</script>

						<?php
						/* -------------------------------------
						 *  Постраничка
						 * -------------------------------------
						 */
						?>
						<?php $this->widget('application.components.widgets.CustomListPager', array(
							'pages'          => $feedbacks->pagination,
							'htmlOptions'    => array('class' => '-menu-inline -pager'),
							'maxButtonCount' => 5,
						)); ?>
					</form>

				</div>
				<!-- eof pager -->
			</div>
			<script>
				$(function(){
					$('body')
						.on('click', '.toggleReplyForm, .hideReplyForm', function(){
							if ($(this).hasClass('toggleReplyForm')) {
								// Находим "Ответить на отзыв" и следующую за ней форму
								$(this).next().andSelf().toggleClass('-hidden');
							}
							else if ($(this).hasClass('hideReplyForm')) {
								// Находим форму и предшествующий ей "Ответить на отзыв"
								var form = $(this).parents('form'),
									sel = form.add(form.prev());
								sel.toggleClass('-hidden');
							}
						})
						.on('click', '.edit_answer_to_review', function(){
							var reply = $(this).parents('.reply');
							reply.find('form').add(reply.find('p.-em-all')).toggleClass('-hidden');
						})
						.on('click', '.answer_to_review', function(){
							var reply = $(this).parents('.reply');
							var data = {};

							data.message = reply.find('textarea').val();
							data.commentid = reply.attr('data-commentid');
							data.answerid = reply.attr('data-answerid');
							data.typeView = 'minisite';

							if (data.message.length == 0) {
								alert('Необходимо написать ответ!');
								return false;
							}

							$.post(
								'/catalog2/store/feedbackAnswer',
								data,
								function(response) {
									response = $.parseJSON(response);
									if (response.success) {
										reply.html(response.html);
										reply.attr('data-answerid', response.answerId)
									} else {
										alert(response.message);
									}
								}
							);

							return false;
						})
						.on('click', '.delete_answer_to_review', function(){
							var reply = $(this).parents('.reply');
							var data = {};

							data.commentid = reply.attr('data-commentid');
							data.answerid = reply.attr('data-answerid');

							doAction({
								'yes':function () {

									$.post(
										'/catalog2/store/deleteFeedbackAnswer',
										data,
										function (response) {
											response = $.parseJSON(response);
											if (response.success) {
												reply.html(response.html);
											} else {
												alert(response.message);
											}
											return false;
										}
									);
								},
								'no':function () {
									return false;
								}
							}, 'Удалить ответ', 'После удаления ответа он не будет виден на сайте')
						});

				});
			</script>

			<?php
			/* -----------------------------------------------------
			 *  Список отзывов
			 * -----------------------------------------------------
			 */
			?>

			<?php $this->widget('zii.widgets.CListView', array(
				'dataProvider' => $feedbacks,
				'itemView'     => '_moneyFeedbackItem',
				'template'     => '{items}',
				'viewData'     => array('model' => $store),
				'emptyText'    => '',
			));?>

			<?php if(!Yii::app()->user->isGuest && !$store->checkFeedback) { ?>

				<div class="full-review" id="product_comment_form">

					<div class="-col-wrap -inset-right-qr">
						<?php echo CHtml::image('/'.Yii::app()->user->model->getPreview( Config::$preview['crop_25'] ), '', array('class' => '-quad-25', 'width' => 25, 'height' => 25));?>
					</div>
					<div class="-col-wrap -small">
						<span class="-gutter-right"><?php echo Yii::app()->user->model->name;?></span>
					</div>
					<div class="reply -gutter-top">
						<?php $form = $this->beginWidget('CActiveForm', array(
							'htmlOptions' => array('class' => '-grid', 'id' => 'form-feedback')
						)); ?>
							<div class="-col-2">Ваш отзыв</div>
							<div class="-col-6">
								<?php echo $form->textArea($feedback, 'message', array('id'=>'lc-merits', 'rows' => 8)); ?>
							</div>
							<div class="-col-2 -gutter-top">Оценка</div>
							<div class="-col-6">
								<div class="-col-wrap -gutter-top rating-stars" id="rating-1">
									<i class="-icon-star-empty -icon-only" data-rating='Ужасный магазин'></i>
									<i class="-icon-star-empty -icon-only" data-rating='Плохой магазин'></i>
									<i class="-icon-star-empty -icon-only" data-rating='Обычный магазин'></i>
									<i class="-icon-star-empty -icon-only" data-rating='Хороший магазин'></i>
									<i class="-icon-star-empty -icon-only" data-rating='Отличный магазин'></i>
									<span class="-gutter-left-hf -small -gray">&nbsp;</span>
									<?php echo $form->hiddenField($feedback, 'mark', array('id' => 'idea-rating')); ?>
								</div>
								<script>
									CCommon.rating($('#rating-1'))
								</script>

							</div>
							<div class="-col-6 -skip-2 -gutter-top">
								<?php echo CHtml::tag('button', array('id'=>'post-feedback', 'class'=>'-button-skyblue'), 'Опубликовать'); ?>
							</div>

							<div class="-col-2"></div>
							<div class="-col-6">
								<p class="error-title" style="display: none;"></p>
							</div>

						<?php $this->endWidget(); ?>

					</div>
				</div>

			<?php } ?>




			<?php if(Yii::app()->user->isGuest) : ?>
				<form class="shadow_block white padding-18">
					<p class="lc-not">Чтобы оставить отзыв о магазине, <a href="#" class="-login">авторизуйтесь</a> или <a href="/site/registration">зарегистрируйтесь</a></p>
				</form>
			<?php endif; ?>


			<?php $showAlreadyFeedback = ($store->checkFeedback) ? 'block' : 'none'; ?>

			<form class="shadow_block white padding-18" style="display: <?php echo $showAlreadyFeedback; ?>;" id="alreadyFeedback">
				<p class="lc-not">Вы оставили отзыв для этого магазина</p>
			</form>



			<div class="-grid">
				<!-- pager -->
				<div class="-col-8 -text-align-right -inset-top -gutter-top-dbl -border-dotted-top">

					<?php
					/* -------------------------------------
					 *  Постраничка
					 * -------------------------------------
					 */
					?>
					<?php $this->widget('application.components.widgets.CustomListPager', array(
						'pages'          => $feedbacks->pagination,
						'htmlOptions'    => array('class' => '-menu-inline -pager'),
						'maxButtonCount' => 5,
					)); ?>

				</div>
				<!-- eof pager -->
			</div>


		</div>
		<div class="-col-3 -skip-1">
			<?php $this->renderPartial('_moneyRightSidebar', array(
				'store' => $store
			)); ?>
		</div>
	</div>
</div>




<?php Yii::app()->clientScript->registerScript('feedback-form', '
        $("#post-feedback").click(function(){
        	$(".error-title").hide();
                $.post(
                	"'.$this->createUrl("createFeedback", array("id"=>$store->id)).'",
                	$("#form-feedback").serialize(),
                	function(response){
				response = $.parseJSON(response);
				if(response.success) {
					$("#form-feedback").hide();
					$("#alreadyFeedback").show();
					$("#update-form").submit();
				} else {
					var errors = "";
					$(".error-title").show();
					$.each(response.errors, function(index, value) {
						errors = errors + value + "<br>";
					})
					$(".error-title").html(errors);
				}
                	}
                );

                return false;
        });

        $(".drop_down ul li").click(function(){
                CCommon.setCookie("store_feedback_pagesize", $(this).attr("data-value"), {expires:31*24*60*60, path:"/"});
                $("#update-form").submit();
        });

', CClientScript::POS_END);?>