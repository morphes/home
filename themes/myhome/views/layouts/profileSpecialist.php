<?php
Yii::app()->clientScript->registerCssFile('/css-new/generated/profile.css');
Yii::app()->clientScript->registerScriptFile('/js-new/common.js');
?>
<script>
	CCommon.initPayment();

</script>

<div class="-grid-wrapper page-title">
	<div class="-grid">
		<div class="-col-12">
			<?php
			if(isset($_SERVER['REQUEST_URI'])){
				$uri = $_SERVER['REQUEST_URI'];
				$section = basename($uri);

				switch($section) {
					case 'portfolio':
						$menuName = 'Портфолио';
						break;
					case 'services':
						$menuName = 'Услуги';
						break;
					case 'reviews':
						$menuName = 'Отзывы';
						break;
					case 'contacts' :
						$menuName = 'Контакты';
						break;
					default :
						break;
				}
			}



			/* -----------------------------------------------------
			 *  Хлебные крошки
			 * -----------------------------------------------------
			 */
			$serviceList = $user->getSimpleServiceList();
			$serviceId = Yii::app()->getUser()->getState('user:service');
			if (empty($serviceId) || !in_array($serviceId, $serviceList)) {
				$serviceId = @reset($serviceList);
			}

			/** @var $service Service */
			$service = null;
			$service = Service::model()->findByPk($serviceId);

			$city = Yii::app()->getUser()->getSelectedCity();
			$links = array();
			$links['Специалисты'] = '/specialist/';
			if ( $service !== null ) {
				$params = array('service'=>$service->url);
				if ( $city instanceof City ) {
					$params['city'] = $city->eng_name;
				}
				$links[$service->name] = $this->createUrl('/member/specialist/list', $params);
			}
			?>

			<ul class="-menu-inline -breadcrumbs">
				<li><a href="<?php echo Yii::app()->homeUrl;?>">Главная</a></li>
				<?php foreach ($links as $name => $url) { ?>
					<li><a href="<?php echo $url;?>"><?php echo $name;?></a></li>
				<?php } ?>

				<?php if(isset($menuName)) {?>
					<li><a href="<?php echo $user->getLinkProfile();?>"><?php echo $user->name;?></a></li>
					<li><span class="-gray"><?php echo $menuName;?></span></li>
				<?php } else { ?>
				<li><span class="-gray"><?php echo $user->name;?></span></li>
				<?php } ?>
			</ul>
		</div>
		<div class="-col-12">
			<h1 class="-inline"><?php echo $user->name; ?></h1>
			<?php if(Yii::app()->user->id == $user->id) : ?>
				<a href="/member/profile/settings" class="-icon-pencil-xs -gutter-left -gray -small">Редактировать профиль</a>
			<?php endif; ?>
		</div>
	</div>
</div>

