<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import type { Organization } from '@/types';
import { computed } from 'vue';

interface Props {
    organization: Organization;
    showRole?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showRole: false,
    size: 'md',
});

const { getInitials } = useInitials();

// Compute whether we should show the avatar image
const showAvatar = computed(() => props.organization.avatar_url && props.organization.avatar_url !== '');
</script>

<template>
    <Avatar class="size-8  overflow-hidden rounded-lg">
        <AvatarImage v-if="showAvatar" :src="organization.avatar_url" :alt="organization.name" />
        <AvatarFallback class="rounded-lg text-black dark:text-white">
            {{ getInitials(organization.name) }}
        </AvatarFallback>
    </Avatar>

    <div class="grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-medium">{{ organization.name }}</span>
        <span v-if="organization?.role" class="truncate text-xs text-muted-foreground">{{ organization.role.label }}</span>
    </div>
</template>
