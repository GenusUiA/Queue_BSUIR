<script setup>
import { ref, watchEffect } from "vue";
import { useRouter } from "vue-router";

const componentName = ref("signUp");

const router = useRouter();
</script>

<template>
  <div class="card">
    <div class="header">
      <h3
        :class="{ active: $route.name === 'signUp' }"
        @click="router.push({ name: 'signUp' })"
      >
        SIGN UP
      </h3>
      <h3
        :class="{ active: $route.name === 'signIn' }"
        @click="router.push({ name: 'signIn' })"
      >
        SIGN IN
      </h3>
    </div>
    <router-view />
  </div>
</template>

<style>
.header {
  display: flex;
  justify-content: center;
}

h3 {
  cursor: pointer;
  padding: 10px 20px;
  transition: background-color 0.3s ease;
  border-radius: 8px;
  margin: 0;
  color: gray;
}

h3.active {
  background-color: #3a547f;
  color: white;
}
</style>
