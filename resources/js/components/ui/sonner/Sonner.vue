<script lang="ts" setup>
import type { ToasterProps } from "vue-sonner"
import { Toaster as Sonner } from "vue-sonner"

const props = withDefaults(defineProps<ToasterProps>(), {
    position: 'bottom-right',
})
</script>

<template>
  <Sonner
    class="toaster group"
    :theme="undefined"
    v-bind="props"
    :toast-options="{
      classNames: {
        toast: 'group toast group-[.toaster]:bg-card group-[.toaster]:text-card-foreground group-[.toaster]:border-border group-[.toaster]:shadow-2xs group-[.toaster]:rounded-md group-[.toaster]:gap-2 group-[.toaster]:px-4 group-[.toaster]:py-3',
        description: 'group-[.toast]:text-muted-foreground group-[.toast]:text-sm',
        actionButton: 'group-[.toast]:bg-primary group-[.toast]:text-primary-foreground group-[.toast]:rounded-md group-[.toast]:px-3 group-[.toast]:py-1.5 group-[.toast]:text-sm group-[.toast]:font-medium group-[.toast]:shadow-2xs group-[.toast]:transition-all group-[.toast]:hover:bg-primary/90 group-[.toast]:focus-visible:ring-ring/50 group-[.toast]:focus-visible:ring-[3px]',
        cancelButton: 'group-[.toast]:bg-secondary group-[.toast]:text-secondary-foreground group-[.toast]:rounded-md group-[.toast]:px-3 group-[.toast]:py-1.5 group-[.toast]:text-sm group-[.toast]:font-medium group-[.toast]:shadow-2xs group-[.toast]:transition-all group-[.toast]:hover:bg-secondary/80 group-[.toast]:focus-visible:ring-ring/50 group-[.toast]:focus-visible:ring-[3px]',
        closeButton: 'group-[.toast]:bg-background group-[.toast]:text-foreground group-[.toast]:border group-[.toast]:border-border group-[.toast]:rounded-md group-[.toast]:p-1.5 group-[.toast]:hover:bg-accent group-[.toast]:hover:text-accent-foreground group-[.toast]:transition-colors',
        success: 'group-[.toaster]:bg-card group-[.toaster]:text-card-foreground group-[.toaster]:border-l-4 group-[.toaster]:border-l-emerald-500',
        error: 'group-[.toaster]:bg-card group-[.toaster]:text-card-foreground group-[.toaster]:border-l-4 group-[.toaster]:border-l-destructive',
        warning: 'group-[.toaster]:bg-card group-[.toaster]:text-card-foreground group-[.toaster]:border-l-4 group-[.toaster]:border-l-amber-500',
        info: 'group-[.toaster]:bg-card group-[.toaster]:text-card-foreground group-[.toaster]:border-l-4 group-[.toaster]:border-l-blue-500',
        loading: 'group-[.toaster]:bg-card group-[.toaster]:text-card-foreground',
      },
    }"
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

<style>
/* Additional toast-specific styling to ensure perfect integration */
.toaster {
  font-family: var(--font-sans);
}

.toaster [data-sonner-toast] {
  background: hsl(var(--card)) !important;
  border: 1px solid hsl(var(--border)) !important;
  color: hsl(var(--card-foreground)) !important;
  border-radius: var(--radius) !important;
  font-family: var(--font-sans) !important;
  box-shadow: 
    0 1px 2px 0 rgba(0, 0, 0, 0.05),
    0 1px 3px 0 rgba(0, 0, 0, 0.1) !important;
}

.toaster [data-sonner-toast][data-type="success"] {
  border-left: 4px solid hsl(142, 69%, 58%) !important;
}

.toaster [data-sonner-toast][data-type="error"] {
  border-left: 4px solid hsl(var(--destructive)) !important;
}

.toaster [data-sonner-toast][data-type="warning"] {
  border-left: 4px solid hsl(43, 96%, 56%) !important;
}

.toaster [data-sonner-toast][data-type="info"] {
  border-left: 4px solid hsl(221, 83%, 53%) !important;
}

/* Button styling within toasts */
.toaster [data-sonner-toast] button[data-action="true"] {
  background: hsl(var(--primary)) !important;
  color: hsl(var(--primary-foreground)) !important;
  border-radius: var(--radius-md) !important;
  padding: 0.375rem 0.75rem !important;
  font-size: 0.875rem !important;
  font-weight: 500 !important;
  transition: all 0.15s ease !important;
  box-shadow: 
    0 1px 2px 0 rgba(0, 0, 0, 0.05),
    0 1px 3px 0 rgba(0, 0, 0, 0.1) !important;
}

.toaster [data-sonner-toast] button[data-action="true"]:hover {
  background: hsl(var(--primary) / 0.9) !important;
}

.toaster [data-sonner-toast] button[data-cancel="true"] {
  background: hsl(var(--secondary)) !important;
  color: hsl(var(--secondary-foreground)) !important;
  border-radius: var(--radius-md) !important;
  padding: 0.375rem 0.75rem !important;
  font-size: 0.875rem !important;
  font-weight: 500 !important;
  transition: all 0.15s ease !important;
  box-shadow: 
    0 1px 2px 0 rgba(0, 0, 0, 0.05),
    0 1px 3px 0 rgba(0, 0, 0, 0.1) !important;
}

.toaster [data-sonner-toast] button[data-cancel="true"]:hover {
  background: hsl(var(--secondary) / 0.8) !important;
}

/* Close button */
.toaster [data-sonner-toast] button[data-close-button="true"] {
  background: hsl(var(--background)) !important;
  color: hsl(var(--foreground)) !important;
  border: 1px solid hsl(var(--border)) !important;
  border-radius: var(--radius-md) !important;
  padding: 0.375rem !important;
  transition: all 0.15s ease !important;
}

.toaster [data-sonner-toast] button[data-close-button="true"]:hover {
  background: hsl(var(--accent)) !important;
  color: hsl(var(--accent-foreground)) !important;
}

/* Toast description styling */
.toaster [data-sonner-toast] [data-description] {
  color: hsl(var(--muted-foreground)) !important;
  font-size: 0.875rem !important;
  line-height: 1.25rem !important;
}

/* Toast title styling */
.toaster [data-sonner-toast] [data-title] {
  font-weight: 500 !important;
  font-size: 0.875rem !important;
  line-height: 1.25rem !important;
}

/* Loading spinner color */
.toaster [data-sonner-toast] [data-loading-icon] {
  color: hsl(var(--muted-foreground)) !important;
}
</style>
