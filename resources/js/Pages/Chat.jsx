import React, { useState, useEffect } from 'react'
import { Head, useForm } from '@inertiajs/react'
import AppShell from '@/Layouts/AppShell'
import { cn } from '@/lib/utils'
import { motion, AnimatePresence } from 'framer-motion'
import axios from 'axios'

export default function Chat({ categories = [], accounts = [] }) {
  const [toastMsg, setToastMsg] = useState(null)
  const [isOpen, setIsOpen] = useState(false)
  const [search, setSearch] = useState('')
  
  const form = useForm({
    type: 'expense', // income, expense, savings
    category_id: '',
    amount: '',
    description: '',
    transaction_date: new Date().toISOString().split('T')[0]
  })

  // Filter categories based on transaction type and search query
  const filteredCategories = categories.filter(c => c.type === form.data.type)
  const filteredAndSearchedCategories = filteredCategories.filter(c => 
    c.name.toLowerCase().includes(search.toLowerCase())
  )

  const activeCategory = categories.find(c => c.id == form.data.category_id)

  useEffect(() => {
    // Reset category when type changes
    form.setData('category_id', '')
  }, [form.data.type])

  const showToast = (msg, isError = false) => {
    setToastMsg({ msg, isError })
    setTimeout(() => setToastMsg(null), 3000)
  }

  const formatCurrencyInput = (val) => {
    const num = val.replace(/\D/g, '')
    return num ? parseInt(num).toLocaleString('id-ID') : ''
  }

  const handleSubmit = (e) => {
    e.preventDefault()
    
    // Convert formatted amount back to pure number
    const rawForm = {
      ...form.data,
      amount: form.data.amount.replace(/\D/g, '')
    }

    form.transform(data => ({
      ...data,
      amount: (data.amount || "").toString().replace(/\D/g, '')
    }))

    form.post(route('transactions.store'), {
      onSuccess: () => {
        showToast('Transaksi berhasil disimpan! ✓')
        form.reset('amount', 'description')
      },
      onError: (errs) => {
        showToast('Mohon lengkapi data dengan benar', true)
      }
    })
  }

  return (
    <AppShell title="Catat Transaksi">
      <Head title="Pencatatan Keuangan" />

      {/* Toast Notification */}
      <AnimatePresence>
        {toastMsg && (
          <motion.div
            initial={{ opacity: 0, y: -20, x: '-50%' }} animate={{ opacity: 1, y: 0, x: '-50%' }} exit={{ opacity: 0, y: -20, x: '-50%' }}
            className={cn(
              "fixed top-20 left-1/2 z-[100] px-6 py-3 rounded-2xl text-white text-[13px] font-bold shadow-2xl backdrop-blur-md",
              toastMsg.isError ? "bg-[#ba1a1a]" : "bg-[#005bbf]"
            )}
          >
            {toastMsg.msg}
          </motion.div>
        )}
      </AnimatePresence>

      <div className="max-w-md mx-auto pt-2 pb-10 space-y-4">
        <div className="bg-white rounded-[20px] shadow-[0px_4px_24px_rgba(25,28,29,0.04)] border border-[#c1c6d6]/20 overflow-hidden">
          
          {/* Segmented Tab Toggle */}
          <div className="p-1 px-4 pt-4">
            <div className="relative flex bg-[#f1f3f4] p-1 rounded-[14px]">
              {/* Animated Background Slider */}
              <motion.div
                className="absolute top-1 bottom-1 rounded-[10px] bg-white shadow-sm z-0"
                initial={false}
                animate={{
                  left: form.data.type === 'income' ? '4px' : form.data.type === 'expense' ? '33.33%' : '66.66%',
                  width: 'calc(33.33% - 4px)'
                }}
                transition={{ type: 'spring', stiffness: 300, damping: 30 }}
              />
              
              {['income', 'expense', 'savings'].map((type) => (
                <button
                  key={type}
                  onClick={() => form.setData('type', type)}
                  className={cn(
                    "flex-1 relative z-10 py-2.5 text-[11px] font-black uppercase tracking-wider transition-colors duration-200",
                    form.data.type === type 
                      ? (type === 'income' ? "text-[#005bbf]" : type === 'expense' ? "text-[#ba1a1a]" : "text-[#1a73e8]")
                      : "text-[#727785]"
                  )}
                >
                  {type === 'income' ? 'Pemasukan' : type === 'expense' ? 'Pengeluaran' : 'Tabungan'}
                </button>
              ))}
            </div>
          </div>

          <form onSubmit={handleSubmit} className="p-4 space-y-4">
            {/* Amount Field (Downsized) */}
            <div className="space-y-1">
              <label className="text-[9px] font-black text-[#727785] uppercase tracking-[0.15em] pl-1">Nominal</label>
              <div className="relative">
                <span className="absolute left-4 top-1/2 -translate-y-1/2 text-base font-black text-[#191c1d]">Rp</span>
                <input
                  type="text"
                  inputMode="numeric"
                  value={form.data.amount}
                  onChange={(e) => form.setData('amount', formatCurrencyInput(e.target.value))}
                  placeholder="0"
                  className={cn(
                    "w-full bg-[#f8f9fa] border-2 border-transparent rounded-[14px] py-3 pl-11 pr-4 text-xl font-black text-[#191c1d] outline-none transition-all placeholder:text-[#c1c6d6]",
                    form.data.type === 'income' ? 'focus:border-[#005bbf]/20 focus:ring-4 focus:ring-[#005bbf]/5' :
                    form.data.type === 'expense' ? 'focus:border-[#ba1a1a]/20 focus:ring-4 focus:ring-[#ba1a1a]/5' :
                    'focus:border-[#1a73e8]/20 focus:ring-4 focus:ring-[#1a73e8]/5'
                  )}
                />
              </div>
              {form.errors.amount && <p className="text-[10px] text-[#ba1a1a] pl-2 font-bold">{form.errors.amount}</p>}
            </div>

            <div className="grid grid-cols-2 gap-3">
              {/* Searchable Category Selector (Downsized) */}
              <div className="space-y-1 relative">
                <label className="text-[9px] font-black text-[#727785] uppercase tracking-[0.15em] pl-1">Kategori</label>
                <div 
                  onClick={() => setIsOpen(!isOpen)}
                  className={cn(
                    "w-full bg-[#f8f9fa] border-2 border-transparent rounded-[12px] py-2.5 pl-9 pr-3 text-[12px] font-bold text-[#191c1d] flex items-center justify-between cursor-pointer transition-all hover:bg-[#eef0f2]",
                    isOpen && "border-[#c1c6d6]/30 bg-white shadow-sm"
                  )}
                >
                  <span className="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#727785] text-base">
                    {activeCategory?.icon || 'category'}
                  </span>
                  <span className={cn("truncate", !activeCategory ? "text-[#c1c6d6]" : "")}>
                    {activeCategory?.name || 'Pilih...'}
                  </span>
                  <span className="material-symbols-outlined text-[#727785] text-sm" style={{ transform: isOpen ? 'rotate(180deg)' : 'none' }}>
                    expand_more
                  </span>
                </div>

                <AnimatePresence>
                  {isOpen && (
                    <motion.div 
                      initial={{ opacity: 0, y: 5, scale: 0.98 }}
                      animate={{ opacity: 1, y: 0, scale: 1 }}
                      exit={{ opacity: 0, y: 5, scale: 0.98 }}
                      className="absolute z-[110] top-full left-0 right-0 mt-1 bg-white rounded-[16px] shadow-[0px_8px_32px_rgba(25,28,29,0.12)] border border-[#c1c6d6]/20 overflow-hidden"
                    >
                      <div className="p-1.5 border-b border-[#f3f4f5]">
                        <div className="relative">
                          <span className="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-[#c1c6d6] text-xs">search</span>
                          <input 
                            autoFocus
                            placeholder="Cari..." 
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            className="w-full bg-[#f3f4f5] border-none rounded-lg py-1.5 pl-8 pr-2 text-[11px] font-bold text-[#191c1d] outline-none"
                          />
                        </div>
                      </div>

                      <div className="max-h-[180px] overflow-y-auto py-1 custom-scrollbar">
                        {filteredAndSearchedCategories.length > 0 ? (
                          filteredAndSearchedCategories.map(cat => (
                            <button
                              key={cat.id}
                              type="button"
                              onClick={() => {
                                form.setData('category_id', cat.id)
                                setIsOpen(false)
                                setSearch('')
                              }}
                              className={cn(
                                "w-full flex items-center gap-2.5 px-3 py-2.5 hover:bg-[#f3f4f5] transition-colors text-left",
                                form.data.category_id === cat.id && "bg-[#005bbf]/5"
                              )}
                            >
                              <span className="material-symbols-outlined text-base" style={{ color: cat.color }}>{cat.icon}</span>
                              <span className={cn("text-[12px] font-bold", form.data.category_id === cat.id ? "text-[#005bbf]" : "text-[#191c1d]")}>
                                {cat.name}
                              </span>
                            </button>
                          ))
                        ) : (
                          <div className="py-4 text-center text-[#c1c6d6] text-[10px] font-bold">Kosong</div>
                        )}
                      </div>
                    </motion.div>
                  )}
                </AnimatePresence>
              </div>

              {/* Date selector (Downsized) */}
              <div className="space-y-1">
                <label className="text-[9px] font-black text-[#727785] uppercase tracking-[0.15em] pl-1">Tanggal</label>
                <div className="relative">
                  <span className="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-[#727785] text-base">calendar_today</span>
                  <input
                    type="date"
                    value={form.data.transaction_date}
                    onChange={(e) => form.setData('transaction_date', e.target.value)}
                    className="w-full bg-[#f8f9fa] border-2 border-transparent rounded-[12px] py-2.5 pl-9 pr-2 text-[12px] font-bold text-[#191c1d] outline-none"
                  />
                </div>
              </div>
            </div>

            {/* Description (Downsized) */}
            <div className="space-y-1">
              <label className="text-[9px] font-black text-[#727785] uppercase tracking-[0.15em] pl-1">Keterangan</label>
              <textarea
                value={form.data.description}
                onChange={(e) => form.setData('description', e.target.value)}
                placeholder="Misal: Makan siang..."
                rows="1"
                className="w-full bg-[#f8f9fa] border-2 border-transparent rounded-[12px] p-3 text-[12px] font-semibold text-[#191c1d] outline-none transition-all placeholder:text-[#c1c6d6] resize-none"
              />
            </div>

            {/* Action Button (Stay Sm) */}
            <button
              type="submit"
              disabled={form.processing}
              className={cn(
                "w-full py-3.5 rounded-[16px] text-white font-black text-[13px] shadow-lg active:scale-[0.98] transition-all flex items-center justify-center gap-2",
                form.data.type === 'income' ? 'bg-[#005bbf] shadow-[#005bbf]/20' : 
                form.data.type === 'expense' ? 'bg-[#ba1a1a] shadow-[#ba1a1a]/20' : 
                'bg-[#1a73e8] shadow-[#1a73e8]/20'
              )}
              style={
                form.data.type === 'income' ? { background: 'linear-gradient(135deg, #1a73e8 0%, #005bbf 100%)' } : 
                form.data.type === 'expense' ? { background: 'linear-gradient(135deg, #e53935 0%, #ba1a1a 100%)' } :
                { background: 'linear-gradient(135deg, #42a5f5 0%, #1a73e8 100%)' }
              }
            >
              {form.processing ? (
                <span className="w-5 h-5 border-2 border-white/40 border-t-white rounded-full animate-spin" />
              ) : (
                <>
                  <span className="material-symbols-outlined text-base">check_circle</span>
                  Simpan Transaksi
                </>
              )}
            </button>
          </form>
        </div>

        {/* Info Card (Compact) */}
        <div className="bg-[#005bbf]/5 rounded-[16px] p-3 border border-[#005bbf]/10 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="w-8 h-8 rounded-lg bg-white flex items-center justify-center shadow-sm">
              <span className="material-symbols-outlined text-[#005bbf] text-base">settings</span>
            </div>
            <div>
              <span className="text-[10px] font-black text-[#005bbf] uppercase tracking-wider">Kategori</span>
              <p className="text-[9px] text-[#414754] leading-none">Manage budget tags</p>
            </div>
          </div>
          <span className="material-symbols-outlined text-[#005bbf] text-sm">chevron_right</span>
        </div>
      </div>
    </AppShell>
  )
}
