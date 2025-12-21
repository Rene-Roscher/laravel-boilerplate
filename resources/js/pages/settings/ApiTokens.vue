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
import { Checkbox } from '@/components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    Check,
    ChevronDown,
    Clipboard,
    Info,
    Key,
    LoaderCircle,
    Plus,
    Trash2,
} from 'lucide-vue-next';
import { computed, getCurrentInstance, onMounted, ref } from 'vue';
import { toast } from 'vue-sonner';
import axios from 'axios';

interface ApiToken {
    id: string;
    name: string;
    abilities: string[];
    last_used_at: string | null;
    created_at: string;
    expires_at: string | null;
}

interface PageProps {
    tokens: ApiToken[];
    availableAbilities: Record<string, string>;
}

const page = usePage<PageProps>();
const instance = getCurrentInstance();
const __ = instance?.appContext.config.globalProperties.__ || ((key: string) => key);

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'settings.navigation.breadcrumb.apiTokens',
        href: '/settings/api-tokens',
    },
];

const tokens = ref<ApiToken[]>(page.props.tokens || []);
const availableAbilities = ref<Record<string, string>>(page.props.availableAbilities || {});

const newToken = ref({
    name: '',
    abilities: [] as string[],
    expirationOption: 'never',
    expires_at: null as string | null,
});

const isCreating = ref(false);
const showCreateDialog = ref(false);
const showTokenModal = ref(false);
const createdTokenValue = ref('');
const tokenCopied = ref(false);

const showDeleteDialog = ref(false);
const tokenToDelete = ref<ApiToken | null>(null);
const isDeleting = ref(false);

const canCreateToken = computed(() => {
    return newToken.value.name.trim() !== '' && newToken.value.abilities.length > 0;
});

const isAbilityChecked = (ability: string) => {
    return newToken.value.abilities.includes(ability);
};

const handleAbilityChange = (ability: string) => {
    const index = newToken.value.abilities.indexOf(ability);
    if (index > -1) {
        // Remove ability
        newToken.value.abilities.splice(index, 1);
    } else {
        // Add ability
        newToken.value.abilities.push(ability);
    }
    // Force reactivity update
    newToken.value.abilities = [...newToken.value.abilities];
};

const updateExpirationDate = (value: string) => {
    newToken.value.expirationOption = value;
    const now = new Date();
    switch (value) {
        case '7days':
            now.setDate(now.getDate() + 7);
            break;
        case '30days':
            now.setDate(now.getDate() + 30);
            break;
        case '90days':
            now.setDate(now.getDate() + 90);
            break;
        case '1year':
            now.setFullYear(now.getFullYear() + 1);
            break;
        default:
            newToken.value.expires_at = null;
            return;
    }
    newToken.value.expires_at = now.toISOString();
};

const createToken = async () => {
    if (!canCreateToken.value || isCreating.value) return;

    isCreating.value = true;
    try {
        const response = await axios.post(route('user.api-tokens.store'), {
            name: newToken.value.name,
            abilities: newToken.value.abilities,
            expires_at: newToken.value.expires_at,
        });

        if (response.data.success) {
            tokens.value.unshift(response.data.accessToken);
            createdTokenValue.value = response.data.token;
            showCreateDialog.value = false;
            showTokenModal.value = true;

            // Reset form
            newToken.value = {
                name: '',
                abilities: [],
                expirationOption: 'never',
                expires_at: null,
            };

            toast.success(__('settings.api-tokens.tokenCreatedSuccess'));
        }
    } catch (error) {
        console.error('Failed to create token:', error);
        toast.error(__('settings.api-tokens.tokenCreationFailed'));
    } finally {
        isCreating.value = false;
    }
};

const copyToken = async () => {
    try {
        await navigator.clipboard.writeText(createdTokenValue.value);
        tokenCopied.value = true;
        toast.success(__('settings.api-tokens.copiedToClipboard'));
        setTimeout(() => {
            tokenCopied.value = false;
        }, 2000);
    } catch (error) {
        console.error('Failed to copy token:', error);
        toast.error(__('settings.api-tokens.copyFailed'));
    }
};

const closeTokenModal = () => {
    showTokenModal.value = false;
    createdTokenValue.value = '';
    tokenCopied.value = false;
};

const confirmDelete = (token: ApiToken) => {
    tokenToDelete.value = token;
    showDeleteDialog.value = true;
};

