<script setup>
import ElementQueue from "./ElementQueue.vue";
const props = defineProps({
  queueItems: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits();

function subscribe(number) {
  emit("subscribe", number);
}

function unsubscribe(queueId) {
  emit("unsubscribe", queueId);
}

function excahge(uid) {
  emit("excahge", uid);
}
</script>

<template>
  <div class="list">
    <ElementQueue
      @subscribe="subscribe"
      @unsubscribe="unsubscribe"
      @excahge="excahge"
      v-for="item in queueItems"
      :key="item.number"
      :item="item"
    />
  </div>
</template>

<style scoped>
.list {
  list-style-type: none;
  display: flex;
  flex-direction: column;
  justify-content: start;
  gap: 0.3rem;
  padding: 0;
  overflow-y: auto;
  flex: 1;
}

.element {
  display: flex;
  justify-content: space-between;
  padding: 0.7rem;
  border: 1px solid grey;
  border-radius: 0.3rem;
  align-items: center;
}
</style>
