import React, { useState, useRef, useEffect } from 'react'
import { Head, useForm } from '@inertiajs/react'
import AppShell from '@/Layouts/AppShell'
import { cn } from '@/lib/utils'
import { motion, AnimatePresence } from 'framer-motion'
import axios from 'axios'

export default function Chat({ categories = [], accounts = [] }) {
  const [messages, setMessages] = useState([])
  const [isTyping, setIsTyping] = useState(false)
  const messagesEndRef = useRef(null)
  const inputRef = useRef(null)

  const form = useForm({ message: '' })

  useEffect(() => {
    scrollToBottom()
  }, [messages])

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' })
  }

  const sendMessage = async (e) => {
    e.preventDefault()
    if (!form.data.message.trim()) return

    const userMessage = form.data.message
    form.setData('message', '')

    setMessages(prev => [...prev, { role: 'user', content: userMessage }])
    setIsTyping(true)

    try {
      const response = await axios.post(route('chat.process'), {
        message: userMessage,
        categories: categories,
        accounts: accounts,
        history: messages.slice(-10).map(m => ({ role: m.role, content: m.content }))
      })

      const botMessage = response.data.message
      const quickReplies = response.data.quick_replies || []

      setMessages(prev => [...prev, {
        role: 'assistant',
        content: botMessage,
        quick_replies: quickReplies,
        parsed: response.data.parsed
      }])
    } catch (error) {
      setMessages(prev => [...prev, {
        role: 'assistant',
        content: 'Maaf, terjadi kesalahan. Coba lagi ya.',
        quick_replies: []
      }])
    } finally {
      setIsTyping(false)
    }
  }

  const sendQuickReply = (reply) => {
    form.setData('message', reply)
    form.submit('post', route('chat.process'), {
      onSuccess: () => {
        form.setData('message', '')
      }
    })
  }

  const welcomeMessage = "Halo! 👋 Saya asisten keuangan keluarga kamu.\n\nKamu bisa catat transaksi dengan format sederhana:\n• \"Makan siang 25rb\"\n• \"Gaji masuk 5jt\"\n• \"Bensin 50rb kemarin\"\n\nAtau tanya langsung apa saja!"

  return (
    <AppShell title="Asisten Keuangan">
      <Head title="Chat Asisten" />

      <div className="flex flex-col h-[calc(100vh-8rem)]">
        {/* Messages Area */}
        <div className="flex-1 overflow-y-auto px-2 py-4 space-y-4 custom-scrollbar">
          {messages.length === 0 && (
            <div className="flex items-center justify-center h-full">
              <div className="text-center max-w-xs">
                <div className="w-16 h-16 rounded-2xl bg-[#005bbf]/10 flex items-center justify-center mx-auto mb-4">
                  <span className="material-symbols-outlined text-[#005bbf] text-3xl">smart_toy</span>
                </div>
                <h3 className="text-sm font-bold text-[#191c1d] mb-2">Asisten Keuangan Keluarga</h3>
                <p className="text-xs text-[#414754] whitespace-pre-line">{welcomeMessage}</p>
              </div>
            </div>
          )}

          <AnimatePresence>
            {messages.map((msg, idx) => (
              <motion.div
                key={idx}
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                className={cn(
                  "flex",
                  msg.role === 'user' ? "justify-end" : "justify-start"
                )}
              >
                <div className={cn(
                  "max-w-[85%] rounded-2xl px-4 py-3 text-[13px] leading-relaxed",
                  msg.role === 'user'
                    ? "bg-[#005bbf] text-white rounded-br-md"
                    : "bg-white text-[#191c1d] shadow-sm rounded-bl-md"
                )}>
                  <p className="whitespace-pre-line">{msg.content}</p>

                  {msg.quick_replies && msg.quick_replies.length > 0 && (
                    <div className="flex flex-wrap gap-2 mt-3">
                      {msg.quick_replies.map((reply, rIdx) => (
                        <button
                          key={rIdx}
                          onClick={() => sendQuickReply(reply)}
                          className={cn(
                            "px-3 py-1.5 rounded-full text-xs font-semibold transition-all active:scale-95",
                            msg.role === 'user'
                              ? "bg-white/20 text-white hover:bg-white/30"
                              : "bg-[#005bbf]/10 text-[#005bbf] hover:bg-[#005bbf]/20"
                          )}
                        >
                          {reply}
                        </button>
                      ))}
                    </div>
                  )}
                </div>
              </motion.div>
            ))}
          </AnimatePresence>

          {isTyping && (
            <motion.div
              initial={{ opacity: 0, y: 10 }}
              animate={{ opacity: 1, y: 0 }}
              className="flex justify-start"
            >
              <div className="bg-white rounded-2xl rounded-bl-md px-4 py-3 shadow-sm">
                <div className="flex gap-1.5">
                  <span className="w-2 h-2 bg-[#727785] rounded-full animate-bounce" style={{ animationDelay: '0ms' }}></span>
                  <span className="w-2 h-2 bg-[#727785] rounded-full animate-bounce" style={{ animationDelay: '150ms' }}></span>
                  <span className="w-2 h-2 bg-[#727785] rounded-full animate-bounce" style={{ animationDelay: '300ms' }}></span>
                </div>
              </div>
            </motion.div>
          )}

          <div ref={messagesEndRef} />
        </div>

        {/* Input Area */}
        <div className="sticky bottom-0 bg-[#f8f9fa] border-t border-[#414754]/10 px-3 py-3">
          <form onSubmit={sendMessage} className="flex items-center gap-2">
            <input
              ref={inputRef}
              type="text"
              value={form.data.message}
              onChange={e => form.setData('message', e.target.value)}
              placeholder="Ketik pesan atau catatan transaksi..."
              className="flex-1 bg-white border border-[#c1c6d6]/30 rounded-full px-4 py-2.5 text-[13px] outline-none focus:border-[#005bbf]/40 focus:ring-2 focus:ring-[#005bbf]/10 transition-all"
            />
            <button
              type="submit"
              disabled={!form.data.message.trim() || isTyping}
              className="w-10 h-10 rounded-full bg-[#005bbf] flex items-center justify-center disabled:opacity-40 active:scale-95 transition-all"
            >
              <span className="material-symbols-outlined text-white text-xl">send</span>
            </button>
          </form>
        </div>
      </div>
    </AppShell>
  )
}
