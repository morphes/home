<?php

class NewsController extends FrontController
{

        public function actionIndex()
        {
		$this->hide_div_content = true;
                $dataProvider = new CActiveDataProvider('News', array(
                            'criteria' => array(
                                'condition' => 'status=:status AND public_time<=:time',
                                'order' => 'public_time DESC, id DESC',
                                'params'=> array(':status'=>News::STATUS_ACTIVE, ':time'=>time()),
                            ),
                            'pagination' => array(
                                'pageSize' => News::PAGE_SIZE,
                            ),
                        ));
                
                $this->render('//content/news/index', array(
                    'dataProvider'=>$dataProvider,
                ));
        }
        
        public function actionView($id = null)
        {
		$this->hide_div_content = true;
               $model = News::model()->findByPk($id);
               
               if(!$model)
                       throw new CHttpException(404);
               
               $this->render('//content/news/view', array(
                    'model'=>$model,
                ));
        }

}
