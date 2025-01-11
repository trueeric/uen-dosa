<!-- resources/js/Pages/Students/Index.vue -->
<template>
  <div>
    <h1>Students List</h1>
    <div v-for="student in students" :key="student.id">
      {{ student.name }}
    </div>
  </div>
</template>

<script setup>
defineProps({
  students: {
    type: Array,
    required: true,
  },
});
</script>
