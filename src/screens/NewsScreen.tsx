import React, { useState } from 'react';
import { motion, AnimatePresence } from 'motion/react';
import { useFoundationData } from '../context/FoundationDataContext';
import { NewsArticle } from '../types';
import { Icon } from '../components/Icon';

interface NewsScreenProps {
  initialArticleId?: string | null;
  onClearInitialArticle?: () => void;
}

export const NewsScreen: React.FC<NewsScreenProps> = ({
  initialArticleId,
  onClearInitialArticle,
}) => {
  const { news } = useFoundationData();
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCategory, setSelectedCategory] = useState<string>('all');
  const [copiedLink, setCopiedLink] = useState(false);
  const [readingArticle, setReadingArticle] = useState<NewsArticle | null>(() => {
    if (initialArticleId) {
      return news.find((a) => a.id === initialArticleId) || null;
    }
    return null;
  });

  const categories = [
    { id: 'all', label: 'All Notices', icon: 'all_inclusive' },
    { id: 'scholarship', label: 'Scholarship Bulletins', icon: 'school' },
    { id: 'health', label: 'Health Drives', icon: 'health_and_safety' },
    { id: 'mentorship', label: 'Mentorship Calls', icon: 'groups' },
    { id: 'bulletin', label: 'Governance', icon: 'policy' },
  ];

  const filteredNews = news.filter((item) => {
    const matchesCategory =
      selectedCategory === 'all' || item.categoryType === selectedCategory;
    const matchesSearch =
      item.title.toLowerCase().includes(searchQuery.toLowerCase()) ||
      item.summary.toLowerCase().includes(searchQuery.toLowerCase());
    return matchesCategory && matchesSearch;
  });

  const handleCloseArticle = () => {
    setReadingArticle(null);
    if (onClearInitialArticle) {
      onClearInitialArticle();
    }
  };

  const handleShareArticle = (article: NewsArticle) => {
    const url = `${window.location.origin}/#news-${article.id}`;
    if (navigator.clipboard) {
      navigator.clipboard.writeText(url);
      setCopiedLink(true);
      setTimeout(() => setCopiedLink(false), 2000);
    }
  };

  return (
    <div className="flex flex-col w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-16 gap-8 sm:gap-12">
      {/* Header */}
      <div className="flex flex-col gap-4 max-w-3xl">
        <div className="inline-flex items-center gap-2 py-1 px-3.5 rounded-full liquid-glass-pill-active text-[#D97706] text-xs uppercase font-bold self-start border border-amber-200/60 shadow-xs">
          <Icon name="newspaper" size={15} className="text-[#D97706]" />
          <span>Official Bulletins &amp; Dispatches</span>
        </div>
        <h1 className="font-serif text-3xl sm:text-5xl font-bold text-[#1E1B4B] leading-tight">
          News &amp; Foundation Announcements
        </h1>
        <p className="text-base sm:text-lg text-[#4B485A] leading-relaxed">
          The verified public record of scholarship releases, outreach workshops, leadership calls, and institutional updates from the Karl Peace Legacy Foundation.
        </p>
      </div>

      {/* Search & Category Filter Controls */}
      <div className="flex flex-col lg:flex-row items-stretch lg:items-center justify-between gap-4 border-b border-black/5 pb-6">
        {/* Category Dock */}
        <div className="flex items-center gap-1.5 p-1.5 rounded-2xl liquid-glass-dock overflow-x-auto scrollbar-none border border-white/80 shadow-sm">
          {categories.map((cat) => {
            const isActive = selectedCategory === cat.id;
            return (
              <button
                key={cat.id}
                onClick={() => setSelectedCategory(cat.id)}
                className={`relative flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs sm:text-sm font-bold whitespace-nowrap transition-all select-none ${
                  isActive
                    ? 'text-[#1E1B4B] font-bold shadow-xs'
                    : 'text-[#4B485A] hover:text-[#1E1B4B] hover:bg-white/40'
                }`}
              >
                {isActive && (
                  <motion.div
                    layoutId="active-news-tab-pill"
                    className="absolute inset-0 rounded-xl liquid-glass-pill-active -z-10"
                    transition={{ type: 'spring', damping: 26, stiffness: 350 }}
                  />
                )}
                <Icon name={cat.icon} size={16} className={isActive ? 'text-[#D97706]' : 'text-[#6E6B7E]'} />
                <span>{cat.label}</span>
              </button>
            );
          })}
        </div>

        {/* Search Input */}
        <div className="relative w-full lg:min-w-[240px] lg:max-w-sm">
          <Icon name="search" size={18} className="text-[#6E6B7E] absolute left-3.5 top-1/2 -translate-y-1/2" />
          <input
            type="text"
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            placeholder="Search bulletins..."
            className="w-full h-11 pl-10 pr-4 rounded-2xl liquid-glass-card border border-white/80 text-xs sm:text-sm text-[#1E1B2E] placeholder:text-[#6E6B7E]/70 focus:outline-none focus:ring-2 focus:ring-[#D97706]/25 shadow-2xs"
          />
        </div>
      </div>

      {/* Announcements Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <AnimatePresence>
          {filteredNews.map((article, idx) => (
            <motion.article
              key={article.id}
              initial={{ opacity: 0, y: 12 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, scale: 0.98 }}
              transition={{ duration: 0.25, delay: idx * 0.04 }}
              onClick={() => setReadingArticle(article)}
              className="liquid-glass-card card-hover-lift rounded-3xl p-6 sm:p-7 border border-white/80 shadow-xs flex flex-col justify-between gap-4 cursor-pointer group relative overflow-hidden"
            >
              {article.isUrgent && (
                <div className="absolute top-0 right-0 left-0 h-1 bg-gradient-to-r from-[#D97706] to-[#F59E0B]" />
              )}
              <div className="flex flex-col gap-3">
                <div className="flex items-center justify-between">
                  <span className="px-3 py-1 rounded-full bg-amber-50/80 text-[#904d00] text-xs font-bold border border-amber-200/60 shadow-2xs">
                    {article.category}
                  </span>
                  <span className="text-xs font-mono text-[#6E6B7E]">{article.date}</span>
                </div>
                <h2 className="font-serif text-lg sm:text-xl font-bold text-[#1E1B4B] group-hover:text-[#D97706] transition-colors leading-snug">
                  {article.title}
                </h2>
                <p className="text-xs sm:text-sm text-[#4B485A] leading-relaxed line-clamp-3">
                  {article.summary}
                </p>
              </div>

              <div className="pt-3 border-t border-black/5 flex items-center justify-between">
                <span className="text-xs text-[#6E6B7E] flex items-center gap-1 font-medium">
                  <Icon name="location_on" size={15} className="text-[#D97706]" />
                  {article.location}
                </span>
                <span className="text-xs font-bold text-[#1E1B4B] group-hover:text-[#D97706] flex items-center gap-1">
                  <span>Read Bulletin</span>
                  <Icon name="arrow_forward" size={16} className="group-hover:translate-x-0.5 transition-transform" />
                </span>
              </div>
            </motion.article>
          ))}
        </AnimatePresence>

        {filteredNews.length === 0 && (
          <div className="md:col-span-2 p-12 text-center rounded-3xl liquid-glass-card border border-white/80 shadow-xs text-sm text-[#6E6B7E]">
            No announcements found matching your criteria.
          </div>
        )}
      </div>

      {/* Reader Modal with Liquid Glass Container */}
      <AnimatePresence>
        {readingArticle && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 z-50 flex items-center justify-center bg-[#161338]/70 backdrop-blur-md p-4"
            onClick={handleCloseArticle}
          >
            <motion.div
              initial={{ scale: 0.94, opacity: 0, y: 16 }}
              animate={{ scale: 1, opacity: 1, y: 0 }}
              exit={{ scale: 0.94, opacity: 0, y: 16 }}
              transition={{ type: 'spring', damping: 26, stiffness: 320 }}
              className="w-full max-w-2xl liquid-glass-modal rounded-3xl p-6 sm:p-8 shadow-2xl border border-white/80 flex flex-col gap-5 max-h-[85vh] overflow-y-auto relative"
              onClick={(e) => e.stopPropagation()}
            >
              <div className="flex items-center justify-between border-b border-black/5 pb-4">
                <div className="flex items-center gap-2">
                  <span className="px-3 py-1 rounded-full bg-amber-50 text-[#904d00] text-xs font-bold border border-amber-200/60 shadow-2xs">
                    {readingArticle.category}
                  </span>
                  <span className="text-xs text-[#6E6B7E] font-medium">{readingArticle.cycle}</span>
                </div>
                <div className="flex items-center gap-2">
                  <button
                    onClick={() => handleShareArticle(readingArticle)}
                    className="px-3 py-1.5 rounded-xl liquid-glass-card hover:bg-white/90 text-xs font-semibold text-[#1E1B4B] flex items-center gap-1 shadow-2xs"
                  >
                    <Icon name={copiedLink ? 'done' : 'share'} size={15} />
                    <span>{copiedLink ? 'Copied' : 'Share'}</span>
                  </button>
                  <button
                    onClick={handleCloseArticle}
                    className="w-8 h-8 rounded-full liquid-glass-card hover:bg-white/90 flex items-center justify-center text-[#6E6B7E]"
                  >
                    <Icon name="close" size={18} />
                  </button>
                </div>
              </div>

              <div className="flex flex-col gap-2">
                <h2 className="font-serif text-2xl font-bold text-[#1E1B4B] leading-snug">
                  {readingArticle.title}
                </h2>
                <div className="flex items-center gap-4 text-xs text-[#6E6B7E]">
                  <span className="font-mono">{readingArticle.date}</span>
                  <span>•</span>
                  <span className="font-medium">{readingArticle.location}</span>
                </div>
              </div>

              <div className="text-sm text-[#4B485A] space-y-3 leading-relaxed whitespace-pre-line border-t border-black/5 pt-4">
                {readingArticle.fullContent}
              </div>

              <div className="p-4 rounded-2xl liquid-glass-card border border-white/80 text-xs text-[#6E6B7E] flex items-center justify-between shadow-2xs">
                <span>Official Foundation Bulletin • karlpeacelegacy.org</span>
                <a
                  href="mailto:contact@karlpeacelegacy.org"
                  className="text-[#D97706] font-bold hover:underline"
                >
                  Contact Secretariat
                </a>
              </div>
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
};
