<script setup>
import { reactive, ref, provide } from "vue";
import ComboBox from "../UI/comboBox.vue";
import { useRouter } from "vue-router";
import { useQuery, useMutation, useQueryClient } from "@tanstack/vue-query";
import store from "../store/index";

// --- состояние формы ---
const email = ref("");
const pass = ref("");
const router = useRouter();
const queryClient = useQueryClient();

const emailInput = ref(null);
const passInput = ref(null);

const errors = reactive({
  emailError: "",
  passwordError: "",
});

const addUserMutation = useMutation({
  mutationFn: async () => {
    const response = await fetch("https://localhost:7243/api/users/login", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        Password: pass.value,
        Email: email.value,
      }),
      credentials: "include",
    });

    if (!response.ok) {
      const errorText = await response.text();
      throw new Error(`Ошибка входа: ${errorText}`);
    }

    const data = await response.json();

    // 🟢 проверь структуру data — возможно, сервер возвращает просто user, а не { user: ... }
    console.log("➡️ Ответ сервера:", data);

    store.state.userId = data.id;
    store.state.groupId = data.groupId;
    store.state.numberSubgroup = data.subgroupNumber;

    return data;
  },
  onSuccess: () => {
    queryClient.invalidateQueries(["user"]);
  },
  onError: (error) => {
    console.error("❌ Ошибка при входе:", error);
  },
});

// --- отправка формы ---
const submit = async () => {
  errors.emailError = "";
  errors.passwordError = "";
  if (email.value == "") {
    errors.emailError = "Поле с почтой не может быть пустым";
    emailInput.value?.focus();
  } else if (pass.value == "") {
    errors.passwordError = "Поле с паролем не может быть пустым";
    passInput.value?.focus();
  } else {
    await addUserMutation.mutateAsync();
    router.push("/queue");
  }
};

//provide("student", student);
</script>

<template>
  <form @submit.prevent="submit" class="form">
    <div>
      <input
        :class="{
          input: errors.emailError === '',
          inputError: errors.emailError !== '',
        }"
        v-model="email"
        id="email"
        type="email"
        placeholder="Email"
        ref="emailInput"
      />
      <p class="error-message" v-if="errors.emailError !== ''">
        {{ errors.emailError }}
      </p>
    </div>

    <div>
      <input
        :class="{
          input: errors.passwordError === '',
          inputError: errors.passwordError !== '',
        }"
        v-model="pass"
        id="pass"
        type="password"
        placeholder="Пароль"
        ref="passInput"
      />
      <p class="error-message" v-if="errors.passwordError !== ''">
        {{ errors.passwordError }}
      </p>
    </div>

    <button type="submit">Войти</button>
  </form>
</template>

<style scoped>
button {
  border: none;
  padding: 10px 20px;
  font-size: 1.17em;
  border-radius: 0.3rem;
  background-color: #3a547f;
  color: white;
}
.active {
  background-color: aqua;
}

.error-message {
  color: #dc3545; /* Цвет сообщения об ошибке */
  font-size: 14px;
  margin-top: 5px;
}
</style>
