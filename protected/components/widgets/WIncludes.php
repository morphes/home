<?php

/**
 * @brief This widget render a few stars.
 */
class WIncludes extends CWidget
{
	/**
	 * @var null Ключ, по которому выводим включаемую область
	 */
	public $key = null;


        public function init()
	{
		if (is_null($this->key))
			throw new CException(__CLASS__ . ': Input params are incorrect');

		Yii::import('application.modules.admin.models.Includes');
	}

	public function run()
	{
		/** @var $model Includes */
		$model = Includes::model()->findByAttributes(array('key' => $this->key));
		if ($model) {

			if ( ! array_key_exists(Yii::app()->user->role, Config::$rolesAdmin))
				echo $this->simpleText($model);
			else
				echo $this->editableText($model);
		} else {
			echo $this->newText();
		}
	}


	private function simpleText($model)
	{
		return $model->text;
	}

	private function editableText($model)
	{
		$html = '';

		$html .= '<span class="includes_text" data-id="'.$model->id.'">';
		$html .= $model->text;
		$html .= '</span>';


		/** @var $cs CClientScript */
		$cs = Yii::app()->getClientScript();
		$cs->registerCss('includes_style', '
			.includes_text:hover { border: 1px dashed red; }
		');

		$cs->registerScript('includes_script', '

			$("body").on({
				mouseenter:function(){
					var id = $(this).attr("data-id");
					$(this).data("text", $(this).text());
					var link = $("<a style=\"text-decoration:none;\">");
					link.attr("href", "/admin/includes/update/id/"+id);
					link.text( $(this).data("text") );
					link.attr("target", "_blank");
					$(this).html(link);
				},
				mouseleave: function(){
					$(this).html($(this).data("text"))
				}
			}, ".includes_text");
		');

		return $html;
	}

	private function newText()
	{
		$html = '';
		$html .= '<span>';
		$html .= '<a target="_blank" href="/admin/includes/create?key='.$this->key.'">»»Создать включаемую область««</a>';
		$html .= '</span>';

		return $html;
	}

}

?>