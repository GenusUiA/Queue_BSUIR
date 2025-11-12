<script setup>
import { ESModulesEvaluator } from "vite/module-runner";
import ElementNotification from "./ElementNotification.vue";
import { useMutation, useQuery, useQueryClient } from "@tanstack/vue-query";

async function fetchNotifications() {
  try {
    const res = await fetch(`https://localhost:7243/api/exchange`, {
      method: "GET",
      credentials: "include",
    });

    if (res.ok) {
      const data = await res.json();
      console.log("data: ", data);
      return Array.isArray(data) ? data : []; // гарантируем, что вернется массив
    } else {
      throw new Error(`HTTP error! status: ${res.status}`);
    }
  } catch (error) {
    console.error("Ошибка загрузки уведомлений:", error);
    return []; // возвращаем пустой массив при ошибке
  }
}

const {
  data: notifications = [],
  isLoadingNotifications,
  isErrorNotifications,
  refetch: refetchNotifications,
} = useQuery({
  queryKey: ["notifications"],
  queryFn: fetchNotifications,
  refetchOnWindowFocus: false,
  retry: false,
  staleTime: 0,
  refetchInterval: 3000,
});
</script>

<template>
  <div class="conatiner">
    <ElementNotification
      v-for="value in notifications"
      :notification="value"
      :key="value.id"
    />
  </div>
</template>

<style scoped>
.conatiner {
  padding: 1rem;
}
</style>
