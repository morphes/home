<?php

/**
 * @brief Обработка текстовых переменных
 * @author Kuzakov Roman
 */
class Amputate
{

        /**
         * @brief Method for amputations of the text and add dots to the amputated member. 
         * @param string $text (Source text)
         * @param int $limit (Maximum number of characters)
         * @param string $postfix
         * @param string $charset
         * @param bool $returnLimb - вернуть обрезок по буквам (не по словам)
         * @return string
         */
        public static function getLimb($text='', $limit=20, $postfix='...', $charset='utf-8', $returnLimb = false)
        {
                if (mb_strlen($text, $charset) > $limit) {

                        $limb = mb_substr($text, 0, $limit, $charset);

                        if($returnLimb)
                                return $limb . $postfix;

                        return mb_substr($limb, 0, mb_strrpos($limb, " ", null, $charset), $charset) . $postfix;
                } else {
                        return $text;
                }
        }

        public static function selectQueryInText($text, $query, $charset='utf-8')
        {
                $query = preg_replace('/[^\w -]+/u', '', trim($query));
                $q_pos = mb_stripos($text, $query, null, $charset);

                if($q_pos === false) {
                        $query_split=preg_split('/[\s,-]+/', $query, 5);
                        if ($query_split) {
                                foreach($query_split as $q) {
					if (empty($q))
						continue;
                                        $text = preg_replace("#($q)#iu", '<span class="search_word">\1</span>', $text);
                                }
                        }
                } else {
                        $text = preg_replace("#($query)#iu", '<span class="search_word">\1</span>', $text);
                }

                return $text;
        }

        /**
         * Ищет фразу в тексте, вырезает контекст найденной фразы
         * @param $text - текст
         * @param $query - фраза
         * @param int $limit - длина контекста
         * @param string $postfix
         * @param string $charset
         * @return mixed|string
         */
        public static function getSearchedContext($text, $query, $limit=150, $postfix='...', $charset='utf-8')
        {
                $query = preg_replace('/[^\w -]+/u', '', trim($query));
                $q_len = mb_strlen($query, $charset);
                $t_len = mb_strlen($text, $charset);
                $text = strip_tags($text);

                /**
                 * Ищем фразу целиком
                 */
                $q_pos = mb_stripos($text, $query, null, $charset);

                /**
                 * Если фраза не найдена целиком, то ищем в тексте по словам из фразы
                 * Контекст будет возвращен для первого найденного слова
                 */
                if($q_pos === false) {
                        $query_split=preg_split('/[\s,-]+/', $query, 5);
                        if ($query_split) {
                                foreach($query_split as $q) {
                                        if(mb_strlen($q, 'utf-8') < 3) continue;
                                        $q_pos = mb_stripos($text, $q, null, $charset);
                                        if($q_pos !== false && $q_pos >= 0) {
                                                $q_len = mb_strlen($q, $charset);
                                                $query = $q;
                                                break;
                                        }
                                }
                        }
                }

                /**
                 * Если есть найденный элемент, то выделяем его контекст
                 */
                if ($q_pos !== false && $q_pos >= 0) {

                        $q_offset = round(($limit - $q_len) / 2);

                        /**
                         * Есть слева и справа достаточно места для вырезки контекста
                         */
                        if ($q_pos - $q_offset > 0 && $q_pos + $q_len + $q_offset <= $t_len) {

                                $start_pos = $q_pos - $q_offset;
                                $end_pos = $q_len + 2 * $q_offset;
                        // если слева не хватает
                        } elseif ($q_pos - $q_offset <= 0) {

                                $start_pos = 0;
                                $end_pos = $limit;
                        // если справа не хватает
                        } elseif ($q_pos + $q_len + $q_offset >= $t_len) {

                                $start_pos = $t_len - $limit;
                                $end_pos = $t_len;
                                if($start_pos < 0) $start_pos = 0;
                        }

                        /**
                         * Вырезаем фразу с отступами
                         */
                        $result = mb_substr($text, $start_pos, $end_pos, $charset);

                        /**
                         * Обрезаем по словам слева
                         */
                        if($start_pos > 0)
                                $result = $postfix . mb_substr($result, mb_strpos($result, " ", null, $charset), mb_strlen($result, $charset), $charset);

                        /**
                         * Обрезаем по словам справа
                         */
                        if($end_pos < $t_len)
                                $result = mb_substr($result, 0, mb_strrpos($result, " ", null, $charset), $charset) . $postfix;

                        /**
                         * Выделяем искомую фразу в вырезанном контексте
                         */
                        if(isset($query_split) && $query_split) {
                                foreach($query_split as $query) {
					if (empty($query))
						continue;
                                        $result = preg_replace("#($query)#iu", '<span class="search_word">\1</span>', $result);
				}
			} else
                                $result = preg_replace("#($query)#iu", '<span class="search_word">\1</span>', $result);

                } else {
                        $result = self::getLimb($text, $limit);
                }

                return $result;
        }

