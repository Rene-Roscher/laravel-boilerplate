<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { useConfirmationStore } from '@/stores/confirmationStore';

const store = useConfirmationStore();
</script>

<template>
    <Dialog v-model:open="store.isConfirmationVisible" @close="store.closeConfirmationDialog">
        <DialogContent>
            <div class="space-y-6">
                <DialogHeader class="space-y-3">
                    <DialogTitle>{{ store.confirmationTitle }}</DialogTitle>
                    <DialogDescription>{{ store.confirmationMessage }}</DialogDescription>
                </DialogHeader>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button variant="secondary" @click="store.closeConfirmationDialog">
                            {{ store.cancellationButtonText }}
                        </Button>
                    </DialogClose>

                    <Button :variant="store.confirmationButtonVariant" @click="store.confirmAction">
                        {{ store.confirmationButtonText }}
                    </Button>
                </DialogFooter>
            </div>
        </DialogContent>
    </Dialog>
</template>
