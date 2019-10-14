<?php

interface IComment
{
        public function getCommentsVisibility();
        public function afterComment($comment);
	/** проверка владения */
	public function getIsOwner();
	/** Ссыслка на страницу модели(с комментариями) */
	public function getElementLink();
}