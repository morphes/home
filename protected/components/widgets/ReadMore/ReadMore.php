<?php
/**
 * Created by JetBrains PhpStorm.
 * User: user
 * Date: 20.05.13
 * Time: 15:56
 * To change this template use File | Settings | File Templates.
 */

class ReadMore extends CWidget {

	public $model = false;

	public function init()
	{

	}

	public function run()
	{
		$model = $this->model;
		$modelId = (int)$model->id;
		$className = get_class($model);
		$selectIdArray = array();
		$data = array();
		$exclusionData = array();
		$exclusionData[] = $model->id;

		if($model->article_first)
		{
			$articleModel = self::getReadMoreModel($model->model_first);

			if($articleModel)
			{
				$item = $articleModel->findByPk($model->article_first);
			}

			if($item)
			{
				$data[] = $item;
				$exclusionData[] = $model->article_first;
			}
		}

		if($model->article_second)
		{
			$articleModel = self::getReadMoreModel($model->model_second);

			if($articleModel)
			{
				$item = $articleModel->findByPk($model->article_second);
			}

			if($item)
			{
				$data[] = $item;
				$exclusionData[] = $model->article_second;
			}
		}

		if($model->article_third)
		{
			$articleModel = self::getReadMoreModel($model->model_third);

			if($articleModel)
			{
				$item = $articleModel->findByPk($model->article_third);
			}

			if($item)
			{
				$data[] = $item;
				$exclusionData[] = $model->article_third;
			}
		}

		if(count($data)<3)
		{
			$exclusionString = implode(",",$exclusionData);
			$limit = 3 - count($data);
			$limit = (int) $limit;
			$arrayItems = array();
			$sql = "SELECT model_id, COUNT(*)
				FROM media_theme_select
				WHERE theme_id IN
				(SELECT  theme_id
					FROM media_theme_select
					WHERE model_id=:modelId AND model = :modelName )
				AND model_id NOT IN (".$exclusionString.")
				AND model = :modelName GROUP BY model_id ORDER BY COUNT(*) DESC LIMIT :limit";

			$arrayIds = Yii::app()->db->createCommand($sql)
				->bindParam(':modelId', $modelId)
				->bindParam(':modelName', $className)
				->bindParam(':limit', $limit)
				->queryColumn();
			$arrayItems = $model::model()->findAllByPk($arrayIds);

			$data = array_merge($data,$arrayItems);
		}


		$this->render('item',array(
			'data' => $data,

		));


	}


	/**
	 * метод возвращает экземпляр модели
	 * которой выводится статья в блоке
	 * ReadMore
	 * @param $idModel
	 *
	 * @return bool|MediaKnowledge|MediaNew
	 */
	private function getReadMoreModel($idModel)
	{
		switch($idModel)
		{
			case MediaNew::ARTICLE_MODEL_NEW:
				return MediaNew::model();

			case MediaNew::ARTICLE_MODEL_KNOWLEDGE:
				return MediaKnowledge::model();
			default:
				return false;
		}
	}




}