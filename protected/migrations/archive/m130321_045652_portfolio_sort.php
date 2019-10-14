<?php

class m130321_045652_portfolio_sort extends CDbMigration
{
	public function up()
	{
		$this->createTable('portfolio_sort', array(
			'item_id' => 'INT(11) NOT NULL',
			'user_id' => 'INT(11) NOT NULL',
			'idea_type_id' => 'INT(11) not null',
			'service_id' => 'INT(11) not null',
			'position' => 'INT(11) not null',
			'update_time' => 'INT(11) NOT NULL, PRIMARY KEY (`item_id`, `idea_type_id`, `service_id`)'
		), 'ENGINE=InnoDB CHARACTER SET=utf8 COLLATE=utf8_general_ci');

		$this->createIndex('user_id', 'portfolio_sort', 'user_id');

		$time = time();

		$sql = 'SELECT id, author_id FROM interior';
		$data = Yii::app()->db->createCommand($sql)->queryAll();


		if (!empty($data)) {
			$sql = 'INSERT INTO portfolio_sort (item_id, user_id, idea_type_id, service_id, `position`, update_time) VALUES ';
			$cnt=0;
			foreach ($data as $item) {
				if ($cnt===0)
					$cnt++;
				else
					$sql .=',';
				$sql .= '('.$item['id'].','.$item['author_id'].','.Config::INTERIOR.',2,1,'.$time.')';

			}
			$result = Yii::app()->db->createCommand($sql)->execute();
			print_r('total='.$result);

		}

		$sql = 'SELECT id, author_id FROM interiorpublic';
		$data = Yii::app()->db->createCommand($sql)->queryAll();

		if (!empty($data)) {
			$sql = 'INSERT INTO portfolio_sort (item_id, user_id, idea_type_id, service_id, `position`, update_time) VALUES ';
			$cnt=0;
			foreach ($data as $item) {
				if ($cnt===0)
					$cnt++;
				else
					$sql .=',';
				$sql .= '('.$item['id'].','.$item['author_id'].','.Config::INTERIOR_PUBLIC.',2,1,'.$time.')';
			}
			$result = Yii::app()->db->createCommand($sql)->execute();
			print_r('total='.$result);

		}

		$sql = 'SELECT id, author_id FROM architecture';
		$data = Yii::app()->db->createCommand($sql)->queryAll();

		if (!empty($data)) {
			$sql = 'INSERT INTO portfolio_sort (item_id, user_id, idea_type_id, service_id, `position`, update_time) VALUES ';
			$cnt=0;
			foreach ($data as $item) {
				if ($cnt===0)
					$cnt++;
				else
					$sql .=',';
				$sql .= '('.$item['id'].','.$item['author_id'].','.Config::ARCHITECTURE.',4,1,'.$time.')';
			}
			$result = Yii::app()->db->createCommand($sql)->execute();
			print_r('total='.$result);

		}

		$sql = 'SELECT id, service_id, author_id FROM portfolio';
		$data = Yii::app()->db->createCommand($sql)->queryAll();

		if (!empty($data)) {
			$sql = 'INSERT INTO portfolio_sort (item_id, user_id, idea_type_id, service_id, `position`, update_time) VALUES ';
			$cnt=0;
			foreach ($data as $item) {
				if ($cnt===0)
					$cnt++;
				else
					$sql .=',';
				$sql .= '('.$item['id'].','.$item['author_id'].','.Config::PORTFOLIO.','.$item['service_id'].',1,'.$time.')';
			}
			$result = Yii::app()->db->createCommand($sql)->execute();
			print_r('total='.$result);

		}


		//////////////
		$sql = 'select s.user_id from portfolio_sort as s WHERE s.idea_type_id in (1,3) GROUP BY s.user_id';
		$users = Yii::app()->db->createCommand($sql)->queryColumn();

		foreach ($users as $user) {
			$sql = 'SET @a:=0; UPDATE portfolio_sort SET position = @a := @a + 1 WHERE user_id=:uid AND service_id=2 ORDER BY item_id DESC';
			Yii::app()->db->createCommand($sql)->bindParam(':uid', $user)->execute();
		}

	}

	public function down()
	{
		$this->dropTable('portfolio_sort');
	}
}