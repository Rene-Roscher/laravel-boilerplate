<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { reactive, ref } from 'vue';

interface Emits {
    confirmed: void;
}

const emit = defineEmits<Emits>();

interface Props {
    title: string;
    content: string;
    button: string;
    bypass: boolean;
    emitPassword: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Confirm Password',
    content: 'For your security, please confirm your password to continue.',
    button: 'Confirm',
    bypass: false,
    emitPassword: false,
});

const confirmingPassword = ref<boolean>(false);

const form = reactive({
    password: '',
    error: '',
    processing: false,
});

const passwordInput = ref<HTMLInputElement | null>(null);

const startConfirmingPassword = () => {
    if (props.bypass) {
        emit('confirmed');
        return;
    }

    axios.get(route('password.confirmation')).then((response) => {
        if (response.data.confirmed && !props.emitPassword) {
            emit('confirmed');
        } else {
            confirmingPassword.value = true;

            setTimeout(() => passwordInput.value?.focus(), 250);
        }
    });
};

const confirmPassword = () => {
    form.processing = true;

    axios
        .post(route('password.confirm'), {
            password: form.password,
        })
        .then(async () => {
            form.processing = false;

            emit('confirmed', props.emitPassword ? form.password : null);

            closeModal();
        })
        .catch((error) => {
            form.processing = false;
            form.error = error.response.data.errors.password[0];
            passwordInput.value?.focus();
        });
};

const closeModal = () => {
    confirmingPassword.value = false;
    form.password = '';
    form.error = '';
};
</script>

<template>
    <span>
        <span @click="startConfirmingPassword">
            <slot />
        </span>
        <Dialog v-model:open="confirmingPassword">
            <DialogContent>
                <div class="space-y-6">
                    <DialogHeader class="space-y-3">
                        <DialogTitle>{{ __('features.confirms-password.confirmPassword') }}</DialogTitle>
                        <DialogDescription>{{ __('features.confirms-password.confirmPasswordContent') }}</DialogDescription>
                    </DialogHeader>

                    <div class="grid gap-2">
                        <Label for="password" class="sr-only">{{ __('features.confirms-password.password') }}</Label>
                        <Input
                            ref="passwordInput"
                            v-model="form.password"
                            type="password"
                            :placeholder="__('features.confirms-password.password')"
                            autocomplete="current-password"
                            @keyup.enter="confirmPassword"
                        />
                        <InputError :message="form.error" />
                    </div>

                    <DialogFooter class="gap-2">
                        <DialogClose as-child>
                            <Button variant="secondary" @click="closeModal">{{ __('features.confirms-password.cancel') }}</Button>
                        </DialogClose>

                        <Button variant="default" :disabled="form.processing" @click="confirmPassword">
                            {{ __('features.confirms-password.confirm') }}
                        </Button>
                    </DialogFooter>
                </div>
            </DialogContent>
        </Dialog>
    </span>
</template>
