<?php

/**
 * This is the model class for table "cat_store_price".
 *
 * The followings are the available columns in table 'cat_store_price':
 * @property integer $store_id
 * @property integer $product_id
 * @property float $price
 * @property integer $discount
 * @property integer $status
 * @property integer $by_vendor
 * @property integer $price_type
 * @property string $url
 * @property integer $create_time
 * @property integer $update_time
 */
class StorePrice extends Catalog2ActiveRecord
{
	// Переменная используется для нумерации выводимых записей из ДатаПровайдера
	static public $index = 1;

        /**
         * статусы товара в магазине
         */
        const STATUS_ORDER = 1; // под заказ
        const STATUS_AVAILABLE = 2; // в наличии
        const STATUS_NOT_AVAILABLE = 3; // нет в наличии

        static public $statuses = array(
            self::STATUS_ORDER     => 'Под заказ',
            self::STATUS_AVAILABLE => 'В наличии',
            self::STATUS_NOT_AVAILABLE => 'Нет в наличии',
        );

        /**
         * Типы цены
         */
        const PRICE_TYPE_EQUALLY = 1; // равно
        const PRICE_TYPE_MORE = 2; // от
        //const PRICE_TYPE_REQUEST = 3; // по запросу

        static public $price_types = array(
		self::PRICE_TYPE_EQUALLY => 'Равно',
		self::PRICE_TYPE_MORE    => 'От',
                //self::PRICE_TYPE_REQUEST => 'По запросу',
        );

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return StorePrice the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'cat_store_price';
	}

	public function init()
	{
		parent::init();
		$this->onBeforeSave = array($this, 'setDate');
	}

	public function setDate()
	{
		if ($this->isNewRecord)
			$this->create_time = $this->update_time = time();
		else
			$this->update_time = time();
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('store_id, product_id, create_time, update_time, status, price_type, by_vendor', 'numerical', 'integerOnly'=>true),
			array('price', 'numerical'),
			array('discount', 'numerical', 'min' => 0, 'max' => 100, 'tooBig' => 'Скидка максимум 100%'),
                        array('status', 'in', 'range'=>array(self::STATUS_AVAILABLE, self::STATUS_ORDER)),
                        array('price_type', 'in', 'range'=>array(self::PRICE_TYPE_EQUALLY, self::PRICE_TYPE_MORE)),
			array('url', 'url', 'allowEmpty' => true,
//					     'message' => 'Неправильный URL',
//					     'pattern'=>'/^(http(s?)\:\/\/)?(([0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя:][0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя:_-]*)(\.[0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя:][0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя:\\._-]*)+(\/[0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя:\\.][0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя:_\\.-]*)*(\/?(\?([0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя][-0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя_\[\]]*(=[-0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя:\\/\\._\[\]\,\'\\\+%\$#]*){0,1}(&[0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя:][-0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя:_\[\]]*(=[-0-9a-zA-ZА-Яабвгдеёжзийклмнопрстуфхцчшщъыьэюя:_\[\]\,\'\\\+%\$#]*){0,1})*){0,1})?))$/i',
			),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('store_id, product_id, available, url, price_type, create_time, update_time', 'safe', 'on'=>'search'),
		);
	}
        
	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'store' => array(self::BELONGS_TO, 'Store', 'store_id'),
			'product' => array(self::BELONGS_TO, 'Product', 'product_id')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'store_id'      => 'Магазин',
			'product_id'    => 'Товар',
			'price'         => 'Цена',
			'discount'      => '% cкидки',
			'create_time'   => 'Дата создания',
			'update_time'   => 'Дата обновления',
			'url'   	=> 'URL товара в интернет-магазине',
                        'status'        => 'Наличие',
                        'price_type'    => 'Тип указанной цены',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('store_id',$this->store_id);
		$criteria->compare('product_id',$this->product_id);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Привязывает цены товаров $products к магазину $store
	 * @param $products Product[] Массив продуктов
	 * @param $store Store Магазин, к которому делается привязка.
	 */
	static public function savePrices($products, $store)
	{
		// Генерим sql запрос на встаку еще не связанных товаров с магазином
		$sqlInsert = 'INSERT INTO cat_store_price (`store_id`, `product_id`, `status`, `price_type`, `create_time`, `update_time`) VALUES';
		$sqlValues = '';

		foreach($products as $prod)
		{
			$currentTime = time();

			/** @var $model StorePrice */
			$model = StorePrice::model()->findByAttributes(array('store_id' => $store->id, 'product_id' => $prod->id));
			if ( ! $model)
			{
				if ($sqlValues != '')
					$sqlValues .= ',';
				$sqlValues .= "('{$store->id}', '{$prod->id}', '".StorePrice::STATUS_AVAILABLE."', '".StorePrice::PRICE_TYPE_EQUALLY."', {$currentTime}, {$currentTime})";
			}
			elseif ($model->by_vendor == 1)
			{	// Если связку товара с магазинам нашли, а она была фейковая — меняем ее на настоящую
				StorePrice::model()->updateByPk(array('store_id'=>$store->id, 'product_id' => $prod->id), array(
					'by_vendor' => 0
				));
			}
		}

		// Если есть, что вставить в базу, делаем INSERT
		if ($sqlValues != '') {
			Yii::app()->dbcatalog2->createCommand($sqlInsert.' '.$sqlValues)->execute();
		}
	}


	/**
	 * Возвращает input для ввода цены в списке товаров
	 */
	public function getPriceHtml()
	{

		$html = '';

		// Инпут с ценой
		$html .= CHtml::textField(
			'price',
			$this->price,
			array(
				'class'    => 'prod_price span3',
				'tabindex' => StorePrice::$index++,
				'data-sid' => $this->store_id,
				'data-pid' => $this->product_id
			)
		);
		// Лоадер обновления цены
		$html .= CHtml::image(
			'/img/load.gif',
			'',
			array('style' => 'vertical-align: text-bottom; visibility: hidden;')
		);

		echo $html;
	}

	public function getDiscountHtml()
	{
		$html = '';

		// Инпут поле для ввода скидки
		$html .= CHtml::textField(
			'discount',
			$this->discount,
			array(
				'class'    => 'prod_discount span2',
				'data-sid' => $this->store_id,
				'data-pid' => $this->product_id
			)
		);

		// Лоадер обновления цены
		$html .= CHtml::image(
			'/img/load.gif',
			'',
			array('style' => 'vertical-align: text-bottom; visibility: hidden;')
		);

		echo $html;
	}


	/**
	 * Возвращает предложение по цене для товара $pid
	 *
	 * @param $pid
	 * @return array array(
	 * 	'min' => 'средняя цена',
	 * 	'max' => 'максимальная цена'
	 * )
	 */
	static public function getPriceOffer($pid)
	{
		// Список цен товара в разных магазинах
		$priceRes = array(
			'min' => 0.0,
			'mid' => 0.0,
			'max' => 0.0
		);

		$prices = array();
		$models = StorePrice::model()->findAllByAttributes(array('product_id' => (int)$pid), 'price > 0');
		foreach ($models as $model) {
			$prices[] = $model->price;
		}

		$qntPrices = count($prices);

		// Если только одно предложение по цене, то выводить "среднюю" цену и не выводить цену "от"
		if ($qntPrices == 1)
		{
			$priceRes['mid'] = $prices[0];
		}
		elseif ($qntPrices > 1)
		{
			$priceRes['min'] = min($prices);
			$priceRes['mid'] = array_sum($prices) / $qntPrices;
			$priceRes['max'] = max($prices);
		}

		return $priceRes;
	}


	/**
	 * Задает размер скидки. Ограничивает ее интервалом [0; 100]
	 *
	 * @param $discount
	 */
	public function setDiscount($discount) {
		// Ограничиваем скиду Интервалом [0; 100]
		$discount = min(100, max((float)$discount, 0));
		$discount = round($discount, 4);

		$this->discount = $discount;
	}


	/**
	 * Получить размер скидки в числовом
	 * приставлении
	 */
	public function  getNumberDiscount($value = false) {
		if($value) {
			$discountValue = $value;
		} else {
			$discountValue = $this->discount;
		}
		if($this->price > 0) {
			$discount = $this->price - $this->price * ($discountValue / 100);
			return round($discount);
		}
		return null;
	}


	/**
	 * @param $discountNumber
	 *
	 * @return float|null
	 */
	public function convertNumberDiscount($discountNumber) {

		if($this->price>0) {
			$tmp = $this->price - $discountNumber;
			$result = $tmp/($this->price/100);

			return $result;
		}

		return null;
	}

}