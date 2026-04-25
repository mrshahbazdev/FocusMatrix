<template>
    <div class="min-h-screen flex">
        <!-- Left panel: branding (hidden on mobile) -->
        <div class="hidden lg:flex lg:w-1/2 bg-navy-900 text-white flex-col justify-between p-12">
            <div>
                <slot name="logo" />
            </div>
            <div>
                <h1 class="font-display text-4xl font-bold leading-tight">
                    Focus on what<br />
                    <span class="text-accent">only you</span> can do.
                </h1>
                <p class="mt-4 text-navy-200 text-lg max-w-md">
                    Triage every task, delegate with clarity, drop the rest. FocusMatrix gives leaders their time back.
                </p>
            </div>
            <div class="flex items-center gap-2 text-navy-300 text-sm">
                <span class="grid grid-cols-2 grid-rows-2 gap-0.5 w-5 h-5">
                    <span class="bg-white/80"></span>
                    <span class="bg-accent"></span>
                    <span class="bg-navy-400"></span>
                    <span class="bg-white/80"></span>
                </span>
                <span>&copy; {{ new Date().getFullYear() }} FocusMatrix</span>
            </div>
        </div>

        <!-- Right panel: form -->
        <div class="flex-1 flex flex-col items-center justify-center px-6 py-12 bg-graphite-50">
            <div class="lg:hidden mb-8">
                <slot name="logo" />
            </div>
            <div class="w-full max-w-md">
                <div class="bg-white rounded-2xl shadow-sm border border-graphite-200 p-8">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
