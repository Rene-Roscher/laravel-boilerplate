<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import ConfirmationStoreDialog from "@/components/ConfirmationStoreDialog.vue";
import CreateOrganizationDialog from "@/components/organization/CreateOrganizationDialog.vue";

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <slot />
    </AppLayout>
    <ConfirmationStoreDialog/>
    <CreateOrganizationDialog/>
</template>
