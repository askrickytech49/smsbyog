import React, { useState } from 'react';
import { motion, AnimatePresence } from 'motion/react';
import { ScreenType } from '../types';
import { useFoundationData } from '../context/FoundationDataContext';
import { Icon } from '../components/Icon';

interface ProgramsScreenProps {
  onNavigate: (screen: ScreenType) => void;
  onOpenNotifyModal: () => void;
}

export const ProgramsScreen: React.FC<ProgramsScreenProps> = ({
  onNavigate,
  onOpenNotifyModal,
}) => {
  const { programs } = useFoundationData();
  const [activeCategory, setActiveCategory] = useState<string>('all');
  const [searchQuery, setSearchQuery] = useState<string>('');

  const categories = [
    { id: 'all', label: 'All Programs', icon: 'all_inclusive' },
    { id: 'scholarships', label: 'Scholarships', icon: 'school' },
    { id: 'mentorship', label: 'Mentorship', icon: 'diversity_3' },
    { id: 'health', label: 'Public Health', icon: 'health_and_safety' },
    { id: 'opportunities', label: 'Opportunities', icon: 'lightbulb' },
  ];

  const filteredPrograms = programs.filter((prog) => {
    const matchesCategory = activeCategory === 'all' || prog.category === activeCategory;
    const matchesSearch =
      prog.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
      prog.description.toLowerCase().includes(searchQuery.toLowerCase()) ||
      prog.keyPoints?.some((kp) => kp.toLowerCase().includes(searchQuery.toLowerCase()));
    return matchesCategory && matchesSearch;
  });

  return (
    <div className="flex flex-col w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-16 gap-8 sm:gap-12">
      {/* Header */}
      <div className="flex flex-col gap-4 max-w-3xl">
        <div className="inline-flex items-center gap-2 py-1 px-3.5 rounded-full liquid-glass-pill-active text-[#D97706] text-xs uppercase font-bold self-start border border-amber-200/60 shadow-xs">
          <Icon name="account_tree" size={15} className="text-[#D97706]" />
          <span>Core Programmatic Pillars</span>
        </div>
        <h1 className="font-serif text-3xl sm:text-5xl font-bold text-[#1E1B4B] leading-tight">
          Structured Pathways to Excellence
        </h1>
        <p className="text-base sm:text-lg text-[#4B485A] leading-relaxed">
          Through deliberate, high-impact initiatives, the Karl Peace Legacy Foundation equips ambitious young Nigerians with the academic, professional, and health resources required to lead transformative lives.
        </p>
      </div>

      {/* Interactive Controls: Liquid Glass Segmented Dock + Fast Search */}
      <div className="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4 border-b border-black/5 pb-6">
        {/* Category Dock */}
        <div className="flex items-center gap-1.5 p-1.5 rounded-2xl liquid-glass-dock overflow-x-auto scrollbar-none border border-white/80 shadow-md">
          {categories.map((cat) => {
            const isActive = activeCategory === cat.id;
            return (
              <button
                key={cat.id}
                onClick={() => setActiveCategory(cat.id)}
                className={`relative flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all select-none ${
                  isActive
                    ? 'text-[#1E1B4B] font-bold shadow-xs'
                    : 'text-[#4B485A] hover:text-[#1E1B4B] hover:bg-white/40'
                }`}
              >
                {isActive && (
                  <motion.div
                    layoutId="active-programs-tab-pill"
                    className="absolute inset-0 rounded-xl liquid-glass-pill-active -z-10"
                    transition={{ type: 'spring', damping: 26, stiffness: 350 }}
                  />
                )}
                <Icon name={cat.icon} size={17} className={isActive ? 'text-[#D97706]' : 'text-[#6E6B7E]'} />
                <span>{cat.label}</span>
              </button>
            );
          })}
        </div>

        {/* Search Field */}
        <div className="relative w-full lg:min-w-[240px] lg:max-w-sm">
          <Icon name="search" size={18} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#6E6B7E]" />
          <input
            type="text"
            placeholder="Search programs or criteria..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="w-full h-11 pl-10 pr-8 rounded-2xl liquid-glass-card border border-white/80 text-xs sm:text-sm text-[#1E1B2E] placeholder:text-[#6E6B7E]/70 focus:outline-none focus:ring-2 focus:ring-[#D97706]/25 shadow-2xs"
          />
          {searchQuery && (
            <button
              onClick={() => setSearchQuery('')}
              className="absolute right-3 top-1/2 -translate-y-1/2 text-[#6E6B7E] hover:text-[#1E1B4B]"
            >
              <Icon name="close" size={16} />
            </button>
          )}
        </div>
      </div>

      {/* Program Detailed Cards */}
      <div className="grid grid-cols-1 gap-8">
        <AnimatePresence>
          {filteredPrograms.map((prog, index) => (
            <motion.article
              key={prog.id}
              initial={{ opacity: 0, y: 16 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, scale: 0.98 }}
              transition={{ duration: 0.3, delay: index * 0.05 }}
              className="liquid-glass-card card-hover-lift rounded-3xl overflow-hidden border border-white/80 grid grid-cols-1 md:grid-cols-12 shadow-md relative"
            >
              {/* Image Column */}
              <div className="md:col-span-5 h-64 md:h-auto relative overflow-hidden bg-[#161338]">
                <img
                  src={prog.image}
                  alt={prog.title}
                  className="w-full h-full object-cover transition-transform duration-700 hover:scale-105"
                  referrerPolicy="no-referrer"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent pointer-events-none" />
                <div className="absolute top-4 left-4">
                  <span className={`px-3 py-1.5 rounded-full text-xs font-bold shadow-md backdrop-blur-md ${prog.badgeColor}`}>
                    {prog.badge}
                  </span>
                </div>
              </div>

              {/* Content Column */}
              <div className="md:col-span-7 p-6 sm:p-8 flex flex-col justify-between gap-5 bg-white/50 backdrop-blur-xs">
                <div className="flex flex-col gap-3">
                  <div className="flex items-center gap-2">
                    <span className="text-xs uppercase font-bold text-[#D97706] tracking-wider">
                      Initiative • {prog.category.toUpperCase()}
                    </span>
                  </div>
                  <h2 className="font-serif text-2xl sm:text-3xl font-bold text-[#1E1B4B]">
                    {prog.title}
                  </h2>
                  <p className="text-sm sm:text-base text-[#4B485A] leading-relaxed">
                    {prog.detailedNarrative || prog.description}
                  </p>

                  {/* Key Activities List */}
                  <div className="pt-2">
                    <h4 className="text-xs font-bold uppercase tracking-wider text-[#1E1B4B] mb-2">
                      Key Activities &amp; Resource Delivery:
                    </h4>
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
                      {prog.keyPoints.map((point, idx) => (
                        <div
                          key={idx}
                          className="flex items-center gap-2 p-2.5 rounded-xl bg-white/70 border border-white/80 text-xs font-medium text-[#1E1B2E] shadow-2xs"
                        >
                          <Icon name="check_circle" size={17} className="text-[#0D9488] shrink-0" />
                          <span>{point}</span>
                        </div>
                      ))}
                    </div>
                  </div>

                  {/* Eligibility & Timeline */}
                  <div className="mt-2 p-3.5 rounded-2xl bg-amber-50/70 border border-amber-200/60 text-xs text-[#904d00] flex flex-col gap-1 backdrop-blur-xs shadow-2xs">
                    <div>
                      <strong>Target Group: </strong>
                      <span>{prog.eligibilitySnippet}</span>
                    </div>
                    <div>
                      <strong>Operational Status: </strong>
                      <span>{prog.timeline}</span>
                    </div>
                  </div>
                </div>

                {/* Action Buttons with Liquid Glass refinement */}
                <div className="flex flex-col sm:flex-row gap-3 pt-3 border-t border-black/5">
                  {prog.category === 'scholarships' ? (
                    <>
                      <motion.button
                        whileHover={{ scale: 1.02 }}
                        whileTap={{ scale: 0.97 }}
                        onClick={() => onNavigate('scholarships')}
                        className="liquid-glass-amber-btn glass-refraction h-11 px-6 rounded-xl text-white text-xs sm:text-sm font-bold flex items-center justify-center gap-2 shadow-md"
                      >
                        <Icon name="workspace_premium" size={18} />
                        <span>Scholarship Guidelines &amp; Criteria</span>
                      </motion.button>
                      <motion.button
                        whileHover={{ scale: 1.02 }}
                        whileTap={{ scale: 0.97 }}
                        onClick={onOpenNotifyModal}
                        className="liquid-glass-dark-btn glass-refraction h-11 px-5 rounded-xl text-white text-xs sm:text-sm font-bold flex items-center justify-center gap-2 shadow-sm"
                      >
                        <Icon name="notifications_active" size={18} className="text-[#F59E0B]" />
                        <span>Join Notification Register</span>
                      </motion.button>
                    </>
                  ) : (
                    <>
                      <motion.button
                        whileHover={{ scale: 1.02 }}
                        whileTap={{ scale: 0.97 }}
                        onClick={() => onNavigate('contact-us')}
                        className="liquid-glass-dark-btn glass-refraction h-11 px-6 rounded-xl text-white text-xs sm:text-sm font-bold flex items-center justify-center gap-2 shadow-sm"
                      >
                        <span>Express Interest / Get Involved</span>
                        <Icon name="arrow_forward" size={16} />
                      </motion.button>
                      <motion.button
                        whileHover={{ scale: 1.02 }}
                        whileTap={{ scale: 0.97 }}
                        onClick={() => onNavigate('impact-and-gallery')}
                        className="liquid-glass-light-btn glass-refraction h-11 px-5 rounded-xl text-[#1E1B4B] text-xs sm:text-sm font-bold flex items-center justify-center gap-2 shadow-2xs"
                      >
                        <span>View Community Photos</span>
                      </motion.button>
                    </>
                  )}
                </div>
              </div>
            </motion.article>
          ))}
        </AnimatePresence>

        {filteredPrograms.length === 0 && (
          <div className="p-12 rounded-3xl liquid-glass-card text-center flex flex-col items-center gap-3 border border-white/80 shadow-xs">
            <Icon name="search_off" size={48} className="text-[#6E6B7E]" />
            <h3 className="font-serif text-xl font-bold text-[#1E1B4B]">No matching programs found</h3>
            <p className="text-sm text-[#4B485A]">Try selecting another category or clearing your search keywords.</p>
            <button
              onClick={() => {
                setActiveCategory('all');
                setSearchQuery('');
              }}
              className="mt-2 px-5 py-2 rounded-xl liquid-glass-amber-btn text-white text-xs font-bold"
            >
              Reset Filters
            </button>
          </div>
        )}
      </div>
    </div>
  );
};
