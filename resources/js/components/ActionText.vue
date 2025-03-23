<script setup lang="ts">
interface Props {
  as?: 'a' | 'button' | 'span' | 'div';
}

withDefaults(defineProps<Props>(), {
  as: 'button',
});
</script>

<template>
  <component
    :is="as"
    class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:!decoration-current dark:decoration-neutral-500"
  >
    <slot />
  </component>
</template>
