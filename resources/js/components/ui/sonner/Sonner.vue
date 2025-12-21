<script lang="ts" setup>
import type { ToasterProps } from "vue-sonner"
import { Toaster as Sonner } from "vue-sonner"

const props = withDefaults(defineProps<ToasterProps>(), {
    position: 'bottom-center',
})
</script>

<template>
  <Sonner
    class="toaster group"
    :icons="{
      success: undefined,
      error: undefined,
      warning: undefined,
      info: undefined,
      loading: undefined,
    }"
    v-bind="props"
    :style="{
      '--normal-bg': 'hsl(var(--card))',
      '--normal-text': 'hsl(var(--card-foreground))',
      '--normal-border': 'hsl(var(--border))',
      '--success-bg': 'hsl(var(--card))',
      '--success-text': 'hsl(var(--card-foreground))',
      '--success-border': 'hsl(var(--border))',
      '--error-bg': 'hsl(var(--card))',
      '--error-text': 'hsl(var(--card-foreground))',
      '--error-border': 'hsl(var(--border))',
      '--warning-bg': 'hsl(var(--card))',
      '--warning-text': 'hsl(var(--card-foreground))',
      '--warning-border': 'hsl(var(--border))',
      '--info-bg': 'hsl(var(--card))',
      '--info-text': 'hsl(var(--card-foreground))',
      '--info-border': 'hsl(var(--border))',
      '--loading-bg': 'hsl(var(--card))',
      '--loading-text': 'hsl(var(--card-foreground))',
      '--loading-border': 'hsl(var(--border))',
      '--toast-font': 'var(--font-sans)',
      '--border-radius': 'var(--radius)',
      '--toast-gap': '0.5rem',
      '--toast-shadow': 'var(--shadow-2xs)',
    }"
  />
</template>

<style lang="postcss">
@layer components {
  .toaster {
    font-family: var(--font-sans), ui-sans-serif, system-ui, sans-serif;
  }

  .toaster [data-sonner-toast] {
    @apply bg-card text-card-foreground border border-border rounded-md shadow-2xs relative overflow-hidden;
    font-family: var(--font-sans), ui-sans-serif, system-ui, sans-serif;
  }

  /* Comprehensive icon hiding - cover all possible selectors and states */
  .toaster [data-sonner-toast] [data-icon],
  .toaster [data-sonner-toast] svg[data-testid],
  .toaster [data-sonner-toast] svg,
  .toaster [data-sonner-toast] [data-status-icon],
  .toaster [data-sonner-toast] [data-sonner-icon],
  .toaster [data-sonner-toast] .sonner-icon,
  .toaster [data-sonner-toast] [aria-hidden="true"]:has(svg),
  .toaster [data-sonner-toast] > div:first-child:has(svg),
  .toaster [data-sonner-toast] > div:first-child > svg {
    display: none !important;
    visibility: hidden !important;
    width: 0 !important;
    height: 0 !important;
    opacity: 0 !important;
    position: absolute !important;
    left: -9999px !important;
  }

  /* Add padding for status bar - only for typed toasts */
  .toaster [data-sonner-toast][data-type]:not([data-type=""]) {
    padding-left: 26px !important;
  }

  /* Status bar positioning - fixed and stable */
  .toaster [data-sonner-toast][data-type]:not([data-type=""]):before {
    content: '' !important;
    position: absolute !important;
    left: 8px !important;
    top: 8px !important;
    width: 5px !important;
    height: calc(100% - 16px) !important;
    border-radius: 2.5px !important;
    pointer-events: none !important;
    z-index: 10 !important;
    transition: none !important;
  }

  /* Theme-aware status bar colors using CSS custom properties */
  .toaster [data-sonner-toast][data-type="success"]:before {
    @apply bg-emerald-600 dark:bg-emerald-400;
  }

  .toaster [data-sonner-toast][data-type="error"]:before {
    @apply bg-destructive;
  }

  .toaster [data-sonner-toast][data-type="warning"]:before {
    @apply bg-amber-600 dark:bg-amber-400;
  }

  .toaster [data-sonner-toast][data-type="info"]:before {
    @apply bg-blue-600 dark:bg-blue-400;
  }

  /* Button styling within toasts */
  .toaster [data-sonner-toast] button[data-action="true"] {
    @apply bg-primary text-primary-foreground rounded-md px-3 py-1.5 text-sm font-medium shadow-2xs transition-all hover:bg-primary/90;
  }

  .toaster [data-sonner-toast] button[data-cancel="true"] {
    @apply bg-secondary text-secondary-foreground rounded-md px-3 py-1.5 text-sm font-medium shadow-2xs transition-all hover:bg-secondary/80;
  }

  /* Close button */
  .toaster [data-sonner-toast] button[data-close-button="true"] {
    @apply bg-background text-foreground border border-border rounded-md p-1.5 transition-colors hover:bg-accent hover:text-accent-foreground;
  }

  /* Toast description styling */
  .toaster [data-sonner-toast] [data-description] {
    @apply text-muted-foreground text-sm leading-5;
  }

  /* Toast title styling */
  .toaster [data-sonner-toast] [data-title] {
    @apply font-medium text-sm leading-5;
  }

  /* Loading spinner color */
  .toaster [data-sonner-toast] [data-loading-icon] {
    @apply text-muted-foreground;
  }

  /* Ensure toast content stays properly positioned */
  .toaster [data-sonner-toast] > * {
    position: relative;
    z-index: 2;
  }

  /* Override any potential icon containers that might still be visible */
  .toaster [data-sonner-toast] > div:first-child {
    display: flex !important;
    align-items: flex-start !important;
    gap: 0 !important;
  }

  .toaster [data-sonner-toast] > div:first-child > div:first-child:empty,
  .toaster [data-sonner-toast] > div:first-child > div:has(svg) {
    display: none !important;
  }
}
</style>
