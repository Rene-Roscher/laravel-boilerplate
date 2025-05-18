<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import XConfirmsPassword from '@/components/XConfirmsPassword.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { PinInput, PinInputGroup, PinInputSlot } from '@/components/ui/pin-input';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, ref, watch } from 'vue';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'settings.navigation.breadcrumb.twoFactorAuthentication',
        href: '/settings/two-factor-authentication',
    },
];

interface Props {
    requiresConfirmation: boolean;
    confirmPassword: boolean;
    isSetup: boolean;
}

const props = defineProps<Props>();

const page = usePage<SharedData>();
const enabling = ref(false);
const confirming = ref(false);
const disabling = ref(false);
const qrCode = ref(null);
const setupKey = ref(null);
const recoveryCodes = ref([]);

const confirmationForm = useForm({
    code: [],
});

const twoFactorEnabled = computed(() => !enabling.value && page.props.auth.user?.two_factor_enabled);

watch(twoFactorEnabled, () => {
    if (!twoFactorEnabled.value) {
        confirmationForm.reset();
        confirmationForm.clearErrors();
    }
});

onMounted(async () => {
    await nextTick();

    if (props.isSetup) {
        confirming.value = true;

        await showQrCode();
        await showSetupKey();
        await showRecoveryCodes();
    }
});

const enableTwoFactorAuthentication = () => {
    enabling.value = true;

    router.post(
        route('two-factor.enable'),
        {},
        {
            preserveScroll: true,
            onSuccess: () => Promise.all([showQrCode(), showSetupKey(), showRecoveryCodes()]),
            onFinish: () => {
                enabling.value = false;
                confirming.value = props.requiresConfirmation;
            },
        },
    );
};

const showQrCode = () => {
    return axios.get(route('two-factor.qr-code')).then((response) => {
        qrCode.value = response.data.svg;
    });
};

const showSetupKey = () => {
    return axios.get(route('two-factor.secret-key')).then((response) => {
        setupKey.value = response.data.secretKey;
    });
};

const showRecoveryCodes = () => {
    return axios.get(route('two-factor.recovery-codes')).then((response) => {
        recoveryCodes.value = response.data;
    });
};

const confirmTwoFactorAuthentication = () => {
    confirmationForm
        .transform((data) => ({
            code: data.code.filter(Boolean).join(''),
        }))
        .post(route('two-factor.confirm'), {
            errorBag: 'confirmTwoFactorAuthentication',
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                confirming.value = false;
                qrCode.value = null;
                setupKey.value = null;
                confirmationForm.reset();
            },
        });
};

const regenerateRecoveryCodes = () => {
    axios.post(route('two-factor.recovery-codes')).then(() => showRecoveryCodes());
};

const disableTwoFactorAuthentication = () => {
    disabling.value = true;

    router.delete(route('two-factor.disable'), {
        preserveScroll: true,
        onSuccess: () => {
            disabling.value = false;
            confirming.value = false;
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="__('settings.two-factor.twoFactorAuthentication')" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall
                    :title="__('settings.two-factor.twoFactorAuthentication')"
                    :description="__('settings.two-factor.twoFactorAuthenticationDescription')"
                />

                <HeadingSmall
                    v-if="twoFactorEnabled"
                    :title="__('settings.two-factor.status')"
                    :description="
                        twoFactorEnabled && !confirming ? __('settings.two-factor.enabled') : __('settings.two-factor.finishEnabling')
                    "
                />

                <HeadingSmall v-else :title="__('settings.two-factor.status')" :description="__('settings.two-factor.notEnabled')" />

                <div class="text-sm text-muted-foreground">
                    <p>
                        {{ __('settings.two-factor.whenEnabled') }} {{ __('settings.two-factor.googleAuthenticator') }}
                    </p>
                </div>

                <div v-if="twoFactorEnabled">
                    <div v-if="qrCode">
                        <div class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                            <p v-if="confirming" class="font-medium text-muted-foreground">
                                {{ __('settings.two-factor.scanQrCode') }}
                            </p>

                            <p v-else>
                                {{ __('settings.two-factor.enabledNow') }}
                            </p>
                        </div>

                        <div class="mt-4 inline-block rounded-lg bg-white p-2" v-html="qrCode" />

                        <div v-if="setupKey" class="mt-4 max-w-xl text-sm text-gray-600 dark:text-gray-400">
                            <p class="font-semibold">{{ __('settings.two-factor.setupKey') }} <span v-html="setupKey"></span></p>
                        </div>

                        <div v-if="confirming" class="mt-4">
                            <Label htmlFor="code" :value="__('settings.two-factor.Code')" />

                            <PinInput id="pin-input" v-model="confirmationForm.code" placeholder="○" otp type="number">
                                <PinInputGroup>
                                    <PinInputSlot v-for="(id, index) in 6" :key="id" :index="index" />
                                </PinInputGroup>
                            </PinInput>

                            <InputError :message="confirmationForm.errors.code" class="mt-2" />
                        </div>
                    </div>

                    <div v-if="recoveryCodes.length > 0 && !confirming">
                        <HeadingSmall
                            :title="__('settings.two-factor.recoveryCodes')"
                            :description="__('settings.two-factor.recoveryCodesDescription')"
                        />

                        <div class="mt-4 grid max-w-xl gap-1 rounded-lg bg-accent px-4 py-4 font-mono text-sm dark:text-gray-100">
                            <div v-for="code in recoveryCodes" :key="code">
                                {{ code }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-5">
                    <div v-if="!twoFactorEnabled">
                        <XConfirmsPassword @confirmed="enableTwoFactorAuthentication" :bypass="!confirmPassword">
                            <Button :class="{ 'opacity-25': enabling }" :disabled="enabling">{{ __('settings.two-factor.enable') }}</Button>
                        </XConfirmsPassword>
                    </div>

                    <div v-else>
                        <XConfirmsPassword @confirmed="confirmTwoFactorAuthentication" :bypass="!confirmPassword">
                            <Button
                                variant="default"
                                v-if="confirming"
                                type="button"
                                class="me-3"
                                :class="{ 'opacity-25': enabling }"
                                :disabled="enabling"
                            >
                                {{ __('settings.two-factor.confirm') }}
                            </Button>
                        </XConfirmsPassword>

                        <XConfirmsPassword @confirmed="regenerateRecoveryCodes" :bypass="!confirmPassword">
                            <Button variant="secondary" v-if="recoveryCodes.length > 0 && !confirming" class="me-3">
                                {{ __('settings.two-factor.regenerateRecoveryCodes') }}
                            </Button>
                        </XConfirmsPassword>

                        <XConfirmsPassword @confirmed="showRecoveryCodes" :bypass="!confirmPassword">
                            <Button variant="secondary" v-if="recoveryCodes.length === 0 && !confirming" class="me-3">
                                {{ __('settings.two-factor.showRecoveryCodes') }}
                            </Button>
                        </XConfirmsPassword>

                        <XConfirmsPassword @confirmed="disableTwoFactorAuthentication" :bypass="!confirmPassword">
                            <Button variant="secondary" v-if="confirming" :class="{ 'opacity-25': disabling }" :disabled="disabling">
                                {{ __('settings.two-factor.cancel') }}
                            </Button>
                        </XConfirmsPassword>

                        <XConfirmsPassword @confirmed="disableTwoFactorAuthentication" :bypass="!confirmPassword">
                            <Button variant="destructive" v-if="!confirming" :class="{ 'opacity-25': disabling }" :disabled="disabling">
                                {{ __('settings.two-factor.disable') }}
                            </Button>
                        </XConfirmsPassword>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

