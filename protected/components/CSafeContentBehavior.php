<?php

class CSafeContentBehavior extends CActiveRecordBehavior
{

        public $attributes = array();
	public $options = array('HTML.AllowedElements'=>array());
        protected $purifier;

        function __construct()
        {
        }

        public function beforeSave($event)
        {
		if (is_null($this->purifier)) {
			$this->purifier = new CHtmlPurifier();
			$this->purifier->options = $this->options;
		}
                foreach ($this->attributes as $attribute) {
                        $this->getOwner()->{$attribute} = $this->purifier->purify($this->getOwner()->{$attribute});
                }
        }

}
