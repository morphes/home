<?php

class ModelTimeBehavior extends CActiveRecordBehavior
{
        public $nameCTime = 'create_time';
	public $nameUTime = 'update_time';

        function __construct()
        {
        }

        public function beforeSave($event)
        {
		$owner = $this->getOwner();

		if ($owner->getIsNewRecord())
			$owner->{$this->nameCTime} = $owner->{$this->nameUTime} = time();
		else
			$owner->{$this->nameUTime} = time();
        }

}
