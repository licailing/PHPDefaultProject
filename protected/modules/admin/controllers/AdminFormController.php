<?php

class AdminFormController extends AdminController
{
	protected $moduleTitle = 'AdminFormController';

	protected $formModel;#用于创建列表页的搜索form表单名
	protected $formSearchModel;#用于创建列表页的搜索form表单名
	protected $model;#对应的数据表对象名
	protected $form;
        
	protected $formSearch;#用于创建列表页的搜索form表单对象

	protected $arrConditions;#搜索条件
	protected $arrParameters;#搜索绑定的参数
	protected $arrParametersUrl;#保存分页对象url的查找数据
	protected $arrOrderField;#可选排序字段
	protected $arrRowCount;#可选每页条数
	protected $arrOrderDirection;#可选排序方向
	protected $orderFieldsForSearch;#字段排序
	protected $orderFieldDefaultForSearch;#默认的排序字段
	protected $orderDirectionDefaultForSearch;#默认的排序方向

	protected $modelForSave;#用于保存添加的数据
	protected $formModelForSave;#用于添加时创建form表单
	protected $modelForUpdate;
	protected $formModelForUpdate;
	protected $modelForView;#保存查看时

	protected $renderData;#传入视图数据

	protected $criteriaForSearch;#保存搜索条件（criteria对象）

	protected $storeAttributes = true;#存储属性
	protected $hasPagination   = true;#分页

	public function actionIndex()
	{
		if ($this->beforeActionIndex() == true)
		{
                        #搜索条件
			if ($this->criteriaForSearch == null)
			{
				$this->criteriaForSearch = new CDbCriteria();
			}
			$model                  = new $this->model;#model
			$this->formSearch       = new $this->formSearchModel;#创建并初始化搜索model 会执行init函数
			$this->arrConditions    = array();
			$this->arrParameters    = array();
			$this->arrParametersUrl = array();
			$this->arrOrderField    = array();

			$this->createParameters();

			$this->criteriaForSearch->condition = implode(' AND ', $this->arrConditions);#搜索条件
			$this->criteriaForSearch->params    = $this->arrParameters;

			$canProcessData = $this->actionIndexCanProcessData();

			// cria parâmetros de ordenação para a pesquisa
			foreach($this->orderFieldsForSearch as $key => $value)
			{
				$this->arrOrderField = array_merge($this->arrOrderField, array($key => $value));
			}

			$this->arrOrderDirection = ComponentsForSearch::listForOrder();
			$this->arrRowCount       = ComponentsForSearch::listForRowCount();

                        #初始化搜索form属性
			ComponentsForSearch::paramsForSearchQuery($this->formSearch, $this->criteriaForSearch, $this->arrParametersUrl, $this->orderFieldDefaultForSearch, $this->orderDirectionDefaultForSearch);

			// cria paginação e limite de registros
			if ($canProcessData)
			{
				if ($this->hasPagination)
				{
					$pagination = new CPagination( $model->count($this->criteriaForSearch) );
					$pagination->setCurrentPage($this->formSearch->page-1);
					$pagination->setPageSize($this->arrRowCount[$this->formSearch->rowCount]);

					$this->criteriaForSearch->offset = ($pagination->getCurrentPage() * $pagination->getPageSize());
					$this->criteriaForSearch->limit  = $pagination->getPageSize();

					$pagination->params = $this->arrParametersUrl;
				}
			}

			// grava os objetos de pesquisa
			if ($this->storeAttributes)
			{
				$this->formSearch->storeAttributes();#把搜索字段存入session
			}

			// cria objeto para o form
			$list = array();

			if ($canProcessData)#是否输出数据
			{
				$list = $model->findAll($this->criteriaForSearch);
			}

			// dados a serem renderizados
			$this->renderData = array(
				'moduleTitle'       => $this->moduleTitle,
				'form'              => $this->formSearch,#搜索form
				'list'              => $list,#列表数据
				'arrOrderField'     => $this->arrOrderField,#可选排序字段
				'arrOrderDirection' => $this->arrOrderDirection,#可选排序方向
				'arrRowCount'       => $this->arrRowCount,#可选每页条数
				'pagination'        => ($this->hasPagination ? $pagination : null),#分页对象
			);

			if ($this->afterActionIndex())
			{
				$this->render($this->getAction()->getId(), $this->renderData);
			}
		}
	}

