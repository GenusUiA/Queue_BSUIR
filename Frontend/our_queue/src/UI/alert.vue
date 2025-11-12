<template>
  <div class="alert-wrapper">
    <div class="span-container">
      <span
        :class="[
          'alert-container',
          { success: status === 'success' },
          { error: status === 'error' },
        ]"
        >{{ text }}</span
      >
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from "vue";

const props = defineProps({
  text: {
    type: String,
    required: true,
  },
  status: {
    type: String,
    default: "success",
  },
  duration: {
    type: Number,
    default: 2000, // общая продолжительность всей анимации
  },
});

const isVisible = ref(true);

onMounted(() => {
  setTimeout(() => {
    isVisible.value = false;
  }, props.duration);
});
</script>

<style scoped>
.alert-wrapper {
  position: fixed;
  bottom: 20px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 1000;
}

.span-container {
  display: inline-flex;
  justify-content: center;
  animation: continuousSlideUp v-bind(duration + "ms") ease-in-out forwards;
}

.alert-container {
  padding: 0.75rem 1.5rem;
  border-radius: 0.5rem;
  font-weight: 500;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.success {
  background-color: #10b981;
  color: white;
}

.error {
  background-color: #ef4444;
  color: white;
  outline: none;
}

/* Непрерывная анимация без задержки */
@keyframes continuousSlideUp {
  0% {
    transform: translateY(100px);
    opacity: 0;
  }
  30% {
    transform: translateY(0);
    opacity: 1;
  }
  70% {
    transform: translateY(0);
    opacity: 1;
  }
  100% {
    transform: translateY(-50px);
    opacity: 0;
  }
}

/* Альтернативный вариант - более плавный */
@keyframes smoothContinuous {
  0% {
    transform: translateY(100px) scale(0.8);
    opacity: 0;
  }
  25% {
    transform: translateY(0) scale(1);
    opacity: 1;
  }
  75% {
    transform: translateY(0) scale(1);
    opacity: 1;
  }
  100% {
    transform: translateY(-30px) scale(0.95);
    opacity: 0;
  }
}

/* Самый быстрый вариант */
@keyframes quickSlide {
  0% {
    transform: translateY(100px);
    opacity: 0;
  }
  20% {
    transform: translateY(0);
    opacity: 1;
  }
  80% {
    transform: translateY(0);
    opacity: 1;
  }
  100% {
    transform: translateY(-30px);
    opacity: 0;
  }
}
</style>
