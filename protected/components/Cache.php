<?php

/**
 * @brief Конфигурация кэша приложения
 * @author Alexey Shvedov <alexsh@yandex.ru>
 */
class Cache
{
        const DURATION_MAIN_PAGE = 600;
        // Время кэша меню на фронтенде
        const DURATION_MENU = 86000;
        // Время кэша главного меню админки.
        const DURATION_ADMIN_MENU = 0;
        // Время кэша новостей
        const DURATION_NEWS = 86000;
	const DURATION_FAVORITE_COUNT = 86400;
	// Минимальное кэширование (малые выборки)
	const DURATION_REAL_TIME = 300;
	/** Кэширование пути до картинки */
	const DURATION_IMAGE_PATH = 1800;

	const DURATION_HOUR = 3600;
	const DURATION_DAY = 86400;

	/**
	 * Хранилище связанных с запросом данных
	 * @var array
	 */
	private static $_requestedData = array();
	private static $_instance = null;

	public function __construct() {}

	public static function getInstance()
	{
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Получение данных о кеше AR моделей для SEO rewrite
	 */
	public static function getCacheInfo()
	{
		$data = array();
		foreach (self::$_requestedData as $model) {
			if ( !$model instanceof CActiveRecord )
				continue;
			$data[ get_class($model) ] = $model->getPrimaryKey();
		}
		return $data;
	}

	/**
	 * Применение данных для восстановления кэша
	 * @param $data
	 */
	public static function applyCacheInfo($data) {
		if (!is_array($data))
			return;
		Yii::import('application.modules.catalog.models.MallBuild');
		Yii::import('application.modules.catalog.models.MallService');
		Yii::import('application.modules.member.models.Service');
		// обязательно подключение исп моделей
		foreach ($data as $class=>$pk) {

			if (class_exists($class)) {
				$lowName = strtolower($class);
				self::getInstance()->$lowName = CActiveRecord::model($class)->findByPk($pk);
			}
		}
	}

	public function __get($name)
	{
		$name = strtolower($name);
		if ( isset(self::$_requestedData[$name]) )
			return self::$_requestedData[$name];
		else
			return null;
	}

	public function __set($name, $value)
	{
		$name = strtolower($name);
		self::$_requestedData[$name] = $value;
		return true;
	}
}