	public function actionView()
	{
		if ($this->beforeActionView() == true)
		{

			// recebe ID do registro
			$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

			// verifica se é um registro válido
			if ($id <= 0)
			{
				UserUtil::getAdminWebUser()->setFlash(Constants::ERROR_MESSAGE_ID, ConstantMessages::invalidRegistry());
				$this->redirect(array('/admin/' . $this->getId()));
			}
			else
			{
				// tenta instanciar objeto pelo ID recebido
				$model = new $this->model;
				$form  = new $this->formModel;

				$this->modelForView = $model->findByPk($id);

				if (!$this->modelForView)
				{
					UserUtil::getAdminWebUser()->setFlash(Constants::ERROR_MESSAGE_ID, ConstantMessages::invalidRegistry());
					$this->redirect(array('/admin/' . $this->getId()));
				}
				else
				{
					$this->modelForView->clearErrors();
					$form->clearErrors();

					$this->renderData = array(
						'moduleTitle' => $this->moduleTitle,
						'model'       => $this->modelForView,
						'form'        => $form,
					);

					if ($this->afterActionView() == true)
					{
						$this->render($this->getAction()->getId(), $this->renderData);
					}
				}
			}

		}
	}

	public function actionAdd()
	{
		$this->modelForSave     = new $this->model;
		$this->formModelForSave = new $this->formModel;

		if ($this->beforeActionAdd() == true)
		{
			if ($this->getFormData() != null)
			{
				$this->formModelForSave->setAttributes($this->getFormData());

				if ($this->beforeValidateOnSaveModel())
				{
					if ($this->formModelForSave->validate())
					{
						$this->modelForSave->setAttributes($this->formModelForSave->getAttributes());

						if ($this->beforeSaveModel())
						{
							$this->modelForSave->save();

							if ($this->afterSaveModel())
							{
								UserUtil::getAdminWebUser()->setFlash(Constants::SUCCESS_MESSAGE_ID, ConstantMessages::addedRegistry($this->modelForSave->id));
								$this->redirect(array('/admin/' . $this->getId()));
							}
						}
					}
				}
			}
			else
			{
				$this->formModelForSave->clearErrors();
				$this->afterLoadModelForSave();
			}

			$this->renderData = array(
				'moduleTitle' => $this->moduleTitle,
				'form'        => $this->formModelForSave,
				'model'       => $this->modelForSave,
			);

			if ($this->afterActionAdd() == true)
			{
				$this->render($this->getAction()->getId(), $this->renderData);
			}

		}
	}

	public function actionUpdate()
	{
		$this->formModelForUpdate = new $this->formModel;
		$this->modelForUpdate = null;

		if ($this->beforeActionUpdate() == true)
		{
			if ($this->getFormData() != null)
			{
				$this->formModelForUpdate->setAttributes($this->getFormData());
				$this->modelForUpdate = new $this->model;
				$this->modelForUpdate = $this->modelForUpdate->findByPk($this->formModelForUpdate->id);

				if ($this->beforeValidateOnUpdateModel())
				{
					if ($this->formModelForUpdate->validate())
					{
						$this->modelForUpdate->setAttributes($this->formModelForUpdate->getAttributes());

						if ($this->beforeUpdateModel())
						{
							$this->modelForUpdate->update();

							if ($this->afterUpdateModel())
							{
								UserUtil::getAdminWebUser()->setFlash(Constants::SUCCESS_MESSAGE_ID, ConstantMessages::updatedRegistry($this->modelForUpdate->id));
								$this->redirect(array('/admin/' . $this->getId()));
							}
						}
					}
				}
			}
			else
			{
				// verifica se é um registro válido
				$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

				if ($id <= 0)
				{
					UserUtil::getAdminWebUser()->setFlash(Constants::ERROR_MESSAGE_ID, ConstantMessages::invalidRegistry());
					$this->redirect(array('/admin/' . $this->getId()));
				}
				else
				{
					// tenta instanciar objeto pelo ID recebido
					$this->modelForUpdate = new $this->model;
					$this->modelForUpdate = $this->modelForUpdate->findByPk($id);

					if (!$this->modelForUpdate)
					{
						UserUtil::getAdminWebUser()->setFlash(Constants::ERROR_MESSAGE_ID, ConstantMessages::invalidRegistry());
						$this->redirect(array('/admin/' . $this->getId()));
					}
					else
					{
						$this->formModelForUpdate->setAttributes($this->modelForUpdate->getAttributes());

						if ($this->beforeValidateFormModelForUpdate())
						{
							$this->formModelForUpdate->validate();
							$this->afterLoadModelForUpdate();
						}
					}
				}
			}

			$this->renderData = array(
				'moduleTitle' => $this->moduleTitle,
				'form'        => $this->formModelForUpdate,
				'model'       => $this->modelForUpdate,
			);

			if ($this->afterActionUpdate() == true)
			{
				$this->render($this->getAction()->getId(), $this->renderData);
			}
		}
	}

