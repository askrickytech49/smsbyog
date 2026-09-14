import React from 'react';
import { motion, type Variants } from 'motion/react';
import { ScreenType } from '../types';
import { FOUNDATION_IMAGES } from '../data/foundationData';
import { LeadershipSection } from '../components/LeadershipSection';
import { useFoundationData } from '../context/FoundationDataContext';
import { Icon } from '../components/Icon';

interface HomeScreenProps {
  onNavigate: (screen: ScreenType) => void;
  onOpenNotifyModal: () => void;
  onOpenDonate: () => void;
  onSelectArticle?: (id: string) => void;
}

// Sophisticated entrance animation variants
const sectionContainerVariants: Variants = {
  hidden: { opacity: 0 },
  visible: {
    opacity: 1,
    transition: {
      staggerChildren: 0.1,
      delayChildren: 0.05,
    },
  },
};

const headerItemVariants: Variants = {
  hidden: { opacity: 0, y: 22 },
  visible: {
    opacity: 1,
    y: 0,
    transition: {
      duration: 0.55,
      ease: [0.22, 1, 0.36, 1],
    },
  },
};

const cardStaggerVariants: Variants = {
  hidden: { opacity: 0 },
  visible: {
    opacity: 1,
    transition: {
      staggerChildren: 0.12,
      delayChildren: 0.08,
    },
  },
};

const cardItemVariants: Variants = {
  hidden: { opacity: 0, y: 24, scale: 0.98 },
  visible: {
    opacity: 1,
    y: 0,
    scale: 1,
    transition: {
      duration: 0.55,
      ease: [0.22, 1, 0.36, 1],
    },
  },
};

