<?php
/**
 * Created by JetBrains PhpStorm.
 * User: desher
 * Date: 18.10.12
 * Time: 16:01
 * To change this template use File | Settings | File Templates.
 */
class RESTfulHelper extends CComponent
{
        /**
         * Возвращает массив, переданный в виде JSON PUT- запросом
         * @return mixed
         */
        static public function getPutJson()
        {
                $json = file_get_contents('php://input');
                $put_vars = CJSON::decode($json,true);
                return $put_vars;
        }

        static public  function sendResponse($status = 200, $body = '', $content_type = 'text/html')
        {
                // set the status
                $status_header = 'HTTP/1.1 ' . $status . ' ' . self::getStatusCodeMessage($status);
                header($status_header);
                // and the content type
                header('Content-type: ' . $content_type);

                // pages with body are easy
                if($body != '')
                {
                        // send the body
                        echo $body;
                }
                // we need to create the body if none is passed
                else
                {
                        // create some body messages
                        $message = '';

                        // this is purely optional, but makes the pages a little nicer to read
                        // for your users.  Since you won't likely send a lot of different status codes,
                        // this also shouldn't be too ponderous to maintain
                        switch($status)
                        {
                                case 401:
                                        $message = 'You must be authorized to view this page.';
                                        break;
                                case 404:
                                        $message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
                                        break;
                                case 500:
                                        $message = 'The server encountered an error processing your request.';
                                        break;
                                case 501:
                                        $message = 'The requested method is not implemented.';
                                        break;
                        }

                        // servers don't always have a signature turned on
                        // (this is an apache directive "ServerSignature On")
                        $signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];

                        // this should be templated in a real-world solution
                        $body = '
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <title>' . $status . ' ' . self::getStatusCodeMessage($status) . '</title>
</head>
<body>
    <h1>' . self::getStatusCodeMessage($status) . '</h1>
    <p>' . $message . '</p>
    <hr />
    <address>' . $signature . '</address>
</body>
</html>';

                        echo $body;
                }
                Yii::app()->end();
        }

        static public function getStatusCodeMessage($status)
        {
                // these could be stored in a .ini file and loaded
                // via parse_ini_file()... however, this will suffice
                // for an example
                $codes = Array(
                        200 => 'OK',
                        400 => 'Bad Request',
                        401 => 'Unauthorized',
                        402 => 'Payment Required',
                        403 => 'Forbidden',
                        404 => 'Not Found',
                        500 => 'Internal Server Error',
                        501 => 'Not Implemented',
                );
                return (isset($codes[$status])) ? $codes[$status] : '';
        }
}