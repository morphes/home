<?php
/**
 * User: desher
 * Date: 08.06.12
 * Time: 16:52
 */
class EmailTemplateTest extends CWidget
{
        public $template_key;
        public $action_url = '/admin/mailtemplate/templateTest';

        public function init()
        {
                if(!$this->template_key)
                        throw new CException(__CLASS__ . ': Template key required');
        }

        public function run()
        {
                $this->render('_mailToForm', array(
                        'template_key'=>$this->template_key,
                        'action_url'=>$this->action_url,
                ));
        }
}
