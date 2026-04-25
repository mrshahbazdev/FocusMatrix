<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    status: String,
});

const form = useForm({});

const submit = () => {
    form.post(route('verification.send'));
};

const verificationLinkSent = computed(() => props.status === 'verification-link-sent');
</script>

<template>
    <Head title="Email Verification" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <h2 class="font-display text-2xl font-bold text-navy-900 mb-1">Verify your email</h2>
        <p class="text-sm text-graphite-500 mb-6">
            We sent a verification link to your email. Please click on it to continue.
        </p>

        <div v-if="verificationLinkSent" class="mb-4 px-4 py-2 rounded-lg bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">
            A new verification link has been sent to your email.
        </div>

        <form @submit.prevent="submit">
            <PrimaryButton class="w-full" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Resend verification email
            </PrimaryButton>

            <div class="mt-4 flex items-center justify-between text-sm">
                <Link
                    :href="route('profile.show')"
                    class="text-accent hover:text-accent/80"
                >
                    Edit profile
                </Link>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="text-graphite-500 hover:text-navy-800"
                >
                    Log out
                </Link>
            </div>
        </form>
    </AuthenticationCard>
</template>
