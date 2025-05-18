<script setup lang="ts">
import ActionText from '@/components/ActionText.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { PinInput, PinInputGroup, PinInputSlot, PinInputSeparator } from '@/components/ui/pin-input';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
import { nextTick, ref } from 'vue';
import PinCodeInput from '@/components/PinCodeInput.vue';

const recovery = ref<boolean>(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

const recoveryCodeInput = ref<HTMLInputElement | null>(null);
const codeInput = ref<HTMLInputElement | null>(null);
const pinInput = ref(null);

const toggleRecovery = async () => {
    recovery.value = !recovery.value;

    await nextTick();

    if (recovery.value) {
        recoveryCodeInput.value?.focus();
        form.code = '';
    } else {
        codeInput.value?.focus();
        form.recovery_code = '';
    }
};

const submit = () => {
    form.post(route('two-factor.login'), {
        onFinish: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <AuthLayout
        title="Two-factor Confirmation"
        :description="
            !recovery
                ? 'Please confirm access to your account by entering the authentication code provided by your authenticator application.'
                : 'Please confirm access to your account by entering one of your emergency recovery codes.'
        "
    >
        <Head title="Two-factor Confirmation" />

        <form @submit.prevent="submit">
            <div class="space-y-6">
                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label htmlFor="code">Code</Label>
                        <ActionText class="text-sm" @click.prevent="toggleRecovery">
                            <template v-if="!recovery">Use a recovery code</template>
                            <template v-else>Use an authentication code</template>
                        </ActionText>
                    </div>
                    <template v-if="!recovery">
                        <PinCodeInput
                            class="justify-center"
                            ref="pinInput"
                            v-model="form.code"
                        />
                        <InputError :message="form.errors.code" />
                    </template>
                    <template v-else>
                        <Input id="code" ref="codeInput" type="text" class="mt-1 block w-full" v-model="form.code" required autofocus />
                        <InputError :message="form.errors.recovery_code" />
                    </template>
                </div>

                <div class="flex items-center">
                    <Button class="w-full" :disabled="form.processing">
                        <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin" />
                        Log in
                    </Button>
                </div>
            </div>
        </form>
    </AuthLayout>
</template>
