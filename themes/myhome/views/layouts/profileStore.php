
<?php Yii::app()->clientScript->registerCssFile('/css/style.css'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css-new/generated/profile.css'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/storeProfile.css'); ?>
<?php Yii::app()->clientScript->registerScriptFile('/js/CStoreProfile.js'); ?>

<?php
if ( !in_array($this->id, array('favorite', 'review'))) {
	$this->bodyClass = 'profile';
}
?>


<div class="-grid-wrapper page-title">
	<div class="-grid">
		<div class="-col-12">
			<ul class="-menu-inline -breadcrumbs">
				<li><a href="<?php echo Yii::app()->homeUrl;?>">Главная</a></li>
			</ul>
		</div>
		<div class="-col-12">
			<h1 class="-inline">
				<?php
				switch($this->action->id) {
					case 'storeList': echo 'Список магазинов'; break;
					case 'StoreProductList' || 'storeProductList': echo 'Список товаров магазина'; break;
					case 'storeCreate': echo 'Добавление магазина'; break;
					case 'storeUpdate': echo 'Редактирование магазина'; break;
					case 'storeShowcase': echo 'Редактирование витрины магазина'; break;
					case 'list' : echo 'Добавление товаров'; break;
					case 'productSelectCategory' : echo 'Добавление товаров'; break;
					case 'productUpdate': echo 'Добавление товаров'; break;
				}
				?>
			</h1>
		</div>
	</div>
</div>

<div class="-grid-wrapper page-content">
	<div class="-grid">
		<div class="-col-12">

			<?php $this->renderPartial('//member/profile/store/admin/_menu', array('user' => $user)); ?>


			<?php echo $content; ?>
		</div>
	</div>
</div>