<?php
echo CHtml::openTag('p', $this->htmlOptions);
echo CHtml::tag('span', array(), 'Поделиться ссылкой через ', true);
foreach ($this->shareDefinitions as $name => $def) {
	$linkText = "";
	$url = strtr($def['url'], array(
		'{title}' => htmlentities(urlencode($this->title)),
		'{message}' => $this->message,
		'{url}' => htmlentities(urlencode($this->url)),
	));
	$link = CHtml::link($linkText, $url, array(
		'rel' => 'nofollow',
		'target' => '_blank',
		'title' => $def['title'],
		'class' => $classDefinition[$name],
	));
	echo $link.' ';
}
echo CHtml::closeTag('p');
?>
<!--
<p class="-gutter-top -inset-top-hf">Поделиться ссылкой через
	<a class="-gutter-left-hf -icon-facebook" href="#"></a>
	<a class="-icon-vkontakte" href="#"></a>
	<a class="-icon-twitter" href="#"></a>
	<a class="-icon-google-plus" href="#"></a>
	<a class="-icon-odnoklassniki" href="#"></a>
</p> -->