        /**
         * @brief Method for encoding of the Cyrillic text to Latin 
         * @param string $string
         * @return string 
         */
        public static function rus2translit($string)
        {
                $converter = array(
                    'а' => 'a', 'б' => 'b', 'в' => 'v',
                    'г' => 'g', 'д' => 'd', 'е' => 'e',
                    'ё' => 'e', 'ж' => 'zh', 'з' => 'z',
                    'и' => 'i', 'й' => 'y', 'к' => 'k',
                    'л' => 'l', 'м' => 'm', 'н' => 'n',
                    'о' => 'o', 'п' => 'p', 'р' => 'r',
                    'с' => 's', 'т' => 't', 'у' => 'u',
                    'ф' => 'f', 'х' => 'h', 'ц' => 'c',
                    'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
                    'ь' => "'", 'ы' => 'y', 'ъ' => "'",
                    'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
                    'А' => 'A', 'Б' => 'B', 'В' => 'V',
                    'Г' => 'G', 'Д' => 'D', 'Е' => 'E',
                    'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z',
                    'И' => 'I', 'Й' => 'Y', 'К' => 'K',
                    'Л' => 'L', 'М' => 'M', 'Н' => 'N',
                    'О' => 'O', 'П' => 'P', 'Р' => 'R',
                    'С' => 'S', 'Т' => 'T', 'У' => 'U',
                    'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
                    'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',
                    'Ь' => "'", 'Ы' => 'Y', 'Ъ' => "'",
                    'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
                    ' ' => '_',
                );
                return strtr($string, $converter);
        }

        /**
         * @brief Method for encoding of the Cyrillic text to Route-friendly Latin
         * @param string $string
         * @return string 
         */
        public static function rus2route($string)
        {
                $converter = array(
                    'а' => 'a', 'б' => 'b', 'в' => 'v',
                    'г' => 'g', 'д' => 'd', 'е' => 'e',
                    'ё' => 'e', 'ж' => 'zh', 'з' => 'z',
                    'и' => 'i', 'й' => 'y', 'к' => 'k',
                    'л' => 'l', 'м' => 'm', 'н' => 'n',
                    'о' => 'o', 'п' => 'p', 'р' => 'r',
                    'с' => 's', 'т' => 't', 'у' => 'u',
                    'ф' => 'f', 'х' => 'h', 'ц' => 'c',
                    'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
                    'ь' => '', 'ы' => 'y', 'ъ' => '',
                    'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
                    'А' => 'A', 'Б' => 'B', 'В' => 'V',
                    'Г' => 'G', 'Д' => 'D', 'Е' => 'E',
                    'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z',
                    'И' => 'I', 'Й' => 'Y', 'К' => 'K',
                    'Л' => 'L', 'М' => 'M', 'Н' => 'N',
                    'О' => 'O', 'П' => 'P', 'Р' => 'R',
                    'С' => 'S', 'Т' => 'T', 'У' => 'U',
                    'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
                    'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',
                    'Ь' => '', 'Ы' => 'Y', 'Ъ' => '',
                    'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
                    ' ' => '-', ',' => '', '_' => '-',
                    /* '.' => '', */ '"' => ','
                );
                return strtr($string, $converter);
        }

        /**
         * @brief Method for getting filename without extension
         * @param string $filename
         * @return string 
         */
        public static function getFilenameWithoutExt($filename)
        {
                $path_parts = pathinfo($filename);
                return $path_parts['filename'];
        }

        /**
         * @brief Method for encoding of the Cyrillic text to Route-friendly Latin
         * @param string $string
         * @return string 
         */
        public static function rus2filename($string)
        {
                $converter = array(
                    'а' => 'a', 'б' => 'b', 'в' => 'v',
                    'г' => 'g', 'д' => 'd', 'е' => 'e',
                    'ё' => 'e', 'ж' => 'zh', 'з' => 'z',
                    'и' => 'i', 'й' => 'y', 'к' => 'k',
                    'л' => 'l', 'м' => 'm', 'н' => 'n',
                    'о' => 'o', 'п' => 'p', 'р' => 'r',
                    'с' => 's', 'т' => 't', 'у' => 'u',
                    'ф' => 'f', 'х' => 'h', 'ц' => 'c',
                    'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch',
                    'ь' => '', 'ы' => 'y', 'ъ' => '',
                    'э' => 'e', 'ю' => 'yu', 'я' => 'ya',
                    'А' => 'A', 'Б' => 'B', 'В' => 'V',
                    'Г' => 'G', 'Д' => 'D', 'Е' => 'E',
                    'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z',
                    'И' => 'I', 'Й' => 'Y', 'К' => 'K',
                    'Л' => 'L', 'М' => 'M', 'Н' => 'N',
                    'О' => 'O', 'П' => 'P', 'Р' => 'R',
                    'С' => 'S', 'Т' => 'T', 'У' => 'U',
                    'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C',
                    'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch',
                    'Ь' => '', 'Ы' => 'Y', 'Ъ' => '',
                    'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya',
                    ' ' => '_', ',' => '', '"' => ',',
			'/' => '_', '\\' => '_', ':' => '_',
                );
                $converted = strtr($string, $converter);
		return preg_replace('/[^a-zA-Z_0-9]/i', '_', $converted);
        }

