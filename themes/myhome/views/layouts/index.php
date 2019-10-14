<?php Yii::app()->clientScript->registerCoreScript('jquery'); ?>

<!DOCTYPE HTML>
<html>
<head>
	<meta charset="utf-8">
	<title><?php echo CHtml::encode($this->pageTitle); ?></title><?php
	$cs = Yii::app()->getClientScript();

	$cs->registerMetaTag(CHtml::encode($this->description), 'description');
	$cs->registerMetaTag(CHtml::encode($this->keywords), 'keywords');
	//$cs->registerMetaTag(null, null, null, array('charset' => 'utf-8'));
	$cs->registerMetaTag('904ad3cb7f072c576881f8dfaead4a80', 'cmsmagazine');
        $cs->registerCssFile('/css/jquery-ui-1.8.18.custom.css');
	$cs->registerCssFile('/css/style.css?d='.date('dmYH'));
	$cs->registerCssFile('/css/index.css?d='.date('dmYH'));
	$cs->registerScriptFile('/js/f.js?d='.date('dmYH'));
	$cs->registerScriptFile('/js/functions.js?d='.date('dmYH'));
        $cs->registerCoreScript('jquery.ui');
        // global search initial script
        $cs->registerScript('init', '
                js.initSearch();
        ', CClientScript::POS_READY);
?>


</head>

<body>
	

<div class="wrapper-height"><div class="wrapper-height-inner">

	<?php
	// Рендерим верхнюю часть лайаута, в котором меню, кнопки войти/зарегистироваться
	$this->renderPartial('//layouts/layoutTop');?>


	<?php echo $content;?>



</div></div><!-- /wrapper-height && /wrapper-height-inner -->


<?php
// Рендерим нижнюю часть лэйаута, в котором строка поиска, футер и счетчики
$this->renderPartial('//layouts/layoutBottom');?>


</body>
</html>
