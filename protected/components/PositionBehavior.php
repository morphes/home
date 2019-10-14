<?php
/**
 * Это поведение реализоваывает для моделей свойтсво с позицией.
 * Обязательное наличие поля "position" и "update_time".
 * При смене позиции у любого элемента, делается пересчет с учетом всех остальных.
 *
 * Class PositionBehavior
 *
 * @property $whereLimitField
 */
class PositionBehavior extends CActiveRecordBehavior
{
	// Способ сортировки по времени, для перестроения позиций
	private $timeOrder = 'ASC';

	public $positionName = 'position';

	/**
	 * @var array Название поля в таблице, по которому нужно взять группу
	 * элементов для перенумерации.<br>
	 * Например, есть таблица с полями: <b>id, store_id, name, title</b>.
	 * Когда мы пытаемся обновить позицию элемента с идентификатором id,
	 * значние в поле  store_id этой записи будет использовано для того,
	 * чтобы найти другие элементы с таким же значением, и только их использовать
	 * для перенумерации.
	 */
	public $whereLimitField = '';

	/**
	 * @var string Значение поля, которое находися в поле $this->whereLimitField
	 */
	private $whereLimitValue = null;

        function __construct()
        {
        }


	public function beforeSave($event)
	{
		$this->setTimeOrder($this->getOwner());
	}


	public function afterSave($event)
	{

		$this->updatePosition($this->getOwner());
	}


	public function afterDelete($event)
	{
		$this->updatePosition($this->getOwner());
	}


	/**
	 * Меняет способ сортировки по времени, для перестроения позиций списка.
	 * Нужно для корректной перенумерации от большего к меньшему, и от меньшего к большему.
	 */
	private function setTimeOrder($owner)
	{
		$fields = array($this->positionName);
		if ($this->whereLimitField != '') {
			$fields[] = $this->whereLimitField;
		}

		// Получаем старое значение для позиции

		$res = Yii::app()->db
			->createCommand('SELECT ' . implode(',', $fields) . ' FROM ' . $owner->tableName() . ' WHERE id = :id')
			->bindValue(':id', $owner->id)
			->queryRow();

		$oldPos = intval($res[$this->positionName]);

		if ($this->whereLimitField != '') {
			$this->whereLimitValue = $res[$this->whereLimitField];
		}


		if ($oldPos == 0) {
			$this->timeOrder = 'DESC';
		} elseif ($owner->{$this->positionName} < $oldPos) {
			$this->timeOrder = 'DESC';
		} else {
			$this->timeOrder = 'ASC';
		}
	}


	/**
	 * Перенумеровывает позиции услуг с единицы.
	 * Если пользователь ставит две одинаковые позиции, то с меньшим номером станет последняя.
	 */
	private function updatePosition($owner)
	{
		if ($this->timeOrder != 'DESC' && $this->timeOrder != 'ASC') {
			throw new CHttpException(400, 'Неверное значение $timeOrder');
		}

		$sql = 'SET @a:=0;'
			. ' UPDATE ' . $owner->tableName()
			. ' SET ' . $this->positionName . ' = @a := @a + 1';

		// Дополнительное условие фильтрации
		if (!is_null($this->whereLimitValue)) {
			$sql .= ' WHERE ' . $this->whereLimitField . ' = ' . $this->whereLimitValue;
		}

		$sql .= ' ORDER BY ' . $this->positionName . ' ASC, update_time ' . $this->timeOrder;
		Yii::app()->db->createCommand($sql)->execute();
	}
}
