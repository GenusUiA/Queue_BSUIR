import { createApp } from "vue";
import App from "./App.vue";
import router from "./router";
import store from "./store";
import "./style.css";
import { QueryClient, VueQueryPlugin } from "@tanstack/vue-query"; // Импортируйте QueryClient и VueQueryPlugin

const queryClient = new QueryClient();

createApp(App)
  .use(store)
  .use(router)
  .use(VueQueryPlugin, { queryClient }) // Используйте VueQueryPlugin с созданным queryClient
  .mount("#app");
