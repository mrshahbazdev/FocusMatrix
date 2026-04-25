<script setup>
import ApiTokenManager from '@/Pages/API/Partials/ApiTokenManager.vue';
import FocusLayout from '@/Layouts/FocusLayout.vue';

defineProps({
    tokens: Array,
    availablePermissions: Array,
    defaultPermissions: Array,
});
</script>

<template>
    <FocusLayout>
        <div class="max-w-4xl mx-auto space-y-10">
            <div>
                <h1 class="font-display text-2xl font-bold text-navy-900">API Tokens</h1>
                <p class="mt-1 text-sm text-graphite-500">Manage your API tokens for third-party integrations.</p>
            </div>

            <ApiTokenManager
                :tokens="tokens"
                :available-permissions="availablePermissions"
                :default-permissions="defaultPermissions"
            />
        </div>
    </FocusLayout>
</template>
