<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Skeleton } from '@/components/ui/skeleton';
import { type Passkey, usePasskeys } from '@/composables/usePasskeys';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { AlertCircle, Key, LoaderCircle, Monitor, Plus, Smartphone, Tablet, Trash2 } from 'lucide-vue-next';
import { getCurrentInstance, ref } from 'vue';
import { toast } from 'vue-sonner';

interface Props {
    passkeys: Passkey[];
}

const props = defineProps<Props>();

// Get translation function from global properties
const instance = getCurrentInstance();
const __ = instance?.appContext.config.globalProperties.__ || ((key: string) => key);

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'settings.navigation.breadcrumb.passkeys',
        href: '/settings/passkeys',
    },
];

const {
    browserSupported,
    isRegistering,
    error,
    registerPasskey,
    deletePasskey,
    formatLastUsed,
} = usePasskeys();

const showCreateDialog = ref(false);
const showDeleteDialog = ref(false);
const passkeyToDelete = ref<Passkey | null>(null);
const passkeyName = ref('');
const isDeleting = ref(false);

// Create new passkey
const handleCreatePasskey = async () => {
    const success = await registerPasskey(passkeyName.value || undefined);
    if (success) {
        toast.success(__('settings.passkeys.createSuccess'));
        showCreateDialog.value = false;
        passkeyName.value = '';
    } else if (error.value) {
        toast.error(error.value);
    }
};

// Confirm delete
const confirmDelete = (passkey: Passkey) => {
    passkeyToDelete.value = passkey;
    showDeleteDialog.value = true;
};

// Delete passkey
const handleDeletePasskey = async () => {
    if (!passkeyToDelete.value) return;

    isDeleting.value = true;
    const success = await deletePasskey(passkeyToDelete.value.id);
    isDeleting.value = false;

    if (success) {
        toast.success(__('settings.passkeys.deleteSuccess'));
        showDeleteDialog.value = false;
        passkeyToDelete.value = null;
    } else if (error.value) {
        toast.error(error.value);
    }
};

// Get device icon component
const getDeviceIcon = (deviceType: string | null) => {
    switch (deviceType?.toLowerCase()) {
        case 'mobile':
            return Smartphone;
        case 'tablet':
            return Tablet;
        default:
            return Monitor;
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="__('settings.passkeys.title')" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    :title="__('settings.passkeys.title')"
                    :description="__('settings.passkeys.description')"
                />

                <!-- Browser support warning -->
                <Card v-if="!browserSupported" class="border-destructive">
                    <CardContent class="flex items-center space-x-3 p-4">
                        <AlertCircle class="h-5 w-5 text-destructive" />
                        <p class="text-sm">
                            {{ __('settings.passkeys.browserNotSupported') }}
                        </p>
                    </CardContent>
                </Card>

                <!-- Create new passkey button -->
                <div class="flex justify-end">
                    <Button @click="showCreateDialog = true" :disabled="!browserSupported || isRegistering">
                        <Plus class="mr-2 h-4 w-4" />
                        {{ __('settings.passkeys.createNew') }}
                    </Button>
                </div>

                <!-- Passkeys list -->
                <div v-if="passkeys.length > 0" class="space-y-4">
                    <Card v-for="passkey in passkeys" :key="passkey.id">
                        <CardContent class="flex items-center justify-between p-6">
                            <div class="flex items-start space-x-4">
                                <div class="rounded-lg bg-accent p-3">
                                    <component
                                        :is="getDeviceIcon(passkey.device_type)"
                                        class="h-5 w-5 text-muted-foreground"
                                    />
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-medium">{{ passkey.name }}</h3>
                                        <Badge v-if="passkey.is_recently_used" variant="secondary">
                                            {{ __('settings.passkeys.recentlyUsed') }}
                                        </Badge>
                                    </div>
                                    <div class="mt-1 text-sm text-muted-foreground">
                                        <div v-if="passkey.browser_name">
                                            {{ __('settings.passkeys.browser') }}: {{ passkey.browser_name }}
                                        </div>
                                        <div v-if="passkey.operating_system">
                                            {{ __('settings.passkeys.os') }}: {{ passkey.operating_system }}
                                        </div>
                                        <div class="mt-1">
                                            {{ __('settings.passkeys.lastUsed') }}:
                                            {{ formatLastUsed(passkey.last_used_at) }}
                                        </div>
                                        <div class="mt-1 text-xs">
                                            {{ __('settings.passkeys.created') }}:
                                            {{ new Date(passkey.created_at).toLocaleDateString() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <Button variant="ghost" size="icon" @click="confirmDelete(passkey)">
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </CardContent>
                    </Card>
                </div>

                <!-- Empty state -->
                <Card v-else>
                    <CardContent class="flex flex-col items-center justify-center py-12">
                        <Key class="mb-4 h-12 w-12 text-muted-foreground/50" />
                        <CardTitle class="mb-2 text-lg">{{ __('settings.passkeys.emptyTitle') }}</CardTitle>
                        <CardDescription class="mb-4 text-center">
                            {{ __('settings.passkeys.emptyDescription') }}
                        </CardDescription>
                        <Button @click="showCreateDialog = true" :disabled="!browserSupported">
                            <Plus class="mr-2 h-4 w-4" />
                            {{ __('settings.passkeys.createFirst') }}
                        </Button>
                    </CardContent>
                </Card>

                <!-- Information card -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-base">{{ __('settings.passkeys.whatArePasskeys') }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-muted-foreground">
                            {{ __('settings.passkeys.passkeyExplanation') }}
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Create passkey dialog -->
            <Dialog v-model:open="showCreateDialog">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{{ __('settings.passkeys.createNewTitle') }}</DialogTitle>
                        <DialogDescription>
                            {{ __('settings.passkeys.createNewDescription') }}
                        </DialogDescription>
                    </DialogHeader>
                    <div class="space-y-4 py-4">
                        <div class="space-y-2">
                            <Label for="passkey-name">{{ __('settings.passkeys.nameLabel') }}</Label>
                            <Input
                                id="passkey-name"
                                v-model="passkeyName"
                                :placeholder="__('settings.passkeys.namePlaceholder')"
                                :disabled="isRegistering"
                            />
                            <p class="text-sm text-muted-foreground">
                                {{ __('settings.passkeys.nameHelp') }}
                            </p>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" @click="showCreateDialog = false" :disabled="isRegistering">
                            Cancel
                        </Button>
                        <Button @click="handleCreatePasskey" :disabled="isRegistering">
                            <LoaderCircle v-if="isRegistering" class="mr-2 h-4 w-4 animate-spin" />
                            {{ __('settings.passkeys.create') }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <!-- Delete confirmation dialog -->
            <Dialog v-model:open="showDeleteDialog">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{{ __('settings.passkeys.deleteTitle') }}</DialogTitle>
                        <DialogDescription>
                            {{ __('settings.passkeys.deleteDescription', { name: passkeyToDelete?.name || 'this passkey' }) }}
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" @click="showDeleteDialog = false" :disabled="isDeleting">
                            Cancel
                        </Button>
                        <Button variant="destructive" @click="handleDeletePasskey" :disabled="isDeleting">
                            <LoaderCircle v-if="isDeleting" class="mr-2 h-4 w-4 animate-spin" />
                            Delete
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </SettingsLayout>
    </AppLayout>
</template>