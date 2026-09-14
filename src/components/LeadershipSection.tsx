import React, { useState } from 'react';
import { motion, AnimatePresence, type Variants } from 'motion/react';
import { useFoundationData } from '../context/FoundationDataContext';
import { TeamMember } from '../types';
import { Icon } from './Icon';

interface LeadershipSectionProps {
  title?: string;
  subtitle?: string;
  showFilters?: boolean;
  limit?: number;
  onViewAll?: () => void;
}

const headerContainerVariants: Variants = {
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
  hidden: { opacity: 0, y: 20 },
  visible: {
    opacity: 1,
    y: 0,
    transition: {
      duration: 0.55,
      ease: [0.22, 1, 0.36, 1],
    },
  },
};

const gridContainerVariants: Variants = {
  hidden: { opacity: 0 },
  visible: {
    opacity: 1,
    transition: {
      staggerChildren: 0.1,
      delayChildren: 0.05,
    },
  },
};

const cardItemVariants: Variants = {
  hidden: { opacity: 0, y: 22, scale: 0.98 },
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

export const LeadershipSection: React.FC<LeadershipSectionProps> = ({
  title = 'Our Dedicated Leadership Team',
  subtitle = 'Meet the passionate professionals, visionary founders, and trustees guiding the Karl Peace Legacy Foundation’s educational, mentorship, and public health missions in Nigeria.',
  showFilters = true,
  limit,
  onViewAll,
}) => {
  const { leaders } = useFoundationData();
  const [selectedCategory, setSelectedCategory] = useState<string>('all');
  const [activeLeader, setActiveLeader] = useState<TeamMember | null>(null);

  const filterTabs = [
    { id: 'all', label: 'All Leadership' },
    { id: 'founder', label: 'Founders & Presidency' },
    { id: 'executive', label: 'Executive Directors' },
    { id: 'operations', label: 'Operations & Secretariat' },
    { id: 'patron', label: 'Inspiration & Patron' },
  ];

  const filteredLeaders = leaders.filter((leader) => {
    if (selectedCategory === 'all') return true;
    return leader.category === selectedCategory;
  });

  const displayedLeaders = limit ? filteredLeaders.slice(0, limit) : filteredLeaders;

  return (
    <section id="leadership-section" className="w-full py-8 sm:py-16">
      <div className="flex flex-col gap-8 sm:gap-12">
        {/* Header */}
        <motion.div
          variants={headerContainerVariants}
          initial="hidden"
          whileInView="visible"
          viewport={{ once: true, amount: 0.2 }}
          className="flex flex-col sm:flex-row sm:items-end justify-between gap-6 border-b border-[#E8E4DA] pb-6"
        >
          <div className="flex flex-col gap-3 max-w-2xl">
            <motion.div
              variants={headerItemVariants}
              className="inline-flex items-center gap-2 py-1 px-3.5 rounded-full bg-[#FEF3C7] text-[#904d00] text-xs uppercase font-bold self-start"
            >
              <Icon name="groups" size={15} />
              <span>Founding Vision &amp; Governance</span>
            </motion.div>
            <motion.h2
              variants={headerItemVariants}
              className="font-serif text-3xl sm:text-4xl lg:text-5xl font-bold text-[#1E1B4B] tracking-tight"
            >
              {title}
            </motion.h2>
            <motion.p
              variants={headerItemVariants}
              className="text-sm sm:text-base text-[#4B485A] leading-relaxed"
            >
              {subtitle}
            </motion.p>
          </div>

          {onViewAll && limit && limit < leaders.length && (
            <motion.button
              variants={headerItemVariants}
              onClick={onViewAll}
              className="inline-flex items-center gap-2 text-sm font-bold text-[#D97706] hover:text-[#B45309] transition-colors shrink-0 group py-2"
            >
              <span>Explore Full Executive Council</span>
              <Icon name="arrow_forward" size={18} className="group-hover:translate-x-1 transition-transform" />
            </motion.button>
          )}
        </motion.div>

        {/* Filter Pills with Liquid Glass Segmented Dock */}
        {showFilters && (
          <div className="flex items-center gap-1.5 p-1.5 rounded-2xl liquid-glass-dock overflow-x-auto scrollbar-none self-start border border-white/80 shadow-md">
            {filterTabs.map((tab) => {
              const isActive = selectedCategory === tab.id;
              return (
                <button
                  key={tab.id}
                  onClick={() => setSelectedCategory(tab.id)}
                  className={`relative px-4 py-2 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all select-none ${
                    isActive
                      ? 'text-[#1E1B4B] font-bold shadow-xs'
                      : 'text-[#4B485A] hover:text-[#1E1B4B] hover:bg-white/40'
                  }`}
                >
                  {isActive && (
                    <motion.div
                      layoutId="active-leadership-filter-pill"
                      className="absolute inset-0 rounded-xl liquid-glass-pill-active -z-10"
                      transition={{ type: 'spring', damping: 26, stiffness: 350 }}
                    />
                  )}
                  {tab.label}
                </button>
              );
            })}
          </div>
        )}

        {/* Leaders Grid with Staggered Entrance */}
        <motion.div
          key={selectedCategory}
          variants={gridContainerVariants}
          initial="hidden"
          animate="visible"
          className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8"
        >
          {displayedLeaders.map((leader) => (
            <motion.div
              key={leader.id}
              variants={cardItemVariants}
              className="group bg-white rounded-3xl border border-[#E8E4DA] p-6 flex flex-col justify-between gap-6 hover:shadow-lg transition-all hover:border-[#D97706]/40 relative overflow-hidden"
            >
              <div className="flex flex-col gap-4">
                {/* Image & Badges - Normal, uncropped display with portrait framing */}
                <div className="relative aspect-[3/4] sm:aspect-[4/5] rounded-2xl overflow-hidden bg-[#F4F1EA] flex items-center justify-center border border-[#E8E4DA]/60">
                  {/* Ambient backdrop mirroring genuine photo tones */}
                  <img
                    src={leader.image}
                    alt=""
                    aria-hidden="true"
                    className="absolute inset-0 w-full h-full object-cover blur-xl opacity-30 scale-110 select-none pointer-events-none"
                  />
                  {/* Main uncropped leader photograph */}
                  <img
                    src={leader.image}
                    alt={leader.name}
                    className="relative z-10 w-full h-full object-contain group-hover:scale-102 transition-transform duration-500"
                    referrerPolicy="no-referrer"
                    onError={(e) => {
                      // Fallback image if needed
                      (e.target as HTMLImageElement).src =
                        'https://lh3.googleusercontent.com/aida-public/AB6AXuCPBw1aUWDdxeBzqKdnrIB4LHVgGJVtUWTdKo2MKhlUUB9Nsrpg5kHNg5Sknjarqj3UXyt2walkHzJsxLcDmaUTyKRap1lbC682uQcCync6zEQaFSDSR0fazAUZFpFF0SbEgj-TY8mnv0GEgb8JzrdOqSd2_TBAhrSXkJEGC5YNeO_67XAe2D1fYskwRFtbqdGDx2Qv5k1MEaO1JnmdDiuKmKr-B5ZCE1HNz41cr_-aEGvczuV9bIkp';
                    }}
                  />
                  <div className="absolute top-3 left-3 z-20">
                    <span
                      className={`px-3 py-1 rounded-full text-xs font-bold shadow-xs backdrop-blur-md ${
                        leader.badgeColor || 'bg-white/90 text-[#1E1B4B]'
                      }`}
                    >
                      {leader.badge}
                    </span>
                  </div>
                </div>

                {/* Info */}
                <div className="flex flex-col gap-1.5">
                  <div className="text-xs uppercase font-bold text-[#D97706] tracking-wider">
                    {leader.role}
                  </div>
                  <h3 className="font-serif text-xl sm:text-2xl font-bold text-[#1E1B4B] group-hover:text-[#312E81] transition-colors leading-snug">
                    {leader.name}
                  </h3>
                  <p className="text-xs sm:text-sm font-serif italic text-[#6E6B7E]">
                    &ldquo;{leader.tagline}&rdquo;
                  </p>
                  <p className="text-xs sm:text-sm text-[#4B485A] leading-relaxed line-clamp-3 mt-1">
                    {leader.shortBio}
                  </p>
                </div>
              </div>

              {/* Bottom Actions */}
              <div className="pt-4 border-t border-[#f5f3ef] flex items-center justify-between gap-2">
                {/* Social links */}
                <div className="flex items-center gap-2">
                  {leader.linkedin && (
                    <a
                      href={leader.linkedin}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="w-8 h-8 rounded-lg bg-[#f5f3ef] hover:bg-[#0077b5] text-[#4B485A] hover:text-white flex items-center justify-center transition-colors"
                      title={`${leader.name} on LinkedIn`}
                    >
                      <svg className="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.46 8.76a1.45 1.45 0 0 0 0-2.9 1.45 1.45 0 0 0 0 2.9m1.4 9.74v-8.37H5.06v8.37h2.8z" />
                      </svg>
                    </a>
                  )}
                  {leader.twitter && (
                    <a
                      href={leader.twitter}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="w-8 h-8 rounded-lg bg-[#f5f3ef] hover:bg-black text-[#4B485A] hover:text-white flex items-center justify-center transition-colors"
                      title="Follow on X"
                    >
                      <svg className="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                        <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                      </svg>
                    </a>
                  )}
                  {leader.email && (
                    <a
                      href={`mailto:${leader.email}?subject=Inquiry%20for%20${encodeURIComponent(leader.name)}`}
                      className="w-8 h-8 rounded-lg bg-[#f5f3ef] hover:bg-[#D97706] text-[#4B485A] hover:text-white flex items-center justify-center transition-colors"
                      title="Contact Secretariat"
                    >
                      <Icon name="mail" size={16} />
                    </a>
                  )}
                </div>

                {/* Modal Trigger */}
                <button
                  onClick={() => setActiveLeader(leader)}
                  className="inline-flex items-center gap-1 text-xs font-bold text-[#1E1B4B] hover:text-[#D97706] transition-colors py-1 px-2 rounded-lg hover:bg-[#f5f3ef]"
                >
                  <span>Full Profile</span>
                  <Icon name="arrow_forward" size={16} />
                </button>
              </div>
            </motion.div>
          ))}
        </motion.div>

        {/* Detailed Leader Profile Modal with Apple Liquid Glass styling */}
        <AnimatePresence>
          {activeLeader && (
            <motion.div
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              className="fixed inset-0 z-50 flex items-center justify-center bg-[#161338]/60 backdrop-blur-md p-4"
              onClick={() => setActiveLeader(null)}
            >
              <motion.div
                initial={{ scale: 0.94, opacity: 0, y: 16 }}
                animate={{ scale: 1, opacity: 1, y: 0 }}
                exit={{ scale: 0.94, opacity: 0, y: 16 }}
                transition={{ type: 'spring', damping: 26, stiffness: 320 }}
                className="w-full max-w-2xl liquid-glass-modal rounded-3xl p-6 sm:p-8 shadow-2xl flex flex-col gap-6 max-h-[90vh] overflow-y-auto relative"
                onClick={(e) => e.stopPropagation()}
              >
                {/* Modal Header */}
                <div className="flex items-start justify-between border-b border-black/5 pb-4">
                  <div className="flex items-center gap-4">
                    <div className="relative w-20 h-24 sm:w-24 sm:h-28 rounded-2xl overflow-hidden bg-[#F4F1EA] shrink-0 border border-white/80 shadow-md flex items-center justify-center">
                      <img
                        src={activeLeader.image}
                        alt=""
                        aria-hidden="true"
                        className="absolute inset-0 w-full h-full object-cover blur-lg opacity-35 scale-110 select-none pointer-events-none"
                      />
                      <img
                        src={activeLeader.image}
                        alt={activeLeader.name}
                        className="relative z-10 w-full h-full object-contain"
                        referrerPolicy="no-referrer"
                      />
                    </div>
                    <div className="flex flex-col">
                      <span className="text-xs uppercase font-bold text-[#D97706] tracking-wider">
                        {activeLeader.role}
                      </span>
                      <h3 className="font-serif text-2xl font-bold text-[#1E1B4B]">
                        {activeLeader.name}
                      </h3>
                      <span className="text-xs text-[#6E6B7E]">
                        {activeLeader.department}
                      </span>
                    </div>
                  </div>

                  <button
                    onClick={() => setActiveLeader(null)}
                    className="w-9 h-9 rounded-full liquid-glass-card hover:bg-white/80 text-[#6E6B7E] flex items-center justify-center transition-all shrink-0 active:scale-90"
                    aria-label="Close modal"
                  >
                    <Icon name="close" size={20} />
                  </button>
                </div>

                {/* Tagline & Quote */}
                <div className="p-4 rounded-2xl bg-amber-50/70 backdrop-blur-md border border-amber-200/60 shadow-xs flex items-start gap-3">
                  <Icon name="format_quote" size={24} className="text-[#D97706] shrink-0" />
                  <div className="flex flex-col">
                    <p className="font-serif text-sm font-semibold text-[#1E1B4B] italic">
                      &ldquo;{activeLeader.quote || activeLeader.tagline}&rdquo;
                    </p>
                    <span className="text-xs text-[#904d00] font-medium mt-1">
                      Leadership Philosophy • Karl Peace Legacy Foundation
                    </span>
                  </div>
                </div>

                {/* Full Bio */}
                <div className="flex flex-col gap-2">
                  <h4 className="text-xs font-bold uppercase tracking-wider text-[#1E1B4B]">
                    Biographical Overview &amp; Mandate
                  </h4>
                  <p className="text-sm text-[#4B485A] leading-relaxed whitespace-pre-line">
                    {activeLeader.fullBio}
                  </p>
                </div>

                {/* Key Contributions */}
                {activeLeader.keyContributions && activeLeader.keyContributions.length > 0 && (
                  <div className="flex flex-col gap-2.5">
                    <h4 className="text-xs font-bold uppercase tracking-wider text-[#1E1B4B]">
                      Key Initiatives &amp; Institutional Milestones
                    </h4>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                      {activeLeader.keyContributions.map((contrib, idx) => (
                        <div
                          key={idx}
                          className="p-3 rounded-xl bg-white/60 backdrop-blur-xs border border-white/60 text-xs text-[#4B485A] flex items-start gap-2 shadow-2xs"
                        >
                          <Icon name="check_circle" size={16} className="text-[#0D9488] shrink-0 mt-0.5" />
                          <span>{contrib}</span>
                        </div>
                      ))}
                    </div>
                  </div>
                )}

                {/* Modal Footer / Direct Channels */}
                <div className="pt-4 border-t border-black/5 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                  <div className="flex items-center gap-2">
                    <span className="text-[#6E6B7E]">Official Verification:</span>
                    <span className="font-bold text-[#1E1B4B]">karlpeacelegacy.org</span>
                  </div>

                  <div className="flex items-center gap-2">
                    {activeLeader.linkedin && (
                      <a
                        href={activeLeader.linkedin}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="px-3.5 py-1.5 rounded-xl bg-[#0077b5] text-white font-bold flex items-center gap-1.5 hover:opacity-90 transition-opacity shadow-sm"
                      >
                        <span>LinkedIn Profile</span>
                      <Icon name="open_in_new" size={14} />
                      </a>
                    )}
                    <a
                      href="mailto:contact@karlpeacelegacy.org"
                      className="liquid-glass-dark-btn px-4 py-1.5 rounded-xl text-white font-bold shadow-sm"
                    >
                      Contact Office
                    </a>
                  </div>
                </div>
              </motion.div>
            </motion.div>
          )}
        </AnimatePresence>
      </div>
    </section>
  );
};
