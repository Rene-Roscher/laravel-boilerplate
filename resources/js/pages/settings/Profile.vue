<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type SharedData, type User } from '@/types';
import { ref } from 'vue';
import { getInitials } from '@/composables/useInitials';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'settings.navigation.breadcrumb.profile',
        href: '/settings/profile',
    },
];

const page = usePage<SharedData>();
const user = page.props.auth.user as User;

const profilePhotoInput = ref<HTMLInputElement | null>(null);
const photoPreview = ref<string | null>(null);

const form = useForm({
    _method: 'PATCH',
    name: user.name,
    email: user.email,
    avatar: null,
});

const submit = () => {
    if (profilePhotoInput.value) {
        form.avatar = profilePhotoInput.value?.files[0];
    }

    form.post(route('user.profile.update'), {
        preserveScroll: true,
        preserveState: false,
    });
};

const deleteAvatarForm = useForm({});

const deleteAvatar = () => {
    deleteAvatarForm.delete(route('user.profile.avatar.delete'), {
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
        <Head :title="__('settings.profile.profileSettings')" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall :title="__('settings.profile.profileInformation')" :description="__('settings.profile.updateNameEmail')" />

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="flex items-center gap-4">
                        <input id="photo" ref="profilePhotoInput" type="file" class="hidden" @change="updatePhotoPreview" />

                        <div>
                            <Avatar class="size-24 overflow-hidden rounded-lg">
                                <AvatarImage
                                    v-if="!!user.avatar_url || !!photoPreview"
                                    :src="photoPreview ? photoPreview : user.avatar_url"
                                    :alt="user.name"
                                />
                                <AvatarFallback class="rounded-lg text-black dark:text-white">
                                    {{ getInitials(user.name) }}
                                </AvatarFallback>
                            </Avatar>
                            <InputError class="mt-2" :message="form.errors.avatar" />
                        </div>
                        <div class="flex items-center gap-x-2">
                            <Button variant="outline" @click="selectNewPhoto" type="button">
                                {{ __('settings.profile.avatarUpload') }}
                            </Button>
                            <Button v-if="user.avatar" variant="destructive" :disabled="deleteAvatarForm.processing" @click.prevent="deleteAvatar" type="button">{{ __('settings.profile.deleteAvatar') }}</Button>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="name">{{ __('settings.profile.name') }}</Label>
                        <Input
                            id="name"
                            class="mt-1 block w-full"
                            v-model="form.name"
                            required
                            autocomplete="name"
                            :placeholder="__('settings.profile.fullName')"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">{{ __('settings.profile.emailAddress') }}</Label>
                        <Input
                            id="email"
                            type="email"
                            class="mt-1 block w-full"
                            v-model="form.email"
                            required
                            autocomplete="username"
                            :placeholder="__('settings.profile.emailAddress')"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            {{ __('settings.profile.emailUnverified') }}
                            <Link
                                :href="route('verification.send')"
                                method="post"
                                as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:!decoration-current dark:decoration-neutral-500"
                            >
                                {{ __('settings.profile.resendVerificationEmail') }}
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                            {{ __('settings.profile.verificationLinkSent') }}
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="form.processing">{{ __('settings.profile.save') }}</Button>

                        <Transition
                            enter-active-class="transition ease-in-out"
                            enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out"
                            leave-to-class="opacity-0"
                        >
                            <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">
                                {{ __('settings.profile.saved') }}
                            </p>
                        </Transition>
                    </div>
                </form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
