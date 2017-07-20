<?php

class SiteController extends CController
{

	protected $requiredAuth = false;

	public function actions()   #外部动作
	{
		return array(
			'captcha'=>array(
				'class'=>'CCaptchaAction',
			),
		);
	}

	public function filters()
	{
		return array(
			'checkIfRequireAuth',
		);
	}

	public function filterCheckIfRequireAuth($filterChain)
	{
		if (UserUtil::getDefaultWebUser()->getIsGuest())#未登录
		{
			if ($this->requiredAuth)#是否需要认证
			{
                                #提示消息
				Yii::app()->user->setFlash(Constants::WARNING_MESSAGE_ID, 'O recurso que você tentou acessar requer um usuário autenticado, por favor, faça a autenticação com o seu usuário.');
				$this->redirect(array('/login/index'));#跳转登陆页面
			}
		}

		$filterChain->run();
	}

}