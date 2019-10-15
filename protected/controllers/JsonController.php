<?php


class JsonController extends Controller
{

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow',  // allow all users to perform 'index' and 'view' actions
                'actions'=>array('getuser'),
                'users'=>array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array(),
                'users'=>array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions'=>array(),
                'users'=>array('admin'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    public function actionGetuser()
    {

        if(!isset($_POST['username']) || !isset($_POST['password'])){
            throw new CHttpException(403,'Data not found');
        }

        $username = $_POST['username'];
        $password = $_POST['password'];

        if($username === '' || $password === ''){
            throw new CHttpException(403,'Data not found');
        }

	header('Content-Type: application/json; charset=UTF-8');


        $user= User::model()->findByAttributes(array(), 'login=:login OR email=:email', array(':login' => $username, ':email' => $username));
        if ($user && $user->password === md5($password)){
            echo json_encode([
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'email' => $username
            ]);
            die;

        }

        echo json_encode([]);
        die;
    }
}