import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'motion/react';
import { Bell, Heart, ShieldCheck, Users, Plus, X, ArrowUp } from 'lucide-react';
import { ScreenType } from '../types';

interface LiquidGlassDockProps {
  currentScreen: ScreenType;
  onNavigate: (screen: ScreenType) => void;
  onOpenNotifyModal: () => void;
  onOpenDonate: () => void;
}

export const LiquidGlassDock: React.FC<LiquidGlassDockProps> = ({
  onNavigate, onOpenNotifyModal, onOpenDonate,
}) => {
  const [isExpanded, setIsExpanded]     = useState(false);
  const [showScrollTop, setShowScrollTop] = useState(false);

  useEffect(() => {
    const onScroll = () => setShowScrollTop(window.scrollY > 300);
    window.addEventListener('scroll', onScroll, { passive: true });
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  const actions = [
    {
      label: 'Scholarship Alert',
      sub: '2026/2027 Cycle',
      Icon: Bell,
      bg: 'bg-[#FEF3C7]',
      iconColor: 'text-[#D97706]',
      onClick: () => { setIsExpanded(false); onOpenNotifyModal(); },
    },
    {
      label: 'Donate to Scholars',
      sub: '100% Direct Impact',
      Icon: Heart,
      bg: 'bg-[#D97706]',
      iconColor: 'text-white',
      onClick: () => { setIsExpanded(false); onOpenDonate(); },
    },
    {
      label: 'Verification Desk',
      sub: 'Secretariat Registry',
      Icon: ShieldCheck,
      bg: 'bg-[#e3dfff]',
      iconColor: 'text-[#181445]',
      onClick: () => { setIsExpanded(false); onNavigate('contact-us'); },
    },
    {
      label: 'Founders & Council',
      sub: 'Leadership Team',
      Icon: Users,
      bg: 'bg-[#f5f3ef]',
      iconColor: 'text-[#4B485A]',
      onClick: () => { setIsExpanded(false); onNavigate('about-us'); },
    },
  ];

  return (
    <div className="fixed bottom-20 lg:bottom-6 right-4 sm:right-6 z-40 flex flex-col items-end gap-2.5 pointer-events-none select-none">
      {/* Expanded flyout */}
      <AnimatePresence>
        {isExpanded && (
          <motion.div
            initial={{ opacity: 0, y: 16, scale: 0.92 }}
            animate={{ opacity: 1, y: 0, scale: 1 }}
            exit={{ opacity: 0, y: 12, scale: 0.94 }}
            transition={{ type: 'spring', damping: 25, stiffness: 350 }}
            className="pointer-events-auto liquid-glass-dock rounded-3xl p-3 shadow-2xl flex flex-col gap-2 min-w-[220px] max-w-[calc(100vw-2rem)]"
          >
            <div className="px-2 py-1 border-b border-white/60 flex items-center justify-between">
              <span className="text-[11px] font-bold uppercase tracking-wider text-[#1E1B4B]">Quick Controls</span>
              <span className="w-2 h-2 rounded-full bg-[#0D9488] animate-pulse" />
            </div>
            {actions.map(({ label, sub, Icon, bg, iconColor, onClick }) => (
              <motion.button
                key={label}
                whileHover={{ x: 2, backgroundColor: 'rgba(255,255,255,0.7)' }}
                whileTap={{ scale: 0.98 }}
                onClick={onClick}
                className="flex items-center gap-2.5 px-3 py-2 rounded-xl text-left text-xs font-bold text-[#1E1B4B] transition-colors"
              >
                <div className={`w-7 h-7 rounded-lg ${bg} ${iconColor} flex items-center justify-center shrink-0 shadow-2xs`}>
                  <Icon size={15} strokeWidth={2} />
                </div>
                <div className="flex flex-col">
                  <span>{label}</span>
                  <span className="text-[10px] text-[#6E6B7E] font-normal">{sub}</span>
                </div>
              </motion.button>
            ))}
          </motion.div>
        )}
      </AnimatePresence>

      {/* Floating pill */}
      <motion.div
        initial={{ y: 20, opacity: 0 }} animate={{ y: 0, opacity: 1 }}
        transition={{ type: 'spring', damping: 20, stiffness: 300 }}
        className="pointer-events-auto flex items-center gap-2"
      >
        <AnimatePresence>
          {showScrollTop && (
            <motion.button
              initial={{ scale: 0, opacity: 0 }} animate={{ scale: 1, opacity: 1 }} exit={{ scale: 0, opacity: 0 }}
              whileHover={{ scale: 1.08, y: -2 }} whileTap={{ scale: 0.92 }}
              onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })}
              title="Return to top"
              className="w-10 h-10 rounded-full liquid-glass-dock flex items-center justify-center text-[#1E1B4B] shadow-lg"
            >
              <ArrowUp size={18} strokeWidth={2.5} />
            </motion.button>
          )}
        </AnimatePresence>

        <div className="liquid-glass-dock rounded-full p-1.5 flex items-center gap-1.5 shadow-xl border border-white/80">
          <motion.button
            whileHover={{ scale: 1.05 }} whileTap={{ scale: 0.95 }}
            onClick={onOpenNotifyModal}
            className="liquid-glass-amber-btn text-white text-xs font-bold h-9 px-3.5 rounded-full flex items-center gap-1.5 shadow-xs"
          >
            <Bell size={14} strokeWidth={2} />
            <span className="hidden sm:inline">Scholarship</span>
            <span>Alert</span>
          </motion.button>

          <motion.button
            whileHover={{ scale: 1.08, backgroundColor: 'rgba(255,255,255,0.9)' }}
            whileTap={{ scale: 0.92 }}
            onClick={() => setIsExpanded(!isExpanded)}
            aria-label="Toggle Quick Controls"
            className={`w-9 h-9 rounded-full flex items-center justify-center transition-all ${
              isExpanded ? 'bg-[#1E1B4B] text-white shadow-xs' : 'text-[#1E1B4B] hover:bg-white/70'
            }`}
          >
            <motion.div
              animate={{ rotate: isExpanded ? 45 : 0 }}
              transition={{ type: 'spring', damping: 20, stiffness: 300 }}
            >
              {isExpanded ? <X size={18} strokeWidth={2.5} /> : <Plus size={20} strokeWidth={2.5} />}
            </motion.div>
          </motion.button>
        </div>
      </motion.div>
    </div>
  );
};
