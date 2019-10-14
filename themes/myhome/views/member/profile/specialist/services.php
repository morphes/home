<?php $this->pageTitle = 'Услуги — ' . $user->name . ' — MyHome.ru'?>

<?php Yii::app()->clientScript->registerCssFile('/css/style.css'); ?>


<?php if($user->data->price_list) : ?>
<a class="pricelist" href="<?php echo UserData::getUrlDownloadPrice($user->data->price_list); ?>">Скачать прайс-лист</a><span class="file_size"><i></i><?php echo UploadedFile::getFileSize($user->data->price_list)?> Мб</span>
<?php endif; ?>

<div class="th">
	<div class="th_row">
		<div class="service_name">Услуга</div>
		<div class="service_count">Кол-во работ</div>
		<div class="price_segment">Ценовой сегмент</div>
		<div class="service_exp">Стаж</div>
		<div class="service_rating">Место в рейтинге</div>
		<div class="clear"></div>
	</div>
</div>

<?php $current_category = null; ?>

<?php foreach($user->serviceListWithParent as $serv) : ?>

<?php
/**
 * Обход по первому элементу массива и построение заголовка таблицы
 */
?>
<?php if(is_null($current_category)) : ?>


	<?php
	/**
	 * Если текущий элемент - родительская категория, то выводим ее название и открываем таблицу для
	 * дочерних элементов категории
	 */
	?>
	<?php if($serv['parent_id'] == 0) : ?>
		<h5><?php echo $serv['service_name'];?></h5>
			<div class="t_serv_list">
			<?php $current_category = $serv['service_id']; ?>
		<?php continue; ?>
		<?php endif; ?>

	<?php
	/**
	 * Если текущий элемент - дочерний, то выводим ее родительскую категорию,
	 * сам элемент выводится ниже, в условии для дочерних элементов
	 */
	?>
	<?php if($serv['parent_id'] != 0) : ?>
		<h5><?php echo Service::getServiceName($serv['parent_id']); ?></h5>
			<div class="t_serv_list">
			<?php $current_category = $serv['parent_id']; ?>
		<?php endif; ?>

	<?php endif; ?>


<?php
/**
 * Проверка на необходимость вывода новой категории и закрытия предыдущей секции
 */
?>
<?php if(!is_null($current_category)) : ?>

	<?php
	/**
	 * Если текущий элемент - родительская категория, и его id отличается от родителя, элементы которого выводили до него,
	 * то закрываем предыдущую секцию таблицы, выводим заголовок категории и открываем новую секцию
	 */
	?>
	<?php if ($serv['parent_id'] == 0 && $serv['service_id'] != $current_category) : ?>
			</div>
			<h5><?php echo $serv['service_name'];?></h5>
			<div class="t_serv_list">
			<?php $current_category = $serv['service_id']; ?>
		<?php continue; ?>
		<?php endif; ?>

	<?php
	/**
	 * Если текущий элемент - дочерний и id его родителя отличается от родителя, элементы которого выводили до него,
	 * то закрываем предыдущую секцию таблицы, выводим заголовок категории и открываем новую секцию
	 */
	?>
	<?php if($serv['parent_id'] != 0 && $serv['parent_id'] != $current_category) : ?>
			</div>
			<h5><?php echo Service::getServiceName($serv['parent_id']); ?></h5>
			<div class="t_serv_list">
			<?php $current_category = $serv['parent_id']; ?>
		<?php endif; ?>

	<?php endif; ?>

<?php
/**
 * Если текущий элемент - дочерний и его родитель является текущим для вывода, то выводим дочерний элемент
 */
?>
<?php if(!is_null($current_category) && $serv['parent_id'] != 0 && $serv['parent_id'] == $current_category) : ?>

		<?php
		$class = '';
		if (empty( $serv['project_qt'] )) {
			$class = 'no_projects';
		} ?>
	<div class="serv_row <?php echo $class; ?>">
		<div class="service_name"><?php
			$prepositionalCase = City::model()->findByPk($user->city_id)->prepositionalCase;

			// Используем CHtml::tag вместо CHtml::link, чтобы атрибут href стоял раньше title
			echo CHtml::tag(
				'a',
				array(
					'href' => Yii::app()->controller->createUrl('/member/specialist/list', array('service'=>$serv['service_url'], 'city'=>City::model()->findByPk($user->city_id)->eng_name)),
					'title' => ($prepositionalCase) ? 'Все специалисты по этой услуге в '.$prepositionalCase : '',
				),
				($prepositionalCase) ? $serv['service_name'].' <span class="text_block">в '.$prepositionalCase.'</span>' : $serv['service_name'],
				true
			);
		?></div>
		<div class="service_count">
			<?php if (empty( $serv['project_qt'] )) {
				echo $serv['project_qt'];
			} else {
				echo CHtml::link($serv['project_qt'], "/users/{$user->login}/portfolio/service/{$serv['id']}");
			}?>
		</div>
		<div class="price_segment"><?php
			if ($serv['segment'] != 0 && $serv['segment_supp'] != 0) {
				echo Config::$segmentName[$serv['segment']].' и '.mb_strtolower( Config::$segmentName[$serv['segment_supp']], 'utf8' );
			} else {
				echo Config::$segmentName[$serv['segment']];
			}
			?></div>
		<div class="service_exp"><?php echo Config::$experienceType[$serv['experience']]; ?></div>
		<div class="service_rating"><span><?php echo $serv['rating_pos']; ?></span> из <?php echo $serv['uq']?></div>
		<div class="clear"></div>
	</div>
	<?php endif; ?>

<?php endforeach; ?>

<?php echo CHtml::closeTag('div'); ?>