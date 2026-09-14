import React, { useState } from 'react';
import { motion, AnimatePresence } from 'motion/react';
import { Icon } from './Icon';
import { BrandLogo } from './BrandLogo';

interface DonateModalProps {
  isOpen: boolean;
  onClose: () => void;
}

export const DonateModal: React.FC<DonateModalProps> = ({ isOpen, onClose }) => {
  const [frequency, setFrequency] = useState<'one-time' | 'monthly'>('one-time');
  const [selectedAmount, setSelectedAmount] = useState<number>(25000);
  const [customAmount, setCustomAmount] = useState<string>('');
  const [cause, setCause] = useState<string>('scholarships');
  const [step, setStep] = useState<'form' | 'transfer' | 'success'>('form');
  const [copiedField, setCopiedField] = useState<string | null>(null);

  const ngnPresets = [10000, 25000, 50000, 100000];

  const handleCopy = (text: string, label: string) => {
    navigator.clipboard?.writeText(text);
    setCopiedField(label);
    setTimeout(() => setCopiedField(null), 2000);
  };

  const finalAmount = customAmount ? Number(customAmount) : selectedAmount;

  return (
    <AnimatePresence>
      {isOpen && (
        <motion.div
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          exit={{ opacity: 0 }}
          className="fixed inset-0 z-50 flex items-center justify-center bg-[#161338]/60 backdrop-blur-md p-4"
          onClick={() => {
            setStep('form');
            onClose();
          }}
        >
          <motion.div
            initial={{ scale: 0.94, opacity: 0, y: 16 }}
            animate={{ scale: 1, opacity: 1, y: 0 }}
            exit={{ scale: 0.94, opacity: 0, y: 16 }}
            transition={{ type: 'spring', damping: 26, stiffness: 320 }}
            className="w-full max-w-lg liquid-glass-modal rounded-3xl p-6 sm:p-7 shadow-2xl flex flex-col gap-4 max-h-[90vh] overflow-y-auto relative"
            onClick={(e) => e.stopPropagation()}
          >
            {/* Header */}
            <div className="flex items-center justify-between border-b border-black/5 pb-3">
              <div className="flex items-center gap-2.5">
                <BrandLogo size="sm" showText={false} />
                <div>
                  <h3 className="font-serif text-lg sm:text-xl font-bold text-[#1E1B4B]">
                    Support the Foundation
                  </h3>
                  <p className="text-xs text-[#6E6B7E]">Direct Impact for Nigerian Undergraduates</p>
                </div>
              </div>
              <button
                onClick={() => {
                  setStep('form');
                  onClose();
                }}
                className="w-8 h-8 rounded-full liquid-glass-card hover:bg-white/80 flex items-center justify-center text-[#6E6B7E] transition-all active:scale-90"
              >
                <Icon name="close" size={18} />
              </button>
            </div>

            {step === 'form' && (
              <div className="flex flex-col gap-4">
                {/* Frequency Toggle */}
                <div className="relative grid grid-cols-2 p-1.5 liquid-glass-dock rounded-2xl border border-white/70">
                  <button
                    type="button"
                    onClick={() => setFrequency('one-time')}
                    className={`relative py-2 text-xs font-bold rounded-xl transition-all select-none ${
                      frequency === 'one-time' ? 'text-[#1E1B4B]' : 'text-[#6E6B7E] hover:text-[#1E1B4B]'
                    }`}
                  >
                    {frequency === 'one-time' && (
                      <motion.div
                        layoutId="active-donation-freq"
                        className="absolute inset-0 rounded-xl liquid-glass-pill-active -z-10"
                        transition={{ type: 'spring', damping: 26, stiffness: 350 }}
                      />
                    )}
                    One-Time Gift
                  </button>
                  <button
                    type="button"
                    onClick={() => setFrequency('monthly')}
                    className={`relative py-2 text-xs font-bold rounded-xl transition-all select-none ${
                      frequency === 'monthly' ? 'text-[#1E1B4B]' : 'text-[#6E6B7E] hover:text-[#1E1B4B]'
                    }`}
                  >
                    {frequency === 'monthly' && (
                      <motion.div
                        layoutId="active-donation-freq"
                        className="absolute inset-0 rounded-xl liquid-glass-pill-active -z-10"
                        transition={{ type: 'spring', damping: 26, stiffness: 350 }}
                      />
                    )}
                    Monthly Scholar Patron
                  </button>
                </div>

                {/* Amount Selection Grid */}
                <div className="grid grid-cols-2 sm:grid-cols-4 gap-2">
                  {ngnPresets.map((amt) => {
                    const isSelected = !customAmount && selectedAmount === amt;
                    return (
                      <button
                        key={amt}
                        type="button"
                        onClick={() => { setSelectedAmount(amt); setCustomAmount(''); }}
                        className={`py-3 px-2 rounded-2xl text-center font-bold text-sm transition-all ${
                          isSelected
                            ? 'liquid-glass-card border-amber-400/80 text-[#904d00] shadow-md ring-2 ring-[#D97706]/40 bg-amber-50/60'
                            : 'bg-white/70 border border-white/80 text-[#1E1B2E] hover:bg-white/90 shadow-2xs'
                        }`}
                      >
                        ₦{amt.toLocaleString()}
                      </button>
                    );
                  })}
                </div>

                {/* Custom Amount */}
                <div className="flex flex-col gap-1">
                  <label className="text-xs font-bold uppercase tracking-wider text-[#1E1B4B]">
                    Or Enter Custom Amount:
                  </label>
                  <div className="relative">
                    <span className="absolute left-3.5 top-1/2 -translate-y-1/2 font-bold text-xs text-[#6E6B7E]">₦</span>
                    <input
                      type="number"
                      placeholder="e.g. 50000"
                      value={customAmount}
                      onChange={(e) => setCustomAmount(e.target.value)}
                      className="w-full h-11 pl-8 pr-4 rounded-xl bg-white/80 border border-black/10 focus:border-[#D97706] focus:ring-2 focus:ring-[#D97706]/20 outline-none text-sm font-semibold"
                    />
                  </div>
                </div>

                {/* Cause Selection */}
                <div className="flex flex-col gap-1">
                  <label className="text-xs font-bold uppercase tracking-wider text-[#1E1B4B]">
                    Direct My Contribution To:
                  </label>
                  <select
                    value={cause}
                    onChange={(e) => setCause(e.target.value)}
                    className="w-full h-11 px-3 rounded-xl bg-white/80 border border-black/10 focus:border-[#D97706] focus:ring-2 focus:ring-[#D97706]/20 outline-none text-xs sm:text-sm font-medium"
                  >
                    <option value="scholarships">Undergraduate Tuition &amp; Stipend Grants</option>
                    <option value="public-health">Grassroots Public Health &amp; Hygiene Drives</option>
                    <option value="mentorship">Student Leadership &amp; Mentorship Programs</option>
                    <option value="general">Greatest Need (Discretionary Board Allocation)</option>
                  </select>
                </div>

                {/* Proceed */}
                <div className="pt-2 flex flex-col gap-2">
                  <motion.button
                    whileHover={{ scale: 1.02 }}
                    whileTap={{ scale: 0.97 }}
                    type="button"
                    onClick={() => setStep('transfer')}
                    className="liquid-glass-amber-btn glass-refraction w-full h-12 rounded-2xl text-white font-bold text-sm flex items-center justify-center gap-2 shadow-lg"
                  >
                    <span>Proceed to Official Giving Channels</span>
                    <Icon name="arrow_forward" size={18} />
                  </motion.button>
                  <p className="text-[11px] text-[#6E6B7E] text-center">
                    100% of public donations directly fund student education and grassroots outreach.
                  </p>
                </div>
              </div>
            )}

            {step === 'transfer' && (
              <div className="flex flex-col gap-4">
                <div className="p-3.5 rounded-2xl bg-amber-50/70 backdrop-blur-md border border-amber-200/70 flex items-center justify-between shadow-xs">
                  <div>
                    <span className="text-xs text-[#904d00] font-medium block">Pledged Amount:</span>
                    <span className="text-lg font-bold text-[#1E1B4B]">
                      ₦{finalAmount.toLocaleString()}
                      <span className="text-xs font-normal text-[#6E6B7E] ml-1">
                        ({frequency === 'monthly' ? 'Monthly' : 'One-time'})
                      </span>
                    </span>
                  </div>
                  <button onClick={() => setStep('form')} className="text-xs text-[#D97706] font-bold underline">
                    Change
                  </button>
                </div>

                <div className="space-y-3">
                  <h4 className="text-xs font-bold uppercase tracking-wider text-[#1E1B4B]">
                    Official Bank Transfer Details
                  </h4>

                  {/* NGN Bank Details */}
                  <div className="p-4 rounded-2xl liquid-glass-card border border-white/80 flex flex-col gap-2 text-xs sm:text-sm shadow-xs">
                    <div className="flex items-center justify-between">
                      <span className="font-bold text-[#1E1B4B]">Account Name:</span>
                      <span className="font-medium text-[#1E1B2E]">Karl Peace Legacy Foundation</span>
                    </div>
                    <div className="flex items-center justify-between">
                      <span className="font-bold text-[#1E1B4B]">Bank:</span>
                      <span className="font-medium text-[#1E1B2E]">Zenith Bank</span>
                    </div>
                    <div className="flex items-center justify-between bg-white/80 backdrop-blur-xs p-2.5 rounded-xl border border-black/5 shadow-2xs">
                      <div>
                        <span className="text-[11px] text-[#6E6B7E] block">Account Number:</span>
                        <span className="font-mono font-bold text-[#1E1B4B] text-base">1312620302</span>
                      </div>
                      <button
                        onClick={() => handleCopy('1312620302', 'zenith')}
                        className="px-3 py-1.5 rounded-xl liquid-glass-light-btn text-xs font-bold text-[#1E1B4B] flex items-center gap-1 shadow-xs"
                      >
                        <Icon name="content_copy" size={14} />
                        <span>{copiedField === 'zenith' ? 'Copied!' : 'Copy'}</span>
                      </button>
                    </div>
                  </div>
                </div>

                <div className="pt-2 flex flex-col gap-2">
                  <motion.button
                    whileHover={{ scale: 1.02 }}
                    whileTap={{ scale: 0.97 }}
                    type="button"
                    onClick={() => setStep('success')}
                    className="liquid-glass-amber-btn glass-refraction w-full h-12 rounded-2xl text-white font-bold text-sm flex items-center justify-center gap-2 shadow-lg"
                  >
                    <Icon name="verified" size={18} />
                    <span>I Have Completed the Transfer</span>
                  </motion.button>
                  <button
                    type="button"
                    onClick={() => setStep('form')}
                    className="text-xs text-[#6E6B7E] hover:text-[#1E1B4B] text-center py-1"
                  >
                    Back to Amount Selection
                  </button>
                </div>
              </div>
            )}

            {step === 'success' && (
              <div className="flex flex-col items-center text-center gap-3 py-4">
                <div className="w-16 h-16 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-600 flex items-center justify-center shadow-sm">
                  <Icon name="task_alt" size={32} />
                </div>
                <h4 className="font-serif text-xl font-bold text-[#1E1B4B]">
                  Thank You for Your Generosity!
                </h4>
                <p className="text-xs sm:text-sm text-[#4B485A] leading-relaxed max-w-sm">
                  Your commitment empowers Nigerian students to achieve academic excellence and fosters healthy communities. A confirmation has been logged for our secretariat records.
                </p>
                <div className="w-full p-3.5 rounded-2xl liquid-glass-card border border-white/70 text-xs text-[#4B485A] text-left flex flex-col gap-1.5 shadow-2xs mt-2">
                  <span className="font-bold text-[#1E1B4B]">Confirmation Reference:</span>
                  <span className="font-mono text-[#D97706] font-semibold">
                    KPLF-DON-{Math.floor(100000 + Math.random() * 900000)}
                  </span>
                  <span className="text-[11px] text-[#6E6B7E]">
                    To receive an official tax-deductible receipt, send your transfer screenshot to{' '}
                    <a href="mailto:donations@karlpeacelegacy.org" className="underline font-bold text-[#1E1B4B]">
                      donations@karlpeacelegacy.org
                    </a>
                  </span>
                </div>
                <motion.button
                  whileHover={{ scale: 1.02 }}
                  whileTap={{ scale: 0.97 }}
                  onClick={() => { setStep('form'); onClose(); }}
                  className="mt-2 w-full h-11 rounded-2xl liquid-glass-dark-btn text-white text-xs font-bold shadow-md"
                >
                  Return to Website
                </motion.button>
              </div>
            )}
          </motion.div>
        </motion.div>
      )}
    </AnimatePresence>
  );
};
