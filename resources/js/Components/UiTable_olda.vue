<template>
  <div>
    <table v-if="rows && rows.length">
      <tr v-for="row in rows" :key="row.id">
        <td>{{ row.name }}</td>
        <td>{{ row.age }}</td>
      </tr>
    </table>
    <p v-else>暫無數據</p>
  </div>
</template>

<script setup>
defineProps({
  rows: {
    type: Array,
    default: () => [],
  },
});
</script>
