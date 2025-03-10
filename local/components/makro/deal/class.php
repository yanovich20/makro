<?php
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Application;
use Bitrix\Crm\DealTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use \Bitrix\Main\Errorable;

class DealList  extends \CBitrixComponent implements  Controllerableб, Errorable{

    private $curUserId;

    protected $errorCollection;
    public function configureActions()
    {
        return [ 
            'getList' => [  
                'prefilters' => [
                    new HttpMethod(
                        array(HttpMethod::METHOD_POST)
                    ),
                    new Csrf(),
                ],
                'postfilters' => []
            ],
        ];
    }
    public function getDealListAction(){
        try{
        [$items,$order,$navHtml] = $this->getResult();
        $_SESSION["order"] = $order;
        $_SESSION["sort"] = "TITLE";
        }
        catch(\Throwable $e)
        {
            \Bitrix\Main\Diag\Debug::writeToFile($e->gtMesage,"error","/local/debug.log");
            $this->errorCollection[] = new Error($e->getMessage());
            return null;
        }
        return ["ITEMS"=>$items,"ORDER"=>$order,"NAV_HTML"=>$navHtml];
    }

    public function executeComponent(){
        $this->curUserId = CurrentUser::get()->getId();
        try{
        [$this->arResult["ITEMS"] , $order,$this->arResult["NAV_HTML"]]= $this->getResult();
        }
        catch(\Throwable $e)
        {
            \Bitrix\Main\Diag\Debug::writeToFile($e->gtMesage,"error","/local/debug.log");
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
        if(!$by)
        {
            if(!$_SESSION["sort"])
            {
                $by = "ID";
            }
            else
            {
                $by = $_SESSION["sort"];
            }
        }
        echo "by". $by. "<br/>";
        $order = $request->get("order");
        if(!$order)
        {
            if(!$_SESSION["order"])
                $order = "ASC";
            else
                $order = $_SESSION["order"];
        }
        
        $nav = new \Bitrix\Main\UI\PageNavigation("nav-deal-list");
        $nav->allowAllRecords(true)
            ->setPageSize(5)
            ->initFromUri();
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
        return  [$dealList->fetchAll(), $order, $pageNav];
    }
}