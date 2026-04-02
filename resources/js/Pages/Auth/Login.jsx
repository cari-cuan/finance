import React, { useState } from 'react'
import { Head, Link, useForm } from '@inertiajs/react'
import GuestLayout from '@/Layouts/GuestLayout'
import { cn } from '@/lib/utils'

export default function Login() {
  const [showPass, setShowPass] = useState(false)
  const [errors, setErrors] = useState({})
  const form = useForm({ email: '', password: '', remember: true })

  const validate = () => {
    const e = {}
    if (!form.data.email.trim()) e.email = 'Email tidak boleh kosong'
    else if (!/\S+@\S+\.\S+/.test(form.data.email)) e.email = 'Format email tidak valid'
    if (!form.data.password) e.password = 'Password tidak boleh kosong'
    return e
  }

  const submit = (e) => {
    e.preventDefault()
    const clientErrors = validate()
    if (Object.keys(clientErrors).length > 0) { setErrors(clientErrors); return }
    setErrors({})
    form.post('/login')
  }

  const allErrors = { ...errors, ...form.errors }

  return (
    <GuestLayout title="Welcome Back" description="Enter your credentials to access your account.">
      <Head title="Login" />

      <form onSubmit={submit} className="space-y-4" noValidate>
        {/* Email */}
        <div className="space-y-1.5">
          <label className="text-[#414754] text-xs font-semibold uppercase tracking-wider pl-1" htmlFor="email">Email Address</label>
          <div className="relative group">
            <span className={cn(
              "material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[20px] leading-none transition-colors",
              allErrors.email ? "text-[#ba1a1a]" : "text-[#727785] group-focus-within:text-[#005bbf]"
            )}>mail</span>
            <input
              id="email"
              type="email"
              placeholder="name@company.com"
              value={form.data.email}
              onChange={e => { form.setData('email', e.target.value); setErrors(p => ({ ...p, email: undefined })) }}
              className={cn(
                "w-full border rounded-lg py-2.5 pl-10 pr-4 text-[13px] text-[#191c1d] outline-none transition-all placeholder:text-[#727785]",
                allErrors.email
                  ? "bg-[#ffdad6]/30 border-[#ba1a1a] focus:ring-2 focus:ring-[#ba1a1a]/20"
                  : "bg-[#f3f4f5] border-transparent focus:ring-2 focus:ring-[#005bbf]/20 focus:bg-[#e7e8e9]"
              )}
            />
          </div>
          {allErrors.email && (
            <div className="flex items-center gap-1.5 pl-1">
              <span className="material-symbols-outlined text-[#ba1a1a] leading-none" style={{ fontSize: '14px' }}>error</span>
              <p className="text-[11px] text-[#ba1a1a] font-medium">{allErrors.email}</p>
            </div>
          )}
        </div>

        {/* Password */}
        <div className="space-y-1.5">
          <div className="flex items-center justify-between px-1">
            <label className="text-[#414754] text-xs font-semibold uppercase tracking-wider" htmlFor="password">Password</label>
            <a href="#" className="text-[#005bbf] text-[11px] font-bold hover:underline">Lupa Password?</a>
          </div>
          <div className="relative group">
            <span className={cn(
              "material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[20px] leading-none transition-colors",
              allErrors.password ? "text-[#ba1a1a]" : "text-[#727785] group-focus-within:text-[#005bbf]"
            )}>lock</span>
            <input
              id="password"
              type={showPass ? 'text' : 'password'}
              placeholder="••••••••"
              value={form.data.password}
              onChange={e => { form.setData('password', e.target.value); setErrors(p => ({ ...p, password: undefined })) }}
              className={cn(
                "w-full border rounded-lg py-2.5 pl-10 pr-10 text-[13px] text-[#191c1d] outline-none transition-all placeholder:text-[#727785]",
                allErrors.password
                  ? "bg-[#ffdad6]/30 border-[#ba1a1a] focus:ring-2 focus:ring-[#ba1a1a]/20"
                  : "bg-[#f3f4f5] border-transparent focus:ring-2 focus:ring-[#005bbf]/20 focus:bg-[#e7e8e9]"
              )}
            />
            <button type="button" onClick={() => setShowPass(!showPass)} className="absolute right-3 top-1/2 -translate-y-1/2">
              <span className="material-symbols-outlined text-[#727785] leading-none hover:text-[#191c1d] transition-colors" style={{ fontSize: '20px' }}>
                {showPass ? 'visibility_off' : 'visibility'}
              </span>
            </button>
          </div>
          {allErrors.password && (
            <div className="flex items-center gap-1.5 pl-1">
              <span className="material-symbols-outlined text-[#ba1a1a] leading-none" style={{ fontSize: '14px' }}>error</span>
              <p className="text-[11px] text-[#ba1a1a] font-medium">{allErrors.password}</p>
            </div>
          )}
        </div>

        {/* Remember */}
        <div className="flex items-center space-x-2 pt-1">
          <input
            id="remember"
            type="checkbox"
            checked={form.data.remember}
            onChange={e => form.setData('remember', e.target.checked)}
            className="w-4 h-4 rounded border-[#c1c6d6] text-[#005bbf] focus:ring-[#005bbf]/20 bg-[#f3f4f5]"
          />
          <label htmlFor="remember" className="text-[#414754] text-[12px] font-medium cursor-pointer">Remember this device</label>
        </div>

        {/* Submit */}
        <button
          type="submit"
          disabled={form.processing}
          className="w-full text-white font-bold py-3 px-4 rounded-lg shadow-sm hover:opacity-90 active:scale-[0.98] transition-all text-sm mt-2 flex items-center justify-center gap-2 disabled:opacity-60"
          style={{ background: 'linear-gradient(135deg, #1a73e8 0%, #005bbf 100%)' }}
        >
          Masuk <span className="material-symbols-outlined text-sm leading-none">arrow_forward</span>
        </button>
      </form>

      {/* Divider */}
      <div className="relative my-6 text-center">
        <div className="absolute inset-0 flex items-center">
          <div className="w-full border-t border-[#c1c6d6]/20" />
        </div>
        <span className="relative bg-white px-3 text-[11px] text-[#727785] font-bold uppercase tracking-widest">Atau</span>
      </div>

      <p className="text-center text-[13px] text-[#414754]">
        Belum punya akun?{' '}
        <Link href="/register" className="text-[#005bbf] font-bold hover:underline">Daftar Sekarang</Link>
      </p>
    </GuestLayout>
  )
}
