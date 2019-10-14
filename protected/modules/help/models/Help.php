<?php

class Help
{
	// Перечень основных разделов помощи
	const BASE_USER = 1;
	const BASE_SPECIALIST = 2;
	const BASE_STORE = 3;

	public static $baseNames = array(
		self::BASE_USER => 'Владельцам квартир',
		self::BASE_SPECIALIST => 'Специалистам',
		self::BASE_STORE => 'Магазинам',
	);

	public static $baseUrlName = array(
		self::BASE_USER => 'users',
		self::BASE_SPECIALIST => 'specialists',
		self::BASE_STORE => 'stores',
	);

	public static $titleName = array(
		self::BASE_USER => 'Помощь по сайту для владельцев квартир',
		self::BASE_SPECIALIST => 'Помощь по сайту для специалистов',
		self::BASE_STORE => 'Помощь по сайту для магазинов',
	);

	const UPLOAD_DIR = 'uploads/public/help';
}