<script setup>
defineProps({ size: { type: String, default: 'md' } });
</script>

<template>
    <div class="inline-flex items-center gap-2">
        <div class="grid grid-cols-2 grid-rows-2 gap-0.5 w-6 h-6 rounded-md overflow-hidden ring-1 ring-navy-800/10">
            <div class="bg-navy-800"></div>
            <div class="bg-accent"></div>
            <div class="bg-graphite-400"></div>
            <div class="bg-navy-800"></div>
        </div>
        <span class="font-display text-navy-900 font-semibold tracking-tight" :class="size === 'lg' ? 'text-2xl' : 'text-lg'">
            Focus<span class="text-accent">Matrix</span>
        </span>
    </div>
</template>
