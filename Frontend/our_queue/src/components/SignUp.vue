<script setup>
import { reactive, ref, onMounted } from "vue";
import ComboBox from "../UI/comboBox.vue";
import { useRouter } from "vue-router";
import { useQuery, useMutation, useQueryClient } from "@tanstack/vue-query";
import { useStore } from "vuex";

// --- состояние формы ---
const student = reactive({});
const username = ref("");
const email = ref("");
const pass = ref("");
const group_id = ref("");
const subgroup_id = ref(-1);

const nameInput = ref(null);
const emailInput = ref(null);
const passInput = ref(null);
const groupInput = ref(null);

const errors = reactive({
  nameError: "",
  emailError: "",
  passwordError: "",
  groupError: "",
  subgroupError: "",
});

const router = useRouter();
const store = useStore();
const queryClient = useQueryClient();

onMounted(async () => {
  console.log("qqqq");
  const res = await fetch("https://localhost:7243/api/users/refresh", {
    credentials: "include",
    method: "POST",
  });
  if (res.ok) {
    const answer = await res.json();
    console.log(answer);
    store.state.userId = answer.userId;
    store.state.groupId = answer.groupId;
    store.state.numberSubgroup = answer.subgroupNumber;

    router.push({
      name: "queue",
    });
  }
  // должен вернуть массив [{id, name}, ...]
});

// --- загрузка групп ---
const fetchGroups = async () => {
  const res = await fetch("https://localhost:7243/api/group/all", {
    credentials: "include",
  });
  if (!res.ok) throw new Error("Ошибка загрузки групп");
  return res.json(); // должен вернуть массив [{id, name}, ...]
};

// ✅ один useQuery, без дублирования
const {
  data: groups,
  isLoading,
  isError,
} = useQuery({
  queryKey: ["groups"],
  queryFn: fetchGroups,
  refetchOnWindowFocus: false,
  retry: false,
  staleTime: 300000,
});

// --- отправка формы ---
const submit = async () => {
  errors.nameError = "";
  errors.emailError = "";
  errors.passwordError = "";
  errors.groupError = "";
  errors.subgroupError = "";
  if (username.value == "") {
    errors.nameError = "Поле с именем не может быть пустым";
    nameInput.value?.focus();
  } else if (email.value == "") {
    errors.emailError = "Поле с почтой не может быть пустым";
    emailInput.value?.focus();
  } else if (pass.value == "") {
    errors.passwordError = "Поле с паролем не может быть пустым";
    passInput.value?.focus();
  } else if (group_id.value == "") {
    errors.groupError = "Выберите группу";
    groupInput.value?.focus();
  } else if (subgroup_id.value == -1) {
    errors.subgroupError = "Выберите подгруппу";
  } else {
    student.username = username.value;
    student.email = email.value;
    student.pass = pass.value;
    student.group_id = +group_id.value;
    student.subgroup_id = subgroup_id.value;

    // await addUserMutation.mutateAsync();
    const response = await fetch("https://localhost:7243/api/users/registr", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        UserName: username.value,
        Password: pass.value,
        Email: email.value,
        GroupId: group_id.value,
        SubgroupNumber: subgroup_id.value,
      }),
    });
    console.log("➡️ Ответ сервера:", response.status);
    if (response.ok) {
      console.log("Регистрация прошла успешно");
      router.push("/login/signIn");
    }
  }
};

//provide("student", student);
</script>

<template>
  <form @submit.prevent="submit" class="form">
    <div>
      <input
        :class="{
          input: errors.nameError === '',
          inputError: errors.nameError !== '',
        }"
        v-model.trim="username"
        id="name"
        type="text"
        placeholder="Имя"
        ref="nameInput"
      />
      <p class="error-message" v-if="errors.nameError !== ''">
        {{ errors.nameError }}
      </p>
    </div>

    <div>
      <input
        :class="{
          input: errors.emailError === '',
          inputError: errors.emailError !== '',
        }"
        v-model.trim="email"
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
        v-model.trim="pass"
        id="pass"
        type="password"
        placeholder="Пароль"
        ref="passInput"
      />
      <p class="error-message" v-if="errors.passwordError !== ''">
        {{ errors.passwordError }}
      </p>
    </div>
    <div>
      <div v-if="isLoading">Загрузка групп...</div>
      <div v-else-if="isError">Ошибка при загрузке групп</div>
      <ComboBox
        :className="{
          input: errors.groupError === '',
          selectError: errors.groupError !== '',
        }"
        v-else
        v-model="group_id"
        :list="groups"
        title="Выберите группу"
        idKey="id"
        valueKey="name"
      />
      <p class="error-message" v-if="errors.groupError !== ''">
        {{ errors.groupError }}
      </p>
    </div>

    <div style="display: flex; gap: 0.5rem">
      <label class="label_sub" for="sub_1"
        ><input
          v-model="subgroup_id"
          class="sub"
          id="sub_1"
          type="radio"
          value="1"
        />
        Подгруппа 1</label
      >
      <label class="label_sub" for="sub_2"
        ><input
          v-model="subgroup_id"
          class="sub"
          id="sub_2"
          type="radio"
          value="2"
        />
        Подгруппа 2</label
      >
    </div>
    <p class="error-message" v-if="errors.subgroupError !== ''">
      {{ errors.subgroupError }}
    </p>
    <!-- <div>
      <button
        type="button"
        :class="{ active: subgroup_id === 1 }"
        @click="subgroup_id = 1"
      >
        Подгруппа 1
      </button>
      <button
        type="button"
        :class="{ active: subgroup_id === 2 }"
        @click="subgroup_id = 2"
      >
        Подгруппа 2
      </button>
    </div> -->

    <button type="submit">Зарегистрироваться</button>
  </form>
</template>

<style scoped>
:root {
  --color: 0392ff;
}
h1 {
  text-align: center;
}
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

.label_sub {
  display: flex;
  align-items: center;
  /* gap: 0.3rem; */

  font-size: 1.17em;
}

.error-message {
  color: #dc3545; /* Цвет сообщения об ошибке */
  font-size: 14px;
  margin-top: 5px;
}
</style>
