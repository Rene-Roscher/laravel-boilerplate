<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import LocaleTabs from "@/components/LocaleTabs.vue";

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'settings.navigation.breadcrumb.appearance',
        href: '/settings/appearance',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="__('settings.appearance.title')" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall :title="__('settings.appearance.title')" :description="__('settings.appearance.description')" />
                <AppearanceTabs />

                <HeadingSmall :title="__('settings.appearance.languageTitle')" :description="__('settings.appearance.languageDescription')" />
                <LocaleTabs/>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
