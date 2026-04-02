import React, { useState, useRef, useEffect, useCallback } from 'react'
import { Head } from '@inertiajs/react'
import AppShell from '@/Layouts/AppShell'
import { cn } from '@/lib/utils'
import { motion, AnimatePresence } from 'framer-motion'
import axios from 'axios'

const PAGE_SIZE = 10

function renderMessageContent(content) {
  // Split content by markdown tables
  const tableRegex = /\|(.+)\|\s*\n\|[-|\s]+\|\s*\n((?:\|.+\|\s*\n?)*)/g
  const parts = []
  let lastIndex = 0
  let match

  while ((match = tableRegex.exec(content)) !== null) {
    // Add text before table
    if (match.index > lastIndex) {
      parts.push({ type: 'text', content: content.slice(lastIndex, match.index) })
    }

    // Parse table
    const headerRow = match[1].split('|').map(h => h.trim()).filter(Boolean)
    const bodyRows = match[2].trim().split('\n').map(row =>
      row.split('|').map(cell => cell.trim()).filter(Boolean)
    )

    parts.push({ type: 'table', headers: headerRow, rows: bodyRows })
    lastIndex = match.index + match[0].length
  }

  // Add remaining text
  if (lastIndex < content.length) {
    parts.push({ type: 'text', content: content.slice(lastIndex) })
  }

  if (parts.length === 1 && parts[0].type === 'text') {
    return <p className="whitespace-pre-line">{parts[0].content}</p>
  }

  return (
    <div className="space-y-3">
      {parts.map((part, idx) => {
        if (part.type === 'table') {
          return (
            <div key={idx} className="overflow-x-auto rounded-lg border border-[#c1c6d6]/20">
              <table className="w-full text-[11px] border-collapse">
                <thead>
                  <tr className="bg-[#005bbf]/5">
                    {part.headers.map((h, i) => (
                      <th key={i} className="px-2 py-1.5 text-left font-bold text-[#005bbf] border-b border-[#c1c6d6]/20 whitespace-nowrap">
                        {h}
                      </th>
                    ))}
                  </tr>
                </thead>
                <tbody>
                  {part.rows.map((row, ri) => (
                    <tr key={ri} className={ri % 2 === 0 ? 'bg-[#f8f9fa]/50' : ''}>
                      {row.map((cell, ci) => (
                        <td key={ci} className="px-2 py-1.5 border-b border-[#c1c6d6]/10 whitespace-nowrap">
                          {cell}
                        </td>
                      ))}
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )
        }
        return <p key={idx} className="whitespace-pre-line">{part.content}</p>
      })}
    </div>
  )
}

export default function Chat({ history = [] }) {
  const [messages, setMessages] = useState(
    history.map(h => ({
      role: h.role,
      content: h.content,
      quick_replies: h.quick_replies || [],
      parsed: h.parsed
    }))
  )
  const [isTyping, setIsTyping] = useState(false)
  const [input, setInput] = useState('')
  const [isLoadingOlder, setIsLoadingOlder] = useState(false)
  const [hasMore, setHasMore] = useState(history.length >= PAGE_SIZE)
  const [oldestId, setOldestId] = useState(history.length > 0 ? history[0].id : null)

  const messagesContainerRef = useRef(null)
  const inputRef = useRef(null)
  const sentinelRef = useRef(null)
  const wasAtBottom = useRef(true)

  // Auto scroll to bottom on new messages (only if user was at bottom)
  useEffect(() => {
    if (wasAtBottom.current && messages.length > 0) {
      scrollToBottom('instant')
    }
  }, [messages])

  // Observer for detecting when user was at bottom
  useEffect(() => {
    const container = messagesContainerRef.current
    if (!container) return

    const handleScroll = () => {
      const { scrollTop, scrollHeight, clientHeight } = container
      wasAtBottom.current = scrollHeight - scrollTop - clientHeight < 100
    }

    container.addEventListener('scroll', handleScroll)
    return () => container.removeEventListener('scroll', handleScroll)
  }, [])

  // Intersection observer for loading older messages
  useEffect(() => {
    const observer = new IntersectionObserver(
      (entries) => {
        if (entries[0].isIntersecting && hasMore && !isLoadingOlder) {
          loadOlderMessages()
        }
      },
      { root: messagesContainerRef.current, rootMargin: '50px 0px 0px 0px' }
    )

    if (sentinelRef.current) {
      observer.observe(sentinelRef.current)
    }

    return () => observer.disconnect()
  }, [hasMore, isLoadingOlder, oldestId])

  const scrollToBottom = (behavior = 'smooth') => {
    const container = messagesContainerRef.current
    if (container) {
      container.scrollTo({ top: container.scrollHeight, behavior })
    }
  }

  const loadOlderMessages = async () => {
    if (!oldestId || isLoadingOlder) return
    setIsLoadingOlder(true)

    try {
      const response = await axios.get(route('chat.history'), {
        params: { before: oldestId, limit: PAGE_SIZE }
      })

      const olderMessages = response.data.messages || []
      if (olderMessages.length === 0) {
        setHasMore(false)
      } else {
        const mapped = olderMessages.map(h => ({
          id: h.id,
          role: h.role,
          content: h.content,
          quick_replies: h.quick_replies || [],
          parsed: h.parsed
        }))

        // Save current scroll height before adding messages
        const container = messagesContainerRef.current
        const prevScrollHeight = container ? container.scrollHeight : 0

        setMessages(prev => [...mapped, ...prev])
        setOldestId(olderMessages[olderMessages.length - 1].id)

        // Restore scroll position after messages are added
        requestAnimationFrame(() => {
          if (container) {
            const newScrollHeight = container.scrollHeight
            container.scrollTop = newScrollHeight - prevScrollHeight
          }
        })
      }
    } catch (error) {
      console.error('Failed to load older messages:', error)
    } finally {
      setIsLoadingOlder(false)
    }
  }

  const sendMessage = async (text) => {
    if (!text.trim()) return

    const userMessage = text.trim()
    setInput('')

    setMessages(prev => [...prev, { role: 'user', content: userMessage }])
    setIsTyping(true)

    // Scroll to bottom when sending message
    requestAnimationFrame(() => scrollToBottom())

    try {
      const response = await axios.post(route('chat.process'), {
        message: userMessage
      })

      const botMessage = response.data.message
      const quickReplies = response.data.quick_replies || []

      setMessages(prev => [...prev, {
        role: 'assistant',
        content: botMessage,
        quick_replies: quickReplies,
        parsed: response.data.parsed
      }])

      // Scroll to bottom after AI response
      requestAnimationFrame(() => scrollToBottom())
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

  const handleSubmit = (e) => {
    e.preventDefault()
    sendMessage(input)
  }

  const sendQuickReply = (reply) => {
    sendMessage(reply)
  }

  const welcomeMessage = "Halo! 👋 Saya asisten keuangan keluarga kamu.\n\nKamu bisa catat transaksi dengan format sederhana:\n• \"Makan siang 25rb\"\n• \"Gaji masuk 5jt\"\n• \"Bensin 50rb kemarin\"\n\nAtau tanya langsung apa saja!"

  return (
    <AppShell title="Asisten Keuangan">
      <Head title="Chat Asisten" />

      <div className="flex flex-col h-[calc(100vh-8rem)]">
        {/* Messages Area */}
        <div
          ref={messagesContainerRef}
          className="flex-1 overflow-y-auto px-2 py-4 space-y-4 custom-scrollbar"
        >
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

          {/* Sentinel for infinite scroll */}
          <div ref={sentinelRef} className="h-1" />
          {isLoadingOlder && (
            <div className="flex justify-center py-2">
              <div className="flex gap-1.5">
                <span className="w-2 h-2 bg-[#727785] rounded-full animate-bounce" style={{ animationDelay: '0ms' }}></span>
                <span className="w-2 h-2 bg-[#727785] rounded-full animate-bounce" style={{ animationDelay: '150ms' }}></span>
                <span className="w-2 h-2 bg-[#727785] rounded-full animate-bounce" style={{ animationDelay: '300ms' }}></span>
              </div>
            </div>
          )}

          <AnimatePresence>
            {messages.map((msg, idx) => (
              <motion.div
                key={msg.id || idx}
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
                  {renderMessageContent(msg.content)}

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
        </div>

        {/* Input Area */}
        <div className="sticky bottom-0 bg-[#f8f9fa] border-t border-[#414754]/10 px-3 py-3">
          <form onSubmit={handleSubmit} className="flex items-center gap-2">
            <input
              ref={inputRef}
              type="text"
              value={input}
              onChange={e => setInput(e.target.value)}
              placeholder="Ketik pesan atau catatan transaksi..."
              className="flex-1 bg-white border border-[#c1c6d6]/30 rounded-full px-4 py-2.5 text-[13px] outline-none focus:border-[#005bbf]/40 focus:ring-2 focus:ring-[#005bbf]/10 transition-all"
            />
            <button
              type="submit"
              disabled={!input.trim() || isTyping}
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
