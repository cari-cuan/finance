import React, { useState } from 'react'
import { Head, Link, useForm } from '@inertiajs/react'

export default function GuestLayout({ children, title, description }) {
  return (
    <div className="bg-[#f8f9fa] text-[#191c1d] font-[Manrope] min-h-screen flex flex-col">
      {/* Decorative blurs */}
      <div className="fixed top-0 right-0 -mr-24 -mt-24 w-96 h-96 bg-[#d8e2ff]/30 blur-[100px] rounded-full pointer-events-none" />
      <div className="fixed bottom-0 left-0 -ml-24 -mb-24 w-80 h-80 bg-[#d8e2ff]/20 blur-[80px] rounded-full pointer-events-none" />

      <main className="flex-grow flex items-center justify-center p-6 relative z-10">
        <div className="w-full max-w-[400px]">
          {/* Brand */}
          <div className="mb-10 flex flex-col items-center text-center">
            <div className="w-12 h-12 bg-[#005bbf] flex items-center justify-center rounded-xl mb-4 shadow-sm">
              <span className="material-symbols-outlined text-white text-2xl leading-none">account_balance_wallet</span>
            </div>
            <h1 className="text-[#191c1d] font-extrabold text-2xl tracking-tight mb-1">Precision Finance</h1>
            <p className="text-[#414754] text-[13px]">Precision data for modern finance.</p>
          </div>

          {/* Card */}
          <div className="bg-white rounded-xl shadow-[0px_12px_32px_rgba(25,28,29,0.04)] p-8 border border-[#c1c6d6]/15">
            <div className="mb-6">
              <h2 className="text-[#191c1d] font-bold text-lg mb-1">{title}</h2>
              <p className="text-[#414754] text-[13px]">{description}</p>
            </div>
            {children}
          </div>

          {/* Legal links */}
          <div className="mt-10 flex justify-center gap-6 text-[11px] text-[#727785] font-medium">
            <a href="#" className="hover:text-[#005bbf] transition-colors">Privacy Policy</a>
            <a href="#" className="hover:text-[#005bbf] transition-colors">Terms of Service</a>
            <a href="#" className="hover:text-[#005bbf] transition-colors">Support</a>
          </div>
        </div>
      </main>

      {/* Blue accent strip at bottom */}
      <div className="h-1 w-full fixed bottom-0 left-0" style={{ background: 'linear-gradient(135deg, #1a73e8 0%, #005bbf 100%)' }} />
    </div>
  )
}