export const HomeScreen: React.FC<HomeScreenProps> = ({
  onNavigate,
  onOpenNotifyModal,
  onOpenDonate,
  onSelectArticle,
}) => {
  const { settings, news } = useFoundationData();

  const heroBadge = settings?.heroBadge || 'Empowering Future Leaders in Nigeria';
  const heroTitle = settings?.heroTitle || 'Empowering Young People Through Education, Opportunity & Excellence';
  const heroSubtitle = settings?.heroSubtitle || 'The Karl Peace Legacy Foundation is dedicated to empowering young people in Nigeria through transformative education, mentorship, scholarships, and public health initiatives, guided by a steadfast vision of opportunity and excellence.';

  return (
    <div className="flex flex-col w-full">
      {/* 1. Hero Section */}
      <motion.section
        initial="hidden"
        animate="visible"
        variants={sectionContainerVariants}
        className="relative px-4 sm:px-6 lg:px-8 pt-4 sm:pt-8 pb-10 sm:pb-16 bg-[#fbf9f5] flex flex-col gap-6 overflow-hidden max-w-7xl mx-auto w-full"
      >
        {/* Subtle Ambient Glows */}
        <div className="absolute -top-16 -right-16 w-56 h-56 rounded-full bg-[#FEF3C7]/40 blur-3xl pointer-events-none" />
        <div className="absolute top-44 -left-20 w-64 h-64 rounded-full bg-[#e3dfff]/25 blur-3xl pointer-events-none" />

        {/* Eyebrow Badge */}
        <motion.div
          variants={headerItemVariants}
          className="inline-flex items-center gap-1.5 py-1 px-3.5 rounded-full bg-[#FDF1E7] text-[#D97706] self-start shadow-xs"
        >
          <Icon name="stars" size={15} className="text-[#D97706]" />
          <span className="text-xs uppercase tracking-wider font-bold text-[#904d00]">
            {heroBadge}
          </span>
        </motion.div>

        {/* Main Headline & Description */}
        <div className="flex flex-col gap-3 relative z-10 max-w-3xl">
          <motion.h1
            variants={headerItemVariants}
            className="font-serif text-2xl sm:text-4xl lg:text-5xl font-bold text-[#1E1B4B] leading-tight sm:leading-snug"
          >
            {heroTitle}
          </motion.h1>
          <motion.p
            variants={headerItemVariants}
            className="text-sm sm:text-base text-[#4B485A] leading-relaxed"
          >
            {heroSubtitle}
          </motion.p>
        </div>

        {/* Primary CTAs with Liquid Glass Styling */}
        <motion.div
          variants={headerItemVariants}
          className="flex flex-col sm:flex-row gap-3 relative z-10 max-w-xl"
        >
          <motion.button
            whileHover={{ scale: 1.025 }}
            whileTap={{ scale: 0.96 }}
            onClick={onOpenNotifyModal}
            className="liquid-glass-amber-btn glass-refraction w-full sm:w-auto flex-1 h-12 px-6 rounded-2xl text-white text-sm font-bold flex items-center justify-center gap-2 shadow-lg"
          >
            <Icon name="workspace_premium" size={19} />
            <span>Apply for Scholarship</span>
          </motion.button>
          <motion.button
            whileHover={{ scale: 1.025 }}
            whileTap={{ scale: 0.96 }}
            onClick={() => {
              const el = document.getElementById('mission');
              if (el) {
                el.scrollIntoView({ behavior: 'smooth' });
              } else {
                onNavigate('about-us');
              }
            }}
            className="liquid-glass-light-btn glass-refraction w-full sm:w-auto flex-1 h-12 px-6 rounded-2xl text-[#1E1B4B] text-sm font-bold flex items-center justify-center gap-2 shadow-xs"
          >
            <Icon name="lightbulb" size={19} />
            <span>Discover Our Mission</span>
          </motion.button>
        </motion.div>

        {/* Featured Foundation Photographic Context */}
        <motion.div
          variants={cardItemVariants}
          className="relative w-full rounded-2xl overflow-hidden shadow-lg bg-[#161338] aspect-[16/10] sm:aspect-[21/9] mt-2 border border-[#E8E4DA]"
        >
          <img
            className="w-full h-full object-cover opacity-95 transition-transform duration-700 hover:scale-105"
            alt="Ambitious Nigerian university students holding textbooks on a sunlit academic campus"
            src={FOUNDATION_IMAGES.hero}
            referrerPolicy="no-referrer"
          />
          <div className="absolute inset-0 bg-gradient-to-t from-[#161338]/90 via-[#161338]/30 to-transparent flex items-end p-4 sm:p-6">
            <div className="flex items-center gap-2 text-white">
              <Icon name="school" size={22} className="text-[#F59E0B]" />
              <span className="text-xs sm:text-base font-medium text-[#FBF9F5] drop-shadow-sm">
                Fostering Academic &amp; Civic Leadership in Nigeria
              </span>
            </div>
          </div>
        </motion.div>

        {/* Trust Badge Row */}
        <motion.div
          variants={headerItemVariants}
          className="w-full py-2.5 px-4 rounded-xl bg-[#f5f3ef] border border-[#E8E4DA] flex items-center justify-between gap-2 text-[#6E6B7E] text-xs"
        >
          <div className="flex items-center gap-1.5 min-w-0">
            <Icon name="verified_user" size={16} className="text-[#0D9488] shrink-0" />
            <span className="uppercase tracking-wider font-bold truncate">
              Official Online Presence
            </span>
          </div>
          <div className="h-3 w-[1px] bg-[#D1CBBC] shrink-0" />
          <span className="uppercase tracking-wider font-bold text-[#312E81] shrink-0">
            karlpeacelegacy.org
          </span>
        </motion.div>
      </motion.section>

      {/* 2. High-Priority Scholarship Announcement Callout */}
      {settings?.scholarshipAlertActive !== false && (
        <motion.section
          initial="hidden"
          whileInView="visible"
          viewport={{ once: true, amount: 0.2 }}
          variants={sectionContainerVariants}
          className="px-4 sm:px-6 lg:px-8 py-8 sm:py-12 bg-[#f5f3ef] border-y border-[#E8E4DA]"
          id="scholarship-alert"
        >
          <div className="max-w-7xl mx-auto w-full">
            <motion.div
              variants={cardItemVariants}
              className="w-full bg-white rounded-2xl p-6 sm:p-8 shadow-sm border border-[#E8E4DA] flex flex-col gap-4 relative overflow-hidden"
            >
              <div className="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-[#D97706] via-[#fe932c] to-[#F59E0B]" />
              <div className="flex items-start justify-between gap-4">
                <div className="flex items-center gap-3">
                  <div className="w-11 h-11 rounded-xl bg-[#FEF3C7] flex items-center justify-center text-[#D97706] shrink-0">
                    <Icon name="campaign" size={26} />
                  </div>
                  <div>
                    <span className="text-xs text-[#904d00] font-bold uppercase tracking-wide block">
                      Priority Notice
                    </span>
                    <h2 className="font-serif text-lg sm:text-2xl font-bold text-[#1E1B4B] leading-tight">
                      {settings?.scholarshipAlertTitle || 'Scholarship Applications Opening Soon'}
                    </h2>
                  </div>
                </div>
              </div>

              <p className="text-xs sm:text-sm text-[#4B485A] leading-relaxed max-w-3xl">
                {settings?.scholarshipAlertText || 'Karl Peace Legacy Foundation is preparing to open its next scholarship application for eligible tertiary students in Nigeria. Full eligibility requirements, application procedures, deadlines, and other important information will be announced through our official website.'}
              </p>

            {/* Requirements Preview Pill Group */}
            <div className="flex flex-wrap gap-2 pt-1">
              <span className="px-3 py-1.5 rounded-full bg-[#f5f3ef] border border-[#E8E4DA] text-[#1E1B2E] text-xs font-semibold flex items-center gap-1.5">
                <Icon name="check_circle" size={15} className="text-[#D97706]" />
                Nigerian Tertiary Enrolment
              </span>
              <span className="px-3 py-1.5 rounded-full bg-[#f5f3ef] border border-[#E8E4DA] text-[#1E1B2E] text-xs font-semibold flex items-center gap-1.5">
                <Icon name="check_circle" size={15} className="text-[#D97706]" />
                Verified Academic Standing
              </span>
              <span className="px-3 py-1.5 rounded-full bg-[#f5f3ef] border border-[#E8E4DA] text-[#1E1B2E] text-xs font-semibold flex items-center gap-1.5">
                <Icon name="check_circle" size={15} className="text-[#D97706]" />
                Leadership Commitment
              </span>
            </div>

            {/* Quick Interactive Reminder Sheet Trigger */}
            <div className="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
              <motion.button
                whileHover={{ scale: 1.02 }}
                whileTap={{ scale: 0.97 }}
                onClick={onOpenNotifyModal}
                className="liquid-glass-dark-btn glass-refraction h-12 px-6 rounded-xl text-white text-sm font-bold flex items-center justify-center gap-2 shadow-md"
                type="button"
              >
                <Icon name="notifications_active" size={19} className="text-[#F59E0B]" />
                <span>Join Notification List / Learn Requirements</span>
              </motion.button>
              <motion.button
                whileHover={{ scale: 1.02 }}
                whileTap={{ scale: 0.97 }}
                onClick={() => onNavigate('scholarships')}
                className="liquid-glass-light-btn glass-refraction h-12 px-5 rounded-xl text-[#1E1B4B] text-sm font-semibold flex items-center justify-center gap-1.5 shadow-2xs"
              >
                <span>View Full Criteria</span>
                <Icon name="arrow_forward" size={16} />
              </motion.button>
              <span className="text-[11px] text-[#6E6B7E] text-center sm:text-left sm:ml-auto">
                Verified announcements strictly via karlpeacelegacy.org
              </span>
            </div>
          </motion.div>
        </div>
      </motion.section>
      )}

      {/* 3. Mission & Vision Section */}
      <motion.section
        initial="hidden"
        whileInView="visible"
        viewport={{ once: true, amount: 0.2 }}
        variants={sectionContainerVariants}
        className="px-4 sm:px-6 lg:px-8 py-10 sm:py-16 bg-[#fbf9f5] flex flex-col gap-6 max-w-7xl mx-auto w-full"
        id="mission"
      >
        <div className="flex flex-col gap-1 max-w-2xl">
          <motion.span
            variants={headerItemVariants}
            className="text-xs uppercase tracking-wider text-[#D97706] font-bold"
          >
            Institutional Purpose
          </motion.span>
          <motion.h2
            variants={headerItemVariants}
            className="font-serif text-2xl sm:text-3xl font-bold text-[#1E1B4B]"
          >
            Discover Our Mission and Vision for Future Success
          </motion.h2>
        </div>

        {/* Two Distinct Cards */}
        <motion.div
          variants={cardStaggerVariants}
          className="grid grid-cols-1 md:grid-cols-2 gap-5"
        >
          {/* Mission Card */}
          <motion.div
            variants={cardItemVariants}
            className="liquid-glass-card card-hover-lift rounded-3xl p-6 sm:p-8 shadow-xs border border-white/80 flex flex-col gap-3 relative"
          >
            <div className="flex items-center gap-3">
              <div className="w-12 h-12 rounded-2xl liquid-glass-dark-btn text-[#F59E0B] flex items-center justify-center shadow-xs">
                <Icon name="flag" size={24} />
              </div>
              <div>
                <span className="text-xs text-[#D97706] uppercase tracking-wider font-bold">
                  Guiding Purpose
                </span>
                <h3 className="font-serif text-lg sm:text-xl font-bold text-[#1E1B4B]">Our Mission</h3>
              </div>
            </div>
            <p className="text-sm text-[#4B485A] leading-relaxed">
              To empower young people through education, mentorship, scholarships, public health initiatives, and access to opportunities that enable them to reach their full potential and create lasting positive impact in their communities.
            </p>
          </motion.div>

          {/* Vision Card */}
          <motion.div
            variants={cardItemVariants}
            className="liquid-glass-card card-hover-lift rounded-3xl p-6 sm:p-8 shadow-xs border border-white/80 flex flex-col gap-3 relative"
          >
            <div className="flex items-center gap-3">
              <div className="w-12 h-12 rounded-2xl liquid-glass-amber-btn text-white flex items-center justify-center shadow-xs">
                <Icon name="visibility" size={24} />
              </div>
              <div>
                <span className="text-xs text-[#904d00] uppercase tracking-wider font-bold">
                  Long-Term Horizon
                </span>
                <h3 className="font-serif text-lg sm:text-xl font-bold text-[#1E1B4B]">Our Vision</h3>
              </div>
            </div>
            <p className="text-sm text-[#4B485A] leading-relaxed">
              A future where every young person, regardless of background or circumstance, has access to the education, support, resources, and opportunities needed to thrive and contribute meaningfully to society.
            </p>
          </motion.div>
        </motion.div>
      </motion.section>

      {/* 4. Core Program Pillars */}
      <motion.section
        initial="hidden"
        whileInView="visible"
        viewport={{ once: true, amount: 0.15 }}
        variants={sectionContainerVariants}
        className="px-4 sm:px-6 lg:px-8 py-10 sm:py-16 bg-[#f5f3ef] border-t border-[#E8E4DA]"
      >
        <div className="max-w-7xl mx-auto w-full flex flex-col gap-8">
          <div className="flex flex-col gap-1 max-w-2xl">
            <motion.div
              variants={headerItemVariants}
              className="inline-flex items-center gap-1.5 py-0.5 px-3 rounded-full liquid-glass-pill-active text-[#904d00] text-xs uppercase font-bold self-start border border-amber-200/60 shadow-xs"
            >
              <Icon name="layers" size={14} />
              <span>Structured Pathways</span>
            </motion.div>
            <motion.h2
              variants={headerItemVariants}
              className="font-serif text-2xl sm:text-3xl font-bold text-[#1E1B4B]"
            >
              Our Programs
            </motion.h2>
            <motion.p
              variants={headerItemVariants}
              className="text-sm text-[#4B485A] leading-relaxed"
            >
              Our programs are designed to expand access to education, mentorship, health information, and opportunities that empower young people to build stronger futures.
            </motion.p>
          </div>

          {/* Programmatic Stack */}
          <motion.div
            variants={cardStaggerVariants}
            className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
          >
            {/* Pillar 1: Scholarships */}
            <motion.div
              variants={cardItemVariants}
              className="liquid-glass-card card-hover-lift rounded-3xl overflow-hidden shadow-xs border border-white/80 flex flex-col"
            >
              <div className="h-48 w-full bg-[#312E81] relative overflow-hidden">
                <img
                  className="w-full h-full object-cover hover:scale-105 transition-transform duration-700"
                  alt="Nigerian university undergraduates focused on their research and STEM coursework inside an organized university library."
                  src={FOUNDATION_IMAGES.scholarships}
                  referrerPolicy="no-referrer"
                />
                <div className="absolute top-3 right-3">
                  <span className="px-3 py-1 rounded-full liquid-glass-card text-[#904d00] text-xs font-bold shadow-xs border border-white/80">
                    Direct Aid
                  </span>
                </div>
              </div>
              <div className="p-5 sm:p-6 flex flex-col gap-3 flex-1">
                <div className="flex items-center gap-2">
                  <Icon name="workspace_premium" size={22} className="text-[#D97706]" />
                  <h3 className="font-serif text-lg font-bold text-[#1E1B4B]">Scholarships</h3>
                </div>
                <p className="text-xs sm:text-sm text-[#4B485A] leading-relaxed">
                  This program provides financial support for deserving students to pursue education across recognized Nigerian tertiary institutions.
                </p>
                <div className="pt-2 grid grid-cols-1 gap-2 mt-auto">
                  <div className="flex items-center gap-2 p-2 rounded-xl bg-white/70 border border-black/5 text-xs font-medium text-[#1E1B2E]">
                    <span className="w-2 h-2 rounded-full bg-[#D97706] shrink-0" />
                    <span>Tuition Assistance &amp; Registration Relief</span>
                  </div>
                  <div className="flex items-center gap-2 p-2 rounded-xl bg-white/70 border border-black/5 text-xs font-medium text-[#1E1B2E]">
                    <span className="w-2 h-2 rounded-full bg-[#D97706] shrink-0" />
                    <span>Tertiary Grants for Undergraduates</span>
                  </div>
                  <div className="flex items-center gap-2 p-2 rounded-xl bg-white/70 border border-black/5 text-xs font-medium text-[#1E1B2E]">
                    <span className="w-2 h-2 rounded-full bg-[#D97706] shrink-0" />
                    <span>Essential Academic Materials &amp; Textbooks</span>
                  </div>
                </div>
                <motion.button
                  whileHover={{ scale: 1.02 }}
                  whileTap={{ scale: 0.97 }}
                  onClick={() => onNavigate('scholarships')}
                  className="mt-3 w-full py-2.5 rounded-xl liquid-glass-light-btn text-[#1E1B4B] text-xs font-bold flex items-center justify-center gap-1.5 shadow-2xs"
                >
                  <span>Explore Scholarships</span>
                  <Icon name="arrow_forward" size={16} />
                </motion.button>
              </div>
            </motion.div>

            {/* Pillar 2: Mentorship & Youth Development */}
            <motion.div
              variants={cardItemVariants}
              className="liquid-glass-card card-hover-lift rounded-3xl overflow-hidden shadow-xs border border-white/80 flex flex-col"
            >
              <div className="h-48 w-full bg-[#312E81] relative overflow-hidden">
                <img
                  className="w-full h-full object-cover hover:scale-105 transition-transform duration-700"
                  alt="An accomplished Nigerian professional woman mentoring attentive young university students at a collaborative desk."
                  src={FOUNDATION_IMAGES.mentorship}
                  referrerPolicy="no-referrer"
                />
                <div className="absolute top-3 right-3">
                  <span className="px-3 py-1 rounded-full liquid-glass-card text-[#181445] text-xs font-bold shadow-xs border border-white/80">
                    Leadership
                  </span>
                </div>
              </div>
              <div className="p-5 sm:p-6 flex flex-col gap-3 flex-1">
                <div className="flex items-center gap-2">
                  <Icon name="psychology" size={22} className="text-[#312E81]" />
                  <h3 className="font-serif text-lg font-bold text-[#1E1B4B]">
                    Mentorship &amp; Youth Development
                  </h3>
                </div>
                <p className="text-xs sm:text-sm text-[#4B485A] leading-relaxed">
                  This initiative connects young people with experienced guides for career growth, strengthening confidence and leadership.
                </p>
                <div className="pt-2 flex flex-wrap gap-2 mt-auto">
                  <span className="px-3 py-1.5 rounded-xl bg-white/70 border border-black/5 text-[#1E1B2E] text-xs font-medium">
                    One-on-One Career Advisory
                  </span>
                  <span className="px-3 py-1.5 rounded-xl bg-white/70 border border-black/5 text-[#1E1B2E] text-xs font-medium">
                    Ethical Leadership Seminars
                  </span>
                  <span className="px-3 py-1.5 rounded-xl bg-white/70 border border-black/5 text-[#1E1B2E] text-xs font-medium">
                    Resume &amp; Portfolio Reviews
                  </span>
                </div>
                <motion.button
                  whileHover={{ scale: 1.02 }}
                  whileTap={{ scale: 0.97 }}
                  onClick={() => onNavigate('our-programs')}
                  className="mt-3 w-full py-2.5 rounded-xl liquid-glass-light-btn text-[#1E1B4B] text-xs font-bold flex items-center justify-center gap-1.5 shadow-2xs"
                >
                  <span>Explore Mentorship</span>
                  <Icon name="arrow_forward" size={16} />
                </motion.button>
              </div>
            </motion.div>

            {/* Pillar 3: Public Health Initiatives */}
            <motion.div
              variants={cardItemVariants}
              className="liquid-glass-card card-hover-lift rounded-3xl overflow-hidden shadow-xs border border-white/80 flex flex-col"
            >
              <div className="h-48 w-full bg-[#312E81] relative overflow-hidden">
                <img
                  className="w-full h-full object-cover hover:scale-105 transition-transform duration-700"
                  alt="Community public health educator conducting an interactive community hygiene and wellness workshop for youth."
                  src={FOUNDATION_IMAGES.health}
                  referrerPolicy="no-referrer"
                />
                <div className="absolute top-3 right-3">
                  <span className="px-3 py-1 rounded-full liquid-glass-card text-[#0D9488] text-xs font-bold shadow-xs border border-white/80">
                    Community Wellness
                  </span>
                </div>
              </div>
              <div className="p-5 sm:p-6 flex flex-col gap-3 flex-1">
                <div className="flex items-center gap-2">
                  <Icon name="health_and_safety" size={22} className="text-[#0D9488]" />
                  <h3 className="font-serif text-lg font-bold text-[#1E1B4B]">
                    Public Health Initiatives
                  </h3>
                </div>
                <p className="text-xs sm:text-sm text-[#4B485A] leading-relaxed">
                  This campaign delivers vital health resources, hygiene education, and awareness to local communities and campuses.
                </p>
                <div className="pt-2 flex flex-wrap gap-2 mt-auto">
                  <span className="px-3 py-1.5 rounded-xl bg-white/70 border border-black/5 text-[#1E1B2E] text-xs font-medium">
                    Preventative Health Literacy
                  </span>
                  <span className="px-3 py-1.5 rounded-xl bg-white/70 border border-black/5 text-[#1E1B2E] text-xs font-medium">
                    Community Hygiene Drives
                  </span>
                  <span className="px-3 py-1.5 rounded-xl bg-white/70 border border-black/5 text-[#1E1B2E] text-xs font-medium">
                    Youth Wellbeing Circles
                  </span>
                </div>
                <motion.button
                  whileHover={{ scale: 1.02 }}
                  whileTap={{ scale: 0.97 }}
                  onClick={() => onNavigate('our-programs')}
                  className="mt-3 w-full py-2.5 rounded-xl liquid-glass-light-btn text-[#1E1B4B] text-xs font-bold flex items-center justify-center gap-1.5 shadow-2xs"
                >
                  <span>Explore Health Drives</span>
                  <Icon name="arrow_forward" size={16} />
                </motion.button>
              </div>
            </motion.div>
          </motion.div>

          {/* Pillar 4: Access to Opportunities Wide Banner */}
          <motion.div
            variants={cardItemVariants}
            className="liquid-glass-dock rounded-3xl p-6 sm:p-8 shadow-xs border border-white/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6"
          >
            <div className="flex items-start gap-4 max-w-3xl">
              <div className="w-12 h-12 rounded-2xl liquid-glass-amber-btn text-white flex items-center justify-center shrink-0 shadow-xs">
                <Icon name="hub" size={26} />
              </div>
              <div className="flex flex-col gap-1">
                <h3 className="font-serif text-lg sm:text-xl font-bold text-[#1E1B4B]">
                  Access to Opportunities
                </h3>
                <p className="text-xs sm:text-sm text-[#4B485A] leading-relaxed">
                  Connecting young people with educational advancement, professional development, and practical career tools. We equip them with verifiable resources to make the most of emerging prospects.
                </p>
              </div>
            </div>
            <motion.button
              whileHover={{ scale: 1.03 }}
              whileTap={{ scale: 0.97 }}
              onClick={() => onNavigate('our-programs')}
              className="h-12 px-6 rounded-2xl liquid-glass-dark-btn text-white text-xs sm:text-sm font-bold shrink-0 shadow-md"
            >
              View Full Overview
            </motion.button>
          </motion.div>
        </div>
      </motion.section>

      {/* 5. Purpose & Impact Values */}
      <motion.section
        initial="hidden"
        whileInView="visible"
        viewport={{ once: true, amount: 0.2 }}
        variants={sectionContainerVariants}
        className="px-4 sm:px-6 lg:px-8 py-10 sm:py-16 bg-[#fbf9f5] max-w-7xl mx-auto w-full flex flex-col gap-8"
      >
        <div className="flex flex-col gap-1 max-w-2xl">
          <motion.span
            variants={headerItemVariants}
            className="text-xs uppercase tracking-wider text-[#D97706] font-bold"
          >
            Foundational Integrity
          </motion.span>
          <motion.h2
            variants={headerItemVariants}
            className="font-serif text-2xl sm:text-3xl font-bold text-[#1E1B4B]"
          >
            Creating Opportunities for Young People
          </motion.h2>
          <motion.p
            variants={headerItemVariants}
            className="text-sm text-[#4B485A] leading-relaxed"
          >
            Karl Peace Legacy Foundation is committed to helping young people access education, mentorship, and opportunities that support their personal, academic, and professional development.
          </motion.p>
        </div>

        {/* Structured Commitments */}
        <motion.div
          variants={cardStaggerVariants}
          className="grid grid-cols-1 md:grid-cols-3 gap-5"
        >
          <motion.div
            variants={cardItemVariants}
            className="p-6 rounded-2xl bg-white shadow-xs border border-[#E8E4DA] flex flex-col gap-3"
          >
            <div className="w-12 h-12 rounded-xl bg-[#f5f3ef] flex items-center justify-center text-[#1E1B4B] shrink-0">
              <Icon name="account_balance" size={26} />
            </div>
            <div>
              <h4 className="font-serif text-base sm:text-lg font-bold text-[#1E1B4B]">
                Verified Tertiary Focus
              </h4>
              <p className="text-xs sm:text-sm text-[#4B485A] mt-1.5 leading-relaxed">
                Targeting matriculated students in recognized Nigerian colleges and universities needing targeted support to complete their qualifications.
              </p>
            </div>
          </motion.div>

          <motion.div
            variants={cardItemVariants}
            className="p-6 rounded-2xl bg-white shadow-xs border border-[#E8E4DA] flex flex-col gap-3"
          >
            <div className="w-12 h-12 rounded-xl bg-[#f5f3ef] flex items-center justify-center text-[#0D9488] shrink-0">
              <Icon name="clinical_notes" size={26} />
            </div>
            <div>
              <h4 className="font-serif text-base sm:text-lg font-bold text-[#1E1B4B]">
                Public Health Grassroots Outreach
              </h4>
              <p className="text-xs sm:text-sm text-[#4B485A] mt-1.5 leading-relaxed">
                Partnering directly with local health advocates to disseminate life-saving preventive healthcare information and hygiene supplies.
              </p>
            </div>
          </motion.div>

          <motion.div
            variants={cardItemVariants}
            className="p-6 rounded-2xl bg-white shadow-xs border border-[#E8E4DA] flex flex-col gap-3"
          >
            <div className="w-12 h-12 rounded-xl bg-[#f5f3ef] flex items-center justify-center text-[#904d00] shrink-0">
              <Icon name="group" size={26} />
            </div>
            <div>
              <h4 className="font-serif text-base sm:text-lg font-bold text-[#1E1B4B]">
                Structured Mentorship Pairings
              </h4>
              <p className="text-xs sm:text-sm text-[#4B485A] mt-1.5 leading-relaxed">
                Connecting emerging graduates with seasoned professionals in technology, sciences, healthcare, and public administration.
              </p>
            </div>
          </motion.div>
        </motion.div>
      </motion.section>

      {/* 6. Latest News & Announcements */}
      <motion.section
        initial="hidden"
        whileInView="visible"
        viewport={{ once: true, amount: 0.2 }}
        variants={sectionContainerVariants}
        className="px-4 sm:px-6 lg:px-8 py-10 sm:py-16 bg-[#f5f3ef] border-t border-[#E8E4DA]"
      >
        <div className="max-w-7xl mx-auto w-full flex flex-col gap-8">
          <div className="flex flex-col gap-1 max-w-2xl">
            <motion.span
              variants={headerItemVariants}
              className="text-xs uppercase tracking-wider text-[#D97706] font-bold"
            >
              Dispatches &amp; Notices
            </motion.span>
            <motion.h2
              variants={headerItemVariants}
              className="font-serif text-2xl sm:text-3xl font-bold text-[#1E1B4B]"
            >
              Latest News &amp; Updates
            </motion.h2>
            <motion.p
              variants={headerItemVariants}
              className="text-sm text-[#4B485A] leading-relaxed"
            >
              Stay informed about the latest programs, scholarship opportunities, events, announcements, and activities from Karl Peace Legacy Foundation.
            </motion.p>
          </div>

          {/* Announcement Cards */}
          <motion.div
            variants={cardStaggerVariants}
            className="grid grid-cols-1 md:grid-cols-2 gap-5"
          >
            {news.slice(0, 2).map((item) => (
              <motion.article
                key={item.id}
                variants={cardItemVariants}
                onClick={() => onSelectArticle ? onSelectArticle(item.id) : onNavigate('news-and-updates')}
                className="bg-white rounded-2xl p-6 sm:p-7 shadow-xs border border-[#E8E4DA] flex flex-col gap-3 cursor-pointer hover:shadow-md transition-shadow group"
              >
                <div className="flex items-center justify-between">
                  <span className="px-3 py-1 rounded-full bg-[#FEF3C7] text-[#904d00] text-xs font-bold">
                    {item.category || 'Official Bulletin'}
                  </span>
                  <time className="text-xs text-[#6E6B7E]">{item.date}</time>
                </div>
                <h3 className="font-serif text-base sm:text-lg font-bold text-[#1E1B4B] group-hover:text-[#D97706] transition-colors leading-snug">
                  {item.title}
                </h3>
                <p className="text-xs sm:text-sm text-[#4B485A] leading-relaxed line-clamp-3">
                  {item.summary}
                </p>
                <div className="pt-2 flex items-center justify-between mt-auto border-t border-[#f5f3ef]">
                  <span className="text-xs text-[#6E6B7E] flex items-center gap-1">
                    <Icon name="location_on" size={15} />
                    {item.location || 'Nigeria'}
                  </span>
                  <span className="text-xs font-bold text-[#1E1B4B] group-hover:text-[#D97706] flex items-center gap-1 transition-colors">
                    <span>Read Overview</span>
                    <Icon name="arrow_forward" size={16} />
                  </span>
                </div>
              </motion.article>
            ))}
          </motion.div>

          <motion.button
            variants={headerItemVariants}
            onClick={() => onNavigate('news-and-updates')}
            className="w-full sm:w-auto self-center h-12 px-8 rounded-lg bg-[#efeeea] hover:bg-[#eae8e4] text-[#1E1B4B] text-sm font-bold flex items-center justify-center gap-2 transition-colors"
            type="button"
          >
            <span>View All Announcements</span>
            <Icon name="list_alt" size={18} />
          </motion.button>
        </div>
      </motion.section>

      {/* 6.5. Leadership & Founders Section */}
      <motion.section
        initial="hidden"
        whileInView="visible"
        viewport={{ once: true, amount: 0.15 }}
        variants={sectionContainerVariants}
        className="px-4 sm:px-6 lg:px-8 py-10 sm:py-16 bg-[#fbf9f5] border-t border-[#E8E4DA]"
      >
        <div className="max-w-7xl mx-auto w-full">
          <LeadershipSection
            title="Our Dedicated Leadership & Founders"
            subtitle="Meet the passionate professionals and visionary founders guiding the Karl Peace Legacy Foundation's education, mentorship, and public health missions in Nigeria."
            showFilters={true}
            onViewAll={() => onNavigate('about-us')}
          />
        </div>
      </motion.section>

      {/* 7. Get Involved & Official Support Pathways */}
      <motion.section
        initial="hidden"
        whileInView="visible"
        viewport={{ once: true, amount: 0.2 }}
        variants={sectionContainerVariants}
        className="px-4 sm:px-6 lg:px-8 py-12 sm:py-20 bg-[#1E1B4B] text-white relative overflow-hidden"
      >
        <div className="absolute -bottom-10 -right-10 w-64 h-64 rounded-full bg-[#F59E0B]/10 blur-3xl pointer-events-none" />
        <div className="max-w-7xl mx-auto w-full flex flex-col gap-8 relative z-10">
          <div className="flex flex-col gap-1 max-w-2xl">
            <motion.div
              variants={headerItemVariants}
              className="inline-flex items-center gap-1 py-0.5 px-3 rounded-full bg-white/10 text-[#F59E0B] text-xs uppercase font-bold self-start"
            >
              Collaborate &amp; Support
            </motion.div>
            <motion.h2
              variants={headerItemVariants}
              className="font-serif text-2xl sm:text-4xl font-bold text-white"
            >
              Support Our Mission
            </motion.h2>
            <motion.p
              variants={headerItemVariants}
              className="text-sm sm:text-base text-[#F5F3ED]/85 leading-relaxed"
            >
              Whether you are an academic institution, community mentor, or compassionate supporter, your partnership deepens our impact for Nigerian students.
            </motion.p>
          </div>

          {/* Transparent Involvement Pillars */}
          <motion.div
            variants={cardStaggerVariants}
            className="grid grid-cols-1 md:grid-cols-2 gap-5"
          >
            <motion.div
              variants={cardItemVariants}
              className="p-6 rounded-2xl bg-[#161338]/80 backdrop-blur-md border border-white/10 flex flex-col gap-2 shadow-sm"
            >
              <div className="flex items-center gap-2 text-[#F59E0B]">
                <Icon name="handshake" size={22} />
                <h3 className="font-serif text-base sm:text-lg font-bold text-white">
                  Institutional Partnerships
                </h3>
              </div>
              <p className="text-xs sm:text-sm text-[#F5F3ED]/80 leading-relaxed">
                Collaborate with us on scholarship disbursement, student evaluation standards, or health workshop venues across Nigerian campuses.
              </p>
            </motion.div>

            <motion.div
              variants={cardItemVariants}
              className="p-6 rounded-2xl bg-[#161338]/80 backdrop-blur-md border border-white/10 flex flex-col gap-2 shadow-sm"
            >
              <div className="flex items-center gap-2 text-[#F59E0B]">
                <Icon name="person_celebrate" size={22} />
                <h3 className="font-serif text-base sm:text-lg font-bold text-white">
                  Volunteer as a Mentor
                </h3>
              </div>
              <p className="text-xs sm:text-sm text-[#F5F3ED]/80 leading-relaxed">
                Share your professional expertise with promising students navigating career choices, research ambitions, and postgraduate applications.
              </p>
            </motion.div>
          </motion.div>

          {/* Primary Channel and Action */}
          <motion.div
            variants={headerItemVariants}
            className="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-4"
          >
            <a
              className="h-12 px-6 rounded-lg bg-[#D97706] hover:bg-[#F59E0B] text-white text-sm font-bold flex items-center justify-center gap-2 shadow-md transition-all active:scale-[0.99]"
              href="mailto:admin@karlpeacelegacy.org"
            >
              <Icon name="mail" size={20} />
              <span>Contact Foundation Office</span>
            </a>
            <button
              onClick={onOpenDonate}
              className="h-12 px-6 rounded-lg bg-white/10 hover:bg-white/20 border border-white/20 text-white text-sm font-bold flex items-center justify-center gap-2 transition-all"
            >
              <Icon name="volunteer_activism" size={20} />
              <span>Make a Contribution</span>
            </button>
            <div className="p-3 sm:px-5 rounded-lg bg-[#161338] border border-white/10 sm:ml-auto text-center sm:text-right">
              <span className="text-[10px] text-[#F59E0B] uppercase tracking-wider block font-bold">
                Official Foundation Channel
              </span>
              <span className="text-xs sm:text-sm text-white select-all font-mono font-medium">
                admin@karlpeacelegacy.org
              </span>
            </div>
          </motion.div>
        </div>
      </motion.section>
    </div>
  );
};
