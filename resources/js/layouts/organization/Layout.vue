<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import {type NavItem, Organization, type SharedData} from '@/types';
import { Link, usePage } from '@inertiajs/vue3';

interface Props {
    organization: Organization;
}

const props = defineProps<Props>();

const organization = props.organization as Organization;

const sidebarNavItems: NavItem[] = [
    {
        title: 'organization.navigation.sidebar.profile',
        href: route('organization.show', { organization }),
    },
    {
        title: 'organization.navigation.sidebar.members',
        href: route('organization.users.show', { organization }),
    },
    // {
    //     title: 'settings.navigation.sidebar.appearance',
    //     href: route('user.appearance.edit'),
    // },
    // {
    //     title: 'settings.navigation.sidebar.twoFactorAuthentication',
    //     href: route('user.two-factor-authentication.edit'),
    // },
    // {
    //     title: 'settings.navigation.sidebar.browserSessions',
    //     href: route('user.browser-sessions.index'),
    // },
];

const page = usePage<SharedData>();

const currentPath = page.props.ziggy?.location ? new URL(page.props.ziggy.location).pathname : '';
</script>

<template>
    <div class="px-4 py-6">
        <Heading :title="__('organization.profile.settings')" :description="__('organization.profile.settingsDescription')" />

        <div class="flex flex-col space-y-8 md:space-y-0 lg:flex-row lg:space-x-12 lg:space-y-0">
            <aside class="w-full max-w-xl lg:w-60">
                <nav class="flex flex-col space-x-0 space-y-1">
                    <Button
                        v-for="item in sidebarNavItems"
                        :key="item.href"
                        variant="ghost"
                        :class="['w-full justify-start', { 'bg-muted': currentPath === item.href }]"
                        as-child
                    >
                        <Link :href="item.href">
                            {{ __(item.title) }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 md:hidden" />

            <div class="flex-1 md:max-w-2xl">
                <section class="max-w-xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
