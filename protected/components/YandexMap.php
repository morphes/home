<?php
/**
 * YandexMap Helper
 * User: desher
 * Date: 18.09.12
 */
class YandexMap
{
        /**
         * Api Key для доступа к сервисам api
         */
        const API_KEY = 'ANkMWFABAAAATcfFFAIA7y2n9PxUjZ4prOEpUXsNxco-698AAAAAAAAAAADDNR_LkyV0EmmDKBeqSOu-fGn7Ow==';

        /**
         * Url для получения координат по адресу
         */
        const GEOCODE_URL = 'http://geocode-maps.yandex.ru/1.x/';

        /**
         * Определение координат по адресу
         * @param string $address
         * @return string координаты
         */
        static public function getGeocode($address)
        {
                $url= self::GEOCODE_URL.'?geocode='.rawurlencode($address).'&key='.self::API_KEY;
                $results = file($url);
                if($results && is_array($results) && count($results)) {
                        $data=implode("", $results);
                        if(preg_match("#<pos>([0-9\\.]*) ([0-9\\.]*)</pos>#i", $data, $out)) {
                                $lat=floatval(trim($out[1]));
                                $lng=floatval(trim($out[2]));
                                if($lng>0 && $lat>0) {
                                        return serialize(array($lat, $lng));
                                }
                        }
                }
                return serialize(array());
        }


	/**
	 * Определяет расстояние в километрах между двумя точками на карте
	 * @param $lat1 - широта первой точки
	 * @param $lng1 - долгота первой точки
	 * @param $lat2 - широта второй точки
	 * @param $lng2 - долгота второй точки
	 *
	 * @return float - расстояние в километрах между точками
	 */
	static function distance($lat1, $lng1, $lat2, $lng2)
	{
		$pi80 = M_PI / 180;
		$lat1 *= $pi80;
		$lng1 *= $pi80;
		$lat2 *= $pi80;
		$lng2 *= $pi80;

		$r = 6372.797; // mean radius of Earth in km
		$dlat = $lat2 - $lat1;
		$dlng = $lng2 - $lng1;
		$a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlng / 2) * sin($dlng / 2);
		$c = 2 * atan2(sqrt($a), sqrt(1 - $a));
		$km = $r * $c;

		return $km;
	}

}
