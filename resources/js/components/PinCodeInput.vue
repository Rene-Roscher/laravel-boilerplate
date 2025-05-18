<script setup lang="ts">
import { PinInput, PinInputGroup, PinInputSeparator, PinInputSlot } from '@/components/ui/pin-input'
import { computed } from 'vue';

const props = defineProps({
    placeholder: {
        type: String,
        default: '○',
    },
    otp: {
        type: Boolean,
        default: true,
    },
    type: {
        type: String,
        default: 'number',
    },
    length: {
        type: Number,
        default: 6,
    },
    modelValue: {
        type: String,
        default: '',
    },
    compact: {
        type: Boolean,
        default: false,
    }
})

const emit = defineEmits(['update:modelValue'])

const modelValue = computed({
    get: () => String(props.modelValue).split(''),
    set: (value) => {
        emit('update:modelValue', value.filter(Boolean).join(''));
    }
});
</script>

<template>
    <PinInput
        v-model="modelValue"
        :placeholder="placeholder"
        :otp="otp"
        :type="type"
    >
        <PinInputGroup>
            <template v-for="(id, index) in length" :key="id">
                <PinInputSlot :class="{'rounded-md': !compact}" :index="index" />
                <template v-if="index !== length-1 && !compact">
                    <PinInputSeparator />
                </template>
            </template>
        </PinInputGroup>
    </PinInput>
</template>
