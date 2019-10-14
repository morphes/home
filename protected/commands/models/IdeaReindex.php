<?php
/**
 * Переиндексация индекса idea
 */
Yii::import('application.modules.idea.models.Idea');
class IdeaReindex
{
	const STEP_INTERIOR_PUBLIC = 1000;
	const STEP_ARCHITECTURE = 1000;
	const STEP_INTERIOR = 1000;

	public function run()
	{
		$this->architecture();
		$this->interiorPublic();
		$this->interior();
	}

	public function index($typeId, $id)
	{
		$objects = $this->getObjects($typeId, $id, $id+1);
		return $this->updateSphinx($objects, $typeId);
	}

	private function getObjects($typeId, $start, $end)
	{
		switch ($typeId) {
			case Config::INTERIOR: {
				$sql = 'SELECT i.id, i.name, i.status, i.desc, '
					.'"интерьер" as object_type, '
					.'CONCAT_WS(" ", '
						.'GROUP_CONCAT(ih_room.option_value SEPARATOR " " ), '
						.'GROUP_CONCAT(ih_room.desc SEPARATOR " " ) '
					.') as room_name, '
					.'CONCAT_WS(" ", '
						.'GROUP_CONCAT(ih_color.option_value SEPARATOR " " ), '
						.'GROUP_CONCAT(ih_color.desc SEPARATOR " " ) '
					.') as color, '
					.'CONCAT_WS(" ", '
						.'GROUP_CONCAT(ih_style.option_value SEPARATOR " " ), '
						.'GROUP_CONCAT(ih_style.desc SEPARATOR " " ) '
					.') as style, '
					.'"" as heap, i.create_time, i.update_time '
					.'FROM interior as i '
					.'LEFT JOIN interior_content as ic ON ic.interior_id=i.id '
					.'LEFT JOIN idea_heap as ih_room ON ih_room.id=ic.room_id '
					.'LEFT JOIN idea_heap as ih_color ON ih_color.id=ic.color_id '
					.'LEFT JOIN idea_heap as ih_style ON ih_style.id=ic.style_id '
					.'WHERE i.id>='.$start.' AND i.id<'.$end.' '
					.'GROUP BY i.id';

			} break;
			case Config::INTERIOR_PUBLIC: {
				$sql = 'SELECT ip.id, ip.name, ip.status, ip.desc, '
					.'ih_object.option_value as object_type, '
					.'ih_build.option_value as room_name, '
					.'CONCAT_WS(" ", ih_color.option_value, GROUP_CONCAT(ihc.option_value SEPARATOR " " )) as color, '
					 .'ih_style.option_value as style, '
					.'"" as heap, ip.create_time, ip.update_time '
					.'FROM interiorpublic as ip '
					.'LEFT JOIN idea_heap AS ih_color ON ih_color.id = ip.color_id '
					.'LEFT JOIN idea_heap AS ih_object ON ih_object.id = ip.object_id '
					.'LEFT JOIN idea_heap AS ih_build ON ih_build.id = ip.building_type_id '
					.'LEFT JOIN idea_heap AS ih_style ON ih_style.id = ip.style_id '
					.'LEFT JOIN (SELECT * FROM idea_additional_color WHERE idea_type_id = '.Config::INTERIOR_PUBLIC.' AND item_id>='.$start.' AND item_id<'.$end.') as iac ON iac.item_id = ip.id '
					.'LEFT JOIN idea_heap AS ihc ON iac.color_id = ihc.id '
					.'WHERE ip.id>='.$start.' AND ip.id<'.$end.' '
					.'GROUP BY ip.id';

			} break;
			case Config::ARCHITECTURE: {
				$sql = 'SELECT ar.id, ar.name, ar.status, ar.desc, '
					.'ih_object.option_value as object_type, '
					.'CONCAT_WS(" ", '
						.'ih_build.option_value, '
						.'CASE WHEN ar.room_mansard = 1 THEN "мансарда" ELSE "" END, '
						.'CASE WHEN ar.room_garage = 1 THEN "гараж" ELSE "" END, '
						.'CASE WHEN ar.room_ground = 1 THEN "цоколь" ELSE "" END, '
						.'CASE WHEN ar.room_basement = 1 THEN "подвал" ELSE "" END '
					.') as room_name, '
					.'CONCAT_WS(" ", ih_color.option_value, ih_color.desc, GROUP_CONCAT(ihc.option_value SEPARATOR " " ) ) as color, '
					.'CONCAT_WS(" ", ih_style.option_value, ih_style.desc ) as style, '
					.'CONCAT_WS(" ", ih_material.option_value, ih_floor.option_value ) as heap, '
					.'ar.create_time, ar.update_time '
					.'FROM architecture as ar '
					.'LEFT JOIN idea_heap AS ih_color ON ih_color.id = ar.color_id '
					.'LEFT JOIN idea_heap AS ih_object ON ih_object.id = ar.object_id '
					.'LEFT JOIN idea_heap AS ih_build ON ih_build.id = ar.building_type_id '
					.'LEFT JOIN idea_heap AS ih_style ON ih_style.id = ar.style_id '
					.'LEFT JOIN idea_heap AS ih_material ON ih_material.id = ar.material_id '
					.'LEFT JOIN idea_heap AS ih_floor ON ih_floor.id = ar.floor_id '
					.'LEFT JOIN (SELECT * FROM idea_additional_color WHERE idea_type_id = '.Config::ARCHITECTURE.' AND item_id>='.$start.' AND item_id<'.$end.') as iac ON iac.item_id = ar.id '
					.'LEFT JOIN idea_heap AS ihc ON iac.color_id = ihc.id '
					.'WHERE ar.id>='.$start.' AND ar.id<'.$end.' '
					.'GROUP BY ar.id ';
			} break;
			default: return array();
		}

		$objects = Yii::app()->db->createCommand($sql)->queryAll();
		return $objects;
	}


