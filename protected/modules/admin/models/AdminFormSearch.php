<?php

class AdminFormSearch extends CFormModel
{
	public $orderField;#排序字段
    public $orderDirection;#排序方向
    public $rowCount;#每页条数
    public $page;#第几页
    
    #初始化本对象属性
    public function init()
    {
		$this->restoreAttributes();#用$_SESSION中的数据初始化本对象属性，并删除session数据

        $this->setAttributes($_POST);#接收post数据                
        $this->setAttributes($_GET);#接收get数据
        
        if (isset($_POST[get_class($this)]))
        {
            $this->setAttributes($_POST[get_class($this)]);            
        }
        
        if (isset($_GET[get_class($this)]))
        {
            $this->setAttributes($_GET[get_class($this)]);
        }
        
        $this->page = ((int)$this->page <= 0 ? $this->page = 1 : (int)$this->page);#重新计算page属性
                
        $this->validate();
    }
    
    public function rules()
	{
		return array(
			array('orderField, orderDirection, rowCount, page', 'safe'),
            array('page', 'numerical', 'integerOnly'=>true),
		);
	}

	public function attributeLabels()
	{
		return array(
			'orderField'     => 'Ordernar por',
            'orderDirection' => 'Ordem',
            'rowCount'       => 'Quantidade de registros por página',
		);
	}

        #保存搜索数据
	public function storeAttributes()
	{
		$data = array();
		$this->cleanSearchData();#清除数据

		foreach($this->getAttributes() as $key => $value)
		{
			$data[$key] = $value;
		}

		$searchData = UserUtil::getAdminWebUser()->getState('searchData');

		if (!is_array($searchData))
		{
			$searchData = array();
		}

		$searchData[get_class($this)] = $data;#searchData['key'] = value

		UserUtil::getAdminWebUser()->setState('searchData', $searchData);#保存数据
	}

        #用$_SESSION中的数据初始化本对象属性，并删除session数据
	protected function restoreAttributes($clean = true)
	{
		if (isset($_GET['restore']) && $_GET['restore'] == 1)
		{
			$searchData = UserUtil::getAdminWebUser()->getState('searchData');

			if (is_array($searchData) && isset($searchData[get_class($this)]))
			{
				$data = $searchData[get_class($this)];

				foreach($data as $key => $value)
				{
					$this->$key = $value;
				}

				if ($clean == true)
				{
					$this->cleanSearchData();
				}

			}
		}
	}

        #清除保存的搜索数据
	protected function cleanSearchData()
	{
		$searchData = UserUtil::getAdminWebUser()->getState('searchData');#获取$_SESSION['searchData']数据

		if (is_array($searchData) && isset($searchData[get_class($this)]))#数据是否存在
		{
			unset($searchData[get_class($this)]);#清除保存的数据
		}

		UserUtil::getAdminWebUser()->setState('searchData', $searchData);#保存清楚过的数据
	}
}
