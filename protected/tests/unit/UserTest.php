<?php

class UserTest extends CDbTestCase
{

        public $fixtures = array(
            'user' => 'User',
            'mail_template' => ':mail_template',
            'log_user_activation' => ':log_user_activation',
            'user_data' => ':user_data',
        );

        /**
         * Тест авторизации пользователя
         */
        public function testAuth()
        {
                $client_ip = '10.10.10.10';
                $office_ip = '194.186.7.46';

                // проверка авторизации НЕадмина
                $identity = new UserIdentity($this->user['user']['login'], $this->user['user']['unsafe_password'], $client_ip);
                $identity->authenticate();
                $this->assertEquals($identity->errorCode, UserIdentity::ERROR_NONE);
                unset($identity);

                // проверка авторизации админа с офисным IP
                $identity = new UserIdentity($this->user['poweradmin']['login'], $this->user['poweradmin']['unsafe_password'], $office_ip);
                $identity->authenticate();
                $this->assertEquals($identity->errorCode, UserIdentity::ERROR_NONE);
                unset($identity);

                // проверка авторизации админа НЕ с офисным IP и верным временным паролем
                Yii::app()->cache->delete('auth_request_' . $client_ip . '_' . $this->user['poweradmin']['login']);
                $identity = new UserIdentity($this->user['poweradmin']['login'], $this->user['poweradmin']['unsafe_password'], $client_ip);
                $identity->authenticate();
                $this->assertEquals($identity->errorCode, UserIdentity::NOTICE_TMPPASS_REQUIRED);
                unset($identity);
                $tmpPass = Yii::app()->cache->get('auth_request_' . $client_ip . '_' . $this->user['poweradmin']['login']);
                $tmpPass = $tmpPass['tmp_pass'];
                $identity = new UserIdentity($this->user['poweradmin']['login'], $this->user['poweradmin']['unsafe_password'], $client_ip, $tmpPass);
                $identity->authenticate();
                $this->assertEquals($identity->errorCode, UserIdentity::ERROR_NONE);
                unset($identity);

                // проверка авторизации админа НЕ с офисным IP и неверным временным паролем
                $identity = new UserIdentity($this->user['poweradmin']['login'], $this->user['poweradmin']['unsafe_password'], $client_ip);
                $identity->authenticate();
                $this->assertEquals($identity->errorCode, UserIdentity::NOTICE_TMPPASS_REQUIRED);
                unset($identity);
                $tmpPass = '123456789';
                for ($i = 0; $i < 9; $i++) {
                        $identity = new UserIdentity($this->user['poweradmin']['login'], $this->user['poweradmin']['unsafe_password'], $client_ip, $tmpPass);
                        $identity->authenticate();
                        $this->assertEquals($identity->errorCode, UserIdentity::ERROR_TMPPASS);
                        unset($identity);
                }
                // клиент забанен на 10 минут
                $tmpPass = '123456789';
                $identity = new UserIdentity($this->user['poweradmin']['login'], $this->user['poweradmin']['unsafe_password'], $client_ip, $tmpPass);
                $identity->authenticate();
                $this->assertEquals($identity->errorCode, UserIdentity::ERROR_TMPBANNED);
                unset($identity);
        }

        /**
         * @dataProvider userLoginProvider
         */
        public function testSaveInLogIfUserActivated($login)
        {
                $user = $this->user($login);
                
                $this->assertNotNull($user->role, 'Role do not assigned for user');
                
                // попытка дважды занести пользователя в лог
                $i = 0;
                while ($i != 2) {
                        $user->status = User::STATUS_NOT_ACTIVE;
                        $user->save(false);

                        $user->status = User::STATUS_ACTIVE;
                        $user->save(false);
                        $i++;
                }

                // поиск записей о пользователе в журнале
                try {
                        $log = Yii::app()->db->createCommand()
                                ->select('*')
                                ->from('log_user_activation log')
                                ->where('user_id=:id', array(':id' => $user->id))
                                ->queryAll();
                } catch (CDbException $e) {

                        return $this->fail('Invalid request to DataBase. Maybe db or table unavailable?');
                }

                $this->assertNotEmpty($log, 'Record not found in user activation log table.');
                $this->assertCount(1, $log, 'Log table should have only one record about user.');

                // проверка полноты записи в журнале
                $this->assertNotEmpty($log[0]['activate_time'], 'Undefined activate_time in log table');
                $this->assertNotEmpty($log[0]['create_time'], 'Undefined create_time in log table');
        }

        public function userLoginProvider()
        {
                return array(
                    array('poweradmin'),
                    array('user'),
                    array('notActiveReferreredDesigner'),
                );
        }

}