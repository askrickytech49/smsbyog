export type ScreenType = 
  | 'home' 
  | 'about-us' 
  | 'our-programs' 
  | 'scholarships' 
  | 'impact-and-gallery' 
  | 'news-and-updates' 
  | 'contact-us' 
  | 'get-involved'
  | 'apply'
  | 'admin';

export interface SiteSettings {
  heroTitle: string;
  heroSubtitle: string;
  heroBadge: string;
  contactEmail: string;
  contactPhone: string;
  officeAddress: string;
  registeredAddress?: string;
  registrationNumber?: string;
  officialDomain?: string;
  scholarshipAlertActive: boolean;
  scholarshipAlertTitle: string;
  scholarshipAlertText: string;
  scholarshipAlertDeadline: string;
  scholarshipAlertCycle: string;
  updatedAt?: string;
  updatedBy?: string;
}

export interface SubscriberItem {
  id: string;
  name: string;
  email: string;
  institution?: string;
  course?: string;
  createdAt: string;
  status?: 'new' | 'notified' | 'archived';
}

export interface InquiryItem {
  id: string;
  name: string;
  email: string;
  subject?: string;
  message: string;
  role?: string;
  createdAt: string;
  status?: 'unread' | 'replied' | 'archived';
}

export interface AdminUser {
  uid: string;
  email: string;
  displayName?: string;
  photoURL?: string;
  role: 'admin' | 'editor' | 'viewer';
  createdAt?: string;
}

export interface ProgramItem {
  id: string;
  title: string;
  category: 'scholarships' | 'mentorship' | 'health' | 'opportunities';
  badge: string;
  badgeColor: string;
  image: string;
  description: string;
  keyPoints: string[];
  detailedNarrative?: string;
  eligibilitySnippet?: string;
  timeline?: string;
}

export interface NewsArticle {
  id: string;
  title: string;
  category: string;
  categoryType: 'scholarship' | 'health' | 'mentorship' | 'bulletin';
  cycle: string;
  summary: string;
  fullContent: string;
  location: string;
  date: string;
  isUrgent?: boolean;
}

export interface GalleryPhoto {
  id: string;
  title: string;
  caption: string;
  category: 'all' | 'scholarships' | 'mentorship' | 'health' | 'community';
  image: string;
  location: string;
  date: string;
}

export interface TestimonialItem {
  id: string;
  name: string;
  institution: string;
  field: string;
  quote: string;
  year: string;
}

export interface FAQItem {
  question: string;
  answer: string;
  category: 'scholarships' | 'mentorship' | 'general';
}

export interface TeamMember {
  id: string;
  name: string;
  role: string;
  category: 'founder' | 'executive' | 'operations' | 'patron';
  tagline: string;
  shortBio: string;
  fullBio: string;
  image: string;
  badge: string;
  badgeColor?: string;
  linkedin?: string;
  twitter?: string;
  email?: string;
  keyContributions?: string[];
  department?: string;
  quote?: string;
}

export interface ApplicationItem {
  id: string;
  firstName: string;
  lastName: string;
  email: string;
  phone: string;
  dateOfBirth?: string;
  gender?: 'male' | 'female' | 'prefer_not_to_say';
  stateOfOrigin?: string;
  lga?: string;
  homeAddress?: string;
  institution: string;
  institutionType?: 'university' | 'polytechnic' | 'college_of_education' | 'other';
  faculty?: string;
  department: string;
  matricNumber?: string;
  academicLevel: '100' | '200' | '300' | '400' | '500' | 'postgraduate';
  cgpa?: number;
  cgpaScale?: '4.0' | '5.0';
  academicYear?: string;
  scholarshipCycle: string;
  personalStatement?: string;
  whyDeserve?: string;
  careerGoals?: string;
  transcriptUrl?: string;
  idCardUrl?: string;
  admissionLetterUrl?: string;
  passportPhotoUrl?: string;
  recommendationUrl?: string;
  status: 'draft' | 'submitted' | 'under_review' | 'shortlisted' | 'approved' | 'rejected' | 'withdrawn';
  reviewerNotes?: string;
  awardedAmount?: number;
  awardNotes?: string;
  submittedAt?: string;
  createdAt: string;
}
