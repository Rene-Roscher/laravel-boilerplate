import { Organization, SharedData } from '@/types';
import { usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import {defineStore} from "pinia";

export const useOrganization = defineStore('organization-store', () => {
    const page = usePage<SharedData>();
    const currentOrganization = ref<Organization>(page.props.auth.user.current_organization);
    const organizations = ref<Organization[]>(page.props.auth.user.organizations);

    const showCreateOrganization = ref<boolean>(false);

    return {
        currentOrganization,
        organizations,
        showCreateOrganization,
        toggleShowCreateOrganization: () => {
            showCreateOrganization.value = !showCreateOrganization.value
        },
    };
})
