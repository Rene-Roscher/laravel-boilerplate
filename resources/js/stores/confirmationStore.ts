import { defineStore } from 'pinia'
import {computed, ref} from 'vue'

type ButtonVariant = 'default' | 'destructive'

interface ConfirmationDialogOptions {
    message: string;
    title: string;
    buttonVariant?: ButtonVariant;
    confirmButtonText?: string;
    cancelButtonText?: string;
}

export const useConfirmationStore = defineStore('confirmation', () => {
    const isConfirmationVisible = ref<boolean>(false)
    const confirmationTitle = ref<string>('')
    const confirmationMessage = ref<string>('')
    const confirmationButtonText = ref<string>('Confirm')
    const cancellationButtonText = ref<string>('Cancel')
    const confirmationBackgroundColor = ref<string>('red')
    const confirmationButtonVariant = ref<ButtonVariant>('default')
    let resolveConfirmationPromise: ((value: boolean) => void) | null = null

    const openConfirmationDialog = (
        options: ConfirmationDialogOptions
    ): Promise<boolean> => {
        confirmationMessage.value = options.message;
        confirmationTitle.value = options.title;
        isConfirmationVisible.value = true;

        confirmationButtonText.value = options.confirmButtonText || 'Confirm';
        cancellationButtonText.value = options.cancelButtonText || 'Cancel';
        confirmationButtonVariant.value = options.buttonVariant || 'default';

        return new Promise<boolean>((resolve) => {
            resolveConfirmationPromise = resolve;
        });
    };

    const closeConfirmationDialog = (): void => {
        if (resolveConfirmationPromise)
            resolveConfirmationPromise(false)
        resetConfirmationDialog()
    }

    const confirmAction = (): void => {
        if (resolveConfirmationPromise)
            resolveConfirmationPromise(true)
        resetConfirmationDialog()
    }

    const resetConfirmationDialog = (): void => {
        isConfirmationVisible.value = false
        confirmationTitle.value = ''
        confirmationMessage.value = ''
        resolveConfirmationPromise = null
        confirmationButtonText.value = 'Confirm'
        cancellationButtonText.value = 'Cancel'
        confirmationBackgroundColor.value = 'red'
        confirmationButtonVariant.value = 'default'
    }

    return {
        isConfirmationVisible: computed({
            get: () => isConfirmationVisible.value,
            set: (value: boolean) => {
                if (!value) closeConfirmationDialog()
            }
        }),
        confirmationTitle,
        confirmationMessage,
        confirmationButtonText,
        cancellationButtonText,
        confirmationBackgroundColor,
        confirmationButtonVariant,
        openConfirmationDialog,
        closeConfirmationDialog,
        confirmAction,
    }
})
