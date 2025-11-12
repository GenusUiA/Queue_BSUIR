<script setup>
import { inject, ref } from "vue";
import { useStore } from "vuex";

const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
});
const emit = defineEmits();

const isLoading = ref(false);
const isRotating = ref(false); // Добавляем состояние для анимации
const subgroupNumber = inject("subgroupNumber");

const handleExchangeClick = (uid) => {
  console.log("1");

  if (isRotating.value) return;
  isRotating.value = true; // Запускаем анимацию
  emit("excahge", uid);

  // Сбрасываем состояние анимации после завершения
  setTimeout(() => {
    isRotating.value = false;
  }, 600); // Время должно совпадать с длительностью анимации
};

const store = useStore();
</script>

<template>
  <div class="element">
    <div style="display: flex; gap: 1rem">
      <span>{{ item.number }}</span>
      <span>{{ item.name }}</span>
    </div>
    <!-- Если статус "Ожидание" — p, иначе button -->
    <template v-if="item.status === 'Забронировано'">
      <!-- <span>{{ item.status }}</span> -->
      <img
        v-if="item.hideButton"
        class="excahgeImage"
        src="/images/Exchange.png"
        alt=""
        @click="handleExchangeClick(item.uid)"
        :class="{ rotating: isRotating }"
      />
      <span v-if="isLoading" class="loader"></span>
    </template>

    <template v-else-if="item.status === 'Отписаться'">
      <button @click="emit('unsubscribe', item.queueId)" class="unsubscribe">
        {{ item.status }}
      </button>
    </template>

    <template
      v-else-if="
        item.status === 'Записаться' &&
        !item.hideButton &&
        (store.state.numberSubgroup === subgroupNumber || subgroupNumber == -1)
      "
    >
      <button @click="emit('subscribe', item.number)" class="subscribe">
        {{ item.status }}
      </button>
    </template>
  </div>
</template>

<style scoped>
.loader {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  display: inline-block;
  border-top: 4px solid #6ebeff;
  border-right: 4px solid transparent;
  box-sizing: border-box;
  animation: rotation 1s linear infinite;
}
.loader::after {
  content: "";
  box-sizing: border-box;
  position: absolute;
  left: 0;
  top: 0;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  border-left: 4px solid #337ab7;
  border-bottom: 4px solid transparent;
  animation: rotation 0.5s linear infinite reverse;
}
@keyframes rotation {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

button {
  border: none;
  padding: 0.3rem 0.6rem;
  border-radius: 0.3rem;
  color: white;
  cursor: pointer;
}

.subscribe {
  background-color: #0392ff;
}

.unsubscribe {
  background-color: #ff4040;
}

.excahgeImage {
  width: 25px;
  height: auto;
  cursor: pointer;
  transition: transform 0.6s ease-in-out; /* Плавный переход */
}

/* Класс для анимации поворота */
.excahgeImage.rotating {
  transform: rotate(360deg);
}

/* Альтернативный вариант с CSS animation */
.excahgeImage.rotating {
  animation: rotate360 0.6s ease-in-out;
}

@keyframes rotate360 {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
