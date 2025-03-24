<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import XConfirmsPassword from '@/components/XConfirmsPassword.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'settings.navigation.breadcrumb.browserSessions',
        href: '/settings/browser-sessions',
    },
];

interface Props {
    sessions: {
        id: number;
        agent: {
            platform: string;
            browser: string;
            device: string;
            is_desktop: boolean;
            is_mobile: boolean;
        };
        ip_address: string;
        is_current_device: boolean;
        last_active: string;
    }[];
}

defineProps<Props>();

const logoutOtherBrowserSessions = (password: string) => {
    console.log(password);
    router.delete(route('user.other-browser-sessions.destroy'), {
        preserveScroll: true,
        data: {
            password,
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="__('settings.browser-sessions.browserSessions')" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall :title="__('settings.browser-sessions.browserSessions')" :description="__('settings.browser-sessions.manageSessions')" />

                <!-- Other Browser Sessions -->
                <div v-if="sessions.length > 0" class="mt-5 space-y-6 rounded-lg bg-accent p-4">
                    <div v-for="(session, i) in sessions" :key="i" class="flex items-center">
                        <div>
                            <svg
                                v-if="session.agent.is_desktop"
                                class="size-8 text-accent-foreground/75"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25"
                                />
                            </svg>

                            <svg
                                v-else
                                class="size-8 text-accent-foreground/75"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="1.5"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3"
                                />
                            </svg>
                        </div>

                        <div class="ms-3">
                            <div class="text-sm text-accent-foreground">
                                {{ session.agent.platform ? session.agent.platform : __('settings.browser-sessions.platformUnknown') }} -
                                {{ session.agent.browser ? session.agent.browser : __('settings.browser-sessions.platformUnknown') }}
                            </div>

                            <div>
                                <div class="text-xs text-muted-foreground">
                                    {{ session.ip_address }}
                                    <badge v-if="session.is_current_device" variant="green" size="sm" class="ml-0.5">{{ __('settings.browser-sessions.thisDevice') }}</badge>
                                    <span v-else>, {{ __('settings.browser-sessions.lastActive') }} {{ session.last_active }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <HeadingSmall
                    :title="__('settings.browser-sessions.logOutOtherSessions')"
                    :description="__('settings.browser-sessions.logOutOtherSessionsDescription')"
                />

                <div class="mt-5">
                    <XConfirmsPassword @confirmed="logoutOtherBrowserSessions" emit-password>
                        <Button variant="destructive">{{ __('settings.browser-sessions.logOutOtherSessions') }}</Button>
                    </XConfirmsPassword>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

