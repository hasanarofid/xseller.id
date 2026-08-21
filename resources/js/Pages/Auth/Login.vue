<script setup>
import { ref } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Mail, Lock, Eye, EyeOff, ArrowRight } from '@lucide/vue';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const showPassword = ref(false);

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Login | Xseller" />

        <div class="panel-heading">
            <span class="eyebrow">MEMBER LOGIN</span>
            <h2>Masuk ke Akun</h2>
            <p>Silakan masukkan email dan password Anda.</p>
        </div>

        <div v-if="status" class="mb-4 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs font-semibold text-emerald-600">
            {{ status }}
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

            <!-- Password Field -->
            <div class="form-group">
                <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5 uppercase tracking-wider">Password</label>
                <div class="auth-input-wrap">
                    <span class="auth-input-icon">
                        <Lock class="w-4.5 h-4.5" />
                    </span>
                    <input
                        id="password"
                        :type="showPassword ? 'text' : 'password'"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        placeholder="Masukkan password"
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

            <!-- Form Options -->
            <div class="flex items-center justify-between pt-1">
                <label class="remember-me flex items-center cursor-pointer text-xs text-slate-600">
                    <input
                        type="checkbox"
                        v-model="form.remember"
                        class="rounded border-slate-300 text-[#04bdb2] focus:ring-[#04bdb2] accent-[#04bdb2]"
                    />
                    <span class="ms-2 font-medium">Ingat saya</span>
                </label>

                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="text-xs font-bold text-[#009c94] hover:text-[#1653a1] transition-colors"
                >
                    Lupa password?
                </Link>
            </div>

            <!-- Submit Button -->
            <button
                type="submit"
                :disabled="form.processing"
                class="auth-primary-btn w-full mt-3 flex items-center justify-center gap-2 text-xs font-extrabold uppercase tracking-wider text-white disabled:opacity-50"
            >
                <span>Masuk Sekarang</span>
                <ArrowRight class="w-4 h-4" />
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-slate-500 font-medium">
            Belum punya akun?
            <Link :href="route('register')" class="ms-1 font-bold text-[#009c94] hover:text-[#1653a1] transition-colors">
                Daftar sekarang
            </Link>
        </div>
    </GuestLayout>
</template>
