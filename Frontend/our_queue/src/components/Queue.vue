<script setup>
import { computed, inject, provide, ref, watch, watchEffect } from "vue";
import comboBox from "../UI/comboBox.vue";
import alert from "../UI/alert.vue";
import { useStore } from "vuex";
import { useMutation, useQuery, useQueryClient } from "@tanstack/vue-query";
import ListQueue from "./ListQueue.vue";
import ListNotifications from "./ListNotifications.vue";

const selectedSubject = ref("");
const selectedDate = ref("");
const subgroupNumber = ref(null);
const countStudents = ref(-1);

const isLoading = ref(false);
const allertMessage = ref("");
const isShowAlert = ref(false);

const isOpenNotification = ref(false);

const store = useStore();

provide("isLoading", isLoading);
provide("subgroupNumber", subgroupNumber);

const queryClient = useQueryClient();

const mutationAddQueue = useMutation({
  mutationFn: fetchAddQueue,
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ["queue"] });
  },
  onError: (error) => {
    console.error("Ошибка подписки:", error);
  },
});

const mutationDeleteQueue = useMutation({
  mutationFn: fetchDeleteQueue,
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: ["queue"] });
  },
  onError: (error) => {
    console.error("Ошибка при удалении:", error);
  },
});

async function fetchAddQueue(number) {
  const newQueue = {
    userId: store.state.userId,
    dateId: +selectedDate.value,
    numberUser: +number,
  };

  const response = await fetch(`https://localhost:7243/api/queue`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(newQueue),
    credentials: "include",
  });
}

async function fetchDeleteQueue(queueId) {
  const response = await fetch(`https://localhost:7243/api/queue/${queueId}`, {
    method: "DELETE",
    credentials: "include",
  });
}

const fetchSubjects = async () => {
  console.log("📦 groupId для запроса предметов:", store.state.groupId);

  const res = await fetch(
    `https://localhost:7243/api/subject/subjects/${store.state.groupId}`,
    {
      credentials: "include",
    }
  );
  if (!res.ok) throw new Error("Ошибка загрузки групп");
  return res.json(); // должен вернуть массив [{id, name}, ...]
};

const fetchDates = async () => {
  const res = await fetch(
    `https://localhost:7243/api/subject/schedule/${store.state.groupId}`,
    {
      credentials: "include",
    }
  );
  if (!res.ok) throw new Error("Ошибка загрузки групп");
  const data = await res.json(); // должен вернуть массив [{id, name}, ...]
  return data;
};

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

const fetchQueue = async () => {
  const params = new URLSearchParams({
    groupId: store.state.groupId,
    subjectId: +selectedSubject.value,
    subgroupNumber: +subgroupNumber.value,
    dateId: +selectedDate.value,
  }).toString();
  const res = await fetch(`https://localhost:7243/api/queue?${params}`, {
    credentials: "include",
  });
  if (!res.ok) throw new Error("Ошибка загрузки групп");
  const data = await res.json(); // должен вернуть массив [{id, name}, ...]
  return data;
};

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

const {
  data: subjects = [],
  isLoadingGroup,
  isErrorGroup,
} = useQuery({
  queryKey: ["subject"],
  queryFn: fetchSubjects,
  refetchOnWindowFocus: false,
  retry: false,
  staleTime: 300000,
});

const {
  data: dates = [],
  isLoadingDates,
  isErrorDates,
} = useQuery({
  queryKey: ["dates", selectedSubject.value], // <--- ключ теперь зависит от выбранного предмета
  queryFn: fetchDates, // передаём выбранный предмет
  enabled: computed(() => selectedSubject.value !== ""), // вызовется только если selectedSubject не пустой
  refetchOnWindowFocus: false,
  retry: false,
  staleTime: 300000,
});

const {
  data: queue = [],
  isLoadingQueue,
  isErrorQueue,
  refetch: refetchQueue,
} = useQuery({
  queryKey: computed(() => [
    "queue",
    selectedSubject.value,
    selectedDate.value,
    subgroupNumber.value,
  ]),
  queryFn: fetchQueue,
  enabled: computed(
    () =>
      selectedSubject.value !== "" &&
      selectedDate.value !== "" &&
      subgroupNumber.value !== null
  ),
  refetchOnWindowFocus: false,
  retry: false,
  staleTime: 0,
  refetchInterval: 3000,
});

const fetchLengthAllStudents = async () => {
  const response = await fetch(
    `https://localhost:7243/api/users/lengthAllStudentsByGroup/${store.state.groupId}`,
    {
      credentials: "include",
    }
  );
  try {
    if (response.ok) {
      const data = await response.json();
      return data;
    } else return 0;
  } catch {
    return 0;
  }

  return;
};

