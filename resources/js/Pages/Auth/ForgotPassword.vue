<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Mail, ArrowRight, ArrowLeft } from '@lucide/vue';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Password | Xseller" />

        <div class="panel-heading">
            <span class="eyebrow">RESET PASSWORD</span>
            <h2>Lupa Password?</h2>
            <p>Masukkan email akun Anda. Link reset password akan dikirim melalui email.</p>
        </div>

        <div v-if="status" class="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs font-semibold text-emerald-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div class="form-group">
                <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Email</label>
                <div class="auth-input-wrap">
                    <span class="auth-input-icon">
                        <Mail class="w-4.5 h-4.5" />
                    </span>
                    <input
                        id="email"
                        type="email"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="nama@email.com"
                        class="w-full"
                    />
                </div>
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="auth-primary-btn w-full mt-4 flex items-center justify-center gap-2 text-xs font-extrabold uppercase tracking-wider text-white disabled:opacity-50"
            >
                <span>Kirim Link Reset</span>
                <ArrowRight class="w-4 h-4" />
            </button>
        </form>

        <div class="mt-6 text-center">
            <Link :href="route('login')" class="inline-flex items-center gap-2 text-xs font-bold text-[#009c94] hover:text-[#1653a1] transition-colors">
                <ArrowLeft class="w-4 h-4" />
                <span>Kembali ke halaman login</span>
            </Link>
        </div>
    </GuestLayout>
</template>
