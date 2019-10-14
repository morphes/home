<?php

/**
 * @brief Виджет просмотра помещений идей
 */
class InteriorContentView extends CWidget
{
	public $interior = null;
        public $errors = array();

        public function init()
        {
                
        }

        public function run()
        {
		if (is_null($this->interior))
			throw new CException(500, 'Empty interior object');

                $interiorContents = InteriorContent::model()->findAll('interior_id = :sid', array(
                    ':sid' => $this->interior->id,
                        )
                );

                if ($interiorContents) {
			$rooms = IdeaHeap::getRooms(Config::INTERIOR, $this->interior->object_id);
			$colors = IdeaHeap::getColors(Config::INTERIOR, $this->interior->object_id);
			$styles = IdeaHeap::getStyles(Config::INTERIOR, $this->interior->object_id);
			
                        foreach ($interiorContents as $content) {

                                $ac_criteria = new CDbCriteria;
                                $ac_criteria->condition = 'item_id= :scc_id AND idea_type_id = :idea';

                                $ac_criteria->params = array(':scc_id' => $content->id, ':idea'=> Config::INTERIOR);
                                $ac_criteria->order = 'item_id ASC, position ASC';

                                $additional_colors = IdeaAdditionalColor::model()->findAll($ac_criteria);
                                $mainImage = UploadedFile::model()->findByPk($content->image_id);

                                $interiorUploadedFilesId = IdeaUploadedFile::model()->findAllByAttributes(array('item_id' => $content->id, 'idea_type_id'=> Config::INTERIOR));

                                $uploadedFiles = array();
                                if (!empty($interiorUploadedFilesId)) {
                                        $filesId = '(';
                                        foreach ($interiorUploadedFilesId as $fileId) {
                                                if ($fileId->uploaded_file_id == $content->image_id)
                                                        continue;
                                                $filesId .= $fileId->uploaded_file_id . ',';
                                        }
                                        $filesId[strlen($filesId) - 1] = ')';
                                        $condition = 'id IN ' . $filesId;
                                        if ($filesId != ')')
                                                $uploadedFiles = UploadedFile::model()->findAll(array('condition' => $condition));
                                }

                                $content->addErrors(!empty($this->errors['interior_contents'][$content->id]) ? $this->errors['interior_contents'][$content->id] : array());

                                Yii::app()->controller->renderPartial('idea.views.admin.create._interiorContentView', array(
                                    'additional_colors' => $additional_colors,
                                    'content' => $content,
                                    'rooms' => $rooms,
                                    'colors' => $colors,
                                    'styles' => $styles,
                                    'uploadedFiles' => $uploadedFiles,
                                    'mainImage' => $mainImage,
                                    'errors' => $this->errors,
                                ));
                        }
                }
        }

}
