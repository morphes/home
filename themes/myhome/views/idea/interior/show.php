<h2>Список решений пользователя <?php echo $user_name ;?></h2>
<div>
<?php foreach ($interiors as $interior) : ?>
	<div style="float:left; margin: 10px;">
		<a href="<?php echo $this->createUrl('/idea/', array('interior' => $interior->id)); ?>">
		<?php echo CHtml::image('/'.$interior->getPreview(Config::$preview['crop_150'])); ?>
		<br />
		<?php echo CHtml::label($interior->name, false); ?>
		</a>
	</div>
<?php endforeach; ?>
</div>
<div style="clear:both;"></div>
<?php $this->widget('CLinkPager', array('pages' => $pages)); ?>