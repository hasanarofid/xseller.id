<script setup>
import { ref } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Mail, Lock, Eye, EyeOff, KeyRound, ArrowRight, ArrowLeft } from '@lucide/vue';

const props = defineProps({
    email: {
        type: String,
        default: '',
    },
    token: {
        type: String,
        required: true,
    },
});

const showPassword = ref(false);
const showPasswordConfirm = ref(false);

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Reset Password | Xseller" />

        <div class="panel-heading">
            <span class="eyebrow">RESET PASSWORD</span>
            <h2>Buat Password Baru</h2>
            <p>Masukkan email dan password baru Anda untuk melanjutkan.</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Email Field -->
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

            <!-- New Password Field -->
            <div class="form-group">
                <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Password Baru</label>
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
                        placeholder="Masukkan password baru"
                        class="w-full"
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="auth-password-toggle flex items-center justify-center"
                        title="Tampilkan password"
                    >
                        <Eye v-if="!showPassword" class="w-4.5 h-4.5" />
                        <EyeOff v-else class="w-4.5 h-4.5" />
                    </button>
                </div>
                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <!-- Confirm Password Field -->
            <div class="form-group">
                <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Konfirmasi Password</label>
                <div class="auth-input-wrap">
                    <span class="auth-input-icon">
                        <KeyRound class="w-4.5 h-4.5" />
                    </span>
                    <input
                        id="password_confirmation"
                        :type="showPasswordConfirm ? 'text' : 'password'"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="Ulangi password baru"
                        class="w-full"
                    />
                    <button
                        type="button"
                        @click="showPasswordConfirm = !showPasswordConfirm"
                        class="auth-password-toggle flex items-center justify-center"
                        title="Tampilkan password"
                    >
                        <Eye v-if="!showPasswordConfirm" class="w-4.5 h-4.5" />
                        <EyeOff v-else class="w-4.5 h-4.5" />
                    </button>
                </div>
                <InputError class="mt-1.5" :message="form.errors.password_confirmation" />
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                :disabled="form.processing"
                class="auth-primary-btn w-full mt-4 flex items-center justify-center gap-2 text-xs font-extrabold uppercase tracking-wider text-white disabled:opacity-50"
            >
                <span>Simpan Password Baru</span>
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
