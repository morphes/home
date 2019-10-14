<?php
echo CHtml::openTag('div', $this->htmlOptions);
foreach ($this->shareDefinitions as $name => $def) {

	$url = strtr($def['url'], array(
		'{url}' => $this->url,
	));
	$link = CHtml::link('', '#', array(
		'rel' => 'nofollow',
		'target' => '_blank',
		'title' => $def['title'],
		'data-href' => $url,
		'class' => $classDefinition[$name],
	));
	echo $link.' ';
}
echo CHtml::closeTag('div');