        static public function strtolower_cyr($str)
        {
                $str = strtolower($str);

                $search = array(
                    'Й', 'Ц', 'У', 'К', 'Е', 'Н', 'Г', 'Ш', 'Щ', 'З', 'Х', 'Ъ', 'Ф', 'Ы', 'В', 'А', 'П', 'Р', 'О', 'Л', 'Д', 'Ж', 'Э', 'Я', 'Ч', 'С', 'М', 'И', 'Т', 'Ь', 'Б', 'Ю', 'Ё'
                );

                $replace = array(
                    'й', 'ц', 'у', 'к', 'е', 'н', 'г', 'ш', 'щ', 'з', 'х', 'ъ', 'ф', 'ы', 'в', 'а', 'п', 'р', 'о', 'л', 'д', 'ж', 'э', 'я', 'ч', 'с', 'м', 'и', 'т', 'ь', 'б', 'ю', 'ё'
                );

                $str = str_replace($search, $replace, $str);
                return $str;
        }


	/**
	 * Возвращает транслитерацию в формате "Имен для загранспорта РФ"
	 * Он же используется в Яндексе (2012 год).
	 *
	 * @param      $string Входная строка для транслитерации
	 * @param bool $deleteSpecSym Флаг (по-умолчанию true) который стрирает
	 * 	из итоговой строки апострофы
	 *
	 * @return mixed
	 */
	static public function translitYandex($string, $deleteSpecSym = true)
	{
		$convert = array(
			'а' => 'a',
			'б' => 'b', 
			'в' => 'v', 
			'г' => 'g', 
			'д' => 'd', 
			'е' => 'e', 
			'ё' => 'ye',
			'ж' => 'zh', 
			'з' => 'z', 
			'и' => 'i', 
			'й' => 'y', 
			'к' => 'k', 
			'л' => 'l', 
			'м' => 'm', 
			'н' => 'n', 
			'о' => 'o', 
			'п' => 'p', 
			'р' => 'r', 
			'с' => 's', 
			'т' => 't', 
			'у' => 'u', 
			'ф' => 'f', 
			'х' => 'kh', 
			'ц' => 'ts', 
			'ч' => 'ch', 
			'ш' => 'sh', 
			'щ' => 'shch', 
			'ъ' => '``',
			'ы' => 'y', 
			'ь' =>	'`',
			'э' => 'e', 
			'ю' => 'yu',
			'я' => 'ya',
			'ай' => 'ay',
			'ей' => 'ey',
			'ий' => 'iy',
			'ой' => 'oy',
			'уй' => 'uy',
			'ый' => 'yy',
			'эй' =>  'ey',
			'юй' => 'yuy',
			' ' => "-"
		);

		$str = strtr(mb_strtolower($string, 'UTF-8'), $convert);

		if ($deleteSpecSym === true) {
			// Если нужно удалять спец символы, то гасим "апостроф"
			$result = preg_replace('/[^a-zA-Z_0-9-]/i', '', $str);
		} else {
			$result = preg_replace('/[^a-zA-Z_0-9`-]/i', '', $str);
		}

		return $result;
	}

        public static function absoluteUrl($url)
        {
                if (strpos($url, 'http') === 0)
                        return $url;
                else
                        return 'http://' . $url;
        }

        /**
         * Генератор паролей
         * @param integer $length
         * @return string 
         */
        static public function generatePassword($length = 8)
        {
                $chars = 'abdefhiknrstyzABDEFGHKNQRSTYZ23456789';
                $numChars = strlen($chars);
                $string = '';
                for ($i = 0; $i < $length; $i++) {
                        $string .= substr($chars, rand(1, $numChars) - 1, 1);
                }
                return $string;
        }

	/**
	 * Проверяет доступность любого удаленного объекта по его $url
	 *
	 * @static
	 * @param $url
	 * @return bool
	 */
	static public function urlExists($url)
	{
		$headers = @get_headers($url);

		if(strpos($headers[0], '200'))
			return true;
		else
			return false;
	}
}

?>
