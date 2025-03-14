<?php
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Engine\Response\DataType\Page;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Application;
use Bitrix\Crm\DealTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Bitrix\Main\Errorable;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\UI\PageNavigation;

class DealList  extends \CBitrixComponent implements  Controllerable, Errorable{

    private const PAGE_SIZE =5;
    private $curUserId;
    private $nav;
    protected $errorCollection = array();
    public function configureActions()
    {
        return [ 
            'getList' => [  
                'prefilters' => [
                ],
                'postfilters' => []
            ],
        ];
    }
    public function onPrepareComponentParams($arParams)
	{
		$this->errorCollection = new ErrorCollection();
	}
    public function getDealListAction($action, PageNavigation $nav){
        try{
        $nav->setPageSize(self::PAGE_SIZE);
        $this->nav = $nav;
        [$items,$order,$navHtml,$total] = $this->getResult();
        $_SESSION["order"] = $order;
        $_SESSION["sort"] = "TITLE";
        }
        catch(\Throwable $e)
        {
            \Bitrix\Main\Diag\Debug::writeToFile($e->getMessage(),"error","/local/debug.log");
            $this->errorCollection[] = new Error($e->getMessage());
            return null;
        }
        return new Page("ITEMS",$items,$total);
    }

    public function executeComponent(){
        $this->curUserId = CurrentUser::get()->getId();
        try{
        [$this->arResult["ITEMS"] , $order,$this->arResult["NAV_HTML"]]= $this->getResult();
        }
        catch(\Throwable $e)
        {
            \Bitrix\Main\Diag\Debug::writeToFile($e->getMessage(),"error","/local/debug.log");
            $this->errorCollection[] = new Error($e->getMessage());
            return null;
        }
        $this->includeComponentTemplate();
    }
    public function getErrors()
        {
            return $this->errorCollection->toArray();
        }

	public function getErrorByCode($code)
        {
            return $this->errorCollection->getErrorByCode($code);
        }
    public function getResult(){
        if(!Loader::includeModule("crm"))
        {
            echo "Не установлен модуль  crm";
            return;
        }
        $this->curUserId = CurrentUser::get()->getId();
        if(!$this->curUserId)
        {
            echo "Вы не авторизованы";
            return;
        }

        $request = Context::getCurrent()->getRequest();
        $by = $request->get("sort");
        if(empty($by) ||$by ==="undefined")
        {
            if(empty($_SESSION["sort"]))
            {
                $by = "ID";
            }
            else
            {
                $by = $_SESSION["sort"];
            }
        }
        $order = $request->get("order");
        if(empty($order)||$order === "undefined")
        {
            if($_SESSION["order"]==="ASC")
                $order = "ASC";
            else
                $order = "DESC";
        }
        $nav = new PageNavigation("nav-deal-list");
        $nav->allowAllRecords(true)
            ->setPageSize(self::PAGE_SIZE)
            ->initFromUri();
        if(!empty($this->nav))
            $nav=$this->nav;
        $dealList = DealTable::getList(array(
            'order' => array($by => $order),
            'count_total' => true,
            'offset' => $nav->getOffset(),
            'limit' => $nav->getLimit(),
            "select" =>["ID","TITLE","PRODUCT_ID","PRODUCT_NAME"=>"ELEMENT.PRODUCT_NAME","NAME"=>"MODIFY.NAME","LAST_NAME"=>"MODIFY.LAST_NAME"],
            'runtime' => [
                'MODIFY' => [
                    'data_type' => \Bitrix\Main\UserTable::class,
                    'reference' => [
                        '=this.MODIFY_BY_ID' => 'ref.ID',
                    ]
                ],
                'ELEMENT' => [
                    'data_type' => \Bitrix\Crm\ProductRowTable::class,
                    'reference' => [
                        '=this.ID' => 'ref.OWNER_ID',
                    ]
                ]
            ]
        ));
        $nav->setRecordCount($dealList->getCount());
        ob_start();
        global $APPLICATION;
        $APPLICATION->IncludeComponent(
            "bitrix:main.pagenavigation",
            "",
            array(
                "NAV_OBJECT" => $nav,
                "SEF_MODE" => "N",
            ),
            false
        );

        $pageNav = ob_get_clean();
        return  [$dealList->fetchAll(), $order, $pageNav,$dealList->getCount()];
    }
}