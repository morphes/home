<?php if ($item) :

	$hostInfo = Yii::app()->request->getHostInfo();
?>

<div class="-col-3 folder" data-id = <?php echo $item->id;?>>
	<a class="folder-picture" href="<?php echo $item->getLink(); ?>">
		<?php if ($item->status == CatFolders::STATUS_EMPTY) : ?>
		<strong class="-gray">Альбом пуст</strong>
		<span><?php echo $item->name; ?></span>
		<?php else :
		echo CHtml::image(
			'/'.$item->getPreview(CatFolders::$preview['crop_200']),
			'',
			array('class' => '-quad-200')
		);
		?>
		<span><?php echo $item->name; ?></span>
		<?php endif; ?>
	</a>

	<span class="folder-owner -gray">
		<?php if ($item->status != CatFolders::STATUS_EMPTY) : ?>
		<span>
			<a href="#" class="-acronym -small">Изменить обложку</a>
		</span>
		<?php endif; ?>
		<span class="-push-right">
			<a href="#" class="-icon-pencil-xs"></a>
			<a href="#" class="-icon-cross-circle-xs -icon-only"></a>
		</span>
	</span>
	<p class="-gray -gutter-top-hf">
			<span class="-small"><?php
				if ($item->count==0) {
					echo 'Нет товаров';
				} else {
					echo CFormatterEx::formatNumeral($item->count, array('товар', 'товара', 'товаров'));
				}
				?></span>
				<span class="-push-right">
					<a href="#"
					   class="-icon-link -icon-softgray"
					   data-url = "<?php echo $hostInfo.$item->getLink(); ?>"
					   title = "Скопировать ссылку на эту подборку"></a>

<!--					<a href="#" class="-icon-link -icon-softgray" data-tooltip="-tooltip-top-center" data-title="Скопировать ссылку на эту подборку" data-url="http://myhome.ru/"></a>-->
					<?php
					$this->widget('ext.sharebox.EShareBox', array(
						'view' => 'folders',
						'url' => $hostInfo.$item->getLink(),
						'title'=> $item->name,
						'message' => $hostInfo.$item->getLink(),
						'classDefinitions' => array(
							'vkontakte' => '-icon-vkontakte -icon-softgray ',
							'facebook' => '-icon-facebook -icon-softgray',
							'odkl' => '-icon-odnoklassniki  -icon-only -icon-softgray',
						),
						'exclude' => array('livejournal','pinterest','twitter','google+'),
						'htmlOptions' => array('class' => 'share_block'),
					));?>
				</span>
	</p>
</div>
<?php endif; ?>