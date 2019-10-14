<?php $this->pageTitle = 'Входящие сообщения — MyHome.ru'?>
<?php $this->description = ''; ?>
<?php $this->keywords = ''; ?>


<?php Yii::app()->clientScript->registerCssFile('/css/user.css'); ?>
<?php Yii::app()->clientScript->registerScriptFile('/js/CUser.js'); ?>


<?php // Подключаем шапку для сообщений
$this->renderPartial('//member/message/_topMenu', array('current'=>'inbox')); ?>

<?php
$this->widget('application.components.widgets.IdeasWall', array(
	'dataProvider'	=> $messageProvider,
	'itemView'	=> '//member/message/_msgViewInbox',
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


<script type="text/javascript">
	CCommon.userMessage();
	user.messageManage();
</script>



<?php
// Выставляем новое число сообщений
$count = MsgBody::model()->count('recipient_id = :user AND recipient_status = :status', array(
		':user' => Yii::app()->user->id,
		':status' => MsgBody::STATUS_UNREAD
	)
);
Yii::app()->user->setFlash('msg_count', $count);
// Рендерим попап для личных сообщений
$this->renderPartial('//member/message/_newMessage'); ?>