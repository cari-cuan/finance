import React from 'react'
import { motion, AnimatePresence } from 'framer-motion'

export default function LoadingScreen({ show }) {
  return (
    <AnimatePresence>
      {show && (
        <motion.div 
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 z-[100] bg-white/60 backdrop-blur-md flex items-center justify-center flex-col gap-4"
        >
            <div className="relative">
                <motion.div 
                    animate={{ rotate: 360 }}
                    transition={{ repeat: Infinity, duration: 1, ease: 'linear' }}
                    className="h-14 w-14 rounded-2xl border-4 border-indigo-100 border-t-indigo-600 shadow-xl shadow-indigo-100"
                />
                <div className="absolute inset-0 flex items-center justify-center">
                    <div className="h-2 w-2 rounded-full bg-indigo-600 animate-pulse" />
                </div>
            </div>
            <motion.div 
                initial={{ opacity: 0, y: 10 }}
                animate={{ opacity: 1, y: 0 }}
                transition={{ delay: 0.2 }}
                className="text-xs font-black text-indigo-600 uppercase tracking-[0.2em] ml-1"
            >
                Loading...
            </motion.div>
        </motion.div>
      )}
    </AnimatePresence>
  )
}
