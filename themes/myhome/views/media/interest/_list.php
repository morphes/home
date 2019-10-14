<?php
$interestData = $interestProvider->getData();

//Формируем колонки если есть данные
if ($interestData) :

	$column1 = array();
	$column2 = array();
	$column3 = array();

	$column1[] = current($interestData);

	while (1) {
		$tmp = next($interestData);

		if (!$tmp) {
			break;
		}
		$column2[] = $tmp;

		$tmp = next($interestData);

		if (!$tmp) {
			break;
		}
		$column3[] = $tmp;

		$tmp = next($interestData);

		if (!$tmp) {
			break;
		}
		$column1[] = $tmp;
	}


	?>

	<!--Выводим первую колонку-->
	<div class="-col-3">
		<div class="-grid items">
			<?php foreach ($column1 as $cl) {
				echo InterestData::getItemHtml($cl);
			}
			?>

		</div>
	</div>

	<!--Выводим вторую колонку-->
	<div class="-col-6">
		<div class="-grid items">
			<?php
			//Устанавливаем номер позиции
			//Это необходимо что бы кажду третью картинку
			//в колонке выводить большой
			//Первоначально ставим 3 так как первую картику в колонке так же необходимо
			//вывести больной
			$position = 3;
			?>
			<?php foreach ($column2 as $cl) {
				//выводим большую картинку по
				//выполнию условия
				if ($position == 3) {
					echo InterestData::getItemHtml($cl, true);
					$position = 0;
				} else {
					echo InterestData::getItemHtml($cl);
				}
				$position = $position + 1;
			}
			?>
		</div>
	</div>

	<!--Выводим третью картинку-->
	<div class="-col-3">
		<div class="-grid items">
			<?php foreach ($column3 as $cl) {
				echo InterestData::getItemHtml($cl);
			}
			?>

		</div>
	</div>

	<?php

	if ($interestProvider->pagination->currentPage < $interestProvider->pagination->pageCount - 1) {
		echo CHtml::hiddenField(
			'next_page_url',
			Yii::app()->createUrl('media/knowledge/AjaxInterest', $params = array('page' => $interestProvider->pagination->currentPage + 1))
		);
	}

	?>

<?php endif; ?>