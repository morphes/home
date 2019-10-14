<?php $this->pageTitle = 'Исходящие сообщения — MyHome.ru'?>
<?php $this->description = ''; ?>
<?php $this->keywords = ''; ?>
<?php Yii::app()->clientScript->registerCssFile('/css-new/generated/profile.css');?>

<?php Yii::app()->clientScript->registerCssFile('/css/user.css'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/style.css'); ?>


<?php // Подключаем шапку для сообщений
$this->renderPartial('//member/message/_topMenu', array('current'=>'outbox')); ?>

<?php
$this->widget('application.components.widgets.IdeasWall', array(
	'dataProvider'	=> $messageProvider,
	'itemView'	=> '//member/message/_msgViewOutbox',
	'saveUri'	=> true,
	'emptyText'	=> 'Сообщений нет',
	'pageSize'	=> $pageSize,
	'availablePageSizes' => Config::$messagePageSizes,
	'htmlOptions'	=> array('class' => 'msgList'),
	'extraDivClass' => 'messages-list',
));
?>


<script type="text/javascript">
	function msgdelete(id){
		$.ajax({
			url: "<?php echo $this->createUrl($this->id.'/delete'); ?>",
			data: "id="+id,
			async: false,
			success: function(data){
				if(data == "ok"){
					$("#msg-body-"+id).remove();
				}
			}
		});
	}
</script>


<?php // Рендерим попап для личных сообщений
$this->renderPartial('//member/message/_newMessage'); ?>