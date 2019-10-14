<?php $form=$this->beginWidget('ext.bootstrap.widgets.BootActiveForm',array(
	'action'=>Yii::app()->createUrl($this->route),
	'method'=>'get',
));

?>
<div>
	<h3 style="text-align: center">Заявка не найдена. Возможно отменена пользователем.</h3>
	<h3 style="text-align: center"><a href="/member/admin/spam" >Список заявок</a></h3>
</div>


<?php $this->endWidget(); ?>