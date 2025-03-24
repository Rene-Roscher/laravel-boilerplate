<script setup lang="ts">
import { useLocalization } from '@/composables/useLocalization';

interface Props {
    class?: string;
    withLabel?: boolean;
}

const { class: containerClass = '', withLabel: withLabel = true } = defineProps<Props>();

const { currentLocale, changeLanguage } = useLocalization();

const tabs = [
    { value: 'de', label: 'German' },
    { value: 'en', label: 'English' },
] as const;
</script>

<template>
    <div :class="['inline-flex gap-1 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800', containerClass]">
        <button
            v-for="{ value, label } in tabs"
            :key="value"
            @click="changeLanguage(value)"
            :class="[
                'flex items-center rounded-md px-3.5 py-1.5 transition-colors',
                currentLocale === value
                    ? 'bg-white shadow-sm dark:bg-neutral-700 dark:text-neutral-100'
                    : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60',
            ]"
        >
            <img v-if="value === 'de'" src="~img/flags/germany.png" class="-ml-1 size-6" />
            <img v-if="value === 'en'" src="~img/flags/united-states.png" class="-ml-1 size-6" />
            <span v-if="withLabel" class="ml-1.5 text-sm">{{ __(label) }}</span>
        </button>
    </div>
</template>
