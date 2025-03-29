<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useOrganization } from '@/stores/organizationStore';
import {router, useForm} from '@inertiajs/vue3';

const form = useForm({
    name: '',
});

const store = useOrganization();

const submit = () => {
    form.post(route('organization.store'), {
        onSuccess: () => {
            form.reset();
            store.showCreateOrganization = false
            router.reload()
        },
    });
};
</script>

<template>
    <Dialog v-model:open="store.showCreateOrganization">
        <DialogContent>
            <div class="space-y-6">
                <DialogHeader class="space-y-3">
                    <DialogTitle>{{ __('organization.createDialogTitle') }}</DialogTitle>
                    <DialogDescription>{{ __('organization.createDialogDescription') }}</DialogDescription>
                </DialogHeader>

                <div class="grid gap-2">
                    <Label for="password" class="sr-only">{{ __('organization.name') }}</Label>
                    <Input ref="passwordInput" v-model="form.name" :placeholder="__('organization.namePlaceholder')" @keyup.enter="submit" />
                    <InputError :message="form.errors.name" />
                </div>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary">{{ __('close') }}</Button>
                    </DialogClose>

                    <Button variant="default" :disabled="form.processing" @click="submit">{{ __('create') }}</Button>
                </DialogFooter>
            </div>
        </DialogContent>
    </Dialog>
</template>
