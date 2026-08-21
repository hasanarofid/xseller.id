<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard | XSELLER" />

    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-bold leading-tight text-[#1653a1]"
            >
                Dashboard Member
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 space-y-6">
                <div
                    class="overflow-hidden bg-white shadow-sm sm:rounded-3xl border border-slate-100 p-6 md:p-8 space-y-4"
                >
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-[#e6f9f8] text-[#009c94] rounded-2xl">
                            <span class="text-2xl">👋</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-extrabold text-[#1653a1]">Selamat Datang di Xseller Member Area</h3>
                            <p class="text-xs text-slate-500 font-medium">Anda telah berhasil masuk ke sistem.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
