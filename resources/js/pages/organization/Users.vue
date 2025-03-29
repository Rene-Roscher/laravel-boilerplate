<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { DropdownMenu, DropdownMenuContent, DropdownMenuGroup, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import XDataState from '@/components/XDataState.vue';
import { getInitials } from '@/composables/useInitials';
import AppLayout from '@/layouts/AppLayout.vue';
import OrganizationLayout from '@/layouts/organization/Layout.vue';
import { type BreadcrumbItem, Organization, OrganizationRole, User } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Ellipsis, Trash } from 'lucide-vue-next';
import { ref } from 'vue';

interface Props {
    organization: Organization;
    users: User[];
    roles: OrganizationRole[];
    invitations: {
        id: number;
        email: string;
        role: OrganizationRole;
    }[];
    canAddMember: boolean;
}

const props = defineProps<Props>();
const organization = props.organization as Organization;

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'organization.navigation.breadcrumb.users',
        href: route('organization.users.show', { organization }),
    },
];

const detachUser = (user: User) => {
    const deleteMemberForm = useForm({
        user_id: user.id,
    });

    deleteMemberForm.post(route('organization.user.detach', { organization }), {
        preserveScroll: true,
        preserveState: false,
    });
};

const deleteInvitation = (invitation) => {
    const deleteInvitationForm = useForm({
        invitation_id: invitation.id,
    });

    deleteInvitationForm.post(route('organization.user.invitation.delete', { organization }), {
        preserveScroll: true,
        preserveState: false,
    });
};

const showInviteDialog = ref(false);
const inviteForm = useForm({
    email: '',
    role: 'VIEWER',
});

const submitInvite = () => {
    inviteForm.post(route('organization.user.invite', { organization }), {
        onSuccess: () => {
            showInviteDialog.value = false;
            inviteForm.reset();
        }
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="__('organization.profile.profileSettings')" />

        <OrganizationLayout :organization="organization">
            <div class="flex flex-col space-y-6">
                <x-data-state
                    :show="users.length > 0 || invitations.length > 0"
                    :empty-title="__('No members found')"
                    :empty-description="__('You don\'t have any members in your organization yet. You can invite them to join your organization.')"
                    class="space-y-6"
                >
                    <HeadingSmall
                        v-if="users.length > 0"
                        :title="__('Organization Members')"
                        :description="__('You don\'t have any members in your organization yet. You can invite them to join your organization.')"
                    />
                    <div v-if="users.length > 0" class="space-y-6 rounded-lg bg-accent p-4">
                        <div v-for="(user, i) in users" :key="i" class="flex items-center">
                            <div class="flex items-center">
                                <Avatar class="size-8 overflow-hidden rounded-full">
                                    <AvatarImage v-if="user.avatar_url" :src="user.avatar_url" :alt="user.name" />
                                    <AvatarFallback class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white">
                                        {{ getInitials(user?.name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </div>

                            <div class="ms-3 flex w-full items-center justify-between">
                                <div class="flex flex-col">
                                    <div class="text-sm font-medium">
                                        {{ user.name }} - {{ roles.find((o) => o.id === user.membership.role).label }}
                                    </div>
                                    <div>
                                        <div class="text-xs text-muted-foreground">
                                            {{ user.email }}
                                        </div>
                                    </div>
                                </div>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon" class="h-9 w-9">
                                            <Ellipsis class="h-5 w-5" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="start">
                                        <DropdownMenuGroup>
                                            <DropdownMenuItem :as-child="true">
                                                <button class="block w-full cursor-pointer text-destructive" @click.prevent="() => detachUser(user)">
                                                    <Trash class="mr-2 h-4 w-4" />
                                                    Delete
                                                </button>
                                            </DropdownMenuItem>
                                        </DropdownMenuGroup>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>
                        </div>
                    </div>

                    <template v-if="invitations.length > 0">
                        <HeadingSmall
                            :title="__('Pending Invitations')"
                            :description="
                                __(
                                    'It\'s easier in a team, which is why we make it easier for you to work together by allowing you to invite your members directly to your organization.',
                                )
                            "
                        />
                        <div class="mt-5 space-y-6 rounded-lg bg-accent p-4">
                            <div v-for="(invitation, i) in invitations" :key="i" class="flex items-center">
                                <div class="ms-3 flex w-full items-center justify-between">
                                    <div class="flex flex-col">
                                        <div class="text-sm font-medium">
                                            {{ invitation.email }}
                                        </div>
                                        <div>
                                            <div class="text-xs text-muted-foreground">
                                                {{ roles.find((o) => o.id === invitation.role).label }}
                                            </div>
                                        </div>
                                    </div>
                                    <Button variant="ghost" size="icon" class="h-9 w-9 text-destructive" @click.prevent="() => deleteInvitation(invitation)">
                                        <Trash class="h-5 w-5" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div class="mt-5">
                        <Button variant="default" @click.prevent="showInviteDialog = true">{{ __('Invite Member') }} </Button>
                    </div>

                    <template #emptyActions>
                        <Button variant="default" @click.prevent="showInviteDialog = true">{{ __('Invite Member') }} </Button>
                    </template>
                </x-data-state>
            </div>
        </OrganizationLayout>

        <Dialog v-model:open="showInviteDialog">
            <DialogContent>
                <form @submit.prevent="submitInvite" class="space-y-6">
                    <DialogHeader class="space-y-3">
                        <DialogTitle>{{ __('Invite Member') }}</DialogTitle>
                        <DialogDescription>{{ __('') }}</DialogDescription>
                    </DialogHeader>

                    <div class="grid gap-2">
                        <Label for="password" class="sr-only">{{ __('E-Mail') }}</Label>
                        <Input ref="passwordInput" v-model="inviteForm.email" type="email" placeholder="example-user@example.com" />
                        <InputError :message="inviteForm.errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="password" class="sr-only">{{ __('Role') }}</Label>
                        <Select v-model="inviteForm.role">
                            <SelectTrigger>
                                <SelectValue placeholder="Select a Role" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <template v-for="role in roles" :key="role.id">
                                        <SelectItem :value="role.id">
                                            {{ role.label }}
                                        </SelectItem>
                                    </template>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                        <InputError :message="inviteForm.errors.role" />
                    </div>

                    <DialogFooter class="gap-2">
                        <DialogClose as-child>
                            <Button variant="secondary" @click="showInviteDialog = false">
                                {{ __('features.confirms-password.cancel') }}
                            </Button>
                        </DialogClose>

                        <Button variant="default" :disabled="inviteForm.processing" @click="submitInvite">
                            {{ __('features.confirms-password.confirm') }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>

<style scoped></style>
