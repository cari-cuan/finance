import { Link, usePage } from '@inertiajs/react'
import { cn } from '@/lib/utils'

function NavItem({ href, label, active, icon }) {
  return (
    <Link
      href={href}
      className={cn(
        "flex flex-col items-center justify-center px-3 py-1.5 rounded-xl transition-all duration-200 active:scale-90 gap-0.5",
        active
          ? "bg-[#f3f4f5] text-[#1A73E8]"
          : "text-[#414754] hover:text-[#1A73E8]"
      )}
    >
      <span
        className="material-symbols-outlined text-[22px] leading-none"
        style={active ? { fontVariationSettings: "'FILL' 1, 'wght' 400" } : {}}
      >
        {icon}
      </span>
      <span className="text-[10px] font-semibold">{label}</span>
    </Link>
  )
}

export default function AppShell({ title, children }) {
  const { url, props } = usePage()
  const user = props.auth?.user

  return (
    <div className="min-h-screen bg-[#f8f9fa] font-[Manrope] text-[#191c1d]">
      <div className="max-w-screen-sm mx-auto min-h-screen bg-[#f8f9fa] relative">
        {/* TopAppBar */}
        <header className="fixed top-0 w-full max-w-screen-sm z-50 bg-[#f8f9fa]/80 backdrop-blur-md flex items-center justify-between px-4 h-14">
          <div className="flex items-center gap-2">
            <div className="w-7 h-7 bg-[#005bbf] flex items-center justify-center rounded-lg shadow-sm">
              <span className="material-symbols-outlined text-white text-base leading-none">account_balance_wallet</span>
            </div>
            <span className="text-base font-bold text-[#191c1d] tracking-tight">{title}</span>
          </div>
          <div className="flex items-center gap-3">
            <button className="material-symbols-outlined text-[#414754] text-xl active:scale-95 duration-150">search</button>
            {user && (
              <Link
                as="button"
                method="post"
                href="/logout"
                className="material-symbols-outlined text-[#414754] text-xl active:scale-95 duration-150"
                title="Logout"
              >
                logout
              </Link>
            )}
          </div>
        </header>

        <main className="pt-16 pb-20 px-4">
          {children}
        </main>

        {/* Bottom Navigation */}
        {user && (
          <nav className="fixed bottom-0 left-0 right-0 max-w-screen-sm mx-auto flex justify-around items-center px-4 py-2 bg-white border-t border-[#414754]/15 shadow-[0px_-4px_16px_rgba(25,28,29,0.04)] z-50 rounded-t-xl">
            <NavItem href="/dashboard" label="Dashboard" icon="dashboard" active={url.startsWith('/dashboard')} />
            <NavItem href="/catat" label="Catat" icon="add_circle" active={url.startsWith('/catat')} />
            <NavItem href="/chat" label="Chat" icon="chat_bubble" active={url.startsWith('/chat')} />
            <NavItem href="/rekap" label="Rekap" icon="history_edu" active={url.startsWith('/rekap') || url.startsWith('/reports')} />
          </nav>
        )}
      </div>
    </div>
  )
}
