<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
\Bitrix\Main\UI\Extension::load("ui.vue");
\Bitrix\Main\UI\Extension::load("ui.vue.vuex");
?>
<div id="deal-list"></div>
<div class="paginator"><?=$arResult["NAV_HTML"]?></div>
<script>
   var store = BX.Vuex.store({
    state: {
      loaded: false,
      items: <?= CUtil::PhpToJSObject($arResult['ITEMS']) ?>,      
      order:"ASC",
      isAsc:true,
      isDesc:false,
    },
    actions:{ 
    },
    mutations: { //мутаторы, при их вызове будет перерисовываться корзина
      setLoad(state, flag) {
        state.loaded = flag;       
      },
      setDealItems(state, items) {
        state.items = items;        
      },
      setOrder(state, order) {
        state.order = order;        
      },
      setIsAsc(state,isAsc)
      {
        state.isAsc = isAsc;
      },
      setIsDesc(state,isDesc){
        state.isDesc = isDesc;
      }
    }
  })

  let app = BX.Vue.create({
    el: '#deal-list',
    store: store,
    template: `<deal-list/>`,
  })
  
</script>