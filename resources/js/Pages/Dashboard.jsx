import React from 'react'
import { Head, Link } from '@inertiajs/react'
import AppShell from '@/Layouts/AppShell'
import { cn } from '@/lib/utils'
import { motion } from 'framer-motion'

export default function Dashboard({ auth, summary, latestTransactions, topCategories }) {
  const user = auth?.user

  const formatCurrency = (amount) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0)

  return (
    <AppShell title="Precision Finance">
      <Head title="Dashboard" />

      <div className="pt-4 pb-6 space-y-4">
        {/* Ultra Compact 1-Row Stats — Stitch style */}
        <div className="grid grid-cols-2 gap-2">
          <div className="bg-white p-3 rounded-[16px] border border-[#c1c6d6]/20 shadow-sm flex items-center gap-3">
            <div className="w-8 h-8 rounded-lg bg-[#005bbf]/10 flex items-center justify-center shrink-0">
               <span className="material-symbols-outlined text-[#005bbf] text-base">arrow_circle_up</span>
            </div>
            <div>
              <p className="text-[9px] font-black text-[#727785] uppercase tracking-wider">Income</p>
              <p className="text-[13px] font-black text-[#005bbf] leading-tight">{formatCurrency(summary?.income)}</p>
            </div>
          </div>
          <div className="bg-white p-3 rounded-[16px] border border-[#c1c6d6]/20 shadow-sm flex items-center gap-3">
             <div className="w-8 h-8 rounded-lg bg-[#ba1a1a]/10 flex items-center justify-center shrink-0">
               <span className="material-symbols-outlined text-[#ba1a1a] text-base">arrow_circle_down</span>
            </div>
            <div>
              <p className="text-[9px] font-black text-[#727785] uppercase tracking-wider">Expense</p>
              <p className="text-[13px] font-black text-[#ba1a1a] leading-tight">{formatCurrency(summary?.expense)}</p>
            </div>
          </div>
          <div className="bg-white p-3 rounded-[16px] border border-[#c1c6d6]/20 shadow-sm flex items-center gap-3">
             <div className="w-8 h-8 rounded-lg bg-[#1a73e8]/10 flex items-center justify-center shrink-0">
               <span className="material-symbols-outlined text-[#1a73e8] text-base">savings</span>
            </div>
            <div>
              <p className="text-[9px] font-black text-[#727785] uppercase tracking-wider">Tabungan</p>
              <p className="text-[13px] font-black text-[#1a73e8] leading-tight">{formatCurrency(summary?.savings)}</p>
            </div>
          </div>
          <div className="p-3 rounded-[16px] shadow-lg flex flex-col justify-center" style={{ background: 'linear-gradient(135deg, #1a73e8 0%, #005bbf 100%)' }}>
            <p className="text-[9px] font-black text-white/60 uppercase tracking-widest pl-0.5">Total Balance</p>
            <p className="text-[15px] font-black text-white leading-tight">{formatCurrency(summary?.balance)}</p>
          </div>
        </div>

        {/* Top 3 Expenses */}
        {topCategories && topCategories.length > 0 && (
          <section className="bg-[#f3f4f5] rounded-xl p-4 space-y-3">
            <h3 className="text-[11px] font-extrabold uppercase tracking-widest text-[#414754]">Top Pengeluaran</h3>
            <div className="space-y-3">
              {topCategories.slice(0, 3).map((cat, i) => {
                const colors = ['#005bbf', '#c55500', '#ba1a1a']
                const pct = Math.min(100, (cat.total / (topCategories[0]?.total || 1)) * 100)
                return (
                  <div key={i} className="space-y-1">
                    <div className="flex justify-between text-[11px] font-bold text-[#191c1d]">
                      <span>{cat.name}</span>
                      <span>{formatCurrency(cat.total)}</span>
                    </div>
                    <div className="h-1.5 w-full bg-[#e7e8e9] rounded-full overflow-hidden">
                      <div className="h-full rounded-full transition-all" style={{ width: `${pct}%`, background: colors[i] }} />
                    </div>
                  </div>
                )
              })}
            </div>
          </section>
        )}

        {/* Recent Transactions */}
        <div className="space-y-2">
          <div className="flex items-center justify-between px-1 mb-1">
            <h3 className="text-[11px] font-extrabold uppercase tracking-widest text-[#414754]">Transaksi Terakhir</h3>
            <Link href="/rekap" className="text-[11px] font-bold text-[#005bbf] hover:underline">Lihat Semua</Link>
          </div>

          {latestTransactions?.length > 0 ? latestTransactions.map((tx) => (
            <div
              key={tx.id}
              className="bg-white rounded-xl p-3 flex items-center justify-between shadow-[0px_4px_12px_rgba(25,28,29,0.04)] hover:bg-[#f3f4f5] transition-colors duration-150"
            >
              <div className="flex items-center gap-3">
                <div className="w-9 h-9 rounded-lg bg-[#f3f4f5] flex items-center justify-center shrink-0">
                  <span
                    className="material-symbols-outlined text-base"
                    style={{ color: tx.type === 'income' ? '#005bbf' : (tx.type === 'savings' ? '#1a73e8' : '#ba1a1a') }}
                  >
                    {tx.type === 'income' ? 'arrow_upward' : (tx.type === 'savings' ? 'account_balance' : 'arrow_downward')}
                  </span>
                </div>
                <div>
                  <p className="text-[13px] font-semibold text-[#191c1d] leading-tight capitalize">
                    {tx.description || tx.category?.name || 'Transaksi'}
                  </p>
                  <p className="text-[10px] text-[#414754] mt-0.5">
                    {tx.category?.name} · {new Date(tx.transaction_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })}
                  </p>
                </div>
              </div>
              <p className={cn("text-[13px] font-bold tabular-nums", tx.type === 'income' ? 'text-[#005bbf]' : (tx.type === 'savings' ? 'text-[#1a73e8]' : 'text-[#ba1a1a]'))}>
                {tx.type === 'income' ? '+' : '-'}{formatCurrency(tx.amount)}
              </p>
            </div>
          )) : (
            <div className="bg-white rounded-xl p-8 text-center text-[#414754] text-sm">Belum ada transaksi</div>
          )}
        </div>

        {/* Upgrade Banner */}
        <section className="relative bg-[#191c1d] rounded-xl overflow-hidden p-5 flex items-center justify-between gap-4">
          <div className="absolute inset-0 opacity-20 bg-gradient-to-br from-[#1a73e8] to-[#005bbf]" />
          <div className="relative z-10">
            <h2 className="text-white font-extrabold text-sm tracking-tight">Catat via AI Chat</h2>
            <p className="text-[11px] text-[#adc7ff] leading-tight max-w-[180px] mt-0.5">Ketik transaksi dalam bahasa natural, AI yang proses!</p>
          </div>
          <Link
            href="/chat"
            className="relative z-10 text-white text-[11px] font-extrabold px-4 py-2 rounded-lg uppercase tracking-wider active:scale-90 duration-200 shrink-0"
            style={{ background: 'linear-gradient(135deg, #1a73e8 0%, #005bbf 100%)' }}
          >
            Coba Sekarang
          </Link>
        </section>
      </div>
    </AppShell>
  )
}
