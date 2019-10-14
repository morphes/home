<?php

/**
 * Выбор группы пользователя
 */
class WUsergroup extends CWidget
{
	public $user = null;
        public $ajaxUrl = null;
        public $checkedColor = 'black';
        public $uncheckedColor = '#555555';
        
        public function init()
        {
                Yii::import('application.modules.member.models.Usergroup');
                
                if(!$this->ajaxUrl)
                        $this->ajaxUrl = Yii::app()->createUrl('/member/admin/usergroup/append');
                
        }

        public function run()
        {
                if(!Yii::app()->user->checkAccess(array(
			User::ROLE_ADMIN,
			User::ROLE_MODERATOR,
			User::ROLE_POWERADMIN,
			User::ROLE_SALEMANAGER,
			User::ROLE_SENIORMODERATOR,
		)))
                        return false;
                
                $criteria = new CDbCriteria();
                $criteria->limit = '30';
                
		$groups = Usergroup::model()->findAll($criteria);
		
		Yii::app()->controller->renderPartial('//widget/usergroup', array(
			'groups'=> $groups,
                        'user'=>$this->user,
                        'ajaxUrl'=>$this->ajaxUrl,
                        'checkedColor'=>$this->checkedColor,
                        'uncheckedColor'=>$this->uncheckedColor,
		));
        }

}
