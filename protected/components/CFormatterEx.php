<?php 

/**
 * @brief Обработка числительных слов
 * @author Sergey Seregin <sss@medvediza.ru>
 */
class CFormatterEx extends CFormatter {
	// Перечень размеров файлов
	public static $sizeNames = array('Б','Кб','Мб','Гб');
	
	/**
	 * @brief Возвращает правильное слово для указанного числительного
	 * @param integer $value Целое число
	 * @param array $words Массив из 3-х элементов. Например array('морковка', 'морковки', 'морковок')
	 * @param boolean $only_word Если флаг указан true, то возвращается только слово, без числа $value
	 * @return string 
	 */
	public static function formatNumeral($value, $words = array('комментарий', 'комментария', 'комментариев'), $only_word = false)
	{
		$num = (int)$value;
		
		$cases = array (2, 0, 1, 1, 1, 2);
		
		// Получаем правильное слово для указанного числа
		$result = $words[ (abs($num)%100 >4 && abs($num)%100< 20)? 2 : $cases[min(abs($num)%10, 5)] ];

		// Возвращаем вместе с числом или только слово.
		if ($only_word)
			return $result;
		else
			return $num.' '.$result;
	}
	
	public static function formatUsageTime($timeStart, $timeEnd=null )
	{
		if (is_null($timeEnd))
			$timeEnd = time();
		
		$timeStart = DateTime::createFromFormat('U', $timeStart);
		$timeEnd = DateTime::createFromFormat('U', $timeEnd);
		
		$dateInterval = date_diff($timeStart, $timeEnd);
		
		//$years = $dateInterval->format('%y');
		//$months = $dateInterval->format('%m');
		$days = $dateInterval->format('%a');
		/*
		$result = 'На сайте '
			.self::formatNumeral($years, array('год', 'года', 'лет')).', '
			.self::formatNumeral($months, array('месяц', 'месяца', 'месяцев'));
		 */
		$result = 'На сайте '.self::formatNumeral($days, array('день', 'дня', 'дней'));
		return $result;
	}
	
	public static function formatFileSize($size)
	{
		$cnt = 0;
		while ($size > 1024) {
			$size /= 1024;
			$cnt++;
		}
		return round($size, 2).self::$sizeNames[$cnt];
	}

	/**
	 * Форматрирует дату таким образом, что вчера и сегодня выводит словом (сегодня 13:24).
	 * Датам, которые не страее года выводятся в виде 17.08
	 * Даты, которые более года выводятся в виде 17.08.2011
	 *
	 * @static
	 */
	public static function formatDateToday($datetime, $showTime=true)
	{
		// Время в секундах на сегодняшнюю полночь
		$todayMidnight = mktime(0, 0, 0, date('m'), date('d'), date('Y'));

		if ($datetime >= $todayMidnight) {
			if ($showTime)
				return 'Сегодня в '.date('H:i', $datetime);
			else
				return 'Сегодня';
		} elseif (($todayMidnight - $datetime) <= 86400) {
			if ($showTime)
				return 'Вчера в '.date('H:i', $datetime);
			else
				return 'Вчера';
		} else {
			if ($showTime)
				return date('d.m.Y в H:i', $datetime);
			else
				return date('d.m.Y', $datetime);
		}
	}

	/**
	 * Формирование диапазона дат (30 июля — 4 августа, 2012)
	 * @static
	 * @param $timeStart
	 * @param $timeEnd
	 * @return string
	 */
	public static function formatDateRange($timeStart, $timeEnd, $delimiter='—', $showYear=true)
	{
		if (empty($timeEnd)) {
			$format = $showYear ? 'd MMMM, y' : 'd MMMM';
			return Yii::app()->getDateFormatter()->format($format, $timeStart);
		}
		if (date('m', $timeStart) == date('m', $timeEnd))
			$format = 'd';
		else
			$format = 'd MMMM';
		$start = Yii::app()->getDateFormatter()->format($format, $timeStart);

		$format = $showYear ? 'd MMMM, y' : 'd MMMM';
		$end = Yii::app()->getDateFormatter()->format($format, $timeEnd);
		return $start . ' '.$delimiter.' ' . $end;
	}
}