	protected function architecture()
	{
		/** Architecture */
		try {
			echo 'Architecture'."\n";

			$sql = 'SELECT id,MIN(id) as min, MAX(id) as max, COUNT(id) as cnt FROM architecture';

			$result = Yii::app()->db->createCommand($sql)->queryRow();
			$min = $result['min'];
			$max = $result['max'];
			$cnt = $result['cnt'];

			$total = 0;
			echo "{$cnt} items to index\n";

			for ($i = $min; $i<=$max; $i+=self::STEP_ARCHITECTURE) {
				$objects = $this->getObjects(Config::ARCHITECTURE, $i, $i+self::STEP_ARCHITECTURE);
				$this->clearIndex(Config::ARCHITECTURE, $i, $i+self::STEP_ARCHITECTURE);
				$total += $this->updateSphinx($objects, Config::ARCHITECTURE);
				echo "{$total} items was written \n";
			}
			echo "Total index result: {$total}\n";


		} catch (Exception $e) {
			echo $e->getTraceAsString()."\n";
		}
	}

	protected function interior()
	{
		try {
			echo 'Interior'."\n";

			$sql = 'SELECT MIN(id) as min, MAX(id) as max, COUNT(id) as cnt FROM interior';

			$result = Yii::app()->db->createCommand($sql)->queryRow();
			$min = $result['min'];
			$max = $result['max'];
			$cnt = $result['cnt'];

			$total = 0;
			echo "{$cnt} items to index\n";

			for ($i = $min; $i<=$max; $i+=self::STEP_INTERIOR ) {

				$objects = $this->getObjects(Config::INTERIOR, $i, $i+self::STEP_INTERIOR);
				$this->clearIndex(Config::INTERIOR, $i, $i+self::STEP_INTERIOR);
				$total += $this->updateSphinx($objects, Config::INTERIOR);
				echo "{$total} items was written \n";
			}
			echo "Total index result: {$total}\n";
		} catch (Exception $e) {
			echo $e->getTraceAsString()."\n";
		}
	}

	protected function interiorPublic()
	{
		try {
			echo 'InteriorPublic'."\n";

			$sql = 'SELECT MIN(id) as min, MAX(id) as max, COUNT(id) as cnt FROM interiorpublic';

			$result = Yii::app()->db->createCommand($sql)->queryRow();
			$min = $result['min'];
			$max = $result['max'];
			$cnt = $result['cnt'];

			$total = 0;
			echo "{$cnt} items to index\n";

			for ($i = $min; $i<=$max; $i+=self::STEP_INTERIOR_PUBLIC) {
				$objects = $this->getObjects(Config::INTERIOR_PUBLIC, $i, $i+self::STEP_INTERIOR_PUBLIC);
				$this->clearIndex(Config::INTERIOR_PUBLIC, $i, $i+self::STEP_INTERIOR_PUBLIC);
				$total += $this->updateSphinx($objects, Config::INTERIOR_PUBLIC);
				echo "{$total} items was written \n";
			}
			echo "Total index result: {$total}\n";


		} catch (Exception $e) {
			echo $e->getTraceAsString()."\n";
		}
	}



	private function clearIndex($typeId, $start, $end)
	{
		$sphinxDel = 'DELETE FROM {{idea}} WHERE id IN (';
		$cnt = 0;
		for ($j = $start; $j < $end; $j++) {
			if ($cnt > 0)
				$sphinxDel .= ',';
			else
				$cnt++;
			$sphinxDel .= $j + $typeId*Idea::$typeCoef;
		}
		$sphinxDel .= ')';
		Yii::app()->sphinx->createCommand($sphinxDel)->execute();
	}

	/** Генерация запроса и обновление */
	private function updateSphinx($objects, $typeId)
	{
		if (empty($objects))
			return 0;

		$sphinxQl = 'REPLACE INTO {{idea}} (`id`, `status`, `idea_type`, `idea_id`, `name`, `desc`, `service`, `heap`, `object_type`, `room_name`, `color`, `style`, `create_time`, `update_time`) VALUES ';
		$cnt = 0;
		foreach ($objects as $object) {
			if ($cnt > 0)
				$sphinxQl .= ',';
			else
				$cnt++;

			$sphinxQl .= '('.($object['id']+$typeId*Idea::$typeCoef).','.$object['status'].','.$typeId.','
				.$object['id'].',\''.addslashes($object['name']).'\',\''.addslashes($object['desc']).'\',\'архитектура\',\''.addslashes($object['heap'])
				.'\',\''.addslashes($object['object_type']).'\',\''.addslashes($object['room_name']).'\',\''.addslashes($object['color']).'\',\''.addslashes($object['style']).'\','
				.$object['create_time'].','.$object['update_time'].')';
		}
		$result = Yii::app()->sphinx->createCommand($sphinxQl)->execute();
		return $result;
	}
}