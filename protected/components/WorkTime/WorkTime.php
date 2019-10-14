<?php

/**
 * Класс предназначен для реализации дополнительных полей «Рабочего времени» у моделей.
 * У каждой модели заводится люб
 *
 *
 * @property string $attributes
 */
class WorkTime extends CComponent
{
        /**
         * @var string table name for logging mail's
         */
        private $model = null;

	private $data = null;

	// Имя свойства в модели $model, в которой хранятся записи о рабочем времени
	private $attrTime = 'work_time';

	// Набор полей, которые выводятся в форме в качестве рабочего времени
	private $fields = array(
		'weekdays' => 'Время работы в будние дни',
		'saturday' => 'Время работы в субботу',
		'sunday'   => 'Время работы в воскресение'
	);



        public function init(){}

	/**
	 * Устанавливает модель, для которой нужно выводить Рабочее время
	 *
	 * @param $model
	 * @return WorkTime
	 */
	public function setModel($model)
	{
		$this->model = $model;

		return $this;
	}

	/**
	 * Сохраняет пришедшие данные из формы во внутреннюю переменную
	 *
	 * @param array $post
	 * @return WorkTime
	 */
	public function setAttributes($post = array())
	{
		// Вычленяем нужные данные из POST'а
		$data = array_intersect_key($post, $this->fields);

		if ( ! empty($data)) {
			foreach($data as $key=>$value) {
				$this->data[$key] = array(
					'work_from'      => CHtml::encode($value['work_from'], 0),
					'work_to'        => CHtml::encode($value['work_to'], 0),
					'dinner_enabled' => CHtml::encode($value['dinner_enabled'], false),
					'dinner_from'    => CHtml::encode($value['dinner_from'], 0),
					'dinner_to'      => CHtml::encode($value['dinner_to'], 0)
				);
			}
		}

		return $this;
	}

	/**
	 * Задает набор полей, которые будут представлят собой записи о рабочем времени.
	 *
	 * @param array $fields Ассоциативный массив, ключями которого являются имена полей, значениями — рускоязычные описания.
	 * @return WorkTime
	 * @throws CHttpException
	 */
	public function setFields($fields = array())
	{
		if ( ! is_array($fields))
			throw new CHttpException(400, 'Входным параметром только ассоциативный массив');

		$this->fields = $fields;

		return $this;
	}

	/**
	 * Устанавливает имя переменной модели, в которой хранится рабочее время.
	 *
	 * @param $name
	 * @return WorkTime
	 * @throws CHttpException
	 */
	public function setAttrTime($name)
	{
		if (is_string($name) && $name != '')
			$this->attrTime = $name;
		else
			throw new CHttpException('400', 'В качестве имени может быть только строка');

		return $this;
	}


	/**
	 * Возвращает сериализованный массив данных рабочего времени
	 *
	 * @return mixed
	 */
	public function getSerialize()
	{
		return serialize($this->data);
	}


	/**
	 * Renders a view file.
	 * @param string $_viewFile_ view file path
	 * @param array $_data_ optional data to be extracted as local view variables
	 * @param boolean $_return_ whether to return the rendering result instead of displaying it
	 * @return mixed the rendering result if required. Null otherwise.
	 */
	public function render($_viewFile_, $_data_=null, $_return_=false)
	{
		if (is_null($this->model))
			throw new CHttpException(400, 'Модель не была проинициализированна. Используйте метод setModel');

		$path = Yii::getPathOfAlias('application.components.WorkTime.views.'.$_viewFile_);

		if ($path)
			$path .= '.php';
		else
			throw new CHttpException(400, "Невозможно подключить представление {$_viewFile_}");

		// Инициализируем данные для представления
		if (is_array($_data_))
			extract($_data_,EXTR_PREFIX_SAME,'data');

		// Модель используется в отрендериваемом представлении
		$model = $this->model;

		if ($_return_)
		{
			ob_start();
			ob_implicit_flush(false);
			require($path);
			return ob_get_clean();
		}
		else
			require($path);
	}
}