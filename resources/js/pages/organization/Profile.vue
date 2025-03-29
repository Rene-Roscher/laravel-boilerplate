<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { getInitials } from '@/composables/useInitials';
import AppLayout from '@/layouts/AppLayout.vue';
import OrganizationLayout from '@/layouts/organization/Layout.vue';
import { type BreadcrumbItem, Organization, type SharedData, type User } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Props {
    organization: Organization;
}

const props = defineProps<Props>();
const organization = props.organization as Organization;

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'organization.navigation.breadcrumb.profile',
        href: route('organization.show', { organization }),
    },
];

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const profilePhotoInput = ref<HTMLInputElement | null>(null);
const photoPreview = ref<string | null>(null);

const form = useForm({
    _method: 'PATCH',
    name: organization.name,
    avatar: null,
});

const submit = () => {
    if (profilePhotoInput.value) {
        form.avatar = profilePhotoInput.value?.files[0];
    }

    form.post(route('organization.update', { organization }), {
        preserveScroll: true,
        preserveState: false,
    });
};

const deleteAvatarForm = useForm({});

const deleteAvatar = () => {
    deleteAvatarForm.delete(route('organization.avatar.delete', { organization }), {
        preserveScroll: true,
        preserveState: false,
    });
};

const selectNewPhoto = () => {
    if (profilePhotoInput.value) {
        profilePhotoInput.value.click();
    }
};

const updatePhotoPreview = () => {
    const photo = profilePhotoInput.value?.files[0];

    if (!photo) return;

    const reader = new FileReader();

    reader.onload = (e) => {
        photoPreview.value = e.target?.result as string;
    };

    reader.readAsDataURL(photo);
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="__('organization.profile.profileSettings')" />

        <OrganizationLayout :organization="organization">
            <div class="flex flex-col space-y-6">
                <HeadingSmall :title="__('organization.profile.profileInformation')" :description="__('organization.profile.updateNameEmail')" />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="flex items-center gap-4">
                        <input id="photo" ref="profilePhotoInput" type="file" class="hidden" @change="updatePhotoPreview"/>

                        <div>
                            <Avatar class="size-24 overflow-hidden rounded-lg">
                                <AvatarImage
                                    v-if="!!organization.avatar_url || !!photoPreview"
                                    :src="photoPreview ? photoPreview : organization.avatar_url"
                                    :alt="organization.name"
                                />
                                <AvatarFallback class="rounded-lg text-black dark:text-white">
                                    {{ getInitials(organization.name) }}
                                </AvatarFallback>
                            </Avatar>
                            <InputError class="mt-2" :message="form.errors.avatar" />
                        </div>
                        <div class="flex items-center gap-x-2">
                            <Button variant="outline" @click="selectNewPhoto" type="button">
                                {{ __('organization.profile.avatarUpload') }}
                            </Button>
                            <Button
                                v-if="organization.avatar"
                                variant="destructive"
                                :disabled="deleteAvatarForm.processing"
                                @click.prevent="deleteAvatar"
                                type="button"
                                >{{ __('organization.profile.deleteAvatar') }}
                            </Button>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="name">{{ __('organization.profile.name') }}</Label>
                        <Input
                            id="name"
                            class="mt-1 block w-full"
                            v-model="form.name"
                            required
                            autocomplete="name"
                            :placeholder="__('organization.profile.fullName')"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">{{ __('organization.profile.save') }}</Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">
                                {{ __('organization.profile.saved') }}
                            </p>
                        </Transition>
                    </div>
                </form>
            </div>
        </OrganizationLayout>
    </AppLayout>
</template>
