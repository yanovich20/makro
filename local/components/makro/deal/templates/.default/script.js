;(function (window) {   
    "use strict"
 
    const BX = window.BX;
 
    BX.Vue.component("deal-list", { 
       props:{},
       data(){ 
          return { //данные для первоначальной отрисовки
             items: this.$store.state.items,
             loaded: false,
             order: this.$store.state.order,
             isAsc:true,
             isDesc:false,
          }
       },            
       mounted: function() {
         this.getDealList();
       },      
       methods: {         
          getDealList: function (sort,order) {
            let that = this;
             store.commit("setLoad", true);
             var request = BX.ajax.runComponentAction("makro:deal", "getDealList", { //запустится метод GetBasketAction из class.php
                mode: "class",              
                data: {                  
                   sessid: BX.message("bitrix_sessid"),
                   sort:sort,
                   order:order
                },
                navigation : {
                  page: document.querySelector(".navigation-current-page").innerText
                }
             })
 
             request.catch(function (response) {
               console.log(response);
                store.commit("setLoad", false);
             })
 
             request.then(function (response) {  
                if (response.status == "success") {
                   store.commit("setDealItems", response.data["ITEMS"]);                   
                   store.commit("setLoad", false);
                     if(that.$store.state.order==="ASC")
                     {
                        store.commit("setIsAsc",true);
                        store.commit("setIsDesc",false);
                     }
                     else
                     {
                        store.commit("setIsAsc",false);
                        store.commit("setIsDesc",true);
                     }
                }
             })
          },
          sortByTitle:function(){
            let img = document.querySelector("th.title img");
            if(this.$store.state.order == "ASC")
            {
               this.$store.state.order = "DESC";
            }
            else
            {
               this.$store.state.order = "ASC";
            }
            store.commit("setOrder", this.$store.state.order);
            this.getDealList("TITLE",this.$store.state.order);
          }
       },
       computed: {},
       template: `
          <div id="list">
             <h1>Список сделок</h1>       
             <div v-if="this.$store.state.loaded">
                <img  class="loader" src="/local/public/icon.gif"/>
             </div>
             <div v-if="!this.$store.state.loaded">            
                <div v-if="this.$store.state.items.length==0">               
                    Нет активных сделок
                </div>
                <div v-else>                
                   <table>
                   <thead>
                   <tr>
                     <th>
                     ID
                     </th>
                     <th v-on:click="sortByTitle" class="title">
                     Заголовок <img v-bind:class="{'headerSortUp':this.$store.state.isAsc,'headerSortDown':this.$store.state.isDesc}" src="/local/public/down.png"/>
                     </th>
                     <th>
                     Название товара
                     </th>
                     <th>
                     Кем изменен
                     </th>
                   </tr>
                   </thead>
                   <tbody>
                   <tr v-for="item in this.$store.state.items">
                      <td>{{item["ID"]}}</td> 
                      <td> {{item["TITLE"]}}</td>
                      <td> {{item["PRODUCT_NAME"]}}</td>
                      <td> {{item["NAME"]}} {{item["LAST_NAME"]}}
                   </tr>
                   </tbody>
                     </table>
                     <div class="pagination">
                     </div>
                </div>
             </div>
          </div>       
         `,
    })
 })(window);