<?php
$this->breadcrumbs=array(
	'CSV'=>array('index'),
	'Список заданий',
);
?>

<script type="text/javascript">
	$(function(){

		// Клик на кнопку "Скачать"
		$('.task_file_download').click(function(){
			if ( $(this).hasClass('disabled') )
				return false;
		});

		// Клик на кнопку "Удалить"
		$('.task_delete').click(function(){
			if ( $(this).hasClass('disabled') ) {
				return false;
			} else {
				var $tr = $(this).parents('tr');
				var id = $tr.data('task_id');
				$.get(
					'/catalog2/admin/catCsv/deleteTask/id/'+id,
					function(response) {
						if (response.success) {
							$tr.hide('medium');
						} else {
							alert(response.errorMsg);
						}
					}, 'json'
				)

				return false;
			}
		});

		var updateTasks = function() {
			// Собираем список id задач, для которых нужно обновить состояние
			var ids = [];
			$('table.task_list tbody tr.for_update').each(function(index, element){
				var task_id = $(element).data('task_id');
				ids.push(task_id);
			});

			if (ids.length > 0) {
				$.post(
					'/catalog2/admin/catCsv/getProgressTasks',
					{task_ids: ids},
					function(response){
						if (response.success) {

							for (var i = 0; i < response.tasks.length; i ++)
							{
								var task = response.tasks[i];
								// Строка таблицы, в которой находятся данные по задаче
								var tr = $('#task'+task['id']);

								// Обновляем прогресс.
								tr.find('.task_progress').html(task['progress']+'%');
								// Обновляем статус
								tr.find('.task_status').html(task['statusHtml']);
								// Обновляем время в работе
								tr.find('.task_work_time').html(task['workTime']);

								if (task['status'] == '<?php echo CatCsv::STATUS_FINISHED;?>')
									tr.find('.task_file_download').attr('href', task['file']).removeClass('disabled');

								if (task['status'] == '<?php echo CatCsv::STATUS_FINISHED;?>' || task['status'] == '<?php echo CatCsv::STATUS_FAILED;?>')
									tr.find('.task_delete').removeClass('disabled');


								// Если обработка по задаче занкончена снимаем его с обновлений.
								if (task['status'] == '<?php echo CatCsv::STATUS_FINISHED;?>' || task['status'] == '<?php echo CatCsv::STATUS_FAILED;?>')
									tr.removeClass('for_update');

							}

							setTimeout(updateTasks, 1000);

						} else {
							alert(response.errorMsg);
						}
					}, 'json'
				);
			}
		};

		setTimeout(updateTasks, 1000);
	});
</script>

<h1>Список заданий</h1>

<table class="bordered-table zebra-striped task_list">
	<thead>
	<tr>
		<th>ID</th>
		<th>Действие</th>
		<th>Инициатор</th>
		<th>Тип</th>
		<th>Статус</th>
		<th>Прогресс</th>
		<th>Данные</th>
		<th>Дата создания</th>
		<th>Время в работе</th>
		<th>Операции</th>
	</tr>
	</thead>
	<tbody>
	<?php
	$i = 1;

	/** @var $task CatCsv */
	foreach ($models as $task) {
		$trOptions = array();
		$trOptions['id'] = 'task'.$task->id;
		$trOptions['data-task_id'] = $task->id;

		if ($task->status == CatCsv::STATUS_NEW || $task->status == CatCsv::STATUS_IN_PROGRESS)
			$trOptions['class'] = 'for_update';

		echo CHtml::openTag('tr', $trOptions);

		// ID задачи
		echo CHtml::tag('td', array(), '#'.$task->id);

		// Операция (export | import)
		echo CHtml::tag('td', array(), $task->action);

		// Имя инициатора задачи
		echo CHtml::tag('td', array(), $task->author->name);

		// Тип задачи
		echo CHtml::tag('td', array(), $task->type);

		// Статус
		echo CHtml::tag('td', array('class' => 'task_status'), $task->getStatusColor());

		// Процент завершения
		echo CHtml::tag('td', array('class' => 'task_progress'), $task->getProgressPercent().'%');

		/* ---------------------
		 *  Данные по задаче
		 * ---------------------
		 */
		switch($task->type) {
			case CatCsv::TYPE_FOR_VENDORS:
				$vendors = $task->getVendors();
				$htmlData = '<strong>Производители</strong><br>';
				$htmlData .= '<ul>';
				foreach ($vendors as $vendor) {
					$htmlData .= '<li>'.$vendor->name.'</li>';
				}
				$htmlData .= '</ul>';
				break;

			case CatCsv::TYPE_STORE:
				$store = $task->getStore();
				if ($store) {
					$htmlData = '<strong>Магазин</strong><br>';
					$link = CHtml::link($store->name, '/catalog2/admin/store/goods/id/'.$store->id);
					$htmlData .= '«'.$link.'», г.'.$store->city->name.', '.$store->address;
				} else {
					$htmlData = $link = '';
				}

				break;
			case CatCsv::TYPE_CONTRACTOR:
				$contractor = Contractor::model()->findByPk($task->item_id);
				if (is_null($contractor)) {
					$htmlData = '';
				} else {
					$htmlData = '<strong>Контрагент:</strong><br>'.$contractor->name.'';
				}
				break;

			default:
				$htmlData = '';
		}
		echo CHtml::tag('td', array(), $htmlData);


		// Дата создания
		echo CHtml::tag('td', array(), date('d.m.Y', $task->create_time).'<br><strong>'.date('H:i:s', $task->create_time).'</strong>');

		// Время в работе
		echo CHtml::tag('td', array('class' => 'task_work_time'), $task->getWorkTime() );


		// Кнопки скачать и удалить
		switch($task->action) {
			case 'export':
				$btnDownloadOptions = array('class' => 'btn success small task_file_download');
				if ($task->status != CatCsv::STATUS_FINISHED) {
					$btnDownloadOptions['class'] .= ' disabled';
					$url = '#';
				} else {
					$url = '/download/catcsv/id/'.$task->id;
				}
				$btnDownload = CHtml::link('Скачать', $url, $btnDownloadOptions);

				// Если задача окончена, но файла нет, то не выводит кнопку "скачать"
				if ($task->status == CatCsv::STATUS_FINISHED && ! $task->file)
					$btnDownload = '';


				$btnDeleteOptions = array('class' => 'btn danger small task_delete');
				if ($task->status != CatCsv::STATUS_FINISHED && $task->status != CatCsv::STATUS_FAILED)
					$btnDeleteOptions['class'] .= ' disabled';
				$btnDelete = CHtml::link('Удалить', '#', $btnDeleteOptions);


				$htmlButtons = $btnDownload.'&nbsp;'.$btnDelete;
				break;

			case 'import':
				$btnDeleteOptions = array('class' => 'btn danger small task_delete');
				if ($task->status != CatCsv::STATUS_FINISHED && $task->status != CatCsv::STATUS_FAILED)
					$btnDeleteOptions['class'] .= ' disabled';
				$btnDelete = CHtml::link('Удалить', '#', $btnDeleteOptions);

				$htmlButtons = $btnDelete;
				break;
			default:
				$htmlButtons = '';
		}
		echo CHtml::tag('td', array(), $htmlButtons);




		echo CHtml::closeTag('tr');
	}
	?>
	</tbody>
</table>
