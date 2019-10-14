<?php
class EActiveRecord extends CActiveRecord
{
	public function findByPk($pk, $condition = '', $params = array())
	{
		if ( $pk===null )
			return null;

		return parent::findByPk($pk, $condition, $params);
	}


}
