<?php

class CustomerIdentity extends CUserIdentity {

    private $model;

    const ERROR_INACTIVE = 3;       #未激活
    const ERROR_SUSPENDED = 4;       #账号停用
    const ERROR_NOT_VERIFIED = 5;       #未验证

    public function authenticate() {
        Yii::trace('Iniciando autenticação do usuário (' . strtolower($this->username) . ')...');

        $this->model = Customer::model()->find('username = ?', array(trim(strtolower($this->username))));

        if ($this->model === null) {
            $this->errorCode = self::ERROR_USERNAME_INVALID;
            Yii::trace('Erro de autenticação, usuário inválido (' . $this->errorCode . ')');
        } else if (!$this->model->validatePassword($this->password)) {
            $this->errorCode = self::ERROR_PASSWORD_INVALID;
        } else if ($this->model->status == Customer::STATUS_INACTIVE) {
            $this->errorCode = self::ERROR_INACTIVE;
            Yii::trace('Erro de autenticação, usuário inativo (' . $this->errorCode . ')');
        } else if ($this->model->status == Customer::STATUS_SUSPENDED) {
            $this->errorCode = self::ERROR_SUSPENDED;
            Yii::trace('Erro de autenticação, usuário suspenso (' . $this->errorCode . ')');
        } else if ($this->model->status == Customer::STATUS_NOT_VERIFIED) {
            $this->errorCode = self::ERROR_NOT_VERIFIED;
            Yii::trace('Erro de autenticação, usuário não verificado (' . $this->errorCode . ')');
        } else if ($this->model->status == Customer::STATUS_ACTIVE) {
            Yii::trace('Usuário autenticado com sucesso (' . $this->username . ')');

            $this->errorCode = self::ERROR_NONE;

            Yii::app()->session->clear();
            #作为状态存储的信息（通过调用CBaseUserIdentity::setState）将被传递给CWebUser。而后者则把这些信息存放在一个永久存储媒介上（如session）。我们可以把这些信息当作CWebUser的属性来使用
            $this->setState('id', $this->model->id);        #设置state
            $this->setState('name', $this->model->name);
            $this->setState('email', $this->model->email);
            $this->setState('status', $this->model->status);
        }

        return $this->errorCode == self::ERROR_NONE;
    }

    public function getModel() {
        return $this->model;
    }

}
