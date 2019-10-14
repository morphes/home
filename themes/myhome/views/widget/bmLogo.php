<?php if(isset($bmCatalog))
{
	$class = '-col-4';
}
else
{
	$class = '-col-7';
}
?>

<div class="<?php echo $class;?> -relative">
	<div class="title-promo-banner">
		<a href="<?php echo Yii::app()->params->bmHomeUrl.'/about';?>">ТВК &laquo;Большая медведица&raquo;</a>
		<span>Новосибирск, Светлановская, 50</span>
	</div>
</div>