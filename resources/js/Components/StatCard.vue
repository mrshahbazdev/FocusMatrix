<script setup>
defineProps({
    label: String,
    value: [String, Number],
    sub: String,
    tone: { type: String, default: 'navy' },
});

const toneClasses = {
    navy: 'from-navy-800 to-navy-600 text-white',
    accent: 'from-accent to-navy-700 text-white',
    neutral: 'from-white to-white text-navy-900 border border-graphite-200',
};
</script>

<template>
    <div :class="['rounded-2xl p-5 bg-gradient-to-br shadow-soft', toneClasses[tone]]">
        <div class="text-xs uppercase tracking-wider opacity-80">{{ label }}</div>
        <div class="mt-1 text-3xl font-display font-semibold">{{ value }}</div>
        <div v-if="sub" class="mt-1 text-xs opacity-75">{{ sub }}</div>
    </div>
</template>
