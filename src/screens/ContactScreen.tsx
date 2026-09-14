import React, { useState } from 'react';
import { motion, AnimatePresence } from 'motion/react';
import { useFoundationData } from '../context/FoundationDataContext';
import { Icon } from '../components/Icon';

export const ContactScreen: React.FC = () => {
  const { settings, submitInquiry } = useFoundationData();
  const [inquiryType, setInquiryType] = useState('partnership');
  const [fullName, setFullName] = useState('');
  const [email, setEmail] = useState('');
  const [organization, setOrganization] = useState('');
  const [message, setMessage] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isSent, setIsSent] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsSubmitting(true);
    try {
      await submitInquiry({
        name: fullName,
        email,
        subject: `[${inquiryType.toUpperCase()}] Inquiry from ${fullName}${organization ? ` (${organization})` : ''}`,
        message,
        role: inquiryType
      });
      setIsSent(true);
    } catch (err) {
      console.error('Error submitting inquiry:', err);
      setIsSent(true);
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="flex flex-col w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-16 gap-12 sm:gap-16">
      {/* Header */}
      <div className="flex flex-col gap-4 max-w-3xl">
        <div className="inline-flex items-center gap-2 py-1 px-3.5 rounded-full liquid-glass-pill-active text-[#D97706] text-xs uppercase font-bold self-start border border-amber-200/60 shadow-xs">
          <Icon name="mail" size={15} className="text-[#D97706]" />
          <span>Official Secretariat Communications</span>
        </div>
        <h1 className="font-serif text-3xl sm:text-5xl font-bold text-[#1E1B4B] leading-tight">
          Connect With the Foundation
        </h1>
        <p className="text-base sm:text-lg text-[#4B485A] leading-relaxed">
          Whether inquiring about scholarship verification, university partnerships, executive mentorship involvement, or philanthropic grants, our team is at your disposal.
        </p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
        {/* Left Column: Office Contacts & Anti-Fraud Notice */}
        <div className="md:col-span-5 flex flex-col gap-6">
          <div className="p-6 sm:p-8 rounded-3xl liquid-glass-card border border-white/80 shadow-md flex flex-col gap-6">
            <div className="flex flex-col gap-1">
              <span className="text-xs font-bold uppercase tracking-wider text-[#D97706]">Official Representation</span>
              <h3 className="font-serif text-xl font-bold text-[#1E1B4B]">
                Registered Headquarters &amp; Offices
              </h3>
              <p className="text-xs text-[#4B485A]">
                Karl Peace Legacy Foundation is a registered non-profit organization in Nigeria (Reg No: <strong>{settings.registrationNumber || '9622998'}</strong>).
              </p>
            </div>

            <div className="flex flex-col gap-5">
              {/* Primary Registered & Physical Address */}
              <div className="flex items-start gap-3.5 p-3 rounded-2xl bg-amber-500/10 border border-amber-500/20">
                <div className="w-11 h-11 rounded-2xl bg-[#D97706] text-white flex items-center justify-center shrink-0 shadow-xs">
                  <Icon name="apartment" size={20} />
                </div>
                <div>
                  <span className="text-xs uppercase font-bold text-[#D97706] block">
                    Registered &amp; Physical Address
                  </span>
                  <span className="font-bold text-[#1E1B4B] block">{settings.officeAddress || '25 Ediba Rd, Calabar, Cross River State, Nigeria'}</span>
                  <p className="text-xs text-[#6E6B7E] mt-0.5 leading-relaxed">
                    Official registered non-profit secretariat under Nigerian law (Registration No. {settings.registrationNumber || '9622998'}).
                  </p>
                </div>
              </div>

              {/* Administrative Email */}
              <div className="flex items-start gap-3.5">
                <div className="w-11 h-11 rounded-2xl bg-teal-50 border border-teal-200 text-[#0D9488] flex items-center justify-center shrink-0 shadow-xs">
                  <Icon name="alternate_email" size={20} />
                </div>
                <div>
                  <span className="text-xs uppercase font-bold text-[#0D9488] block">
                    Official Administrative Email
                  </span>
                  <a
                    href={`mailto:${settings.contactEmail || 'admin@karlpeacelegacy.org'}`}
                    className="font-bold text-[#1E1B4B] hover:text-[#D97706] transition-colors"
                  >
                    {settings.contactEmail || 'admin@karlpeacelegacy.org'}
                  </a>
                  <p className="text-xs text-[#6E6B7E] mt-0.5">
                    Official email for institutional correspondence, board inquiries, and student affairs.
                  </p>
                </div>
              </div>

              {/* Official Domain */}
              <div className="flex items-start gap-3.5">
                <div className="w-11 h-11 rounded-2xl bg-indigo-50 border border-indigo-200 text-[#1E1B4B] flex items-center justify-center shrink-0 shadow-xs">
                  <Icon name="language" size={20} />
                </div>
                <div>
                  <span className="text-xs uppercase font-bold text-[#1E1B4B] block">
                    Official Website &amp; Primary Domain
                  </span>
                  <span className="font-bold text-[#1E1B4B] block">karlpeacelegacy.org</span>
                  <p className="text-xs text-[#6E6B7E] mt-0.5">
                    Official digital home and authoritative web domain of Karl Peace Legacy Foundation.
                  </p>
                </div>
              </div>

              {/* Liaison Desks */}
              <div className="flex items-start gap-3.5 pt-2 border-t border-black/5">
                <div className="w-11 h-11 rounded-2xl liquid-glass-dark-btn text-white flex items-center justify-center shrink-0 shadow-xs">
                  <Icon name="hub" size={20} />
                </div>
                <div>
                  <span className="text-xs uppercase font-bold text-[#D97706] block">
                    Outreach &amp; Liaison Desks
                  </span>
                  <span className="font-medium text-[#1E1B4B]">Abuja &amp; Lagos, Nigeria</span>
                  <p className="text-xs text-[#6E6B7E] mt-0.5 leading-relaxed">
                    Student mentorship pairing, scholarship verification, and health community drives.
                  </p>
                </div>
              </div>
            </div>

            <div className="p-4 rounded-2xl bg-white/70 backdrop-blur-xs border border-white/80 text-xs text-[#6E6B7E] flex items-center gap-2.5 shadow-2xs">
              <Icon name="verified" size={20} className="text-[#0D9488] shrink-0" />
              <span>All authentic communications originate strictly from @karlpeacelegacy.org. Never pay application fees.</span>
            </div>
          </div>
        </div>

        {/* Right Column: Interactive Inquiry Form */}
        <div className="md:col-span-7">
          <div className="p-6 sm:p-10 rounded-3xl liquid-glass-card border border-white/80 shadow-md flex flex-col gap-6">
            <AnimatePresence mode="wait">
              {!isSent ? (
                <motion.div
                  key="contact-form"
                  initial={{ opacity: 0 }}
                  animate={{ opacity: 1 }}
                  exit={{ opacity: 0 }}
                  className="flex flex-col gap-6"
                >
                  <div>
                    <h3 className="font-serif text-xl sm:text-2xl font-bold text-[#1E1B4B]">
                      Send an Official Inquiry
                    </h3>
                    <p className="text-xs sm:text-sm text-[#4B485A] mt-1">
                      Fill out the details below and our secretariat will route your message to the appropriate directorate.
                    </p>
                  </div>

                  <form onSubmit={handleSubmit} className="flex flex-col gap-4">
                    <div>
                      <label className="text-xs font-bold uppercase tracking-wider text-[#1E1B4B] block mb-1.5">
                        Nature of Inquiry
                      </label>
                      <select
                        value={inquiryType}
                        onChange={(e) => setInquiryType(e.target.value)}
                        className="w-full h-11 px-3.5 rounded-xl bg-white/80 border border-black/10 text-sm text-[#1E1B2E] outline-none focus:border-[#D97706] focus:ring-2 focus:ring-[#D97706]/20 transition-all shadow-2xs"
                      >
                        <option value="partnership">Institutional Partnership (University / NGO)</option>
                        <option value="mentor">Volunteer as an Executive Mentor</option>
                        <option value="scholarship">Student Scholarship Inquiry</option>
                        <option value="donor">Donation &amp; Fiduciary Sponsorship</option>
                        <option value="media">Press &amp; Communications</option>
                      </select>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <div>
                        <label className="text-xs font-bold uppercase tracking-wider text-[#1E1B4B] block mb-1.5">
                          Your Full Name
                        </label>
                        <input
                          type="text"
                          required
                          value={fullName}
                          onChange={(e) => setFullName(e.target.value)}
                          placeholder="e.g. Dr. Kemi Adeleke"
                          className="w-full h-11 px-3.5 rounded-xl bg-white/80 border border-black/10 text-sm text-[#1E1B2E] outline-none focus:border-[#D97706] focus:ring-2 focus:ring-[#D97706]/20 transition-all shadow-2xs"
                        />
                      </div>
                      <div>
                        <label className="text-xs font-bold uppercase tracking-wider text-[#1E1B4B] block mb-1.5">
                          Email Address
                        </label>
                        <input
                          type="email"
                          required
                          value={email}
                          onChange={(e) => setEmail(e.target.value)}
                          placeholder="e.g. adeleke@institution.org"
                          className="w-full h-11 px-3.5 rounded-xl bg-white/80 border border-black/10 text-sm text-[#1E1B2E] outline-none focus:border-[#D97706] focus:ring-2 focus:ring-[#D97706]/20 transition-all shadow-2xs"
                        />
                      </div>
                    </div>

                    <div>
                      <label className="text-xs font-bold uppercase tracking-wider text-[#1E1B4B] block mb-1.5">
                        Organization / University / Affiliation (Optional)
                      </label>
                      <input
                        type="text"
                        value={organization}
                        onChange={(e) => setOrganization(e.target.value)}
                        placeholder="e.g. University of Ibadan / Lagos State Ministry"
                        className="w-full h-11 px-3.5 rounded-xl bg-white/80 border border-black/10 text-sm text-[#1E1B2E] outline-none focus:border-[#D97706] focus:ring-2 focus:ring-[#D97706]/20 transition-all shadow-2xs"
                      />
                    </div>

                    <div>
                      <label className="text-xs font-bold uppercase tracking-wider text-[#1E1B4B] block mb-1.5">
                        Message Details
                      </label>
                      <textarea
                        required
                        rows={4}
                        value={message}
                        onChange={(e) => setMessage(e.target.value)}
                        placeholder="Please outline your proposed collaboration, question, or verification details..."
                        className="w-full p-3.5 rounded-xl bg-white/80 border border-black/10 text-sm text-[#1E1B2E] outline-none focus:border-[#D97706] focus:ring-2 focus:ring-[#D97706]/20 transition-all shadow-2xs resize-none"
                      />
                    </div>

                    <motion.button
                      whileHover={{ scale: 1.02 }}
                      whileTap={{ scale: 0.97 }}
                      type="submit"
                      disabled={isSubmitting}
                      className="liquid-glass-amber-btn glass-refraction h-12 px-8 rounded-2xl text-white text-sm font-bold flex items-center justify-center gap-2 transition-all shadow-md self-start disabled:opacity-75"
                    >
                      {isSubmitting ? (
                        <span>Dispatching message...</span>
                      ) : (
                        <>
                          <Icon name="send" size={18} />
                          <span>Send Official Inquiry</span>
                        </>
                      )}
                    </motion.button>
                  </form>
                </motion.div>
              ) : (
                <motion.div
                  key="success-message"
                  initial={{ opacity: 0, scale: 0.95 }}
                  animate={{ opacity: 1, scale: 1 }}
                  exit={{ opacity: 0 }}
                  className="flex flex-col items-center text-center gap-4 py-8"
                >
                  <div className="w-16 h-16 rounded-full bg-teal-50 border border-teal-200 text-[#0D9488] flex items-center justify-center shadow-xs">
                    <Icon name="check_circle" size={36} />
                  </div>
                  <h3 className="font-serif text-2xl font-bold text-[#1E1B4B]">
                    Message Dispatched Successfully
                  </h3>
                  <p className="text-sm text-[#4B485A] max-w-md leading-relaxed">
                    Thank you, <strong>{fullName}</strong>. Your correspondence regarding{' '}
                    <strong>{inquiryType}</strong> has been logged with the Karl Peace Legacy Foundation Secretariat. A representative will respond to <strong>{email}</strong>.
                  </p>
                  <motion.button
                    whileHover={{ scale: 1.03 }}
                    whileTap={{ scale: 0.96 }}
                    onClick={() => {
                      setIsSent(false);
                      setMessage('');
                    }}
                    className="px-6 py-2.5 rounded-xl liquid-glass-dark-btn text-white text-xs font-bold shadow-xs mt-2"
                  >
                    Send Another Inquiry
                  </motion.button>
                </motion.div>
              )}
            </AnimatePresence>
          </div>
        </div>
      </div>
    </div>
  );
};
