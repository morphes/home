<?php

/**
 * Виртуальная модель, представляющая из себя общую сборку из всех моделей Журнала: Знания, Новости, События
 *
 * Доступны следующие поля в модели:
 * @property integer $item_id
 * @property integer $type
 * @property integer $name
 * @property integer $desc
 */
class Media //extends CActiveRecord
{
	// --- ТИПЫ ---
	const TYPE_KNOWLEDGE 	= 1; // Знания
	const TYPE_NEWS 		= 2; // Новости
	const TYPE_EVENT 	= 3; // События

	// Коэффициент для создания ID в индексе для разных типов Журнала
	const FACTOR = 10000000;

	public $item_id;
	public $type;
	public $name;
	public $desc;

	public $object = null;

	private static $_model=null;

	public static $typeNames = array(
		self::TYPE_KNOWLEDGE => 'Знания',
		self::TYPE_NEWS      => 'Новости',
		self::TYPE_EVENT     => 'События',
	);



	public static function model($className=__CLASS__)
	{
		if (is_null(self::$_model))
			self::$_model=new self();

		return self::$_model;
	}


	public function findByPk($id, $condition = '', $params = array())
	{
		// $id = $object['id'] + $type * Media::FACTOR;

		// Делим ID элемента из индекса на коэффициент, получаем тип
		$type = intval( $id / Media::FACTOR );
		if ( ! in_array($type, array(Media::TYPE_EVENT, Media::TYPE_KNOWLEDGE, Media::TYPE_NEWS)))
			throw new Exception('Invalid media type');


		switch ($type) {
			case Media::TYPE_KNOWLEDGE:
				$class = 'MediaKnowledge';
				break;
			case Media::TYPE_EVENT:
				$class = 'MediaEvent';
				break;
			case Media::TYPE_NEWS:
				$class = 'MediaNew';
				break;
		}

		$pk = $id - $type * Media::FACTOR;

		/** @var $object Media */
		$this->object = $class::model()->findByPk($pk, $condition, $params);

		if (is_null($this->object))
			return null;

		$model = new self();

		$model->type        = $type;
		$model->item_id     = $this->object->id;
		$model->name        = ($type == Media::TYPE_EVENT)
				      ? $this->object->name
				      : $this->object->title;
		$model->desc        = $this->object->content;
                $model->object      = $this->object;


		return $model;
	}

        public function getTypeName()
        {
                return self::$typeNames[$this->type];
        }

        public function getThemesLinks()
        {
                $themes = MediaTheme::model()->findAllByPk($this->object->themes);
                $class = $this->getClassName();
                $links = array();

                foreach ($themes as $theme)
                        $links[] = CHtml::link($theme->name, Yii::app()->createUrl($class::getSectionLink(), array('f_theme' => $theme->id)));

                return $links;
        }

        public function getClassName()
        {
                $class = 'Media';
                switch ($this->type) {
                        case Media::TYPE_KNOWLEDGE:
                                $class = 'MediaKnowledge';
                                break;
                        case Media::TYPE_EVENT:
                                $class = 'MediaEvent';
                                break;
                        case Media::TYPE_NEWS:
                                $class = 'MediaNew';
                                break;
                }
                return $class;
        }


	/**
	 * Получение списка случайных
	 * id статей
	 * опиционально можно
	 * указать категорию
	 *
	 * @param int   $size
	 * @param array $categories
	 *
	 * @return array
	 */
	static public function getRandomMediaIds($size = 6, $type = 1)
	{
		$mediaIds = array();

		$sql = 'SELECT item_id FROM {{media}} WHERE type = 1 ORDER BY RAND() LIMIT ' . $size;
		$mediaIds = Yii::app()->sphinx->createCommand($sql)->queryColumn();

		return $mediaIds;
	}
}