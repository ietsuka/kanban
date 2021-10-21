require('./bootstrap');
Vue.config.devtools = true;

import Vue from 'vue';
Vue.component("kanban-board", require("./components/KanbanBoard.vue").default);

const app = new Vue({
  el: "#app"
});