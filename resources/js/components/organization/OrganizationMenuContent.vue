<script setup lang="ts">
import OrganizationInfo from '@/components/organization/OrganizationInfo.vue';
import { Button } from '@/components/ui/button';
import { DropdownMenuGroup, DropdownMenuItem, DropdownMenuSeparator } from '@/components/ui/dropdown-menu';
import { useOrganization } from '@/stores/organizationStore';
import type { Organization } from '@/types';
import { Link } from '@inertiajs/vue3';

interface Props {
    organization: Organization;
    organizations: Organization[];
}

defineProps<Props>();

const organizationStore = useOrganization();
</script>

<template>
    <DropdownMenuGroup class="max-h-72 w-full overflow-y-auto">
        <template v-for="org in organizations" :key="org.id">
            <DropdownMenuItem :as-child="true">
                <Link
                    class="block w-full"
                    method="put"
                    :href="route('switch-organization', { organization: org })"
                    :preserve-state="false"
                    as="button"
                >
                    <OrganizationInfo :organization="org" :show-role="false" />
                </Link>
            </DropdownMenuItem>
        </template>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true">
        <Button variant="secondary" class="w-full" @click="organizationStore.toggleShowCreateOrganization">Create Organization</Button>
    </DropdownMenuItem>
</template>
