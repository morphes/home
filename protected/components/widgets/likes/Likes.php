<?php

class Likes extends CWidget
{

	public $vkLikePostfix = '1';
	public $okLikePostfix = '1';

        public function init()
        {

        }
        public function run()
        {
                $this->render('main', array(
			'vkLikePostfix'=>$this->vkLikePostfix,
			'okLikePostfix'=>$this->okLikePostfix,
		));
        }
}