const fetchLengthAllStudentsBySubgroup = async () => {
  const response = await fetch(
    `https://localhost:7243/api/users/lengthAllStudentsBySubgroup/${store.state.groupId}/${subgroupNumber.value}`,
    {
      credentials: "include",
    }
  );
  try {
    if (response.ok) {
      const data = await response.json();
      return data;
    } else return 0;
  } catch {
    return 0;
  }

  return;
};

watch(selectedSubject, () => {
  console.log("1 ", selectedDate.value);
  selectedDate.value = "";
});

watchEffect(async () => {
  console.log("2 ", selectedDate.value);
  if (selectedSubject.value && selectedDate.value) {
    console.log("dates = ", dates);
    console.log("dates.value = ", dates?.value);
    console.log("Привет");
    subgroupNumber.value = dates?.value?.find(
      (d) =>
        d.subjectId === +selectedSubject?.value && d.id === +selectedDate?.value
    ).forSubgroup;
    //subjectsOnDate.value = store.getters['date/getSubjectByDate'](+selectedDate.value)
    if (subgroupNumber.value === -1) {
      countStudents.value = await fetchLengthAllStudents();
    } else {
      countStudents.value = await fetchLengthAllStudentsBySubgroup();
    }
  }
});

//countStudents.value
const queueItems = computed(() => {
  if (!countStudents.value || countStudents.value <= 0) {
    return [];
  }

  const userInQueue = queue?.value?.find(
    (el) => el.userId === store.state.userId
  );

  let isYou = Boolean(userInQueue); //userInQueue !== null;

  console.log("isYou: ", isYou);

  const result = new Array(countStudents?.value).fill().map((_, index) => ({
    number: index + 1,
    name: "",
    status: "Записаться",
    hideButton: isYou,
    queueId: null, // добавили queue_id
    uid: -1,
  }));

  for (let el of queue?.value || []) {
    result[el.numberUser - 1] = {
      ...result[el.numberUser - 1],
      name: el.userId === store.state.userId ? "Вы" : el.userName,
      status: el.userId === store.state.userId ? "Отписаться" : "Забронировано",
      hideButton: isYou,
      queueId: el.id,
      uid: el.userId,
    };
  }

  return result;
});
// const queueItems = computed(() => {
//   const result = new Array(countStudents.value).fill().map((_, index) => ({
//     number: index + 1,
//     name: "",
//     status: "",
//     hideButton: false,
//     queue_id: null, // добавили queue_id
//   }));
//   for (let i = 0; i < store.state.queue.queue.length; i++) {
//     result[store.state.queue.queue[i].number_user-1].name =
//   }
// });
// const queueItems = computed(() => {
//   const result = [];
//   let hasYou = false;
//   for (let i = 1; i <= countStudents.value; i++) {
//     // Находим запись в очереди по номеру и дате
//     const record = store.state.queue.queue.find(
//       (x) => x.number_user === i && x.date_id === +selectedDate.value
//     );

//     // Находим пользователя по записи
//     const user = record
//       ? store.state.users.users.find((u) => u.user_id === record.user_id)
//       : null;

//     let status = "";
//     let isYou = false;

//     // Определяем статус
//     if (!user) {
//       status = "Записаться";
//     } else if (user.user_id === store.state.users.users[5].user_id) {
//       status = "Отписаться";
//       isYou = true;
//       hasYou = true;
//     } else {
//       status = "Забронировано";
//     }

//     result.push({
//       number: i,
//       name: user ? user.username : "",
//       status,
//       isYou,
//       queue_id: record ? record.queue_id : null, // добавили queue_id
//     });
//   }
//   // hasYou;
//   // Если пользователь уже записан — скрываем все кнопки "Записаться"
//   //const hasYou = result.some((item) => item.isYou);

//   if (hasYou) {
//     return result.map((item) =>
//       item.status === "Записаться"
//         ? { ...item, status: "", hideButton: true }
//         : item
//     );
//   }

//   return result;
// });

const unsubscribe = (queueId) => {
  mutationDeleteQueue.mutateAsync(queueId);
};

const subscribe = async (number) => {
  mutationAddQueue.mutateAsync(number);
};

