<?php

/**
 * @brief This widget render a few stars.
 */
class WDateSelector extends CWidget
{

	/**
	 * @var 
	 */
	public $name = 'birthday';

	/**
	 *
	 * @var 
	 */
	public $value = '0.0.0000';

	private $day;
	private $month;
	private $year;
	
	public function init()
	{
		if (empty($this->value))
			$this->value = '0.0.0000';
		
		// Разбираем дату
		$arr = explode('.', $this->value);
		
		$this->day = (int)$arr[0];
		$this->month = (int)$arr[1];
		$this->year = (int)$arr[2];
	}

	public function run()
	{
		
		// Заполняем и выводим ДНИ
		$days = array();
		for ($i = 1; $i <= 31; $i++) {
			$days[ $i ] = $i;
		}
		echo CHtml::dropDownList('', $this->day, array('0' => '')+$days, array('id' => 'fp-bd-day', 'class' => 'selectInput dateSelector'));
		
		
		
		// Выводим МЕСЯЦА
		echo CHtml::dropDownList(
			'', $this->month,
			array('0' => '')+array('1' => 'Января', '2' => 'Февраля', '3' => 'Марта', '4' => 'Апреля', '5' => 'Мая', '6' => 'Июня', '7' => 'Июля', '8' => 'Августа', '9' => 'Сентября', '10' => 'Октября', '11' => 'Ноября', '12' => 'Декабря'),
			array('id' => 'fp-bd-month', 'class' => 'selectInput dateSelector')
		);
		
		
		
		// Заполняем и выводим ГОДА
		$years = array();
		for ($i = date('Y') - 10, $ci = date('Y')-100; $i >= $ci; $i --) {
			$years[ $i ] = $i;
		}
		echo CHtml::dropDownList('', $this->year, array('0000' => '')+$years, array('id' => 'fp-bd-year', 'class' => 'selectInput dateSelector'));
		
		
		
		// Добавляем скрытый Input, в который будет сохранятся сформированная строка даты для сохранения.
		echo CHtml::hiddenField($this->name, '0.0.000', array('id' => 'resultDateSelector'));
		
		Yii::app()->clientScript->registerScript('dateSelector', '
			$(".dateSelector").change(getDateSelector);

			Array.prototype.in_array = function(p_val) {
				for(var i = 0, l = this.length; i < l; i++)	{
					if(this[i] == p_val) {
						return true;
					}
				}
				return false;
			}
			
			function getDateSelector()
			{
				var day = $("#fp-bd-day").val();
				var month = $("#fp-bd-month").val();
				var year = $("#fp-bd-year").val();

				// дни в которых всегда 31 день
				var hiDay = [1, 3, 5, 7, 8, 10, 12];

				if ( ! hiDay.in_array(month) && day == 31)
				{
					day = 30;
					$("#fp-bd-day").val(day);
				}
				else if (month == 2 && day > 28)
				{
					if ( (year % 4) == 0)
						day = 29;
					else
						day = 28;
					$("#fp-bd-day").val(day);
				}

				
				$("#resultDateSelector").val( day+"."+month+"."+year );
			}
			
			getDateSelector();
		');
	}

}

?>