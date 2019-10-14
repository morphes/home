<?php
/**
 * @var $spec User
 */
?>
<div class="-col-wrap item <?php echo ($paid) ? 'foreground' : '';?>">
	<div class="head">
		<?php echo CHtml::image( '/'.$spec->getPreview(User::$preview['crop_80']), $spec->name, array('class'=>'-quad-80 -inline -gutter-right')); ?>
		<div class="-col-wrap">
			<div class=" -col-wrap rewiew-item-rating">
				<?php $this->widget('application.components.widgets.WStarGrid', array(
					'selectedStar' => $spec->getData()->average_rating,
				));

				if (!empty($spec->getData()->review_count)) {
					echo CHtml::tag('span',
						array('class'=>'-gutter-left-hf -small'),
						CFormatterEx::formatNumeral($spec->getData()->review_count, array('отзыв', 'отзыва', 'отзывов'))
					);
				}
				?>
			</div>
			<div class="icons">
				<?php

					if ($paid) {
						?>
						<a title="Профиль поднят" class="-icon-upped-profile paid" data-dropdown="paid-user-2" href="#"></a>
						<div id="paid-user-2" class="-dropdown paid user">

						</div>
					<?php } else { ?>
						<a title="Поднять профиль в списке" class="-icon-upped-profile pay"
						   href="#"
						   data-id="<?php echo $spec->id; ?>"
						   data-service-id="<?php echo $serviceId; ?>"
						   data-city-id="<?php
						   if(isset($city->id)) {
							   echo $city->id;
						   }
						   ?>"></a>

					<?php
					}
				 ?>
				<?php
				$this->widget('application.components.widgets.AddToFavorite.AddToFavorite', array(
					'modelId' => $spec->id,
					'modelName' => 'User',
				));
				?>
			</div>
			<?php echo CHtml::link($spec->name,
					$spec->getLinkProfile(),
					array(
						'class'=>'-huge -nodecor -semibold',
						'onclick'=>'CCommon.hitUserServ('.$spec->id.','.$serviceId.','.$cityId.')'
					)
			); ?>
			<div class="-small -gray">
				<?php
				if ($city instanceof City) {
					echo CHtml::tag('span', array(), $city->name);
				} else {
					$city = $spec->getCityObj();
					if (is_null($city))
						echo CHtml::link('');
					else {
						$params = array('city'=>$city->eng_name);
						if ($service instanceof Service)
							$params['service'] = $service->url;
						echo CHtml::link($city->name, Yii::app()->controller->createUrl('/member/specialist/list', $params) );
					}
				}
				?>
				<span class="-gutter-left">Рейтинг <?php echo Yii::app()->numberFormatter->format('0.00', $spec->rating); ?></span>
				<?php
				if (!empty($spec->count_interior)) {

					if (empty($serviceId)) {
						$url = $this->createUrl('/users', array('login'=>$spec->login, 'action'=>'portfolio'));
					} else {
						$url = $this->createUrl("/users/{$spec->login}/portfolio/service/{$serviceId}");
					}
					echo CHtml::link(
						CFormatterEx::formatNumeral($spec->count_interior, array('проект', 'проекта', 'проектов')),
						$url,
						array('class'=>'-nodecor -gutter-left')
					);
					echo CHtml::link(
						'Перейти в портфолио',
						$url,
						array('class'=>'-push-right -skyblue -pointer-right -nodecor')
					);
				} else {
					echo CHtml::tag('span',
						array('class'=>'-gutter-left'),
						CFormatterEx::formatNumeral($spec->count_interior, array('проект', 'проекта', 'проектов'))
					);
				}
				?>
			</div>
		</div>
	</div>
	<?php

	if ($this->beginCache('SPECIALIST:LIST:' . $spec->id.':'.$serviceId, array('duration' => 3600))) {
		$about = $spec->getData()->about;
		if (!empty($about)) {
			$purifier = new CHtmlPurifier();
			$purifier->options = array('HTML.AllowedElements'=>array());
			$about = $purifier->purify($about);
			echo CHtml::tag('p', array(), Amputate::getLimb($about, 250));
		}

		$defSrc = '/'.UploadedFile::getDefaultImage('default', 'default');
		if ($spec->count_interior >= 5) {
			$projects = $spec->getLastProjects($serviceId, 5);
		?>
			<div class="-col-wrap small-images">
				<?php
				if (!isset($projects[1])) {
					$src = $defSrc;
					$link='#';
				} else {
					$src = ($projects[1] instanceof Architecture) ? $projects[1]->getPreview('crop_150') : '/'.$projects[1]->getPreview(Interior::$preview['crop_150']);
					$link = $projects[1]->getElementLink();
				}
				echo CHtml::link(CHtml::image($src, '', array('class'=>'-quad-150')), $link);

				if (!isset($projects[2])) {
					$src = $defSrc;
					$link='#';
				} else {
					$src = ($projects[2] instanceof Architecture) ? $projects[2]->getPreview('crop_150') : '/'.$projects[2]->getPreview(Interior::$preview['crop_150']);
					$link = $projects[2]->getElementLink();
				}
				echo CHtml::link(CHtml::image($src, '', array('class'=>'-quad-150')),$link);
				?>
			</div>
			<div class="-col-wrap -giant-images">
				<?php
				if (!isset($projects[0])) {
					$src = $defSrc;
					$link='#';
				} else {
					$src = ($projects[0] instanceof Architecture) ? $projects[0]->getPreview('crop_360x305') : '/'.$projects[0]->getPreview(Interior::$preview['crop_360x305']);
					$link = $projects[0]->getElementLink();
				}
				echo CHtml::link(CHtml::image($src, '', array('class'=>'-rect-360-305')),$link);
				?>
			</div>
			<div class="-col-wrap small-images">
				<?php
				if (!isset($projects[3])) {
					$src = $defSrc;
					$link='#';
				} else {
					$src = ($projects[3] instanceof Architecture) ? $projects[3]->getPreview('crop_150') : '/'.$projects[3]->getPreview(Interior::$preview['crop_150']);
					$link = $projects[3]->getElementLink();
				}
				echo CHtml::link(CHtml::image($src, '', array('class'=>'-quad-150')),$link);

				if (!isset($projects[4])) {
					$src = $defSrc;
					$link='#';
				} else {
					$src = ($projects[4] instanceof Architecture) ? $projects[4]->getPreview('crop_150') : '/'.$projects[4]->getPreview(Interior::$preview['crop_150']);
					$link = $projects[4]->getElementLink();
				}
				echo CHtml::link(CHtml::image($src, '', array('class'=>'-quad-150')),$link);
				?>
			</div>
		<?php } elseif ($spec->count_interior >= 3) {
			$projects = $spec->getLastProjects($serviceId, 3);
			foreach ($projects as $item) :
				$src = ($item instanceof Architecture) ? $item->getPreview('crop_230') : '/'.$item->getPreview(Interior::$preview['crop_230']);
				?>
				<div class="-col-wrap standart-images">
					<?php echo CHtml::link(CHtml::image($src, '', array('class'=>'-quad-220')), $item->getElementLink()); ?>
				</div>
			<?php endforeach;
			for ($i=count($projects); $i < 3; $i++) { ?>
				<div class="-col-wrap standart-images">
					<a href="#"><img class="-quad-220" src="<?php echo $defSrc; ?>"></a>
				</div>
			<?php }



			?>
		<?php } ?>
	<?php $this->endCache(); } ?>
</div>


