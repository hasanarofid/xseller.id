<script setup>
import { ref } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { User, Mail, Lock, Eye, EyeOff, KeyRound, ArrowRight } from '@lucide/vue';

const showPassword = ref(false);

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    referral: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Daftar Akun | Xseller" />

        <div class="panel-heading">
            <span class="eyebrow">CREATE ACCOUNT</span>
            <h2>Daftar Sekarang</h2>
            <p>Buat akun baru untuk mulai menggunakan Xseller.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-3.5">
            <!-- Name Field -->
            <div class="form-group">
                <label for="name" class="block text-xs font-bold text-slate-700 mb-1 uppercase tracking-wider">Nama Lengkap</label>
                <div class="auth-input-wrap">
                    <span class="auth-input-icon">
                        <User class="w-4.5 h-4.5" />
                    </span>
                    <input
                        id="name"
                        type="text"
                        v-model="form.name"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="Nama lengkap Anda"
                        class="w-full"
                    />
                </div>
                <InputError class="mt-1" :message="form.errors.name" />
            </div>

            <!-- Email Field -->
            <div class="form-group">
                <label for="email" class="block text-xs font-bold text-slate-700 mb-1 uppercase tracking-wider">Email</label>
                <div class="auth-input-wrap">
                    <span class="auth-input-icon">
                        <Mail class="w-4.5 h-4.5" />
                    </span>
                    <input
                        id="email"
                        type="email"
                        v-model="form.email"
                        required
                        autocomplete="email"
                        placeholder="nama@email.com"
                        class="w-full"
                    />
                </div>
                <InputError class="mt-1" :message="form.errors.email" />
            </div>

            <!-- Password Field -->
            <div class="form-group">
                <label for="password" class="block text-xs font-bold text-slate-700 mb-1 uppercase tracking-wider">Password</label>
                <div class="auth-input-wrap">
                    <span class="auth-input-icon">
                        <Lock class="w-4.5 h-4.5" />
                    </span>
                    <input
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        v-model="form.password"
                        required
                        autocomplete="new-password"
                        placeholder="Minimal 6 karakter"
                        class="w-full"
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="auth-password-toggle flex items-center justify-center"
                    >
                        <Eye v-if="!showPassword" class="w-4.5 h-4.5" />
                        <EyeOff v-else class="w-4.5 h-4.5" />
                    </button>
                </div>
                <InputError class="mt-1" :message="form.errors.password" />
            </div>

            <!-- Referral Code -->
            <div class="form-group">
                <div class="flex items-center justify-between mb-1">
                    <label for="referral" class="text-xs font-bold text-slate-700 uppercase tracking-wider">Kode Referral</label>
                    <span class="text-[10px] text-slate-400 font-medium">(opsional)</span>
                </div>
                <div class="auth-input-wrap">
                    <span class="auth-input-icon">
                        <KeyRound class="w-4.5 h-4.5" />
                    </span>
                    <input
                        id="referral"
                        type="text"
                        v-model="form.referral"
                        placeholder="Masukkan kode referral"
                        class="w-full"
                    />
                </div>
            </div>

            <!-- Terms -->
            <label class="flex items-start cursor-pointer text-xs text-slate-600 my-2">
                <input
                    type="checkbox"
                    v-model="form.terms"
                    required
                    class="mt-0.5 rounded border-slate-300 text-[#04bdb2] focus:ring-[#04bdb2] accent-[#04bdb2]"
                />
                <span class="ms-2 leading-tight">Saya menyetujui syarat & ketentuan yang berlaku.</span>
            </label>

            <!-- Submit Button -->
            <button
                type="submit"
                :disabled="form.processing"
                class="auth-primary-btn w-full flex items-center justify-center gap-2 text-xs font-extrabold uppercase tracking-wider text-white disabled:opacity-50"
            >
                <span>Buat Akun</span>
                <ArrowRight class="w-4 h-4" />
            </button>
        </form>

        <div class="mt-5 text-center text-xs text-slate-500 font-medium">
            Sudah punya akun?
            <Link :href="route('login')" class="ms-1 font-bold text-[#009c94] hover:text-[#1653a1] transition-colors">
                Masuk
            </Link>
        </div>
    </GuestLayout>
</template>
