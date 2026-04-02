import React, { useState } from 'react'
import { Head, Link } from '@inertiajs/react'
import { Check, Zap, Shield, Crown, Ticket, ArrowRight, CreditCard } from 'lucide-react'
import { motion } from 'framer-motion'
import { cn } from '@/lib/utils'
import axios from 'axios'

export default function Paywall({ packages }) {
  const [selectedPlanId, setSelectedPlanId] = useState(packages[0]?.id)
  const [voucher, setVoucher] = useState('')
  const [discountInfo, setDiscountInfo] = useState(null)
  const [isApplying, setIsApplying] = useState(false)

  const selectedPlan = packages.find(p => p.id === selectedPlanId) || packages[0]

  const benefits = [
    'Input via chat tanpa batas',
    'Laporan bulanan Excel & Email',
    'Dashboard analisis keuangan lengkap',
    'Auto-backup data ke Google Drive',
    'Multi-account management'
  ]

  const handleApplyVoucher = async () => {
    if (!voucher) return
    setIsApplying(true)
    try {
        const response = await axios.post(route('vouchers.validate'), { 
            code: voucher, 
            package_id: selectedPlanId 
        })
        setDiscountInfo(response.data)
    } catch (e) {
        alert('Kode voucher tidak valid atau sudah kedaluwarsa.')
        setDiscountInfo(null)
    } finally {
        setIsApplying(false)
    }
}

  const handleCheckout = async () => {
    try {
        const response = await axios.post(route('checkout.process'), { 
            package_id: selectedPlanId,
            voucher_id: discountInfo?.voucher_id
        })
        alert(`Order ${response.data.order.order_number} berhasil dibuat!`)
    } catch (e) {
        alert('Terjadi kesalahan saat memproses pembayaran.')
    }
}

  return (
    <div className="min-h-screen bg-slate-50">
      <Head title="Unlock Premium" />
      
      <div className="max-w-screen-sm mx-auto min-h-screen bg-white shadow-xl shadow-slate-200">
        <div className="p-8 pb-32">
          {/* Header */}
          <div className="text-center mb-10 mt-6">
            <motion.div 
              initial={{ scale: 0.8, opacity: 0 }}
              animate={{ scale: 1, opacity: 1 }}
              className="inline-flex h-20 w-20 items-center justify-center rounded-[32px] bg-indigo-600 shadow-2xl shadow-indigo-200 mb-6"
            >
              <Shield className="w-10 h-10 text-white" />
            </motion.div>
            <h1 className="text-2xl font-black text-slate-900 mb-2">Buka Akses Premium</h1>
            <p className="text-sm text-slate-500 max-w-xs mx-auto">
              Kelola keuanganmu lebih cerdas dengan fitur Power-Ups eksklusif.
            </p>
          </div>

          {/* Benefits */}
          <div className="space-y-3 mb-10">
            {benefits.map((benefit, i) => (
              <motion.div 
                initial={{ x: -20, opacity: 0 }}
                animate={{ x: 0, opacity: 1 }}
                transition={{ delay: i * 0.1 }}
                key={i} 
                className="flex items-center gap-3"
              >
                <div className="h-6 w-6 rounded-lg bg-emerald-50 border border-emerald-100 flex items-center justify-center flex-shrink-0">
                  <Check className="w-3.5 h-3.5 text-emerald-600 stroke-[3]" />
                </div>
                <span className="text-[13px] font-bold text-slate-700 leading-tight">{benefit}</span>
              </motion.div>
            ))}
          </div>

          {/* Plans */}
          <div className="space-y-4 mb-8">
            <div className="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1">Pilih Paket</div>
            {packages.map((plan) => (
              <label 
                key={plan.id}
                className={cn(
                  "relative block p-5 rounded-3xl border-2 transition-all cursor-pointer overflow-hidden",
                  selectedPlanId === plan.id 
                    ? "bg-indigo-50/50 border-indigo-600" 
                    : "bg-white border-slate-100 hover:border-slate-200"
                )}
              >
                <input 
                   type="radio" 
                   name="plan" 
                   value={plan.id} 
                   className="sr-only"
                   onChange={() => setSelectedPlanId(plan.id)}
                />
                
                {plan.duration_days >= 365 && (
                  <div className="absolute top-0 right-0 py-1.5 px-4 bg-indigo-600 text-white italic text-[10px] font-black rounded-bl-2xl">
                    POPULER
                  </div>
                )}

                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-4">
                    <div className={cn(
                        "h-12 w-12 rounded-2xl flex items-center justify-center",
                        plan.duration_days >= 365 ? "bg-indigo-50 text-indigo-600" : "bg-amber-50 text-amber-600"
                    )}>
                      {plan.duration_days >= 365 ? <Crown className="w-6 h-6" /> : <Zap className="w-6 h-6" />}
                    </div>
                    <div>
                      <div className="text-base font-black text-slate-900">{plan.name}</div>
                      <div className="flex items-center gap-2 mt-0.5">
                        <span className="text-xs font-bold text-slate-400 line-through">Rp {(plan.price * 1.5).toLocaleString('id-ID')}</span>
                        <span className="text-[10px] bg-rose-100 text-rose-600 font-bold px-2 py-0.5 rounded-full uppercase">Hemat 33%</span>
                      </div>
                    </div>
                  </div>
                  <div className="text-right">
                    <div className="text-lg font-black text-slate-900">Rp {(plan.price / 1000).toLocaleString('id-ID')}k</div>
                    <div className="text-[10px] font-bold text-slate-400 uppercase tracking-tight">/ {plan.duration_days} Hari</div>
                  </div>
                </div>
              </label>
            ))}
          </div>

          {/* Voucher */}
          <div className="mb-6">
             <div className="text-xs font-bold text-slate-400 uppercase tracking-widest ml-1 mb-2">Kode Voucher</div>
             <div className="relative">
                <Ticket className="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                <input 
                  type="text" 
                  value={voucher}
                  onChange={e => setVoucher(e.target.value)}
                  placeholder="Punya kode diskon?"
                  className="w-full h-14 pl-12 pr-28 rounded-2xl bg-slate-50 border-none focus:ring-2 focus:ring-indigo-600 text-sm font-bold uppercase tracking-widest placeholder:normal-case placeholder:tracking-normal"
                />
                <button 
                  onClick={handleApplyVoucher}
                  disabled={!voucher || isApplying}
                  className="absolute right-2 top-1/2 -translate-y-1/2 h-10 px-4 rounded-xl bg-slate-900 text-white text-[11px] font-black uppercase disabled:opacity-30 disabled:bg-slate-400"
                >
                  {isApplying ? '...' : 'Apply'}
                </button>
             </div>
             {discountInfo && (
                 <div className="mt-2 text-[11px] font-bold text-emerald-600 flex items-center gap-1 ml-1">
                     <Check className="w-3 h-3" /> Voucher berhasil diterapkan!
                 </div>
             )}
          </div>
        </div>

        {/* Footer Sticky Button */}
        <div className="fixed bottom-0 left-0 right-0 max-w-screen-sm mx-auto p-6 bg-white border-t border-slate-100 flex items-center justify-between gap-4">
            <div className="hidden sm:block text-left">
                <div className="text-[10px] font-bold text-slate-400 uppercase">Subtotal</div>
                <div className="text-lg font-black text-slate-900">
                    Rp {(discountInfo?.total || selectedPlan?.price || 0).toLocaleString('id-ID')}
                </div>
            </div>
            <button 
               onClick={handleCheckout}
               className="flex-1 h-14 bg-indigo-600 text-white rounded-2xl flex items-center justify-center gap-3 text-sm font-bold shadow-xl shadow-indigo-100 transition-transform active:scale-95 group"
            >
                <CreditCard className="w-5 h-5" /> Bayar Sekarang <ArrowRight className="w-4 h-4 group-hover:translate-x-1 transition-transform" />
            </button>
        </div>
      </div>
    </div>
  )
}
