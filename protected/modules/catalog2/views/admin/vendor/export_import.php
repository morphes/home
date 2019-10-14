<?php
$this->breadcrumbs=array(
	'Производители'=>array('index'),
	'Экспорт-импорт товаров'
);
?>

<h1>Экспорт-импорт товаров производителя «<?php echo $vendor->name;?>»</h1>

<div class="well">
	<div class="span12">
		<h2>Эскпорт</h2>
		<p>
			При нажатии на кнопку "экспорт" все товары текущего производителя, имеющие статус
			Product::STATUS_ACTIVE, будут выгружены в виде CSV файла. В файле поля разделены
			символами «;» (точка с запятой). Кодировка WINDOWS-1251. Первая строка содержит названия
			столбцов.
		</p>
		<br>

		<?php if (Yii::app()->user->hasFlash('alreadyExport')) : ?>
		<div class="row">
		<span class="alert-message warning span7">
			<?php echo Yii::app()->user->getFlash('alreadyExport');?>
		</span>
		</div>
		<?php endif; ?>

		<div class="row">
			<div class="span2">
				<form action="" method="post">
					<input type="hidden" name="action_name" value="export">
					<input type="hidden" name="vendor_id" value="<?php echo $vendor->id;?>">

					<input type="submit" class="btn danger" value="Экспорт">
				</form>
			</div>
			<div class="span6 container_load">
				<?php
				if ( ! $taskExport)
				{
					echo 'Файл еще не был экспортирован.';
				}
				elseif ($taskExport && $taskExport->status == CatExportCsv::STATUS_FAILED)
				{
					echo 'Ошибка! Возникли проблемы при экспорте в файл.';
				}
				elseif ($taskExport && $taskExport->status == CatExportCsv::STATUS_FINISHED)
				{
					if ($taskExport->download_file) {
						// Экспорт завершен
						$info = pathinfo($taskExport->download_file);
						echo CHtml::link('Скачать: '.$info['basename'], '/download/catalogCsv/id/'.$taskExport->id, array('class' => 'btn info'));
					} else {
						echo 'Файл не создан, нет подходящих товаров для экспорта.';
					}

				}
				else
				{	// Экспортирование в процессе.
					?>
					<div class="meter animate" id="export_progress">
						<span style="width: 0%" class="bar"><span></span></span>
						<i>0%</i>
					</div>
					<script type="text/javascript">

						$(function(){
							setTimeout(getStatus, 500);

							function getStatus()
							{
								$.post(
									'/catalog2/admin/vendor/exportImport/vid/<?php echo $vendor->id;?>',
									{action_name: 'exportStatus'},
									function(response){
										var $bar = $('#export_progress');

										if (response.status == '<?php echo CatExportCsv::STATUS_IN_PROGRESS;?>')
										{
											var percent = parseInt(response.doneItems * 100 / response.totalItems);
											$bar.find('.bar').css('width', percent+'%');
											$bar.find('i').text(percent+'%');

											setTimeout(getStatus, 500);
										}
										else if (response.status == '<?php echo CatExportCsv::STATUS_FINISHED;?>')
										{
											$bar.find('.bar').css('width', '100%');
											$bar.find('i').text('100%');

											if (response.download_file) {
												var link = $('<a>');
												link.attr('href', response.download_file);
												link.attr('class', 'btn info');
												link.html('Скачать CSV файл');
											} else {
												var link = 'Файл не создан, нет подходящих товаров для экспорта.';
											}
											$('.container_load').html(link);
										}
									}, 'json'
								);
							}
						});
					</script>
					<?php
				}
				?>
			</div>
		</div>
	</div>
</div>



<div class="well">
	<div class="span12">
		<h2>Импорт</h2>
		<p>
			Чтобы обновить цены у товаров текущего производителя, необходимо загрузить CSV файл
			в кодировке WINDOWS-1251 и нажать кнопку "Импорт". Поля должны быть разделены «;».
			Первой строкой обязательно указываются названия столбцов.
			Формат импортируемого файла: <br>
			<strong>"PID", "Название товара", "Категория", "Цена", "URL"</strong>
			<br>
			Для поля "Цена" разрешается использоватль только цифры, и разделитель десятичной части «.» (точка).
		</p>

		<?php if (Yii::app()->user->hasFlash('importError')) : ?>
		<div class="row">
		<span class="alert-message warning span7">
			<?php echo Yii::app()->user->getFlash('importError');?>
		</span>
		</div>
		<?php endif; ?>

		<div class="row">
			<div class="span7">

				<form action="" method="post" enctype="multipart/form-data">
					<input type="hidden" name="action_name" value="import">
					<input type="hidden" name="vendor_id" value="<?php echo $vendor->id;?>">

					<input type="file" name="file_csv" class=""><br><br>

					<button class="btn primary">Импорт</button>
				</form>
			</div>
		</div>
		<div class="row">
			<div class="span7 container_import">
				<?php
				if ( ! $taskImport)
				{
					echo 'Импорт цен не производился.';
				}
				elseif ($taskImport && $taskImport->status == CatImportCsv::STATUS_FAILED)
				{
					echo 'Ошибка! Возникли проблемы при импорте цен.';
				}
				elseif ($taskImport && $taskImport->status == CatImportCsv::STATUS_FINISHED)
				{
					$len = $taskImport->update_time - $taskImport->create_time;
					echo 'Последний импорт был '.date('d.m.Y H:i').'. Длился '.$len.' сек.';
				}
				else
				{	// Импортирование в процессе.
					?>
					<div class="meter animate span6" id="import_progress">
						<span style="width: 0%" class="bar"><span></span></span>
						<i>0%</i>
					</div>
					<script type="text/javascript">

						$(function(){
							setTimeout(getStatus, 500);

							function getStatus()
							{
								$.post(
									'/catalog2/admin/vendor/exportImport/vid/<?php echo $vendor->id;?>',
									{action_name: 'importStatus'},
									function(response){
										var $bar = $('#import_progress');

										if (response.status == '<?php echo CatImportCsv::STATUS_IN_PROGRESS;?>')
										{
											var percent = parseInt(response.doneItems * 100 / response.totalItems);
											$bar.find('.bar').css('width', percent+'%');
											$bar.find('i').text(percent+'%');

											setTimeout(getStatus, 500);
										}
										else if (response.status == '<?php echo CatImportCsv::STATUS_FINISHED;?>')
										{
											$bar.find('.bar').css('width', '100%');
											$bar.find('i').text('100%');

											document.location.href = '/catalog2/admin/vendor/exportImport/vid/<?php echo $vendor->id;?>';
											$('.container_import').html('Импорт завершен.');
										}
									}, 'json'
								);
							}
						});
					</script>
					<?php
				}
				?>
			</div>
		</div>
	</div>
</div>