<div class="-grid-wrapper page-content">
	<div class="-grid">

		<div class="-col-3 profile-sidebar">
			<?php if ($user->expert_type != User::EXPERT_NONE) : ?>
				<div class="expert-icon"></div>
			<?php endif; ?>

			<div class="usercard-photo">
				<?php echo CHtml::image('/' . $user->getPreview(Config::$preview['crop_180']), $user->name, array('class' => '-quad-180')); ?>
			</div>
			<?php if(Yii::app()->user->id==$user->id) { ?>
			<hr class="-groove">
			<div class="-gutter-all">
				<a data-id='<?php echo $user->id;?>' data-city-id='<?php echo $user->city_id;?>' data-service-id='<?php echo $serviceId; ?>' class="-button -button-green -block pay" href="#">Поднять свой профиль</a>
			</div>
			<?php } ?>

			<div class="usercard-statistics">

				<?php
				/* ---------------------------------------------
				 *  Личные данные
				 * ---------------------------------------------
				 */
				?>
				<div class="-col-wrap -push-left">
					<div class="user-rating">
						<?php for($r = 1; $r <= $user->data->average_rating; $r++) { ?>
							<i class="-icon-star -icon-only -red"></i>
						<?php  }
						$emptyRating = 6-$r;

						for($r = 1; $r <= $emptyRating; $r++) { ?>
							<i class="-icon-star-empty -icon-only -gray"></i>
						<?php } ?>
					</div>
				</div>
				<div class="-col-wrap">
					<?php echo CFormatterEx::formatNumeral($user->data->review_count, array('отзыв', 'отзыва', 'отзывов'));  ?>
					<!-- <a href="#">12 отзывов</a> -->
				</div>
				<div class="-col-wrap -push-left">Проекты</div>
				<div class="-col-wrap">
					<?php echo $user->data->project_quantity; ?>
					<!-- <a href="#">5</a> -->
				</div>
				<div class="-col-wrap -push-left">Рейтинг</div>
				<div class="-col-wrap"><?php echo Yii::app()->numberFormatter->format('0.00', $user->getTotalRating()); ?></div>
			</div>

			<?php if (Yii::app()->user->id != $user->id) : ?>

				<?php
				/* ---------------------------------------------
				 *  Оставить отзыв
				 * ---------------------------------------------
				 */
				?>
				<div class="usercard-front-btn">
					<span id='new_message' class="-button -button-red <?php if (Yii::app()->user->isGuest) echo '-guest';?> " onclick='_gaq.push(["_trackEvent","Message","Написать"]); return true;'>Связаться со мной</span>
				</div>

				<?php if (Yii::app()->getUser()->getIsGuest() || !Review::hasReview($user->id, Yii::app()->getUser()->getId()) ) : ?>
					<div class="usercard-review-btn">
						<?php echo CHtml::link(
							'Написать отзыв',
							$this->createUrl('/member/review/list', array('login'=>$user->login)),
							array("class"=>'-icon-bubbles -icon-skyblue')
						); ?>
					</div>
				<?php else : ?>
					<?php echo CHtml::link('Ваш отзыв', $this->createUrl('/member/review/list', array('login'=>$user->login)), array('class'=>'-gray')); ?>
				<?php endif; ?>


				<?php
				/* ---------------------------------------------
				 *  Попап при клике на "Написать сообщение"
				 * ---------------------------------------------
				 */
				?>
				<?php if (Yii::app()->user->getIsGuest()) { ?>
					<div class="-hidden">

						<div class="popup popup-message-guest"
						     id="popup-message-guest">
							<div class="popup-header">
								<div class="popup-header-wrapper">
									Отправка личного сообщения
								</div>
							</div>
							<div class="popup-body">
								Чтобы отправить сообщение, <a href="#"
											      class="-login">авторизуйтесь</a>
								или <a href="/site/registration">зарегистрируйтесь</a>.
							</div>
						</div>

					</div>

					<script>
						CCommon.userMessage();
					</script>

				<?php } elseif ($user->id != Yii::app()->user->id) { ?>

					<?php // Рендерим попап отправки личного сообщения
					$this->renderPartial(
						'//member/message/_newMessage',
						array(
							'controllerId' => $this->id,
							'userName'     => $user->name,
							'userId'       => $user->id,
						)
					);?>

				<?php } ?>

			<?php else : ?>

				<?php
				/* ---------------------------------------------
				 *  Мои сообщения
				 * ---------------------------------------------
				 */
				?>
				<div class="usercard-message-btn">
					<!-- Это выводим для хозяина профиля. Если сообщений нет, то у ссылки следующие классы: -icon-mail -icon-softgray, если есть - добавляется класс -red //-->
					<?php $msg_cnt = Yii::app()->user->getFlash('msg_count'); ?>
					<a class="-icon-mail -icon-softgray <?php if ($msg_cnt > 0) echo '-red';?>" href="<?php echo $this->createUrl('/member/message/inbox');?>"><i>Мои сообщения</i><span><?php echo $msg_cnt; ?></span></a>
				</div>

			<?php endif;?>

			<!--//-->
			<div class="usercard-opts">
				<?php
				/* ---------------------------------------------
				 *  География
				 * ---------------------------------------------
				 */
				?>
				<ul class="-menu-block">
					<?php if ($user->city_id) { ?>
						<li class="-icon-location-s -icon-red"><?php echo $user->getCity();?></li>
					<?php } ?>

					<?php if (!empty($user->data->service_city_list)) { ?>
						<li class="-icon-map-s -icon-red">География услуг<span><?php echo $user->data->service_city_list; ?></span></li>
					<?php } ?>
				</ul>
			</div>

            <div class="-gutter-top-dbl">
                <?php
                // Яндекс.Директ
                $this->renderPartial('//widget/google/adsense_120x600_specialist_card');
                ?>
            </div>

            <?php if (!$user->getIsPremium()) : ?>
                <?php $similars = UserServicecity::getNewestSpecsByCityAndService($user->city_id, $user->getServiceList(), $user->id);?>
                <?php if ($similars) : ?>
                    <div class="similar-specialists -gutter-top-dbl">
                        <div class="-text-align-center">
                            <span class="-acronym -large button">Похожие специалисты</span>
                        </div>
                        <div class="-hidden -text-align-left list" style="margin-bottom: 10px;">
                            <?php foreach($similars as $similar) : ?>
                                <div class="item" style="margin-top: 5px;">
                                    <?php echo CHtml::image('/' . $similar->getPreview(Config::$preview['crop_25']), $similar->name, array('class' => '-quad-25', 'style'=>'vertical-align:middle; display:inline;')); ?>
                                    <a href="<?php echo $similar->getLinkProfile(); ?>"><?php echo $similar->name; ?></a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
		</div>

		<div class="-col-9">
			<?php
			/* -----------------------------------------------------
			 *  Навигация
			 * -----------------------------------------------------
			 */
			?>
			<?php $this->renderPartial('//member/profile/specialist/_menu', array('user' => $user)); ?>

			<script>
				lib.include('mod.Profile');
			</script>

			<?php echo $content; ?>

		</div>

	</div>
</div>
<div id="pay-form">
</div>

<?php Yii::app()->clientScript->registerScript('similar-specialists', '
    $(".similar-specialists span.button").click(function(){
        var self = $(this);
        var list = $(".similar-specialists div.list")
        if (self.css("font-weight") == "bold") {
            self.css("font-weight", "normal");
            list.hide();
        } else {
            self.css("font-weight", "bold");
            list.show();
        }
    });
', CClientScript::POS_READY);?>