const handleDeleteToken = async () => {
    if (!tokenToDelete.value) return;

    isDeleting.value = true;
    try {
        const response = await axios.delete(route('user.api-tokens.destroy', tokenToDelete.value.id));
        if (response.data.success) {
            tokens.value = tokens.value.filter((t) => t.id !== tokenToDelete.value?.id);
            toast.success(__('settings.api-tokens.tokenRevoked'));
            showDeleteDialog.value = false;
            tokenToDelete.value = null;
        }
    } catch (error) {
        console.error('Failed to revoke token:', error);
        toast.error(__('settings.api-tokens.revokeFailed'));
    } finally {
        isDeleting.value = false;
    }
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatLastUsed = (date: string | null) => {
    if (!date) return __('settings.api-tokens.neverUsed');

    const diff = Date.now() - new Date(date).getTime();
    const seconds = Math.floor(diff / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);

    if (days > 0) return `${days} ${days === 1 ? 'day' : 'days'} ago`;
    if (hours > 0) return `${hours} ${hours === 1 ? 'hour' : 'hours'} ago`;
    if (minutes > 0) return `${minutes} ${minutes === 1 ? 'minute' : 'minutes'} ago`;
    return 'Just now';
};

const isExpired = (date: string | null) => {
    if (!date) return false;
    return new Date(date) < new Date();
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="__('settings.api-tokens.title')" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    :title="__('settings.api-tokens.title')"
                    :description="__('settings.api-tokens.description')"
                />

                <!-- Create new token button -->
                <div class="flex justify-end">
                    <Button @click="showCreateDialog = true">
                        <Plus class="mr-2 h-4 w-4" />
                        {{ __('settings.api-tokens.createNew') }}
                    </Button>
                </div>

                <!-- Existing tokens -->
                <div v-if="tokens.length > 0" class="space-y-4">
                    <Card v-for="token in tokens" :key="token.id">
                        <CardContent class="flex items-center justify-between p-6">
                            <div class="flex items-start space-x-4">
                                <div class="rounded-lg bg-accent p-3">
                                    <Key class="h-5 w-5 text-muted-foreground" />
                                </div>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-medium">{{ token.name }}</h3>
                                        <Badge v-if="isExpired(token.expires_at)" variant="destructive">
                                            {{ __('settings.api-tokens.expired') }}
                                        </Badge>
                                    </div>
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        <Badge
                                            v-for="ability in token.abilities"
                                            :key="ability"
                                            variant="secondary"
                                        >
                                            {{ ability }}
                                        </Badge>
                                    </div>
                                    <p class="mt-2 text-sm text-muted-foreground">
                                        {{ __('settings.api-tokens.lastUsed') }}: {{ formatLastUsed(token.last_used_at) }}
                                        <span class="mx-2">•</span>
                                        {{ __('settings.api-tokens.created') }}: {{ formatDate(token.created_at) }}
                                        <span v-if="token.expires_at" class="mx-2">•</span>
                                        <span
                                            v-if="token.expires_at"
                                            :class="isExpired(token.expires_at) ? 'text-destructive' : ''"
                                        >
                                            {{
                                                isExpired(token.expires_at)
                                                    ? __('settings.api-tokens.expired')
                                                    : __('settings.api-tokens.expires')
                                            }}: {{ formatDate(token.expires_at) }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <Button variant="ghost" size="icon" @click="confirmDelete(token)">
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </CardContent>
                    </Card>
                </div>

                <!-- Empty state -->
                <Card v-else>
                    <CardContent class="flex flex-col items-center justify-center py-12">
                        <Key class="mb-4 h-12 w-12 text-muted-foreground/50" />
                        <CardTitle class="mb-2 text-lg">{{ __('settings.api-tokens.noTokens') }}</CardTitle>
                        <CardDescription class="mb-4 text-center">
                            {{ __('settings.api-tokens.noTokensDescription') }}
                        </CardDescription>
                        <Button @click="showCreateDialog = true">
                            <Plus class="mr-2 h-4 w-4" />
                            {{ __('settings.api-tokens.createToken') }}
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <!-- Create token dialog -->
            <Dialog v-model:open="showCreateDialog">
                <DialogContent class="max-w-2xl">
                    <DialogHeader>
                        <DialogTitle>{{ __('settings.api-tokens.createNew') }}</DialogTitle>
                        <DialogDescription>
                            {{ __('settings.api-tokens.tokenNameHelp') }}
                        </DialogDescription>
                    </DialogHeader>
                    <div class="space-y-4 py-4">
                        <!-- Token Name -->
                        <div class="space-y-2">
                            <Label for="token-name">{{ __('settings.api-tokens.tokenName') }}</Label>
                            <Input
                                id="token-name"
                                v-model="newToken.name"
                                :placeholder="__('settings.api-tokens.tokenNamePlaceholder')"
                                :disabled="isCreating"
                            />
                        </div>

                        <!-- Permissions -->
                        <div class="space-y-2">
                            <Label>{{ __('settings.api-tokens.permissions') }}</Label>
                            <div class="space-y-3 rounded-lg border p-4">
                                <div
                                    v-for="(description, ability) in availableAbilities"
                                    :key="ability"
                                    class="flex items-start space-x-3"
                                >
                                    <Checkbox
                                        :id="'ability-' + ability"
                                        :checked="isAbilityChecked(ability)"
                                        @click.stop="handleAbilityChange(ability)"
                                        :disabled="isCreating"
                                    />
                                    <div class="grid gap-1.5 leading-none">
                                        <label
                                            :for="'ability-' + ability"
                                            class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                        >
                                            {{ ability }}
                                        </label>
                                        <p class="text-sm text-muted-foreground">{{ description }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Expiration -->
                        <div class="space-y-2">
                            <Label for="token-expiry">{{ __('settings.api-tokens.expiration') }}</Label>
                            <Select
                                :value="newToken.expirationOption"
                                @update:value="updateExpirationDate"
                                :disabled="isCreating"
                            >
                                <SelectTrigger>
                                    <SelectValue :placeholder="__('settings.api-tokens.neverExpires')" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="never">{{ __('settings.api-tokens.neverExpires') }}</SelectItem>
                                    <SelectItem value="7days">{{ __('settings.api-tokens.expires7Days') }}</SelectItem>
                                    <SelectItem value="30days">{{ __('settings.api-tokens.expires30Days') }}</SelectItem>
                                    <SelectItem value="90days">{{ __('settings.api-tokens.expires90Days') }}</SelectItem>
                                    <SelectItem value="1year">{{ __('settings.api-tokens.expires1Year') }}</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" @click="showCreateDialog = false" :disabled="isCreating">
                            {{ __('settings.api-tokens.cancel') }}
                        </Button>
                        <Button @click="createToken" :disabled="!canCreateToken || isCreating">
                            <LoaderCircle v-if="isCreating" class="mr-2 h-4 w-4 animate-spin" />
                            {{ __('settings.api-tokens.createToken') }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <!-- Token created modal -->
            <Dialog v-model:open="showTokenModal">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle class="flex items-center gap-2 text-green-600 dark:text-green-400">
                            <Check class="h-5 w-5" />
                            {{ __('settings.api-tokens.tokenCreated') }}
                        </DialogTitle>
                        <DialogDescription>
                            {{ __('settings.api-tokens.tokenCreatedDescription') }}
                        </DialogDescription>
                    </DialogHeader>
                    <div class="py-4">
                        <div class="rounded-lg bg-muted p-3">
                            <code class="text-xs break-all select-all">{{ createdTokenValue }}</code>
                        </div>
                    </div>
                    <DialogFooter>
                        <Button variant="outline" @click="closeTokenModal">
                            {{ __('settings.api-tokens.close') }}
                        </Button>
                        <Button @click="copyToken">
                            <Clipboard v-if="!tokenCopied" class="mr-2 h-4 w-4" />
                            <Check v-else class="mr-2 h-4 w-4" />
                            {{ tokenCopied ? __('settings.api-tokens.copied') : __('settings.api-tokens.copy') }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <!-- Delete confirmation dialog -->
            <Dialog v-model:open="showDeleteDialog">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{{ __('settings.api-tokens.revokeToken') }}</DialogTitle>
                        <DialogDescription>
                            {{
                                tokenToDelete
                                    ? __('settings.api-tokens.revokeTokenConfirm').replace(
                                          '{name}',
                                          tokenToDelete.name,
                                      )
                                    : ''
                            }}
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" @click="showDeleteDialog = false" :disabled="isDeleting">
                            {{ __('settings.api-tokens.cancel') }}
                        </Button>
                        <Button variant="destructive" @click="handleDeleteToken" :disabled="isDeleting">
                            <LoaderCircle v-if="isDeleting" class="mr-2 h-4 w-4 animate-spin" />
                            {{ __('settings.api-tokens.revoke') }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </SettingsLayout>
    </AppLayout>
</template>