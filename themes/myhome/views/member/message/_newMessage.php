<?php
/**
 * Способ работы "Нового сообщения".
 *
 * Всплывающая форма получает от пользователя данные (имя, сообщение, файлы)
 * Затем отправляет форму на //member/message/create методом POST.
 * Метод create обрабатывает данные и редиректит обратно на ту страницу,
 * с которой была отправлена формы. Передает параметры, чтобы сразу
 * открлся папап "Новое сообщение". В setFlash устанавливает ошибки/гуды.
 */

// URL для обратного редиректа
$params = array();

$id = Yii::app()->request->getParam('id');
if ($id)
	$params['id'] = $id;

$query = Yii::app()->request->getParam('qsearch');
if ($query)
	$params['qsearch'] = $query;

Yii::app()->user->setReturnUrl($this->createUrl('/' . preg_replace('/\?(.*)$/', '', Yii::app()->request->getUrl()), $params));

// Если при отправке были ошибки, нужно показать окно сообщения.
if (isset($_REQUEST['msg']) && $_REQUEST['msg'] == 'show') {
	Yii::app()->clientScript->registerScript('newMessage', '
		$("#new_message").trigger("click");
	', CClientScript::POS_READY);
}
?>

<script>
	$(function () {
		// Инициализация отображения попапа личных сообщений
		CCommon.userMessage();
	})
</script>

<?php // Если все отправилось ОК, тогда показываем попап с благодарностями ?>
<?php if (isset($_REQUEST['send']) && $_REQUEST['send'] == 'good') { ?>
	<div class="-hidden">
		<div class="popup popup-message-good -col-6"
		     id="popup-message-good">
			<div class="popup-header">
				<div class="popup-header-wrapper">
					Написать сообщение
				</div>
			</div>
			<div class="lb_body">
				Ваше сообщение отправлено
			</div>
		</div>
	</div>
	<script>
		CCommon.userMessageGood();
	</script>
<?php } ?>


<?php // НОВОЕ сообщение ?>
<div class="-col-wrap -inset-all-dbl -hidden -white-bg"
     id="popup-message">
	<div class="-grid">
		<h2 class="-giant -col-6">Написать сообщение</h2>
		<?php
		$body = new MsgBody('create');
		$form = $this->beginWidget('CActiveForm', array(
			'action'               => '/member/message/create',
			'id'                   => 'msg-body-create-form',
			'enableAjaxValidation' => false,
			'htmlOptions'          => array('enctype' => 'multipart/form-data', 'class' => '-form-block'),
		)); ?>

		<?php if (Yii::app()->user->hasFlash('msg_list_error')): ?>
			<div class="error-title -col-wrap"
			     style="display: block;">
				<?php echo Yii::app()->user->getFlash('msg_list_error'); ?>
			</div>
		<?php endif; ?>

		<label class="-block -col-7"
		       for=>
			<strong>Кому</strong>


			<?php
			if (isset($_REQUEST['msg']) && $_REQUEST['msg'] == 'show') {
				$create = "\$(\"#recipient\").autocomplete( \"search\");";
			} else {
				$create = '';
			}

			$htmlOptions = array('class' => 'textInput -col-7 ');
			if (isset($userId))
			{
				$htmlOptions['disabled'] = 'disabled';
				$htmlOptions['class'] = 'textInput -col-7 -gray';
				?>
				<script>
					CCommon.setOptions({'userId':<?php echo $userId ?>});;
				</script>

			<?php
			}


			$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
				'name'        => 'recipient',
				'sourceUrl'   => '/utility/autocompleteuser',
				'value'       => (isset($userName)) ? $userName
					: @$_REQUEST['recipient'],
				'options'     => array(
					'showAnim'  => 'fold',
					'delay'     => 10,
					'autoFocus' => true,
					'create'    => (!isset($userId))
						? 'js:function(event, ui) { $("#recipient").autocomplete( "search"); }'
						: '',
					'select'    => 'js:function(event, ui) { $("#MsgBody_recipient_id").val(ui.item.id); }',

				),
				'htmlOptions' => $htmlOptions,
				'themeUrl'    => '',
				'theme'       => 'css',
				'cssFile'     => 'jquery-ui-1.8.18.custom.css',
				'scriptUrl'   => '/js',
				'scriptFile'  => 'jquery-ui-1.8.18.custom.min.js',
			));
			echo $form->hiddenField($body, 'recipient_id', array(
				'value'              => (isset($userId))
					? $userId
					: 0, 'class' => 'recipient'
			));
			?>
		</label>

		<label for="p-message-body"
		       class="-block -col-7">
			<strong>Сообщение</strong>
			<?php echo $form->textArea($body, 'message', array('id' => 'p-message-body', 'class' => 'text -col-7 -gray', 'rows'=>7)); ?>

		</label>

		<div class="-col-7 -block -gutter-top -gutter-bottom-dbl MultiFile">
			<!--<input class="" size="61" id="userMessage_files" type="file" value="" name="MsgBody[attach][]"> -->
			<?php $this->widget('CMultiFileUpload',
				array(
					'model'       => $body,
					'attribute'   => 'attach',
					'accept'      => 'jpg|jpeg|png|bmp|zip|pdf',
					'denied'      => 'Данный тип файла запрещен к загрузке',
					'max'         => 10,
					'remove'      => ' ',
					'duplicate'   => 'Уже выбран',
					'htmlOptions' => array('class' => '', 'id' => "userMessage_files"),
					'options'     => array(
						'afterFileAppend' => 'js:function (element, value, master_element) {
									var selector = master_element.list.selector;
									$(selector).appendTo("#fileslist");
								}',
					)
				)
			);?>

			<div class="MultiFile-select">
				<span class="-icon-attach -icon-red -pseudolink -red"><i>Прикрепить
											 файл</i></span>
			</div>

			<div id="fileslist">

			</div>

		</div>

		<div class="-col-7 -gutter-top">
			<button class="-button -button-skyblue -col-wrap"
				onclick="_gaq.push(['_trackEvent','Message','Отправить']); yaCounter11382007.reachGoal('grbtmsend'); return true;">
				Отправить
			</button>
		</div>

		<?php $this->endWidget(); ?>
	</div>
</div>