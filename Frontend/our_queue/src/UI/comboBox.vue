<script setup>
const props = defineProps({
  title: {
    type: String,
    required: true,
  },
  list: {
    type: Array,
    required: true,
  },
  valueKey: {
    type: String,
    required: true,
  },
  idKey: {
    type: String,
    required: true,
  },
  className: {
    type: String,
    default: "select",
  },

  // url: {
  // 	type: String,
  // 	required: true
  // },
});

const selectedValue = defineModel();
</script>

<template>
  <div class="select-container">
    <!-- <img :src="url" alt=""> -->
    <select :class="className" v-model="selectedValue" name="" id="">
      <option value="">{{ title }}</option>
      <option v-for="item in list" :key="item[idKey]" :value="item[idKey]">
        {{ item[valueKey] }}
      </option>
    </select>
  </div>
</template>

<style scoped>
.select {
  padding: 0.7rem;
}

select {
  width: 100%;
}

select:focus {
  outline: 1px solid aqua;
}

.select-container {
  position: relative;
}

.title {
  position: absolute;
  top: 0.3%;
  left: 1rem;
  transform: translateY(-50%);
  color: #888;
  pointer-events: none; /* чтобы не мешал клику по select */
  font-size: 0.9rem;
  background: white; /* если нужно закрыть фон под текстом */
  padding: 0 4px;
}
</style>