const getCurrentDate = () => {
  const currentDate = new Date();

  // Получаем компоненты даты и времени
  const day = String(currentDate.getDate()).padStart(2, "0"); // День (с нулем спереди)
  const month = String(currentDate.getMonth() + 1).padStart(2, "0"); // Месяц (нумерация с 0)
  const year = currentDate.getFullYear(); // Год

  const hours = String(currentDate.getHours()).padStart(2, "0"); // Часы
  const minutes = String(currentDate.getMinutes()).padStart(2, "0"); // Минуты

  // Формируем строку с нужным форматом
  const formattedDate = `${day}.${month}.${year} ${hours}:${minutes}`;
  return formattedDate;
};

const excahgeFunction = async (uId) => {
  const date = getCurrentDate();
  const fromUserId = store.state.userId;
  const toUserId = uId;
  const dateId = selectedDate.value;
  isLoading.value = true;
  try {
    const response = await fetch("https://localhost:7243/api/exchange", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        CreatedAt: date,
        FromUserId: fromUserId,
        ToUserId: toUserId,
        DateId: dateId,
      }),
    });

    if (response.ok()) {
      allertMessage.value = "Запрос на обмен местами успешно отправлен";
    }
  } catch (error) {
    allertMessage.value = error.message;
  } finally {
    isLoading.value = false;
    isShowAlert.value = true;
    setTimeout(() => (isShowAlert.value = false), 3000);
  }
};

const toggleNotification = () => {
  isOpenNotification.value = !isOpenNotification.value;
  console.log(isOpenNotification.value);
};

// const length = computed(() => notifications.value.length);
</script>

<template>
  <div class="card" @click="isOpenNotification = false">
    <header>
      <div>
        <h1>Запись в очередь на предмет</h1>
        <p>Выберите предмет и дату, чтобы занять очередь</p>
      </div>
      <div
        style="
          display: flex;
          align-items: start;
          padding-top: 1.5rem;
          position: relative;
        "
      >
        <img
          src="/images/Bell.png"
          class="notificationsImage"
          @click.stop="toggleNotification"
          alt=""
        />
        <p class="countNotifications">
          {{ notifications.length < 9 ? notifications.length : "9+" }}
        </p>
        <ListNotifications
          v-if="isOpenNotification"
          :notifications="notifications"
          class="notifications-list"
        />
      </div>
    </header>

    <div style="display: flex; flex-direction: column; gap: 0.5rem">
      <combo-box
        :list="subjects || []"
        id-key="id"
        value-key="name"
        v-model="selectedSubject"
        title="Выберите предмет"
      />
      <combo-box
        :list="dates?.filter((d) => d.subjectId === +selectedSubject) || []"
        id-key="id"
        value-key="date"
        v-model="selectedDate"
        title="Выберите дату"
      />
    </div>
    <p>
      {{
        !subgroupNumber
          ? ""
          : subgroupNumber === -1
          ? "Общая"
          : `Подгруппа ${subgroupNumber}`
      }}
    </p>
    <div
      style="
        display: flex;
        justify-content: center;
        align-items: center;
        flex: 1;
      "
      v-if="selectedSubject == '' || selectedDate == ''"
    >
      Пусто
    </div>
    <div style="flex: 1; overflow-y: auto" v-else>
      <ListQueue
        @subscribe="subscribe"
        @unsubscribe="unsubscribe"
        @excahge="excahgeFunction"
        :queue-items="queueItems"
      />
    </div>

    <alert v-if="isShowAlert" text="Успешно отправлено" status="success">
    </alert>
  </div>
</template>

<style scoped>
header {
  display: flex;
  gap: 1rem;
}

.notificationsImage {
  width: 25px;
  height: auto;
}

.countNotifications {
  position: absolute;
  background-color: #0392ff;
  border-radius: 50%;
  width: 1.2rem; /* Установите ширину */
  height: 1.2rem; /* Установите высоту одинаково */
  display: flex; /* Упрощает выравнивание содержимого внутри */
  justify-content: center; /* Центрирует содержимое по горизонтали */
  align-items: center; /* Центрирует содержимое по вертикали */
  color: white;
  font-size: 0.8rem; /* Размер шрифта внутри круга */
  left: 1rem;
}

.card {
  display: flex;
  flex-direction: column;
  /* gap: 1rem; */
  background-color: white;
  border-radius: 0.5rem;
  padding: 1rem 2rem 0.5rem 2rem;
  height: 75vh;
  /* width: 20vw; */
}

.notifications-list {
  position: absolute;
  top: 100%;
  right: 0;
  z-index: 1000;
  background: white;
  border: 1px solid #ccc;
  border-radius: 0.5rem;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  min-width: 300px;
  max-height: 400px;
  overflow-y: auto;
}
</style>
