<script setup lang="ts">
import { defineComponent, h } from 'vue';

defineProps({
    show: {
        type: Boolean,
        default: true,
    },
    emptyTitle: {
        type: String,
    },
    emptyDescription: {
        type: String,
    },
    emptyIcon: {
        default: defineComponent({
            render() {
                return h(
                    'svg',
                    {
                        width: '20',
                        height: '22',
                        viewBox: '0 0 20 22',
                        fill: 'none',
                        xmlns: 'http://www.w3.org/2000/svg',
                    },
                    [
                        h('path', {
                            d: 'M7.75 19.7501L9.22297 20.5684C9.50658 20.726 9.64838 20.8047 9.79855 20.8356C9.93146 20.863 10.0685 20.863 10.2015 20.8356C10.3516 20.8047 10.4934 20.726 10.777 20.5684L12.25 19.7501M3.25 17.2501L1.82297 16.4573C1.52346 16.2909 1.37368 16.2077 1.26463 16.0893C1.16816 15.9847 1.09515 15.8606 1.05048 15.7254C1 15.5726 1 15.4013 1 15.0586V13.5001M1 8.50009V6.94153C1 6.59889 1 6.42757 1.05048 6.27477C1.09515 6.13959 1.16816 6.01551 1.26463 5.91082C1.37368 5.79248 1.52345 5.70928 1.82297 5.54288L3.25 4.75009M7.75 2.25008L9.22297 1.43177C9.50658 1.27421 9.64838 1.19543 9.79855 1.16454C9.93146 1.13721 10.0685 1.13721 10.2015 1.16454C10.3516 1.19543 10.4934 1.27421 10.777 1.43177L12.25 2.25008M16.75 4.75008L18.177 5.54288C18.4766 5.70928 18.6263 5.79248 18.7354 5.91082C18.8318 6.01551 18.9049 6.13959 18.9495 6.27477C19 6.42757 19 6.59889 19 6.94153V8.50008M19 13.5001V15.0586C19 15.4013 19 15.5726 18.9495 15.7254C18.9049 15.8606 18.8318 15.9847 18.7354 16.0893C18.6263 16.2077 18.4766 16.2909 18.177 16.4573L16.75 17.2501M7.75 9.75008L10 11.0001M10 11.0001L12.25 9.75008M10 11.0001V13.5001M1 6.00008L3.25 7.25008M16.75 7.25008L19 6.00008M10 18.5001V21.0001',
                            stroke: 'currentColor',
                            'stroke-width': '2',
                            'stroke-linecap': 'round',
                            'stroke-linejoin': 'round',
                        }),
                    ],
                );
            },
        }),
    },
});
</script>

<template>
    <div v-if="show">
        <slot />
    </div>
    <div v-else class="flex flex-col w-full rounded-lg border bg-accent/5 border-accent p-12 text-center">
        <component v-if="emptyIcon" :is="emptyIcon" class="mx-auto size-16 text-primary/75" />
        <h3 class="text-base font-medium" :class="{'mt-4': !!emptyIcon}">
            <slot name="emptyTitle">{{ emptyTitle }}</slot>
        </h3>
        <p class="mt-2 text-sm text-muted-foreground">
            <slot name="emptyDescription">{{ emptyDescription }}</slot>
        </p>
        <div v-if="$slots.emptyActions" class="mt-6">
            <slot name="emptyActions" />
        </div>
    </div>
</template>
