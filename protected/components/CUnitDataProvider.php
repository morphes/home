<?php

/**
 * @brief Создание DataProvider для конфигурационных массивов Unit
 * @author Alexey Shvedov <alexsh@yandex.ru>
 * @see UnitController
 */
class CUnitDataProvider extends CDataProvider
{
	// input unit data array
	private $inputData = null;
	
	public function __construct(array $data, $config = array())
	{
		$this->inputData = $data;
		foreach($config as $key=>$value)
			$this->$key=$value;
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
			$keys[$i]=$data->id;
		}
		return $keys;
	}
	
	/**
	 * @brief Fetches the data from inpet array.
	 * @return array list of data items
	 */
	protected function fetchData()
	{
		if (empty($this->inputData['unitData']))
			return array();
		
		$limit = 1;
		$offset = 0;
		if(($pagination=$this->getPagination())!==false)
		{
			$pagination->setItemCount($this->getTotalItemCount());
			$limit = $pagination->getLimit();
			$offset = $pagination->getOffset();
		}

		$inputData = array_slice($this->inputData['unitData'], $offset, $limit, true);
		
		$data = array();
		foreach ($inputData as $key => $item) {
			$object = new stdClass();
			foreach ($item as $itemKey => $itemVal) {
				$object->$itemKey = $itemVal;
			}
			$object->id = $key;
			$data[] = $object;
		}
		
		return $data;
	}
	
	/**
	 * @brief Calculates the total number of data items.
	 * @return integer the total number of data items.
	 */
	protected function calculateTotalItemCount()
	{
		if (empty($this->inputData['unitData']))
			return 0;
		
		return count($this->inputData['unitData']);
	}
	
}

