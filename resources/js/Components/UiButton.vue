<template>
  <component :is="buttonComponent" v-bind="$attrs" @click="$emit('click')">
    <slot />
  </component>
</template>

<script>
import PrimaryButton from "./PrimaryButton.vue";
import SecondaryButton from "./SecondaryButton.vue";
import DangerButton from "./DangerButton.vue";

export default {
  name: "UiButton",
  props: {
    type: {
      type: String,
      default: "primary", // 預設為主要按鈕
    },
  },
  computed: {
    buttonComponent() {
      switch (this.type) {
        case "secondary":
          return SecondaryButton;
        case "danger":
          return DangerButton;
        default:
          return PrimaryButton;
      }
    },
  },
};
</script>
