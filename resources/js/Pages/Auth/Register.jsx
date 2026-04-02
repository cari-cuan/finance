import React, { useState } from 'react'
import { Head, Link, useForm } from '@inertiajs/react'
import GuestLayout from '@/Layouts/GuestLayout'
import { cn } from '@/lib/utils'

const Field = ({ id, label, icon, type = 'text', placeholder, field, autoFocus, form, allErrors, setErrors }) => {
  const hasError = !!allErrors[field]
  return (
    <div className="space-y-1.5">
      <label className="text-[#414754] text-xs font-semibold uppercase tracking-wider pl-1" htmlFor={id}>
        {label}
      </label>
      <div className="relative group">
        <span
          className={cn(
            "material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[20px] leading-none transition-colors",
            hasError ? "text-[#ba1a1a]" : "text-[#727785] group-focus-within:text-[#005bbf]"
          )}
        >
          {icon}
        </span>
        <input
          id={id}
          type={type}
          placeholder={placeholder}
          value={form.data[field]}
          onChange={e => {
            form.setData(field, e.target.value)
            if (allErrors[field]) setErrors(prev => ({ ...prev, [field]: undefined }))
          }}
          autoFocus={autoFocus}
          className={cn(
            "w-full border rounded-lg py-2.5 pl-10 pr-4 text-[13px] text-[#191c1d] outline-none transition-all placeholder:text-[#727785]",
            hasError
              ? "bg-[#ffdad6]/30 border-[#ba1a1a] focus:ring-2 focus:ring-[#ba1a1a]/20"
              : "bg-[#f3f4f5] border-transparent focus:ring-2 focus:ring-[#005bbf]/20 focus:bg-[#e7e8e9]"
          )}
        />
      </div>
      {allErrors[field] && (
        <div className="flex items-center gap-1.5 pl-1 mt-1">
          <span className="material-symbols-outlined text-[#ba1a1a] text-sm leading-none" style={{ fontSize: '14px' }}>error</span>
          <p className="text-[11px] text-[#ba1a1a] font-medium">{allErrors[field]}</p>
        </div>
      )}
    </div>
  )
}

export default function Register() {
  const form = useForm({ name: '', email: '', password: '', password_confirmation: '' })
  const [errors, setErrors] = useState({})

  const validate = () => {
    const e = {}
    if (!form.data.name.trim()) e.name = 'Nama tidak boleh kosong'
    if (!form.data.email.trim()) e.email = 'Email tidak boleh kosong'
    else if (!/\S+@\S+\.\S+/.test(form.data.email)) e.email = 'Format email tidak valid'
    if (!form.data.password) e.password = 'Password tidak boleh kosong'
    else if (form.data.password.length < 8) e.password = 'Password minimal 8 karakter'
    if (!form.data.password_confirmation) e.password_confirmation = 'Konfirmasi password wajib diisi'
    else if (form.data.password !== form.data.password_confirmation) e.password_confirmation = 'Password tidak cocok'
    return e
  }

  const submit = (e) => {
    e.preventDefault()
    const clientErrors = validate()
    if (Object.keys(clientErrors).length > 0) {
      setErrors(clientErrors)
      return
    }
    setErrors({})
    form.post('/register')
  }

  const allErrors = { ...errors, ...form.errors }

  return (
    <GuestLayout title="Create Account" description="Start your journey to smarter finances.">
      <Head title="Register" />

      <form onSubmit={submit} className="space-y-4" noValidate>
        <Field id="name" label="Full Name" icon="person" placeholder="Nama Lengkap" field="name" autoFocus form={form} allErrors={allErrors} setErrors={setErrors} />
        <Field id="email" label="Email Address" icon="mail" type="email" placeholder="nama@email.com" field="email" form={form} allErrors={allErrors} setErrors={setErrors} />
        <Field id="password" label="Password" icon="lock" type="password" placeholder="••••••••" field="password" form={form} allErrors={allErrors} setErrors={setErrors} />
        <Field id="password_confirmation" label="Confirm Password" icon="lock" type="password" placeholder="••••••••" field="password_confirmation" form={form} allErrors={allErrors} setErrors={setErrors} />

        <button
          type="submit"
          disabled={form.processing}
          className="w-full text-white font-bold py-3 px-4 rounded-lg shadow-sm hover:opacity-90 active:scale-[0.98] transition-all text-sm mt-2 flex items-center justify-center gap-2 disabled:opacity-60"
          style={{ background: 'linear-gradient(135deg, #1a73e8 0%, #005bbf 100%)' }}
        >
          Buat Akun
          <span className="material-symbols-outlined text-sm leading-none">arrow_forward</span>
        </button>
      </form>

      <p className="text-center mt-6 text-[13px] text-[#414754]">
        Sudah punya akun?{' '}
        <Link href="/login" className="text-[#005bbf] font-bold hover:underline">Masuk Sekarang</Link>
      </p>
    </GuestLayout>
  )
}
