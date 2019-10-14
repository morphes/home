<?php

class m130403_042903_product_room extends CDbMigration
{
	public function up()
	{
		$this->createTable('cat_product_room', array(
			'room_id' => 'INT(11) NOT NULL',
			'product_id' => 'INT(11) NOT NULL, PRIMARY KEY (`room_id`, `product_id`)',
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');

		$time = time();
		$this->insert('cat_main_room', array('id'=>9, 'status'=>2, 'position'=>9, 'create_time'=>$time, 'update_time'=>$time, 'name'=>'Сад' ));
		$this->insert('cat_main_room', array('id'=>10, 'status'=>2, 'position'=>10, 'create_time'=>$time, 'update_time'=>$time, 'name'=>'Столовая' ));

		// соответствие usageplaceId=>roomId
		$map = array(
			0 => 2,
			1 => 10,
			2 => 4,
			3 => 3,
			4 => 6,
			5 => 5,
			6 => 7,
			7 => 8,
			9 => 9,
		);

		$sql = 'SELECT MIN(id) as min, MAX(id) as max, COUNT(id) as cnt FROM cat_product';
		$result = Yii::app()->db->createCommand($sql)->queryRow();
		$min = $result['min'];
		$max = $result['max'];
		$cnt = $result['cnt'];

		echo "{$cnt} items for worker\n";

		$step = 1000;

		// Получение и обработка товаров
		for ($i = $min-1; $i<=$max; $i+=$step) {
			$sql = 'SELECT id, usageplace FROM cat_product WHERE id>='.$i.' AND id<'.($i+$step);
			$data = Yii::app()->db->createCommand($sql)->queryAll();

			if (!empty($data)) {
				$sql = 'insert into cat_product_room (`product_id`, `room_id`) VALUES ';
				$values = array();

				foreach ($data as $item) {
					$usagePlaces = unserialize($item['usageplace']);
					if (is_array($usagePlaces)) {
						foreach ($usagePlaces as $usagePlace) {
							if (isset($map[$usagePlace]))
								$values[] = '('.$item['id'].','.$map[$usagePlace].')';
						}
					}
				}
				if (!empty($values)) {
					$sql .= implode(',', $values);
					Yii::app()->db->createCommand($sql)->execute();
				}
			}
		}
	}

	public function down()
	{
		$this->dropTable('cat_product_room');
		$this->delete('cat_main_room', 'id=:id', array(':id'=>9));
		$this->delete('cat_main_room', 'id=:id', array(':id'=>10));
	}
}