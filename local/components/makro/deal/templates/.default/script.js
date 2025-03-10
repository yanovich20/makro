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
             navHtml:this.$store.state.navHtml
          }
       },            
       mounted: function() {
         // BX.Vue.event.$on("updateDeal", this.getDealList); 
         //let div = document.createElement("div");
         //div.innerHTML = this.$store.state.navHtml;
         var textArea = document.createElement('textarea'); 
         textArea.innerHTML = this.$store.state.navHtml; 
         let text = textArea.value;
         let pagination = document.querySelector(".paginator");
         console.log(pagination);
         console.log(this.$store.state.order);
         pagination.innerHTML = text;//div.innerHTML;
       },      
       updated: function () {
          //при изменении обработчик
       },
       beforeDestroy() {
          //обработчик перед destroy
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
             })
 
             request.catch(function (response) {
                console.log(response);
                store.commit("setLoad", false);
             })
 
             request.then(function (response) {               
                if (response.status == "success") {
                  console.log(response);
                   store.commit("setDealItems", response.data["ITEMS"]);
                  // store.commit("setOrder", response.data["ORDER"]);                     
                   store.commit("setLoad", false);
                   store.commit("setNavHtml",response.data["NAV_HTML"]);
                   //window.location.search="?sort=TITLE&order="+this.$store.state.order;
                   //console.log(order);
                  // console.log(response.data["ORDER"]);
                   if(that.$store.state.order==="ASC")
                   //if(response.data["ORDER"] == "ASC")
                     {
                        //this.$store.classSort = "headerSortDown";
                        //img.classList.remove("headerSortUp");
                        //img.classList.add("headerSortDown");
                       // this.$store.isAsc = true;
                      //  this.$store.isDesc = false;
                        store.commit("setIsAsc",true);
                        store.commit("setIsDesc",false);
                     }
                     else
                     {
                        //img.classList.remove("headerSortDown");
                        //img.classList.add("headerSortUp");
                        //this.$store.classSort = "hedaerSortUp";
                       // this.$store.isAsc = false;
                       // this.$store.isDesc = true;
                        store.commit("setIsAsc",false);
                        store.commit("setIsDesc",true);
                     }
                     console.log("that order is "+ that.$store.state.order);
                   //  const url = new URL(window.location.href);
                   //  url.searchParams.set('order',that.$store.state.order); 
                   //  url.searchParams.set('sort','TITLE');
                   //  console.log(url);
                   //  window.location.replace(url.href);
                     //window.location.search = urlParams.toString();
                }
             })
          },
          sortByTitle:function(){
            let img = document.querySelector("th.title img");
            if(this.$store.state.order == "ASC")
            {
               //this.$store.classSort = "headerSortDown";
               //img.classList.remove("headerSortUp");
               //img.classList.add("headerSortDown");
               this.$store.state.order = "DESC";
               //this.$store.isAsc = false;
               //this.$store.isDesc = true;
               //store.commit("setIsAsc",false);
               //store.commit("setIsDesc",true);
            }
            else
            {
               //img.classList.remove("headerSortDown");
               //img.classList.add("headerSortUp");
               //this.$store.classSort = "hedaerSortUp";
               //this.$store.isAsc = true;
               //this.$store.isDesc = false;
               //store.commit("setIsAsc",true);
               //store.commit("setIsDesc",false);
               this.$store.state.order = "ASC";
            }
            store.commit("setOrder", this.$store.state.order);
            console.log("order is" +this.$store.state.order);
            // window.location.search="?sort=TITLE&order="+this.$store.state.order;
            //console.log(window.location.href);
            //window.location.reload();
            /*const url = new URL(window.location.href);
            url.searchParams.set('order',this.$store.state.order); 
            url.searchParams.set('sort','TITLE'); 
            window.location.href = url.toString();
            console.log(url);*/
            var textArea = document.createElement('textarea'); 
         textArea.innerHTML = this.$store.state.navHtml; 
         let text = textArea.value;
         let pagination = document.querySelector(".paginator");
         console.log(pagination);
         console.log(this.$store.state.order);
         pagination.innerHTML = text;//div.innerHTML;
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