<?php

Yii::import('zii.widgets.jui.CJuiInputWidget');
/**
 * Создает автокомплит и связанный с ним инпут, в который записываются переданные id
 * поле name отвечает за name скрытого инпута
 * $valueName - начальное текстовое значение в автокомплите
 */
class EAutoComplete extends CJuiInputWidget
{

	public $sourceUrl;
	public $valueName;

	public function run()
	{
		if ($this->hasModel()) {
			$name = CHtml::resolveName($this->model, $this->attribute);
			$id = CHtml::getIdByName($name);
		}

		if(isset($this->htmlOptions['id']))
			$id=$this->htmlOptions['id'];
		else
			$this->htmlOptions['id']=$id;

		if(isset($this->htmlOptions['name'])) {
			$name=$this->htmlOptions['name'];

			unset($this->htmlOptions['name']);
		}
		$inputId = 'val_'.$id;

		$this->htmlOptions['onkeyup'] = 'if (this.value == "") { $("#'.$inputId.'").val(""); }';

		if($this->hasModel())
			echo CHtml::activeTextField($this->model,$this->attribute,$this->htmlOptions);
		else
			echo CHtml::textField('',$this->valueName,$this->htmlOptions);

		echo CHtml::hiddenField($name, $this->value, array('id'=>$inputId));

		$defOptions = array(
			'select'=>'js:function(event, ui) {$("#'.$inputId.'").val(ui.item.id).keyup();}',
			'focus'	=> 'js:function(event, ui){$("#'.$inputId.'").val(ui.item.id).keyup();}',
		);

		$this->options = array_merge($defOptions, $this->options);

		$this->options['source']=CHtml::normalizeUrl($this->sourceUrl);
		$options=CJavaScript::encode($this->options);

		$js = '<script type="text/javascript">/*<![CDATA[*/ jQuery(document).ready(function(){';
		$js .= "jQuery('#{$id}').autocomplete($options);";

		$js .= '$("#'.$id.'").keydown(function(event){if (event.keyCode != 27 && event.keyCode != 9 && event.keyCode != 13 '
				.'&& event.keyCode != 37 && event.keyCode != 38 && event.keyCode != 39 && event.keyCode != 40 && event.keyCode != 35 && event.keyCode != 36 '
				.') { $("#'.$inputId.'").val("");}});';
		$js .= '}); /*]]>*/</script>';
		echo $js;
	}
}