	public function actionDelete()
	{
		// verifica se é para exclui registros selecionados via checkbox
		if (isset($_POST['chkRow']) && count($_POST['chkRow']) > 0)
		{
			foreach($_POST['chkRow'] as $id)
			{
				$model = new $this->model;
				$model = $model->findByPk($id);

				if ($model)
				{
					$result = $model->canModifyOrDelete();

					if ($result['success'] == false)
					{
						UserUtil::getAdminWebUser()->setFlash(Constants::ERROR_MESSAGE_ID, ConstantMessages::cannotModiyOrDelete($model->id, $result['message']));
						$this->redirect(array('/admin/' . $this->getId()));
						Yii::app()->end();
					}
					else
					{
						if ($this->beforeDeleteModel($model))
						{
							$model->delete();
							$this->afterDeleteModel();
						}
					}
				}
			}

			UserUtil::getAdminWebUser()->setFlash(Constants::SUCCESS_MESSAGE_ID, ConstantMessages::deletedRegistries());
			$this->redirect(array('/admin/' . $this->getId()));
		}

		// recebe ID do registro
		$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

		// verifica se é um registro válido
		if ($id <= 0)
		{
			UserUtil::getAdminWebUser()->setFlash(Constants::ERROR_MESSAGE_ID, ConstantMessages::invalidRegistry());
			$this->redirect(array('/admin/' . $this->getId()));
		}
		else
		{
			// tenta instanciar objeto pelo ID recebido
			$model = new $this->model;
			$model = $model->findByPk($id);


			if (!$model)
			{
				UserUtil::getAdminWebUser()->setFlash(Constants::ERROR_MESSAGE_ID, ConstantMessages::invalidRegistry());
				$this->redirect(array('/admin/' . $this->getId()));
			}
			else
			{
				$result = $model->canModifyOrDelete();

				if ($result['success'] == false)
				{
					UserUtil::getAdminWebUser()->setFlash(Constants::ERROR_MESSAGE_ID, ConstantMessages::cannotModiyOrDelete($model->id, $result['message']));
					$this->redirect(array('/admin/' . $this->getId()));
					Yii::app()->end();
				}
				else
				{
					if ($this->beforeDeleteModel($model))
					{
						$model->delete();
						$this->afterDeleteModel();
					}
				}

				UserUtil::getAdminWebUser()->setFlash(Constants::SUCCESS_MESSAGE_ID, ConstantMessages::deletedRegistry());
				$this->redirect(array('/admin/' . $this->getId()));
			}
		}
	}

	protected function createParameters()
	{

	}

	protected function addToRenderData($key, $value)
	{
		$this->renderData = array_merge($this->renderData, array($key => $value));
	}

	protected function beforeActionIndex()
	{
		return true;
	}

	protected function afterActionIndex()
	{
		return true;
	}

	protected function actionIndexCanProcessData()
	{
		return true;
	}

	protected function beforeActionView()
	{
		return true;
	}

	protected function afterActionView()
	{
		return true;
	}

	protected function beforeActionAdd()
	{
		return true;
	}

	protected function afterActionAdd()
	{
		return true;
	}

	protected function beforeActionUpdate()
	{
		return true;
	}

	protected function afterActionUpdate()
	{
		return true;
	}

	protected function beforeSaveModel()
	{
		return true;
	}

	protected function afterSaveModel()
	{
		return true;
	}

	protected function beforeUpdateModel()
	{
		return true;
	}

	protected function afterUpdateModel()
	{
		return true;
	}

	protected function afterDeleteModel()
	{
		return true;
	}

	protected function beforeDeleteModel($model)
	{
		return true;
	}

	protected function beforeValidateOnSaveModel()
	{
		return true;
	}

	protected function beforeValidateOnUpdateModel()
	{
		return true;
	}

	protected function afterLoadModelForSave()
	{
		return true;
	}

	protected function afterLoadModelForUpdate()
	{
		return true;
	}

	protected function beforeValidateFormModelForUpdate()
	{
		return true;
	}

	protected function getFormData()
	{
		return isset($_POST[$this->formModel]) ? $_POST[$this->formModel] : null;
	}

	protected function getFormSearchData()
	{
		return isset($_POST[$this->formSearchModel]) ? $_POST[$this->formSearchModel] : null;
	}

}