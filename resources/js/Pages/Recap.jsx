import React, { useState } from 'react'
import { Head, Link } from '@inertiajs/react'
import AppShell from '@/Layouts/AppShell'
import { cn } from '@/lib/utils'
import { motion, AnimatePresence } from 'framer-motion'
import axios from 'axios'

const MONTHS_ID = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember']

export default function Recap({ reports = [] }) {
  const [selectedMonth, setSelectedMonth] = useState(null)
  const [monthDetails, setMonthDetails] = useState([])
  const [isLoading, setIsLoading] = useState(false)

  const num = (v) => parseFloat(v) || 0
  const formatCurrency = (amount) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0)

  const fetchMonthDetails = async (report) => {
    setSelectedMonth(report)
    setIsLoading(true)
    try {
      const { data } = await axios.get(route('rekap'), { params: { month_key: report.month_key } })
      setMonthDetails(data.transactions || [])
    } catch {
      setMonthDetails([])
    } finally {
      setIsLoading(false)
    }
  }



  return (
    <AppShell title="Precision Finance">
      <Head title="Rekap Bulanan" />

      <div className="pt-4 pb-6 space-y-6">
        {/* Annual Savings Hero — Stitch style */}
        <section className="bg-[#f3f4f5] rounded-xl p-5 relative overflow-hidden">
          <div className="absolute -right-10 -top-10 w-40 h-40 bg-[#1a73e8]/5 rounded-full blur-3xl" />
          <div className="relative z-10">
            <p className="text-[#414754] text-[11px] font-semibold tracking-wider uppercase mb-1">Total Pemasukan Tahun Ini</p>
            <h2 className="font-extrabold text-3xl text-[#191c1d] tracking-tight mb-4">
              {formatCurrency(reports.reduce((s, r) => s + num(r.income), 0))}
            </h2>
            <div className="flex gap-4 items-end">
              <div className="flex-1">
                <div className="h-1 bg-[#e1e3e4] rounded-full overflow-hidden">
                  <div className="h-full bg-[#1a73e8] w-[65%]" />
                </div>
                <p className="mt-2 text-[#414754] text-[11px]">Rekap {reports.length} bulan terakhir</p>
              </div>
              <div className="bg-[#1a73e8] text-white rounded-lg px-3 py-1 text-xs font-bold">
                {reports.length} Bulan
              </div>
            </div>
          </div>
        </section>

        {/* Monthly Recap List */}
        <div>
          <div className="flex items-center justify-between mb-3 px-1">
            <h3 className="text-sm font-bold text-[#191c1d]">Monthly Recap</h3>
            <span className="text-[11px] text-[#414754] font-semibold">{new Date().getFullYear()}</span>
          </div>

          <div className="space-y-3">
            {reports.length > 0 ? reports.map((report, idx) => {
              const isCurrentMonth = idx === 0
              return (
                <article
                  key={report.month_key}
                  className="bg-white rounded-xl p-4 flex items-center justify-between shadow-[0px_4px_12px_rgba(25,28,29,0.04)] hover:bg-[#f3f4f5] transition-colors duration-150 cursor-pointer group"
                  onClick={() => fetchMonthDetails(report)}
                >
                  <div className="flex-1">
                    <div className="flex items-center gap-2 mb-1">
                      {isCurrentMonth && <div className="w-1.5 h-1.5 rounded-full bg-[#1a73e8] shrink-0" />}
                      <h4 className="font-semibold text-sm text-[#191c1d]">{MONTHS_ID[parseInt(report.month)]}</h4>
                      {isCurrentMonth && <span className="text-[10px] text-[#414754] font-medium">· Bulan Ini</span>}
                    </div>
                    <div className="flex gap-4">
                      <div className="flex items-center gap-1.5">
                        <span className="material-symbols-outlined text-[#005bbf]" style={{ fontSize: '14px' }}>arrow_upward</span>
                        <span className="text-xs text-[#191c1d]">{formatCurrency(num(report.income))}</span>
                      </div>
                      <div className="flex items-center gap-1.5">
                        <span className="material-symbols-outlined text-[#ba1a1a]" style={{ fontSize: '14px' }}>arrow_downward</span>
                        <span className="text-xs text-[#414754]">{formatCurrency(num(report.expense))}</span>
                      </div>
                    </div>
                  </div>
                  <div className="text-right">
                    <p className="font-extrabold text-sm text-[#191c1d]">{formatCurrency(num(report.income) - num(report.expense))}</p>
                    <p className="text-[#414754] text-[10px] uppercase tracking-tighter font-medium">Net Balance</p>
                  </div>
                </article>
              )
            }) : (
              <div className="bg-white rounded-xl p-10 text-center text-[#414754] text-sm">Belum ada riwayat transaksi</div>
            )}
          </div>
        </div>
      </div>

      {/* Detail Bottom Sheet */}
      <AnimatePresence>
        {selectedMonth && (
          <>
            <motion.div
              initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}
              className="fixed inset-0 bg-[#191c1d]/40 backdrop-blur-sm z-50"
              onClick={() => setSelectedMonth(null)}
            />
            <motion.div
              initial={{ y: '100%' }} animate={{ y: 0 }} exit={{ y: '100%' }}
              transition={{ type: 'spring', damping: 28, stiffness: 300 }}
              className="fixed bottom-0 left-0 right-0 max-w-screen-sm mx-auto bg-white rounded-t-2xl z-50 max-h-[85vh] overflow-hidden flex flex-col shadow-2xl"
            >
              <div className="px-5 py-4 flex items-center justify-between border-b border-[#c1c6d6]/20 bg-white sticky top-0 z-10">
                <div>
                  <h3 className="font-bold text-[#191c1d]">{MONTHS_ID[parseInt(selectedMonth.month)]} {selectedMonth.year}</h3>
                  <p className="text-[11px] text-[#414754]">Ringkasan transaksi bulan ini</p>
                </div>
                <button onClick={() => setSelectedMonth(null)} className="w-9 h-9 rounded-lg bg-[#f3f4f5] flex items-center justify-center">
                  <span className="material-symbols-outlined text-[#414754] text-xl leading-none">close</span>
                </button>
              </div>

              <div className="overflow-y-auto flex-1 p-5 space-y-4">
                <div className="grid grid-cols-4 gap-2">
                  <div className="bg-[#f3f4f5] rounded-lg p-2 text-center">
                    <p className="text-[8px] text-[#414754] uppercase font-semibold mb-1 truncate">Income</p>
                    <p className="font-bold text-[#005bbf] text-[11px] truncate">{formatCurrency(num(selectedMonth.income))}</p>
                  </div>
                  <div className="bg-[#f3f4f5] rounded-lg p-2 text-center">
                    <p className="text-[8px] text-[#414754] uppercase font-semibold mb-1 truncate">Expense</p>
                    <p className="font-bold text-[#ba1a1a] text-[11px] truncate">{formatCurrency(num(selectedMonth.expense))}</p>
                  </div>
                  <div className="bg-[#f3f4f5] rounded-lg p-2 text-center">
                    <p className="text-[8px] text-[#414754] uppercase font-semibold mb-1 truncate">Saving</p>
                    <p className="font-bold text-[#1a73e8] text-[11px] truncate">{formatCurrency(num(selectedMonth.savings))}</p>
                  </div>
                  <div className="bg-[#1a73e8]/10 rounded-lg p-2 text-center">
                    <p className="text-[8px] text-[#1a73e8] uppercase font-semibold mb-1 truncate">Net</p>
                    <p className="font-bold text-[#1a73e8] text-[11px] truncate">{formatCurrency(num(selectedMonth.income) - num(selectedMonth.expense) - num(selectedMonth.savings))}</p>
                  </div>
                </div>

                <div className="flex gap-2">
                  <button
                    onClick={() => window.location = route('rekap.export', { month_key: selectedMonth.month_key })}
                    className="flex-1 h-10 bg-[#f3f4f5] text-[#191c1d] rounded-lg text-xs font-bold flex items-center justify-center gap-1.5"
                  >
                    <span className="material-symbols-outlined text-sm">download</span> Export
                  </button>
                  <button
                    onClick={() => axios.post(route('rekap.email'), { month_key: selectedMonth.month_key }).then(r => alert(r.data.message))}
                    className="flex-1 h-10 bg-[#f3f4f5] text-[#191c1d] rounded-lg text-xs font-bold flex items-center justify-center gap-1.5"
                  >
                    <span className="material-symbols-outlined text-sm">mail</span> Email
                  </button>
                </div>

                <div>
                  <p className="text-[11px] font-extrabold uppercase tracking-widest text-[#414754] mb-3">Daftar Transaksi</p>
                  <div className="space-y-2">
                    {isLoading ? (
                      [1, 2, 3, 4].map(i => <div key={i} className="h-14 bg-[#f3f4f5] rounded-xl animate-pulse" />)
                    ) : monthDetails.length > 0 ? monthDetails.map((tx) => (
                      <div key={tx.id} className="bg-white border border-[#c1c6d6]/20 rounded-xl p-3 flex items-center justify-between shadow-[0px_2px_8px_rgba(25,28,29,0.04)]">
                        <div className="flex items-center gap-3">
                          <div className="w-8 h-8 rounded-lg bg-[#f3f4f5] flex items-center justify-center shrink-0">
                            <span className="material-symbols-outlined text-sm" style={{ 
                              color: tx.type === 'income' ? '#005bbf' : tx.type === 'expense' ? '#ba1a1a' : '#1a73e8' 
                            }}>
                              {tx.type === 'income' ? 'arrow_upward' : tx.type === 'expense' ? 'arrow_downward' : 'savings'}
                            </span>
                          </div>
                          <div>
                            <p className="text-[12px] font-semibold text-[#191c1d] leading-none capitalize">{tx.description}</p>
                            <p className="text-[9px] text-[#414754] mt-0.5 uppercase tracking-wide">{tx.category} · {tx.date}</p>
                          </div>
                        </div>
                        <p className={cn("text-[12px] font-bold", 
                            tx.type === 'income' ? 'text-[#005bbf]' : 
                            tx.type === 'expense' ? 'text-[#ba1a1a]' : 'text-[#1a73e8]'
                        )}>
                          {tx.type === 'income' ? '+' : '-'}{formatCurrency(tx.amount)}
                        </p>
                      </div>
                    )) : (
                      <p className="text-center text-[#414754] text-sm py-8">Tidak ada transaksi</p>
                    )}
                  </div>
                </div>
              </div>
            </motion.div>
          </>
        )}
      </AnimatePresence>
    </AppShell>
  )
}
