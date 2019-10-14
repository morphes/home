<?php $this->pageTitle = 'Просмотр сообщений — MyHome.ru'?>
<?php $this->description = ''; ?>
<?php $this->keywords = ''; ?>

<?php Yii::app()->clientScript->registerCssFile('/css/user.css'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css/style.css'); ?>
<?php Yii::app()->clientScript->registerScriptFile('/js/CUser.js'); ?>
<?php Yii::app()->clientScript->registerCssFile('/css-new/generated/profile.css');?>



<?php // Подключаем шапку для сообщений
$this->renderPartial('//member/message/_topMenu', array('current' => '')); ?>

<div class="messages-list">
	<?php // Выводим текущее сообщение ?>

	<div class="item hover"
	     id="msg-body-<?php echo $msg->id ?>">
		<div class="author">
			<p>
				<?php
				echo CHtml::openTag('a', array('href' => $msg->author->getLinkProfile()));

				echo CHtml::image('/' . $msg->author->getPreview(Config::$preview['crop_45']), $msg->author->name, array('width' => 45, 'height' => 45));
				echo CHtml::value($msg, 'author.name');

				echo CHtml::closeTag('a');
				?>
				<br>
				<em><?php echo CHtml::value($msg, 'author.login'); ?></em>
			</p>

			<p class="date"><?php echo Yii::app()->getDateFormatter()->format('d MMMM yyyy <br> в HH:mm:ss', $msg->create_time); ?></p>
		</div>
		<div class="body">
			<p><?php echo nl2br(CHtml::encode($msg->message)); ?></p>

			<?php
			if (!empty($msg->uploadedFiles)) {

				echo CHtml::openTag('p', array('class' => 'files-list'));
				echo CHtml::tag('strong', array(), 'Вложенные файлы (' . count($msg->uploadedFiles) . ')', true);
				echo '<br>';
				foreach ($msg->uploadedFiles as $file) {
					echo CHtml::link($file->name . '.' . $file->ext, Yii::app()->createUrl('/download/attachfile/', array('id' => $file->id)));
					$size = round($file->size / 1024 / 1024, 3);
					echo ", &nbsp{$size} Мб<br>";
				}
				echo CHtml::closeTag('p');
			}
			?>
		</div>
	</div>

	<?php if (Yii::app()->user->hasFlash('msg_list_error')): ?>
		<div class="error-title"
		     style="display: block;">
			<?php echo Yii::app()->user->getFlash('msg_list_error'); ?>
		</div>
	<?php endif; ?>



	<?php $form = $this->beginWidget('CActiveForm', array(
		'id'                   => 'msg-body-create-form',
		'action'               => $this->createUrl($this->id . '/answer', array('recipient_id' => $msg->getRecipient())),
		'enableAjaxValidation' => false,
		'htmlOptions'          => array('enctype' => 'multipart/form-data', 'class' => 'messages-leave shadow_block padding-18'),
	)); ?>
	<p class="col1">
		<label for="ml-message">Ответить:</label>
		<label class="for_file"
		       for="attach_file">Прикрепить
					 изображение:</label>
	</p>

	<div class="col2">
		<p><?php echo $form->textArea($body, 'message', array('id' => 'ml-message', 'class' => 'textInput')); ?></p>

		<div class="input_conteiner">
			<?php  $this->widget('CMultiFileUpload',
				array(
					'model'       => $body,
					'attribute'   => 'attach',
					'accept'      => 'jpg|jpeg|png|bmp',
					'denied'      => 'Данный тип файла запрещен к загрузке',
					'max'         => 10,
					'remove'      => '[x] ',
					'duplicate'   => 'Уже выбран',
					'htmlOptions' => array('class' => 'img_input','id' => 'answer_files'),
					'options'     => array(
						'afterFileAppend' => 'js:function (element, value, master_element) {
							var selector = master_element.list.selector;
							$(selector).appendTo("#answer_fileslist");
						}',
					)
				)
			);?>
			<div class="img_mask">
				<input type="text"
				       class="textInput img_input_text"/>
			</div>
			<div class="clear"></div>
		</div>
		<div id="answer_fileslist" class="-gutter-bottom"></div>
		<div class="clear"></div>
		<div class="btn_conteiner yellow">
			<input type="submit"
			       class="btn_grey"
			       value="Отправить">
		</div>
	</div>

	<?php $this->endWidget(); ?>


	<?php
	$this->widget('application.components.widgets.IdeasWall', array(
		'dataProvider'       => $messageProvider,
		'itemView'           => '//member/message/_msgShow',
		'saveUri'            => true,
		'emptyText'          => 'Сообщений нет',
		//'pageSize'	=> $pageSize,
		//'namePreClip'	=> 'headList',
		'availablePageSizes' => Config::$userPageSizes,
		'htmlOptions'        => array('class' => 'msgList'),
		//'extraDivClass' => 'messages-list',
		//'afterAjaxUpdate' => 'js:function(){ var val = $("#inputSearch").val(); $("input.search").val( val ).focus(); }',
		//'beforeAjaxUpdate' => 'js:function(){ var val = $("input.search").val(); $("#inputSearch").val( val ); }'
	));
	?>
</div>


<?php // Рендерим попап для личных сообщений
$this->renderPartial('//member/message/_newMessage'); ?>

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