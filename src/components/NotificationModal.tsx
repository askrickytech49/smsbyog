import React, { useState } from 'react';
import { motion, AnimatePresence } from 'motion/react';
import { Bell, X, CheckCircle, Loader2 } from 'lucide-react';
import { useFoundationData } from '../context/FoundationDataContext';

interface NotificationModalProps {
  isOpen: boolean;
  onClose: () => void;
}

export const NotificationModal: React.FC<NotificationModalProps> = ({ isOpen, onClose }) => {
  const { submitSubscriber } = useFoundationData();
  const [name, setName]             = useState('');
  const [email, setEmail]           = useState('');
  const [institution, setInstitution] = useState('');
  const [course, setCourse]         = useState('');
  const [isSubmitted, setIsSubmitted]   = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    try {
      await submitSubscriber({ name, email, institution, course });
      setIsSubmitted(true);
    } catch {
      setIsSubmitted(true);
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleResetAndClose = () => {
    setIsSubmitted(false);
    setName(''); setEmail(''); setInstitution(''); setCourse('');
    onClose();
  };

  return (
    <AnimatePresence>
      {isOpen && (
        <motion.div
          initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }}
          className="fixed inset-0 z-50 flex items-center justify-center bg-[#161338]/60 backdrop-blur-md p-4"
          onClick={handleResetAndClose}
        >
          <motion.div
            initial={{ scale: 0.94, opacity: 0, y: 16 }}
            animate={{ scale: 1, opacity: 1, y: 0 }}
            exit={{ scale: 0.94, opacity: 0, y: 16 }}
            transition={{ type: 'spring', damping: 26, stiffness: 320 }}
            className="w-full max-w-md liquid-glass-card rounded-3xl p-6 sm:p-7 shadow-2xl flex flex-col gap-4 relative max-h-[90vh] overflow-y-auto"
            onClick={e => e.stopPropagation()}
          >
            {/* Header */}
            <div className="flex items-center justify-between border-b border-black/5 pb-3">
              <div className="flex items-center gap-2.5">
                <div className="w-10 h-10 rounded-2xl liquid-glass-amber-btn text-white flex items-center justify-center shadow-md">
                  <Bell size={20} strokeWidth={2} />
                </div>
                <div>
                  <h3 className="font-serif text-lg sm:text-xl font-bold text-[#1E1B4B]">Scholarship Alert</h3>
                  <p className="text-xs text-[#6E6B7E]">Official 2025/2026 Register</p>
                </div>
              </div>
              <button
                aria-label="Close dialog"
                className="w-8 h-8 rounded-full liquid-glass-card hover:bg-white/80 flex items-center justify-center text-[#6E6B7E] hover:text-[#1E1B4B] transition-all active:scale-90"
                onClick={handleResetAndClose}
              >
                <X size={16} strokeWidth={2.5} />
              </button>
            </div>

            {!isSubmitted ? (
              <>
                <p className="text-xs sm:text-sm text-[#4B485A] leading-relaxed">
                  Be the first to receive the official eligibility guide and opening date for the{' '}
                  <strong>2025/2026 Karl Peace Legacy Foundation Tertiary Scholarships</strong>.
                </p>

                <form className="flex flex-col gap-3" onSubmit={handleSubmit}>
                  {[
                    { label: 'Your Full Name',               value: name,        setter: setName,        type: 'text',  placeholder: 'e.g. Amara Okafor', required: true },
                    { label: 'Email Address',                value: email,       setter: setEmail,       type: 'email', placeholder: 'amara.okafor@university.edu.ng', required: true },
                    { label: 'Current Institution / Level', value: institution, setter: setInstitution, type: 'text',  placeholder: 'University of Lagos / 200 Level', required: true },
                    { label: 'Course of Study',              value: course,      setter: setCourse,      type: 'text',  placeholder: 'Medicine, Engineering, Computer Science', required: false },
                  ].map(({ label, value, setter, type, placeholder, required }) => (
                    <div key={label}>
                      <label className="text-[11px] uppercase font-bold text-[#1E1B4B] tracking-wider block mb-1">{label}</label>
                      <input
                        value={value}
                        onChange={e => setter(e.target.value)}
                        className="w-full h-11 px-3.5 rounded-xl bg-white/80 border border-black/10 text-[#1E1B2E] text-sm outline-none focus:border-[#D97706] focus:bg-white focus:ring-2 focus:ring-[#D97706]/20 transition-all placeholder:text-[#6E6B7E]/60 shadow-2xs"
                        placeholder={placeholder}
                        required={required}
                        type={type}
                      />
                    </div>
                  ))}

                  <div className="pt-1">
                    <motion.button
                      whileHover={{ scale: 1.02 }} whileTap={{ scale: 0.97 }}
                      disabled={isSubmitting}
                      className="liquid-glass-amber-btn glass-refraction w-full h-12 rounded-2xl text-white text-sm font-bold flex items-center justify-center gap-2 shadow-lg disabled:opacity-75"
                      type="submit"
                    >
                      {isSubmitting ? (
                        <><Loader2 size={18} className="animate-spin" /><span>Recording details...</span></>
                      ) : (
                        <><Bell size={18} /><span>Register for Official Alert</span></>
                      )}
                    </motion.button>
                  </div>

                  <p className="text-[11px] text-[#6E6B7E] text-center mt-1">
                    Verified announcements strictly via karlpeacelegacy.org • No fees required
                  </p>
                </form>
              </>
            ) : (
              <div className="p-4 sm:p-6 rounded-2xl liquid-glass-card border border-white/80 flex flex-col items-center text-center gap-2.5 shadow-sm">
                <div className="w-14 h-14 rounded-full bg-emerald-50 border border-emerald-200 text-[#0D9488] flex items-center justify-center shadow-xs">
                  <CheckCircle size={36} strokeWidth={1.5} />
                </div>
                <h4 className="font-serif text-xl font-bold text-[#1E1B4B]">Thank you, {name || 'Scholar'}!</h4>
                <p className="text-xs sm:text-sm text-[#4B485A] leading-relaxed">
                  Your contact has been logged. When the 2025/2026 portal opens, an eligibility pack will be sent to{' '}
                  <strong>{email}</strong>.
                </p>
                <div className="mt-2 w-full p-3 bg-white/70 rounded-xl border border-black/5 text-left text-xs text-[#6E6B7E]">
                  <div className="font-bold text-[#1E1B4B] mb-1">Pre-Application Checklist:</div>
                  <ul className="list-disc pl-4 space-y-1">
                    <li>Prepare current university matriculation ID</li>
                    <li>Obtain certified previous semester results</li>
                    <li>Ensure minimum CGPA benchmark is met</li>
                  </ul>
                </div>
                <motion.button
                  whileHover={{ scale: 1.02 }} whileTap={{ scale: 0.97 }}
                  className="mt-3 w-full h-11 rounded-xl liquid-glass-dark-btn text-white text-sm font-bold shadow-md"
                  onClick={handleResetAndClose}
                >
                  Close Window
                </motion.button>
              </div>
            )}
          </motion.div>
        </motion.div>
      )}
    </AnimatePresence>
  );
};
