<h2 class="main_page_head"><a href="/specialist">Специалисты</a></h2>
<span class="headline_counter">##specialist_quantity##</span>

<?php if ($popularService) : ?>
<div class="popular_services">

	<span>Популярные услуги специалистов <i></i></span>
	<ul class="">
		<?php
		foreach($popularService as $service)
		{
			echo CHtml::openTag('li');
			echo CHtml::link($service->name, '/specialist/'.$service->url);
			echo CHtml::value($service, 'user_quantity');
			echo CHtml::closeTag('li');
		}
		?>
	</ul>
</div>
<script type="text/javascript">
	index.showPopular();
</script>
<?php endif; ?>

<div class="spec">
	<div class="spec_left">
		<div class="alias">
			Удобная база помощников в благоустройстве дома:	ведущие дизайнеры, архитекторы
			и прорабы со всей страны. Изучайте портфолио, рекомендации и отзывы,
			<a href="/tenders/list">размещайте заказы</a>
		</div>

		<?php // БОЛЬШАЯ фотография
		foreach($largeData as $uid => $data)
		{
			$user = User::model()->findByPk((int)$uid);
			$image = UploadedFile::model()->findByPk((int)$data['image_id']);
			if ( ! $user || ! $image) continue;
			?>
			<div class="item">
				<a class="name" href="<?php echo "/users/{$user->login}";?>">
					<img src="/<?php echo $image->getPreviewName(array('193', '193', 'crop', '90')); ?>" width="193" height="193"/>
					<?php echo $user->name;?>
				</a>
				<p><?php echo Amputate::getLimb(CHtml::encode($data['desc']), 250);?></p>
				<a href="<?php echo "/users/{$user->login}/portfolio"; ?>"><?php echo CFormatterEx::formatNumeral($user->data->project_quantity, array('проект', 'проекта', 'проектов')); ?></a>
			</div>
			<?php
		}
		?>
	</div>

	<div class="spec_right">
		<?php  ?>

		<?php // МАЛЕНЬКИЕ анонсы
		$smallArray = array_slice($smallData, 0, 4, true);
		foreach ($smallArray as $uid => $data)
		{
			$user = User::model()->findByPk((int)$uid);
			$image = UploadedFile::model()->findByPk((int)$data['image_id']);
			if(!$user || !$image) continue;
			?>
			<div class="item">
				<a class="name" href="<?php echo "/users/{$user->login}";?>">
					<img width=120 src="/<?php echo $image->getPreviewName(array('120', '120', 'crop', '90')); ?>"/>
					<?php echo $user->name;?>
				</a><br>
				<?php
				$service = Service::model()->findByPk(isset($data['service_id']) ? $data['service_id'] : 0);
				if ($service) {

					if (mb_strlen($service->name, 'UTF-8') > 17) {
						$servName = mb_substr($service->name, 0, 17, 'UTF-8').'...';
					} else {
						$servName = $service->name;
					}

					echo CHtml::link( $servName, '/specialist/'.$service->url, array('class' => 'scope', 'title' => $service->name));
					echo '<br>';
				}
				?>
				<a href="<?php echo "/users/{$user->login}/portfolio";?>"><?php echo CFormatterEx::formatNumeral($user->data->project_quantity, array('проект', 'проекта', 'проектов'));?></a>
			</div>
			<?php
		}
		?>
	</div>
</div>
<div class="clear"></div>