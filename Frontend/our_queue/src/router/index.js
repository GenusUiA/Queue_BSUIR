import { createRouter, createWebHistory } from "vue-router";
import SignIn from "../components/SignIn.vue";
import SignUp from "../components/SignUp.vue";
import Login from "../components/Login.vue";

const routes = [
  {
    path: "/",
    redirect: "/login/signUp",
  },
  {
    path: "/login",
    name: "login",
    alias: "/",
    component: Login,
    children: [
      {
        path: "signUp",
        name: "signUp",
        component: SignUp,
      },
      {
        path: "signIn",
        name: "signIn",
        component: SignIn,
      },
    ],
  },

  {
    path: "/queue",
    name: "queue",
    // route level code-splitting
    // this generates a separate chunk (about.[hash].js) for this route
    // which is lazy-loaded when the route is visited.
    component: () =>
      import(/* webpackChunkName: "about" */ "../components/Queue.vue"),
  },
];

const router = createRouter({
  history: createWebHistory(import.meta.env.VITE_BASE_URL),
  routes,
});

export default router;
