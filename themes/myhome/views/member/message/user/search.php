<?php $this->pageTitle = 'Поиск сообщений — MyHome.ru'?>
<?php $this->description = ''; ?>
<?php $this->keywords = ''; ?>

<?php Yii::app()->clientScript->registerCssFile('/css/user.css'); ?>


<?php // Подключаем шапку для сообщений
$this->renderPartial('//member/message/_topMenu', array('current'=>'search')); ?>

<?php
$this->widget('application.components.widgets.IdeasWall', array(
	'dataProvider'	=> $messageProvider,
	'itemView'	=> '//member/message/_msgViewSearch',
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