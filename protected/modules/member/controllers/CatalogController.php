<?php
/**
 * @brief Реализация на следующих этапах
 */
class CatalogController extends Controller
{
	public function filters()
        {
                return array('accessControl');
        }

        public function accessRules()
        {
                return array(
                    array('deny',
                        'users' => array('*'),
                    ),
                );
        }
}