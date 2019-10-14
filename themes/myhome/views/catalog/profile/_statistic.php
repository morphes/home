<div class="-force-middle stat-content">
	<div class="-col-4">
		<span class="-large">Посещения магазина</span>
	</div>
	<div class="-col-8 -inset-top -inset-bottom">
		<span class="-giant -semibold"><?php echo $stat[StatStore::TYPE_HIT_STORE];?></span>
	</div>
	<div class="-col-4">
		<span class="-block -large">Просмотры товаров</span>
		<span class="-gray">Собственный каталог</span>
	</div>
	<div class="-col-8 -inset-top -inset-bottom">
		<span class="-giant -semibold"><?php echo $stat[StatStore::TYPE_HIT_OWN_PRODUCT];?></span>
	</div>
	<div class="-col-4">
		<span class="-block -large">Просмотры товаров</span>
		<span class="-gray">Каталог MyHome</span>
	</div>
	<div class="-col-8 -inset-top -inset-bottom">
		<span class="-giant -semibold"><?php echo $stat[StatStore::TYPE_HIT_COMMON_PRODUCT];?></span>
	</div>
	<div class="-col-4">
		<span class="-large">Переходы на сайт</span>
	</div>
	<div class="-col-8 -inset-top -inset-bottom">
		<span class="-giant -semibold"><?php echo $stat[StatStore::TYPE_SITE];?></span>
	</div>
</div>