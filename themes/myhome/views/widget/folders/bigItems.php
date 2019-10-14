<?php if ($items) :

	$hostInfo = Yii::app()->request->getHostInfo();

	$column1 = array();
	$column2 = array();
	$column3 = array();


	$column1[] = current($items);
	while (1) {
		$tmp = next($items);

		if (!$tmp) {
			break;
		}
		$column2[] = $tmp;

		$tmp = next($items);

		if (!$tmp) {
			break;
		}
		$column3[] = $tmp;

		$tmp = next($items);

		if (!$tmp) {
			break;
		}
		$column1[] = $tmp;
	}
	?>
<?php endif ?>

<div class="-col-4">
	<div class="-grid">
		<?php /** @var $cl CatFolders */
		foreach ($column1 as $cl) : ?>
			<div class="-col-4 folder">
				<a href="<?php echo $cl->getLink(); ?>"
				   class="folder-picture">
					<?php
					echo CHtml::image(
						'/'.$cl->getPreview(CatFolders::$preview['crop_292']),
						'', array('class' => '-quad-280')
					);
					?>
					<span class="-huge -semibold"><?php echo $cl->name ?></span>
				</a>

				<p class="-gutter-top -gutter-bottom-hf -text-align-center -small -gray"><?php echo CFormatterEx::formatNumeral($cl->count, array('товар', 'товара', 'товаров')); ?></p>

				<p class="-text-align-center -gray">
					<a href="# "
					   class="-icon-link -icon-softgray"
					   data-url = "<?php echo $hostInfo.$cl->getLink(); ?>"
					   title = "Скопировать ссылку на эту подборку"></a>
					<?php

					$this->widget('ext.sharebox.EShareBox', array(
						'view'             => 'folders',
						'url'              => $hostInfo . $cl->getLink(),
						'title'            => $cl->name,
						'message'          => $hostInfo . $cl->getLink(),
						'classDefinitions' => array(
							'vkontakte' => '-icon-vkontakte -icon-softgray',
							'facebook'  => '-icon-facebook -icon-softgray',
							'odkl'      => '-icon-odnoklassniki -icon-only -icon-softgray',
						),
						'exclude'          => array('livejournal', 'pinterest', 'twitter', 'google+'),
						'htmlOptions'      => array('class' => 'share_block'),
					));?>
				</p>
			</div>
		<?php endforeach; ?>

	</div>
</div>

<div class="-col-4">
	<div class="-grid">
		<?php foreach ($column2 as $cl) : ?>
			<div class="-col-4 folder">
				<a href="<?php echo $cl->getLink(); ?>"
				   class="folder-picture">
					<?php
					echo CHtml::image(
						'/'.$cl->getPreview(CatFolders::$preview['crop_292']),
						'', array('class' => '-quad-280')
					);
					?>
					<span class="-huge -semibold"><?php echo $cl->name ?></span>
				</a>

				<p class="-gutter-top -gutter-bottom-hf -text-align-center -small -gray"><?php echo CFormatterEx::formatNumeral($cl->count, array('товар', 'товара', 'товаров')); ?></p>

				<p class="-text-align-center -gray">
					<a href="#"
					   class="-icon-link -icon-softgray"
					   data-url = "<?php echo $hostInfo.$cl->getLink(); ?>"
					   title = "Скопировать ссылку на эту подборку"></a>
					<?php

					$this->widget('ext.sharebox.EShareBox', array(
						'view'             => 'folders',
						'url'              => $hostInfo . $cl->getLink(),
						'title'            => $cl->name,
						'message'          => $hostInfo . $cl->getLink(),
						'classDefinitions' => array(
							'vkontakte' => '-icon-vkontakte -icon-softgray',
							'facebook'  => '-icon-facebook -icon-softgray ',
							'odkl'      => '-icon-odnoklassniki -icon-only -icon-softgray',
						),
						'exclude'          => array('livejournal', 'pinterest', 'twitter', 'google+'),
						'htmlOptions'      => array('class' => 'share_block'),
					));?>
				</p>
			</div>
		<?php endforeach; ?>

	</div>
</div>

<div class="-col-4">
	<div class="-grid">
		<?php foreach ($column3 as $cl) : ?>
			<div class="-col-4 folder">
				<a href="<?php echo $cl->getLink(); ?>"
				   class="folder-picture">
					<?php
					echo CHtml::image(
						'/'.$cl->getPreview(CatFolders::$preview['crop_292']),
						'', array('class' => '-quad-280')
					);
					?>
					<span class="-huge -semibold"><?php echo $cl->name ?></span>
				</a>

				<p class="-gutter-top -gutter-bottom-hf -text-align-center -small -gray"><?php echo CFormatterEx::formatNumeral($cl->count, array('товар', 'товара', 'товаров')); ?></p>

				<p class="-text-align-center -gray">
					<a href="#"
					   class="-icon-link -icon-softgray"
					   data-url = "<?php echo $hostInfo.$cl->getLink(); ?>"
					   title = "Скопировать ссылку на эту подборку"></a>
					<?php

					$this->widget('ext.sharebox.EShareBox', array(
						'view'             => 'folders',
						'url'              => $hostInfo . $cl->getLink(),
						'title'            => $cl->name,
						'message'          => $hostInfo . $cl->getLink(),
						'classDefinitions' => array(
							'vkontakte' => '-icon-vkontakte -icon-softgray',
							'facebook'  => '-icon-facebook -icon-softgray',
							'odkl'      => '-icon-odnoklassniki -icon-only -icon-softgray',
						),
						'exclude'          => array('livejournal', 'pinterest', 'twitter', 'google+'),
						'htmlOptions'      => array('class' => 'share_block'),
					));?>
				</p>
			</div>
		<?php endforeach; ?>

	</div>
</div>

<?/*Копирование сслыки*/?>
<div class="-white-bg -inset-all -col-7 -hidden" id="popup-copylink">
	<h2>Копировать ссылку</h2>
	<form>
		<input class="-block" type="text">
	</form>
</div>

<script>
	folders.copyLink();
</script>



