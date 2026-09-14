import React, { useState } from 'react';
import { motion, AnimatePresence } from 'motion/react';
import { useFoundationData } from '../context/FoundationDataContext';
import { Icon } from '../components/Icon';

interface ScholarshipsScreenProps {
  onOpenNotifyModal: () => void;
  onNavigate?: (screen: any) => void;
}

export const ScholarshipsScreen: React.FC<ScholarshipsScreenProps> = ({
  onOpenNotifyModal,
  onNavigate,
}) => {
  const { faqs } = useFoundationData();
  // Eligibility Checker State
  const [institutionType, setInstitutionType] = useState('federal-university');
  const [academicLevel, setAcademicLevel] = useState('200');
  const [cgpaRange, setCgpaRange] = useState('3.5-4.49');
  const [isFullTime, setIsFullTime] = useState(true);
  const [isCitizen, setIsCitizen] = useState(true);
  const [checkResult, setCheckResult] = useState<{
    status: 'eligible' | 'review' | 'ineligible';
    title: string;
    details: string;
    tuitionCoverage: string;
    stipendCoverage: string;
    mentorTier: string;
  } | null>({
    status: 'eligible',
    title: 'Highly Competitive Candidate Profile',
    details: 'Based on your parameters, you fulfill all pre-screening benchmarks for full tuition relief, academic book allowances, and executive mentorship pairing.',
    tuitionCoverage: '100% Institutional Tuition',
    stipendCoverage: '₦150,000 / Academic Session',
    mentorTier: 'Paired Executive Scholar Mentor',
  });

  // FAQ Accordion State & Search
  const [openFaqIndex, setOpenFaqIndex] = useState<number | null>(0);
  const [faqCategory, setFaqCategory] = useState<string>('all');

  const handleCheckEligibility = (e: React.FormEvent) => {
    e.preventDefault();
    if (!isCitizen || !isFullTime) {
      setCheckResult({
        status: 'ineligible',
        title: 'Basic Criteria Not Met',
        details: 'The Karl Peace Legacy Tertiary Scholarship strictly requires verified Nigerian citizenship and full-time undergraduate matriculation in an accredited tertiary institution.',
        tuitionCoverage: 'Not Applicable',
        stipendCoverage: 'Not Applicable',
        mentorTier: 'Not Applicable',
      });
      return;
    }

    if (cgpaRange === 'below-2.5') {
      setCheckResult({
        status: 'ineligible',
        title: 'Academic Standing Threshold',
        details: 'Applicants must maintain a minimum academic standard of 3.0 CGPA on a 5.0 scale (or 2.5 on a 4.0 scale). We encourage you to focus on academic recovery before applying.',
        tuitionCoverage: 'Not Eligible',
        stipendCoverage: 'Not Eligible',
        mentorTier: 'Academic Counseling Only',
      });
      return;
    }

    if (cgpaRange === '4.5-5.0' || cgpaRange === '3.5-4.49') {
      setCheckResult({
        status: 'eligible',
        title: 'Highly Competitive Candidate Profile',
        details: 'Based on your parameters, you fulfill all pre-screening benchmarks for full tuition relief, academic book allowances, and executive mentorship pairing.',
        tuitionCoverage: '100% Institutional Tuition',
        stipendCoverage: '₦150,000 / Academic Session',
        mentorTier: 'Paired Executive Scholar Mentor',
      });
    } else {
      setCheckResult({
        status: 'review',
        title: 'Eligible for Second-Tier Consideration',
        details: 'You meet the minimum benchmark threshold. Your application will be evaluated alongside your personal statement of financial need and community leadership credentials.',
        tuitionCoverage: 'Partial / Need-Based Tuition',
        stipendCoverage: '₦75,000 Book Allowance',
        mentorTier: 'Group Mentorship Cohort',
      });
    }
  };

  const filteredFaqs = faqs.filter((faq) => {
    if (faqCategory === 'all') return true;
    if (faqCategory === 'eligibility') return faq.question.toLowerCase().includes('eligible') || faq.question.toLowerCase().includes('cgpa') || faq.question.toLowerCase().includes('who');
    if (faqCategory === 'awards') return faq.question.toLowerCase().includes('cover') || faq.question.toLowerCase().includes('award') || faq.question.toLowerCase().includes('fee');
    if (faqCategory === 'dates') return faq.question.toLowerCase().includes('deadline') || faq.question.toLowerCase().includes('when') || faq.question.toLowerCase().includes('open');
    return true;
  });

  return (
    <div className="flex flex-col w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-16 gap-12 sm:gap-16">
      {/* Header Banner */}
      <div className="flex flex-col gap-4 max-w-3xl">
        <div className="inline-flex items-center gap-2 py-1 px-3.5 rounded-full liquid-glass-pill-active text-[#904d00] text-xs uppercase font-bold self-start border border-amber-200/60 shadow-xs">
          <Icon name="workspace_premium" size={15} />
          <span>Tertiary Scholarships Division</span>
        </div>
        <h1 className="font-serif text-3xl sm:text-5xl font-bold text-[#1E1B4B] leading-tight">
          Invest in Your Academic Journey
        </h1>
        <p className="text-base sm:text-lg text-[#4B485A] leading-relaxed">
          The Karl Peace Legacy Scholarship Program provides verified tuition assistance, textbook stipends, and dedicated executive mentorship to high-potential Nigerian undergraduates facing economic constraints.
        </p>
      </div>

      {/* Priority Notice Callout */}
      <div className="p-6 sm:p-8 rounded-3xl liquid-glass-card border border-white/80 shadow-md flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative overflow-hidden">
        <div className="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-[#D97706] via-[#F59E0B] to-[#D97706]" />
        <div className="flex items-start gap-4">
          <div className="w-12 h-12 rounded-2xl liquid-glass-amber-btn text-white flex items-center justify-center shrink-0 shadow-md">
            <Icon name="campaign" size={26} />
          </div>
          <div>
            <span className="text-xs uppercase font-bold tracking-wider text-[#904d00] block">
              2025/2026 Academic Cycle
            </span>
            <h3 className="font-serif text-xl sm:text-2xl font-bold text-[#1E1B4B]">
              Application Window Opening Soon
            </h3>
            <p className="text-xs sm:text-sm text-[#4B485A] mt-1 max-w-2xl">
              The official portal for student intake will open across accredited Nigerian tertiary institutions. Students are strictly advised not to submit credentials through unofficial third-party agents.
            </p>
          </div>
        </div>
        <motion.button
          whileHover={{ scale: 1.03 }}
          whileTap={{ scale: 0.96 }}
          onClick={onOpenNotifyModal}
          className="liquid-glass-amber-btn glass-refraction h-12 px-6 rounded-2xl text-white text-xs sm:text-sm font-bold flex items-center justify-center gap-2 shrink-0 shadow-lg"
        >
          <Icon name="notifications_active" size={18} />
          <span>Join Priority Notice List</span>
        </motion.button>
        {onNavigate && (
          <motion.button
            whileHover={{ scale: 1.03 }}
            whileTap={{ scale: 0.96 }}
            onClick={() => onNavigate('apply')}
            className="h-12 px-6 rounded-2xl bg-green-600 hover:bg-green-700 text-white text-xs sm:text-sm font-bold flex items-center justify-center gap-2 shrink-0 shadow-lg transition-colors"
          >
            <Icon name="edit_document" size={18} />
            <span>Apply Now</span>
          </motion.button>
        )}
      </div>

      {/* Interactive Eligibility Checker */}
      <section className="liquid-glass-card rounded-3xl p-6 sm:p-10 border border-white/80 shadow-lg flex flex-col gap-6 relative">
        <div className="flex flex-col gap-1">
          <span className="text-xs uppercase tracking-wider text-[#D97706] font-bold">
            Interactive Diagnostic Engine
          </span>
          <h2 className="font-serif text-2xl sm:text-3xl font-bold text-[#1E1B4B]">
            Scholarship Eligibility Calculator
          </h2>
          <p className="text-xs sm:text-sm text-[#4B485A]">
            Select your parameters to instantly simulate qualification status, eligible grant amounts, and mentorship support.
          </p>
        </div>

        <form onSubmit={handleCheckEligibility} className="grid grid-cols-1 md:grid-cols-2 gap-5">
          {/* Institution Type */}
          <div>
            <label className="text-xs font-bold uppercase tracking-wider text-[#1E1B4B] block mb-1.5">
              Institution Classification
            </label>
            <select
              value={institutionType}
              onChange={(e) => setInstitutionType(e.target.value)}
              className="w-full h-11 px-3.5 rounded-xl bg-white/80 border border-black/10 text-sm text-[#1E1B2E] outline-none focus:border-[#D97706] focus:ring-2 focus:ring-[#D97706]/20 transition-all shadow-2xs"
            >
              <option value="federal-university">Federal University (Nigeria)</option>
              <option value="state-university">State University (Nigeria)</option>
              <option value="federal-polytechnic">Federal / State Polytechnic</option>
              <option value="college-education">College of Education</option>
              <option value="private-university">Accredited Private University</option>
            </select>
          </div>

          {/* Academic Level */}
          <div>
            <label className="text-xs font-bold uppercase tracking-wider text-[#1E1B4B] block mb-1.5">
              Current Academic Level
            </label>
            <select
              value={academicLevel}
              onChange={(e) => setAcademicLevel(e.target.value)}
              className="w-full h-11 px-3.5 rounded-xl bg-white/80 border border-black/10 text-sm text-[#1E1B2E] outline-none focus:border-[#D97706] focus:ring-2 focus:ring-[#D97706]/20 transition-all shadow-2xs"
            >
              <option value="100">100 Level / Fresh Undergraduate</option>
              <option value="200">200 Level</option>
              <option value="300">300 Level</option>
              <option value="400">400 Level</option>
              <option value="500">500 Level (Medical / Engineering / Law)</option>
            </select>
          </div>

          {/* CGPA Range */}
          <div>
            <label className="text-xs font-bold uppercase tracking-wider text-[#1E1B4B] block mb-1.5">
              Cumulative Grade Point Average (CGPA)
            </label>
            <select
              value={cgpaRange}
              onChange={(e) => setCgpaRange(e.target.value)}
              className="w-full h-11 px-3.5 rounded-xl bg-white/80 border border-black/10 text-sm text-[#1E1B2E] outline-none focus:border-[#D97706] focus:ring-2 focus:ring-[#D97706]/20 transition-all shadow-2xs"
            >
              <option value="4.5-5.0">First Class Honors (4.50 – 5.00)</option>
              <option value="3.5-4.49">Second Class Upper (3.50 – 4.49)</option>
              <option value="3.0-3.49">Second Class Lower (3.00 – 3.49)</option>
              <option value="below-2.5">Below 3.00 (Academic Probation/Lower)</option>
            </select>
          </div>

          {/* Toggles */}
          <div className="flex flex-col justify-end gap-2.5">
            <label className="flex items-center gap-2.5 cursor-pointer text-xs sm:text-sm font-medium text-[#1E1B2E]">
              <input
                type="checkbox"
                checked={isCitizen}
                onChange={(e) => setIsCitizen(e.target.checked)}
                className="w-4 h-4 rounded text-[#D97706] focus:ring-[#D97706]"
              />
              <span>I am a verified citizen of the Federal Republic of Nigeria</span>
            </label>
            <label className="flex items-center gap-2.5 cursor-pointer text-xs sm:text-sm font-medium text-[#1E1B2E]">
              <input
                type="checkbox"
                checked={isFullTime}
                onChange={(e) => setIsFullTime(e.target.checked)}
                className="w-4 h-4 rounded text-[#D97706] focus:ring-[#D97706]"
              />
              <span>I am currently enrolled in a full-time regular degree program</span>
            </label>
          </div>

          <div className="md:col-span-2 pt-2">
            <motion.button
              whileHover={{ scale: 1.02 }}
              whileTap={{ scale: 0.97 }}
              type="submit"
              className="liquid-glass-dark-btn glass-refraction w-full sm:w-auto h-12 px-8 rounded-2xl text-white text-sm font-bold flex items-center justify-center gap-2 shadow-md"
            >
              <Icon name="calculate" size={18} className="text-[#F59E0B]" />
              <span>Evaluate My Eligibility</span>
            </motion.button>
          </div>
        </form>

        {/* Checker Result Display */}
        {checkResult && (
          <motion.div
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            className={`p-6 rounded-2xl border flex flex-col gap-4 shadow-sm ${
              checkResult.status === 'eligible'
                ? 'bg-emerald-50/80 border-emerald-200/80 text-emerald-900'
                : checkResult.status === 'review'
                ? 'bg-amber-50/80 border-amber-200/80 text-[#904d00]'
                : 'bg-red-50/80 border-red-200/80 text-red-900'
            }`}
          >
            <div className="flex items-center gap-3">
              <Icon name={checkResult.status === 'eligible' ? 'verified' : checkResult.status === 'review' ? 'help' : 'cancel'} size={28} />
              <div>
                <h4 className="font-serif text-lg sm:text-xl font-bold">
                  {checkResult.title}
                </h4>
                <p className="text-xs sm:text-sm opacity-90 leading-relaxed mt-0.5">
                  {checkResult.details}
                </p>
              </div>
            </div>

            {/* Estimated Grant Breakdown Pill Grid */}
            <div className="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-2 border-t border-black/10">
              <div className="p-3 rounded-xl bg-white/70 backdrop-blur-xs flex flex-col">
                <span className="text-[11px] uppercase font-bold text-[#6E6B7E]">Tuition Support:</span>
                <span className="font-bold text-sm text-[#1E1B4B]">{checkResult.tuitionCoverage}</span>
              </div>
              <div className="p-3 rounded-xl bg-white/70 backdrop-blur-xs flex flex-col">
                <span className="text-[11px] uppercase font-bold text-[#6E6B7E]">Stipend &amp; Books:</span>
                <span className="font-bold text-sm text-[#1E1B4B]">{checkResult.stipendCoverage}</span>
              </div>
              <div className="p-3 rounded-xl bg-white/70 backdrop-blur-xs flex flex-col">
                <span className="text-[11px] uppercase font-bold text-[#6E6B7E]">Mentorship Level:</span>
                <span className="font-bold text-sm text-[#1E1B4B]">{checkResult.mentorTier}</span>
              </div>
            </div>

            {checkResult.status !== 'ineligible' && (
              <div className="pt-2">
                <motion.button
                  whileHover={{ scale: 1.02 }}
                  whileTap={{ scale: 0.97 }}
                  onClick={onOpenNotifyModal}
                  className="px-5 py-2.5 rounded-xl liquid-glass-amber-btn text-white text-xs sm:text-sm font-bold shadow-md inline-flex items-center gap-2"
                >
                  <Icon name="notifications_active" size={16} />
                  <span>Register on Priority Applicant List</span>
                </motion.button>
              </div>
            )}
          </motion.div>
        )}
      </section>

      {/* 4-Step Application Roadmap */}
      <section className="flex flex-col gap-6">
        <div className="flex flex-col gap-1 max-w-2xl">
          <span className="text-xs uppercase tracking-wider text-[#D97706] font-bold">
            Transparent Process
          </span>
          <h2 className="font-serif text-2xl sm:text-3xl font-bold text-[#1E1B4B]">
            How the Scholarship Application Works
          </h2>
          <p className="text-sm text-[#4B485A]">
            A transparent, four-phase assessment ensuring awards reach genuinely deserving scholars.
          </p>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
          <div className="p-6 rounded-2xl liquid-glass-card card-hover-lift border border-white/80 shadow-xs flex flex-col gap-2 relative">
            <span className="w-8 h-8 rounded-xl liquid-glass-dark-btn text-white text-xs font-bold flex items-center justify-center mb-1 shadow-xs">
              01
            </span>
            <h4 className="font-serif text-base font-bold text-[#1E1B4B]">
              Online Application
            </h4>
            <p className="text-xs text-[#4B485A] leading-relaxed">
              Submit personal biographical data, institution matriculation details, and course credentials strictly through karlpeacelegacy.org.
            </p>
          </div>

          <div className="p-6 rounded-2xl liquid-glass-card card-hover-lift border border-white/80 shadow-xs flex flex-col gap-2 relative">
            <span className="w-8 h-8 rounded-xl liquid-glass-dark-btn text-white text-xs font-bold flex items-center justify-center mb-1 shadow-xs">
              02
            </span>
            <h4 className="font-serif text-base font-bold text-[#1E1B4B]">
              Academic Verification
            </h4>
            <p className="text-xs text-[#4B485A] leading-relaxed">
              Official verification of enrollment and CGPA statements directly with university student affairs or departmental registrars.
            </p>
          </div>

          <div className="p-6 rounded-2xl liquid-glass-card card-hover-lift border border-white/80 shadow-xs flex flex-col gap-2 relative">
            <span className="w-8 h-8 rounded-xl liquid-glass-dark-btn text-white text-xs font-bold flex items-center justify-center mb-1 shadow-xs">
              03
            </span>
            <h4 className="font-serif text-base font-bold text-[#1E1B4B]">
              Committee Review
            </h4>
            <p className="text-xs text-[#4B485A] leading-relaxed">
              Evaluation of applicant financial need, leadership commitment, and personal statements by the Foundation Scholarship Board.
            </p>
          </div>

          <div className="p-6 rounded-2xl liquid-glass-card card-hover-lift border border-white/80 shadow-xs flex flex-col gap-2 relative">
            <span className="w-8 h-8 rounded-xl liquid-glass-amber-btn text-white text-xs font-bold flex items-center justify-center mb-1 shadow-xs">
              04
            </span>
            <h4 className="font-serif text-base font-bold text-[#1E1B4B]">
              Disbursement &amp; Mentor
            </h4>
            <p className="text-xs text-[#4B485A] leading-relaxed">
              Direct institutional tuition payment, textbook grant issuance, and assignment to a dedicated professional mentor.
            </p>
          </div>
        </div>
      </section>

      {/* Required Documentation Checklist */}
      <section className="p-8 sm:p-10 rounded-3xl liquid-glass-dock border border-white/80 shadow-md flex flex-col lg:flex-row items-start justify-between gap-8">
        <div className="flex flex-col gap-3 max-w-xl">
          <span className="text-xs uppercase tracking-wider text-[#D97706] font-bold">
            Document Preparation Guide
          </span>
          <h3 className="font-serif text-2xl font-bold text-[#1E1B4B]">
            Required Documents for Submission
          </h3>
          <p className="text-xs sm:text-sm text-[#4B485A] leading-relaxed">
            Prospective applicants are strongly advised to obtain and digitize these official credentials prior to the application portal opening:
          </p>
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-2">
            {[
              'Official JAMB & University Admission Letters',
              'Valid Student Identity Card',
              'Certified Result Slip / Transcript of previous semester',
              'Local Government Area (LGA) Identification Letter',
              'Passport Photograph on plain white background',
              'Personal Statement of Purpose (500 words)',
            ].map((doc, i) => (
              <div
                key={i}
                className="flex items-center gap-2 p-2.5 rounded-xl bg-white/80 border border-white/90 text-xs font-medium text-[#1E1B2E] shadow-2xs"
              >
                <Icon name="check_circle" size={17} className="text-[#0D9488]" />
                <span>{doc}</span>
              </div>
            ))}
          </div>
        </div>

        <div className="w-full lg:w-80 p-6 rounded-2xl liquid-glass-card border border-white/80 shadow-sm flex flex-col gap-3 shrink-0">
          <Icon name="verified_user" size={32} className="text-[#D97706]" />
          <h4 className="font-serif text-base font-bold text-[#1E1B4B]">
            Zero-Tolerance Fraud Advisory
          </h4>
          <p className="text-xs text-[#4B485A] leading-relaxed">
            The Karl Peace Legacy Foundation NEVER charges fees at any stage. All authentic updates originate solely from <strong>contact@karlpeacelegacy.org</strong>.
          </p>
          <motion.button
            whileHover={{ scale: 1.02 }}
            whileTap={{ scale: 0.97 }}
            onClick={onOpenNotifyModal}
            className="w-full h-10 rounded-xl liquid-glass-dark-btn text-white text-xs font-bold shadow-xs mt-1"
          >
            Register for Portal Alerts
          </motion.button>
        </div>
      </section>

      {/* Frequently Asked Questions with Category Filter */}
      <section className="flex flex-col gap-6">
        <div className="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
          <div className="flex flex-col gap-1 max-w-xl">
            <span className="text-xs uppercase tracking-wider text-[#D97706] font-bold">
              Scholarship FAQs
            </span>
            <h2 className="font-serif text-2xl sm:text-3xl font-bold text-[#1E1B4B]">
              Frequently Asked Questions
            </h2>
            <p className="text-sm text-[#4B485A]">
              Clear answers regarding eligibility, award terms, and verification procedures.
            </p>
          </div>

          <div className="flex items-center gap-1.5 p-1 rounded-xl liquid-glass-dock border border-white/70 self-start overflow-x-auto max-w-full scrollbar-none">
            {[
              { id: 'all', label: 'All' },
              { id: 'eligibility', label: 'Eligibility' },
              { id: 'awards', label: 'Awards' },
              { id: 'dates', label: 'Deadlines' },
            ].map((tab) => (
              <button
                key={tab.id}
                onClick={() => setFaqCategory(tab.id)}
                className={`px-3 py-1 text-xs font-bold rounded-lg transition-all ${
                  faqCategory === tab.id
                    ? 'liquid-glass-dark-btn text-white shadow-2xs'
                    : 'text-[#4B485A] hover:text-[#1E1B4B]'
                }`}
              >
                {tab.label}
              </button>
            ))}
          </div>
        </div>

        <div className="flex flex-col gap-3">
          {filteredFaqs.map((faq, index) => {
            const isOpen = openFaqIndex === index;
            return (
              <div
                key={index}
                className="liquid-glass-card rounded-2xl border border-white/80 overflow-hidden shadow-2xs transition-all"
              >
                <button
                  type="button"
                  onClick={() => setOpenFaqIndex(isOpen ? null : index)}
                  className="w-full p-5 sm:p-6 text-left flex items-center justify-between gap-4"
                >
                  <span className="font-serif text-sm sm:text-base font-bold text-[#1E1B4B]">
                    {faq.question}
                  </span>
                  <div className={`w-8 h-8 rounded-full flex items-center justify-center transition-transform duration-200 ${isOpen ? 'bg-[#1E1B4B] text-white rotate-180' : 'bg-black/5 text-[#6E6B7E]'}`}>
                  <Icon name="expand_more" size={18} />
                  </div>
                </button>
                <AnimatePresence>
                  {isOpen && (
                    <motion.div
                      initial={{ height: 0, opacity: 0 }}
                      animate={{ height: 'auto', opacity: 1 }}
                      exit={{ height: 0, opacity: 0 }}
                      transition={{ duration: 0.2 }}
                      className="overflow-hidden"
                    >
                      <div className="px-5 sm:px-6 pb-5 sm:pb-6 text-xs sm:text-sm text-[#4B485A] leading-relaxed border-t border-black/5 pt-3">
                        {faq.answer}
                      </div>
                    </motion.div>
                  )}
                </AnimatePresence>
              </div>
            );
          })}
        </div>
      </section>
    </div>
  );
};
