<?php

/**
 * @brief Создание DataProvider для выборки Sphinx
 * @author Alexey Shvedov <alexsh@yandex.ru>
 * @see DGSphinxSearch
 */
class CSphinxDataProvider extends CDataProvider
{
	/**
	 * @brief Parameters for sphinx search
	 */
	public $index = '*';
	public $sphinxClient = null;
	public $select = '*';
	public $matchMode = SPH_MATCH_ANY;
	public $sortMode = SPH_SORT_RELEVANCE;
	public $sortExpr = '';
	public $weight = array();
	public $group = array();
	public $query = '';
	public $cutoff = 0; // deprecated TODO: remove this shit :)
	public $filters = array();
	public $maxmatches = 10000; // Max matches for sphinx find
	public $useGroupAsPk = true; // Используется при группировке, менять если при группировке используется возвращаемый ключ
        public $filterRange = array(); // Используется для применения фильтра setFilterRange
        public $filterFloatRange = array(); // Используется для применения фильтра setFilterFloatRange

	/**
	 * @brief Attributes from sphinx, that set value to AR model
	 * array(sphAttr => ARAttr, ...)
	 * @var array
	 */
	public $additionalAttr = array();
	/**
	 * @brief Class name or CActiveRecord model, it's used in data finding 
	 * @var String/CActiveRecord
	 */
	public $modelClass = null;
	/**
	 * @var string the name of key attribute for {@link modelClass}. If not set,
	 * it means the primary key of the corresponding database table will be used.
	 */
	public $keyAttribute;
	/**
	 * @brief Model used in the data search
	 * @var CActiveRecord
	 */
	private $_model = null;
	private $_status = false;
	
	public function __construct($sphinxClient, $config = array())
	{
		if ($sphinxClient instanceof DGSphinxSearch) {
			$this->sphinxClient = $sphinxClient;
		}
		// Проверка подключения
		$this->_status = (bool)$this->sphinxClient->status();
		
		foreach($config as $key=>$value)
			$this->$key=$value;

		if(is_string($this->modelClass))
		{
			$this->_model=CActiveRecord::model($this->modelClass);
		} else { // Проверять, чтобы поддерживался метод findByPk
			$this->_model=$this->modelClass;
		}
	}

	
	/**
	 * @brief disable sorting
	 */
	public function getSort()
	{
		return false;
	}

	/**
	 * @brief Find keys
	 */
	protected function fetchKeys()
	{
		$keys=array();
		foreach($this->getData() as $i=>$data)
		{
			$key=$this->keyAttribute===null ? $data->getPrimaryKey() : $data->{$this->keyAttribute};
			$keys[$i]=is_array($key) ? implode(',',$key) : $key;
		}
		return $keys;
	}
	
	/**
	 * @brief Fetches the data from DB by PK, founded by sphinx.
	 * @return array list of data items
	 */
	protected function fetchData()
	{
		if (is_null($this->sphinxClient) || !$this->_status)
			return array();
		
		$limit = 1;
		$offset = 0;
		if(($pagination=$this->getPagination())!==false)
		{
                        $totalItemCount = $this->getTotalItemCount();

                        if($totalItemCount > $this->maxmatches)
                                $totalItemCount = $this->maxmatches;

			$pagination->setItemCount($totalItemCount);
			$limit = $pagination->getLimit();
			$offset = $pagination->getOffset();
		}
		$sphinx = $this->prepareQuery($limit, $offset);
		$result = $sphinx->query($this->query, $this->index);

                $data = array();
		foreach ($result['matches'] as $key => $value) {
			$pk = !empty($this->group['field']) && $this->useGroupAsPk ? $value['attrs'][$this->group['field']] : $key;
                        // сохраняет первый оригинальный ключ найденной записи при группировке записей
                        if(isset($this->group['save_original_key']) && $this->group['save_original_key']) {
                                if(empty($value['attrs']['original_key']))
                                        $value['attrs']['original_key'] = $key;
                        }

			$obj = $this->_model->findByPk($pk);
			if (!is_null($obj)) {
				foreach ($this->additionalAttr as $sphKey => $ARKey) {
					if (property_exists(get_class($obj), $ARKey) && isset($value['attrs'][$sphKey])) {
						$obj->{$ARKey} = $value['attrs'][$sphKey];
					}
				}
				$data[] = $obj;
			}
		}

		return $data;
	}
	
	/**
	 * @brief Calculates the total number of data items.
	 * @return integer the total number of data items.
	 */
	protected function calculateTotalItemCount()
	{
		if (is_null($this->sphinxClient) || !$this->_status)
			return 0;
		
		$sphinx = $this->prepareQuery(1, 0);
		$result = $sphinx->query($this->query, $this->index);
		return $result['total_found'];
	}
	
	/**
	 * @brief Prepare sphinxClient object to query
	 * @return DGSphinxSearch
	 */
	private function prepareQuery($limit, $offset)
	{
		$sphinx = clone($this->sphinxClient);
		$sphinx->setSelect($this->select);
		$sphinx->setMatchMode($this->matchMode);
		$sphinx->SetFieldWeights($this->weight);
		if ($this->group && is_array($this->group)) {
		    $sphinx->setGroupBy($this->group['field'], $this->group['mode'], $this->group['order']);
		}
		$sphinx->setSortMode($this->sortMode, $this->sortExpr);
		$sphinx->setLimits($offset, $limit, $this->maxmatches);

                /**
                 * SetFilter
                 */
                if ($this->filters && is_array($this->filters)) {
			foreach ($this->filters as $fil => $vol) {
				if (is_array($vol) && isset($vol['exclude']) && isset($vol['val'])) { // FIX for exclude filters
					$sphinx->SetFilter($fil, (is_array($vol['val'])) ? $vol['val'] : array($vol['val']), $vol['exclude']);
				} else {
					$sphinx->SetFilter($fil, (is_array($vol)) ? $vol : array($vol));
				}
			}
		}
                /**
                 * SetFilterRange
                 */
                if($this->filterRange && is_array($this->filterRange)) {
                        foreach($this->filterRange as $attr => $range) {
                                if(is_array($range) && isset($range['from']) && isset($range['to'])) {
                                        $sphinx->SetFilterRange($attr, $range['from'], $range['to']);
                                }
                        }
                }
		/**
		 * SetFilterFloatRange
		 */
		if($this->filterFloatRange && is_array($this->filterFloatRange)) {
			foreach($this->filterFloatRange as $attr => $range) {
				if(is_array($range) && isset($range['from']) && isset($range['to'])) {
					$sphinx->SetFilterFloatRange($attr, $range['from'], $range['to']);
				}
			}
		}


		return $sphinx;
	}
}
