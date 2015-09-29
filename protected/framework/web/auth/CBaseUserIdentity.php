<?php
/**
 * CBaseUserIdentity class file
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2013 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CBaseUserIdentity is a base class implementing {@link IUserIdentity}.
 *
 * CBaseUserIdentity implements the scheme for representing identity
 * information that needs to be persisted. It also provides the way
 * to represent the authentication errors.
 *
 * Derived classes should implement {@link IUserIdentity::authenticate}
 * and {@link IUserIdentity::getId} that are required by the {@link IUserIdentity}
 * interface.
 *
 * @property mixed $id A value that uniquely represents the identity (e.g. primary key value).
 * The default implementation simply returns {@link name}.
 * @property string $name The display name for the identity.
 * The default implementation simply returns empty string.
 * @property array $persistentStates The identity states that should be persisted.
 * @property boolean $isAuthenticated Whether the authentication is successful.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.web.auth
 * @since 1.0
 */
abstract class CBaseUserIdentity extends CComponent implements IUserIdentity    #认证基类
{
	const ERROR_NONE=0;                 #没有错误
	const ERROR_USERNAME_INVALID=1;     #用户名无效
	const ERROR_PASSWORD_INVALID=2;     #密码无效
	const ERROR_UNKNOWN_IDENTITY=100;   #无效身份

	/**
	 * @var integer the authentication error code. If there is an error, the error code will be non-zero.
	 * Defaults to 100, meaning unknown identity. Calling {@link authenticate} will change this value.
	 */
	public $errorCode=self::ERROR_UNKNOWN_IDENTITY;     #错误号
	/**
	 * @var string the authentication error message. Defaults to empty.
	 */
	public $errorMessage='';       #错误信息

	private $_state=array();       #保存状态（数组）相当于session    $this->setState('name', $this->model->name);

	/**
	 * Returns a value that uniquely represents the identity.
	 * @return mixed a value that uniquely represents the identity (e.g. primary key value).
	 * The default implementation simply returns {@link name}.
	 */
	public function getId()
	{
		return $this->getName();
	}

	/**
	 * Returns the display name for the identity (e.g. username).
	 * @return string the display name for the identity.
	 * The default implementation simply returns empty string.
	 */
	public function getName()
	{
		return '';
	}

	/**
	 * Returns the identity states that should be persisted.
	 * This method is required by {@link IUserIdentity}.
	 * @return array the identity states that should be persisted.
	 */
	public function getPersistentStates()
	{
		return $this->_state;
	}

	/**
	 * Sets an array of persistent states.
	 *
	 * @param array $states the identity states that should be persisted.
	 */
	public function setPersistentStates($states)
	{
		$this->_state = $states;
	}

	/**
	 * Returns a value indicating whether the identity is authenticated.
	 * This method is required by {@link IUserIdentity}.
	 * @return boolean whether the authentication is successful.
	 */
	public function getIsAuthenticated()    #获取用户是否通过验证
	{
		return $this->errorCode==self::ERROR_NONE;
	}

	/**
	 * Gets the persisted state by the specified name.
	 * @param string $name the name of the state
	 * @param mixed $defaultValue the default value to be returned if the named state does not exist
	 * @return mixed the value of the named state
	 */
	public function getState($name,$defaultValue=null)
	{
		return isset($this->_state[$name])?$this->_state[$name]:$defaultValue;
	}

	/**
	 * Sets the named state with a given value.
	 * @param string $name the name of the state
	 * @param mixed $value the value of the named state
	 */
	public function setState($name,$value)
	{
		$this->_state[$name]=$value;
	}

	/**
	 * Removes the specified state.
	 * @param string $name the name of the state
	 */
	public function clearState($name)
	{
		unset($this->_state[$name]);
	}
}
