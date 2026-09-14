import { ProgramItem, NewsArticle, GalleryPhoto, TestimonialItem, FAQItem, TeamMember } from '../types';
import heroStudentPhoto from '../assets/images/nigerian_students_hero_1788724596712.jpg';
import foundationLogo from '../assets/images/karl-peace-logo.png';

// ─── Try to import local images, fall back to external URLs if not present ───
let scholarshipsImg = 'https://lh3.googleusercontent.com/aida-public/AB6AXuAfH0kPikT0QiqDRv7PCerrIkhewtlPT1UilFnJe2J-v9ccpzmYQr7trnsViwywFYFPnIJQh7KlxthyrPVEDDG7cV0rEHj7SpsNrk639xScSKOjsUwjAcjMhc7ft9q4n92BnHiejmNFzgzFBKLKntuiO58p3_PXMQtFnSX9u9h49ssiDLuEOsErBkV5ihedwXEf9N0kPN-JNawg2UEcMKJUdWFOhfplQVFzawVnFEAKPAiH-xPllWwP';
let mentorshipImg  = 'https://lh3.googleusercontent.com/aida-public/AB6AXuAsofiWa8x-TN1fzS6rYdc9yM4-d-JQg8q3jVEzUdZvnqIAdXZaFex4BvjHpNMf0ChZIa57wg_uecatIv6DJHsltwFYn_53KXAS-O-9TlK-oPIovKRxENZ6NlcFMqpc65lp5TNf6KG1lTSVXmYzOuFxnMXwOPgKQ-tWMtLxQQ1p3RTOeehwtvGTALZXxP1wNkvcUOCDqunqq0dx7H9Bvgqf_yOZ5cV9Oe-Jdac65D_mgWWCkDWHTiaQ';
let healthImg      = 'https://lh3.googleusercontent.com/aida-public/AB6AXuCPBw1aUWDdxeBzqKdnrIB4LHVgGJVtUWTdKo2MKhlUUB9Nsrpg5kHNg5Sknjarqj3UXyt2walkHzJsxLcDmaUTyKRap1lbC682uQcCync6zEQaFSDSR0fazAUZFpFF0SbEgj-TY8mnv0GEgb8JzrdOqSd2_TBAhrSXkJEGC5YNeO_67XAe2D1fYskwRFtbqdGDx2Qv5k1MEaO1JnmdDiuKmKr-B5ZCE1HNz41cr_-aEGvczuV9bIkp';

// Once you save local copies, uncomment these lines and delete the fallback URLs above:
// import scholarshipsImg from '../assets/images/scholarships.jpg';
// import mentorshipImg   from '../assets/images/mentorship.jpg';
// import healthImg       from '../assets/images/health.jpg';

export const FOUNDATION_LOGO = foundationLogo;

export const FOUNDATION_IMAGES = {
  hero: heroStudentPhoto,
  scholarships: scholarshipsImg,
  mentorship: mentorshipImg,
  health: healthImg,
};

export const FOUNDATION_PROGRAMS: ProgramItem[] = [
  {
    id: 'scholarships',
    title: 'Scholarships',
    category: 'scholarships',
    badge: 'Direct Aid',
    badgeColor: 'bg-[#FEF3C7] text-[#904d00]',
    image: FOUNDATION_IMAGES.scholarships,
    description: 'This program provides financial support for deserving students to pursue higher education across accredited Nigerian institutions.',
    keyPoints: [
      'Tuition Assistance & Registration Relief',
      'Tertiary Grants for Undergraduates',
      'Essential Academic Materials & Textbooks',
    ],
    detailedNarrative: 'The Karl Peace Legacy Tertiary Scholarship scheme directly relieves the crushing financial pressures on undergraduates enrolled in Nigerian federal, state, and accredited private tertiary institutions. Beyond tuition, we allocate resources for departmental registration dues, laboratory fees, and curriculum literature to prevent academic dropouts.',
    eligibilitySnippet: 'Enrolled undergraduate students in recognized Nigerian Universities, Polytechnics, or Colleges of Education with a minimum CGPA of 3.0/5.0 or 2.5/4.0.',
    timeline: 'Applications for 2025/2026 Academic Cycle opening shortly.'
  },
  {
    id: 'mentorship',
    title: 'Mentorship & Youth Development',
    category: 'mentorship',
    badge: 'Leadership',
    badgeColor: 'bg-[#e3dfff] text-[#181445]',
    image: FOUNDATION_IMAGES.mentorship,
    description: 'This initiative connects young people with experienced guides for career growth, strengthening confidence, and ethical leadership.',
    keyPoints: [
      'One-on-One Career Advisory with Senior Professionals',
      'Ethical Leadership & Civic Governance Seminars',
      'Resume & Technical Portfolio Masterclasses',
      'Graduate Transition & Internship Placement Guidance',
    ],
    detailedNarrative: 'Talent requires cultivation. Our mentorship pairings connect tertiary scholars and ambitious school leavers with vetted mentors across academia, healthcare, biotechnology, engineering, law, and business administration. Through structured virtual and in-person check-ins, mentees cultivate critical thinking, public presentation, and professional integrity.',
    eligibilitySnippet: 'Open to registered scholarship beneficiaries and high-potential Nigerian youth between ages 18 and 29.',
    timeline: 'Rolling cohort enrollment and bi-annual leadership bootcamps.'
  },
  {
    id: 'health',
    title: 'Public Health Initiatives',
    category: 'health',
    badge: 'Community Wellness',
    badgeColor: 'bg-[#FDF1E7] text-[#0D9488]',
    image: FOUNDATION_IMAGES.health,
    description: 'This campaign delivers vital health resources, hygiene education, and awareness to local communities and educational environments.',
    keyPoints: [
      'Preventative Health Literacy Workshops',
      'Community Hygiene & Sanitation Supply Drives',
      'Youth Mental Wellbeing & Resilience Circles',
      'Campus Health Screening & Maternal Care Awareness',
    ],
    detailedNarrative: 'Good health is the bedrock of educational achievement. Guided by Dr. Karl E. Peace’s lifelong contributions to biostatistics and public health science, our grassroots health drives equip underserved communities and university campuses with preventative medical information, water sanitation supplies, and maternal-child health resources.',
    eligibilitySnippet: 'Community organizations, student health unions, and local clinics across partner states.',
    timeline: 'Quarterly field drives in Abuja, Lagos, and surrounding catchment areas.'
  },
  {
    id: 'opportunities',
    title: 'Access to Opportunities',
    category: 'opportunities',
    badge: 'Empowerment',
    badgeColor: 'bg-[#ffdcc3] text-[#663500]',
    image: FOUNDATION_IMAGES.hero,
    description: 'Connecting young people with educational advancement, professional development, and practical career tools to make the most of emerging prospects.',
    keyPoints: [
      'Digital Literacy & Applied Technical Training',
      'Postgraduate Fellowship Preparation & GRE/IELTS Resources',
      'Entrepreneurship Seed Incubators for Student Innovators',
      'Civic Engagement & Community Service Fellowships',
    ],
    detailedNarrative: 'We actively dismantle the information asymmetry that holds back brilliant youth in developing economies. Through curated newsletters, scholarship tracking dashboards, competitive grant drafting workshops, and sponsored access to global educational tools, we expand the horizons of our scholars.',
    eligibilitySnippet: 'All enrolled fellows, alumni, and registered applicants across Nigeria.',
    timeline: 'Continuous resource sharing and seasonal fellowship calls.'
  }
];

export const FOUNDATION_NEWS: NewsArticle[] = [
  {
    id: 'announcement-1',
    title: '2025/2026 Tertiary Scholarship Framework Announced',
    category: 'Official Bulletin',
    categoryType: 'scholarship',
    cycle: 'Academic Cycle 2025/2026',
    date: 'August 28, 2025',
    location: 'Abuja & Lagos, Nigeria',
    isUrgent: true,
    summary: 'The Foundation board has established the criteria, verification safeguards, and allocation roadmap for the upcoming academic cycle. Tertiary students are advised to prepare documentation.',
    fullContent: `The Board of Trustees of the Karl Peace Legacy Foundation is pleased to announce the formal ratification of the 2025/2026 Tertiary Scholarship Framework for Nigerian students. 

Building on our foundational mandate to dismantle educational barriers for committed scholars, this upcoming cycle introduces an expanded allocation covering both university tuition relief and technical learning stipends.

Key highlights of the upcoming cycle:
1. Scope of Support: Full tuition coverage assistance, supplementary book allowance, and mandatory placement in our structured Executive Mentorship Program.
2. Target Beneficiaries: Undergraduate students enrolled full-time in accredited Nigerian federal, state, or approved private universities and polytechnics who demonstrate both academic excellence and verified financial hardship.
3. Verification Rigor: In partnership with university student affairs divisions, all academic transcripts and enrollment statuses will undergo verification to ensure transparent, merit-based disbursement.
4. Portal Opening: The online portal for initial applications will open in the coming weeks strictly via karlpeacelegacy.org. Students are encouraged to register their contact details on our official notification list to receive immediate application instructions upon release.`
  },
  {
    id: 'announcement-2',
    title: 'Community Health Awareness & Youth Mentorship Drive',
    category: 'Outreach Drive',
    categoryType: 'health',
    cycle: 'Quarterly Program',
    date: 'July 14, 2025',
    location: 'Abuja & Lagos, Nigeria',
    isUrgent: false,
    summary: 'Upcoming regional workshops aimed at secondary and university youth, blending preventive health literacy with guided career pathway counseling sessions.',
    fullContent: `The Karl Peace Legacy Foundation will conduct its Q3 Community Health and Youth Mentorship outreach across designated community learning centers and tertiary campuses in Abuja (FCT) and Lagos State.

Public health education and career guidance represent two wings of the same eagle. During these interactive full-day sessions:
- Trained healthcare practitioners will deliver pragmatic guidance on water safety, preventative hygiene practices, and mental resilience during stressful academic exam seasons.
- Senior industry mentors and university professors will facilitate breakout discussions on STEM careers, biostatistics, public administration, and technology entrepreneurship.
- Distribution of basic health kits and scholarly stationery packs to all attendees.

Admission to the sessions is completely free of charge. Local student representatives and youth community leaders wishing to coordinate venue logistics are invited to contact the Foundation office.`
  },
  {
    id: 'announcement-3',
    title: 'Call for Volunteer Mentors: 2025 Mentor Cohort Induction',
    category: 'Leadership Drive',
    categoryType: 'mentorship',
    cycle: 'Cohort 3',
    date: 'June 05, 2025',
    location: 'Virtual & Hybrid (Nigeria & Diaspora)',
    isUrgent: false,
    summary: 'Inviting accomplished Nigerian and international professionals across medicine, sciences, engineering, law, and education to mentor aspiring tertiary scholars.',
    fullContent: `Are you a mid-to-senior level professional, researcher, or institutional leader passionate about paying forward the guidance you received? 

The Karl Peace Legacy Foundation is formally inviting applications from prospective mentors to join our 2025 Mentorship Faculty. Mentors will be paired with 1-2 undergraduate scholars for a structured 9-month mentorship cycle focused on academic discipline, career planning, post-graduate study preparations, and ethical leadership.

Commitment involves approximately 2 hours per month via scheduled virtual advisory sessions, alongside participation in quarterly virtual fireside panels. Interested professionals can submit their credentials via our Get Involved portal.`
  },
  {
    id: 'announcement-4',
    title: 'Annual Institutional Governance and Stewardship Report Released',
    category: 'Governance Bulletin',
    categoryType: 'bulletin',
    cycle: 'FY 2024/2025',
    date: 'May 12, 2025',
    location: 'Official Secretariat, Abuja',
    isUrgent: false,
    summary: 'Reflecting our bedrock dedication to fiduciary integrity, complete transparency, and grassroots efficacy in educational grant allocation.',
    fullContent: `In accordance with the Karl Peace Legacy Foundation charter of transparency and institutional excellence, the Secretariat has made available the summary of our programmatic stewardship for the past operational cycle.

All funds deployed toward scholarship subsidies, workshop venues, and public health supplies have been audited with zero administrative deduction from direct student tuition disbursements. We remain deeply grateful to our institutional allies and volunteer advisors who make this transparent impact possible.`
  }
];

export const FOUNDATION_GALLERY: GalleryPhoto[] = [
  {
    id: 'gal-1',
    title: 'Scholars in Academic Fellowship',
    caption: 'Undergraduate fellows in lively academic discussion at the university quadrangle, embodying optimism and scholarly camaraderie.',
    category: 'scholarships',
    image: FOUNDATION_IMAGES.hero,
    location: 'University Campus, Nigeria',
    date: '2025'
  },
  {
    id: 'gal-2',
    title: 'Focused Study & Research Laboratory',
    caption: 'Tertiary students conducting collaborative STEM and healthcare research supported by curriculum materials and library access.',
    category: 'scholarships',
    image: FOUNDATION_IMAGES.scholarships,
    location: 'Academic Library & Research Hall',
    date: '2025'
  },
  {
    id: 'gal-3',
    title: 'One-on-One Career Advisory',
    caption: 'Executive mentor reviewing a final-year scholar’s capstone portfolio and discussing ethical leadership and career trajectories.',
    category: 'mentorship',
    image: FOUNDATION_IMAGES.mentorship,
    location: 'Lagos Mentorship Center',
    date: '2025'
  },
  {
    id: 'gal-4',
    title: 'Community Health Awareness Session',
    caption: 'Public health educator demonstrating preventive hygiene techniques to local youth and community members.',
    category: 'health',
    image: FOUNDATION_IMAGES.health,
    location: 'Community Health Hub',
    date: '2025'
  }
];

export const FOUNDATION_TESTIMONIALS: TestimonialItem[] = [
  {
    id: 't-1',
    name: 'Chidinma Nwachukwu',
    institution: 'University of Ibadan',
    field: 'Biochemistry, 300L',
    quote: 'The Karl Peace Legacy scholarship lifted an unimaginable burden off my mother’s shoulders when school fees were due. More than the financial relief, my mentor guided me through publishing my first undergraduate seminar abstract.',
    year: '2024 Scholar'
  },
  {
    id: 't-2',
    name: 'Emmanuel Babatunde',
    institution: 'Federal University of Technology, Akure',
    field: 'Computer Engineering, 400L',
    quote: 'Being paired with a senior software architect through the Mentorship Initiative transformed how I approached software design and job interviews. This foundation doesn’t just give money; it invests in your character.',
    year: '2024 Scholar'
  },
  {
    id: 't-3',
    name: 'Zainab Abubakar',
    institution: 'Ahmadu Bello University, Zaria',
    field: 'Medicine & Surgery, 500L',
    quote: 'The focus on public health and community service inspired my clinical group to organize rural vaccination awareness drives. The Foundation’s ethos of giving back is truly contagious.',
    year: '2023 Scholar'
  }
];

export const SCHOLARSHIP_FAQS: FAQItem[] = [
  {
    question: 'Who is eligible to apply for the Karl Peace Legacy Scholarship?',
    answer: 'Applicants must be verified Nigerian citizens currently enrolled as full-time undergraduate students in an accredited Nigerian federal or state university, polytechnic, or college of education. Applicants must demonstrate satisfactory academic performance (minimum 3.0 CGPA on a 5.0 scale or equivalent) and genuine financial need.',
    category: 'scholarships'
  },
  {
    question: 'What does the scholarship award cover?',
    answer: 'The award provides comprehensive assistance toward tuition fees, mandatory departmental dues, and a stipend toward approved core textbooks and academic project expenses for the designated academic year.',
    category: 'scholarships'
  },
  {
    question: 'Is there any application fee required?',
    answer: 'Absolutely not. The Karl Peace Legacy Foundation NEVER charges any fee for scholarship applications, forms, verification, or award disbursement. Any person or portal requesting money is fraudulent. Applications are handled solely through karlpeacelegacy.org.',
    category: 'scholarships'
  },
  {
    question: 'What documents will I need to submit when the portal opens?',
    answer: 'You will need: (1) Official Admission Letter from JAMB and your institution, (2) Current Student ID Card, (3) Certified academic transcript or statement of results for the most recent academic session, (4) A letter of identification from your Local Government Area (LGA) or Dean of Student Affairs, and (5) A concise personal statement detailing your academic objectives and community leadership vision.',
    category: 'scholarships'
  },
  {
    question: 'How does the mentorship component work?',
    answer: 'Every scholarship recipient is paired with a dedicated professional or academic mentor in their discipline or allied industry. Mentors conduct regular advisory sessions covering study strategies, career planning, post-graduate opportunities, and ethics.',
    category: 'mentorship'
  },
  {
    question: 'How will I know if my application is successful?',
    answer: 'Shortlisted candidates will receive official communication directly from our verified domain (contact@karlpeacelegacy.org) followed by a virtual or regional interview before the public publication of awardees.',
    category: 'general'
  }
];

export const FOUNDATION_LEADERS: TeamMember[] = [
  {
    id: 'emmanuel-azu',
    name: 'Emmanuel Unimke Azu',
    role: 'Founder and President',
    category: 'founder',
    badge: 'Founder & President',
    badgeColor: 'bg-[#FEF3C7] text-[#904d00]',
    image: 'https://karlpeacelegacy.org/wp-content/uploads/2026/08/Image_20260206_230804-683x1024.jpeg',
    tagline: 'This leader brings years of experience and a wealth of knowledge to the foundation.',
    shortBio: 'Visionary founder directing the mission, institutional partnerships, and nationwide educational outreach across Nigeria.',
    fullBio: 'Emmanuel Unimke Azu is the Founder and President of the Karl Peace Legacy Foundation. With a profound conviction that education is the ultimate catalyst for generational progress, he founded the organization to advance tertiary scholarships, high-impact youth mentorship, and community health interventions across Nigeria. Drawing inspiration from Dr. Karl E. Peace’s legendary humanitarian commitment, Emmanuel oversees the foundation’s strategic governance, fosters bilateral collaborations with tertiary faculties, and ensures all resources reach verified, high-need Nigerian undergraduates.',
    department: 'Executive Governance & Direction',
    linkedin: 'https://www.linkedin.com/in/azuemmanuel',
    twitter: 'https://x.com/karlpeacelegacy',
    email: 'contact@karlpeacelegacy.org',
    quote: 'True leadership is measured by the doors we unlock for the next generation.',
    keyContributions: [
      'Founded the Karl Peace Legacy Foundation in Nigeria to bridge educational inequalities.',
      'Established transparent, merit-and-need scholarship criteria across accredited tertiary institutions.',
      'Forged partnerships with university deans, community organizations, and healthcare allies.',
      'Instilled strict zero-fee and fiduciary oversight standards across all foundation operations.'
    ]
  },
  {
    id: 'sylvanus-azu',
    name: 'Sylvanus Adingel Azu',
    role: 'Program Director',
    category: 'executive',
    badge: 'Program Director',
    badgeColor: 'bg-[#e3dfff] text-[#181445]',
    image: 'https://karlpeacelegacy.org/wp-content/uploads/2026/08/Sly.jpg',
    tagline: 'This member is known for their creativity and exceptional problem-solving skills.',
    shortBio: 'Strategic programs architect managing scholarship delivery pipelines, mentor matching, and field deployments.',
    fullBio: 'Sylvanus Adingel Azu serves as Program Director, spearheading the operational design and ground-level execution of the foundation’s core initiatives. Renowned for innovative problem-solving and operational rigor, Sylvanus leads the vetting procedures for scholarship applicants, coordinates cohort matchings between Nigerian students and experienced executive mentors, and directs community outreach caravans in Abuja, Lagos, and surrounding states.',
    department: 'Program Operations & Student Affairs',
    linkedin: 'https://www.linkedin.com/in/sylvanus-azu-782091237',
    twitter: 'https://x.com/karlpeacelegacy',
    email: 'contact@karlpeacelegacy.org',
    quote: 'Every program we design must deliver concrete, measurable dignity to our scholars and communities.',
    keyContributions: [
      'Designed the multi-stage scholarship vetting and academic verification roadmap.',
      'Curated a distinguished mentor network spanning medical science, engineering, and enterprise.',
      'Directed logistics for community hygiene and preventative health education caravans.',
      'Established student support channels ensuring rapid assistance for prospective applicants.'
    ]
  },
  {
    id: 'ann-abang',
    name: 'Ann Eekpang Abang',
    role: 'Secretary General',
    category: 'executive',
    badge: 'Secretary General',
    badgeColor: 'bg-[#FDF1E7] text-[#0D9488]',
    image: 'https://karlpeacelegacy.org/wp-content/uploads/2026/08/Ann-768x1024-1.jpeg',
    tagline: 'This expert specializes in scholarship programs and innovative learning solutions.',
    shortBio: 'Governance custodian overseeing institutional compliance, scholarship verification, and modern learning solutions.',
    fullBio: 'Ann Eekpang Abang is the Secretary General of the Karl Peace Legacy Foundation. She manages the institutional records, compliance frameworks, official documentation, and inter-organizational correspondence. With a specialized focus on academic equity and modern learning methodologies, Ann guarantees that candidate evaluation maintains unimpeachable integrity, while crafting academic enrichment resources that help undergraduate scholars flourish.',
    department: 'Institutional Secretariat & Compliance',
    linkedin: 'https://www.linkedin.com/in/ann-abang',
    twitter: 'https://x.com/karlpeacelegacy',
    email: 'contact@karlpeacelegacy.org',
    quote: 'Integrity in administration is the cornerstone upon which every student’s dream is safely built.',
    keyContributions: [
      'Oversees official institutional correspondence with universities and accreditation bodies.',
      'Maintains rigorous compliance audits for all applicant submissions and academic transcripts.',
      'Pioneered curriculum enrichment materials for digital and remote student learning.',
      'Coordinates foundation communications and press dispatches across all official channels.'
    ]
  },
  {
    id: 'winner-kalu',
    name: 'Winner Kalu',
    role: 'Administrative Officer',
    category: 'operations',
    badge: 'Administrative Officer',
    badgeColor: 'bg-[#ffdcc3] text-[#663500]',
    image: 'https://karlpeacelegacy.org/wp-content/uploads/2026/08/Winner-768x1024-1.jpg',
    tagline: 'This member excels in community leadership and strategic health initiatives.',
    shortBio: 'Grassroots administrative leader driving community mobilization, student inquiries, and public health campaigns.',
    fullBio: 'Winner Kalu serves as Administrative Officer, driving day-to-day coordination at the foundation secretariat and in the field. An energetic champion of community wellness and youth advocacy, Winner coordinates logistics for preventative medical clinics, liaises directly with tertiary student associations, and manages prompt communications with students, parents, and community partners.',
    department: 'Field Administration & Public Health',
    linkedin: 'https://www.linkedin.com/in/winner-kalu-894331322?',
    twitter: 'https://x.com/karlpeacelegacy',
    email: 'contact@karlpeacelegacy.org',
    quote: 'Grassroots engagement is where true empathy meets lasting positive transformation.',
    keyContributions: [
      'Mobilizes student cohorts and community participants for health literacy drives.',
      'Manages intake and categorization of inquiries through official foundation communication desks.',
      'Organizes on-site logistics, health kit distribution, and volunteer orientation sessions.',
      'Acts as key administrative liaison with local clinics and youth groups across Nigeria.'
    ]
  },
  {
    id: 'dr-karl-peace',
    name: 'Dr. Karl E. Peace',
    role: 'Inspiring Namesake & Global Patron',
    category: 'patron',
    badge: 'Inspiration & Namesake',
    badgeColor: 'bg-[#FEF3C7] text-[#904d00]',
    image: 'https://lh3.googleusercontent.com/aida-public/AB6AXuCPBw1aUWDdxeBzqKdnrIB4LHVgGJVtUWTdKo2MKhlUUB9Nsrpg5kHNg5Sknjarqj3UXyt2walkHzJsxLcDmaUTyKRap1lbC682uQcCync6zEQaFSDSR0fazAUZFpFF0SbEgj-TY8mnv0GEgb8JzrdOqSd2_TBAhrSXkJEGC5YNeO_67XAe2D1fYskwRFtbqdGDx2Qv5k1MEaO1JnmdDiuKmKr-B5ZCE1HNz41cr_-aEGvczuV9bIkp',
    tagline: 'Eminent biostatistician, author, and humanitarian whose life journey inspires our foundation.',
    shortBio: 'World-renowned biostatistician, professor, and philanthropist whose lifetime dedication to public health and student scholarships anchors our ethos.',
    fullBio: 'Dr. Karl E. Peace (Ph.D., F.A.S.A.) is an internationally revered biostatistician, distinguished professor, and humanitarian philanthropist. Raised in rural Georgia working in the fields as a child of sharecroppers, he surmounted immense hardship to become a monumental figure in pharmaceutical science, contributing to clinical trials for dozens of vital medicines for Alzheimer’s, oncology, and cardiovascular diseases. In gratitude for his education, Dr. Peace gave tens of millions of dollars to establish the Jiann-Ping Hsu College of Public Health (JPHCOPH) at Georgia Southern University and funded countless endowed scholarships. Emmanuel Unimke Azu founded the Karl Peace Legacy Foundation in Nigeria to honor and perpetuate Dr. Peace’s extraordinary commitment to public health science and human potential.',
    department: 'Global Legacy & Philanthropic Inspiration',
    linkedin: 'https://www.linkedin.com',
    twitter: 'https://x.com/karlpeacelegacy',
    email: 'contact@karlpeacelegacy.org',
    quote: 'To whom much is given through education and opportunity, much is required in service to humanity.',
    keyContributions: [
      'Pioneered life-saving biostatistical methodology for clinical pharmaceutical drug development.',
      'Founded and endowed the Jiann-Ping Hsu College of Public Health at Georgia Southern University.',
      'Endowed 50+ perpetual scholarships supporting undergraduate and graduate students globally.',
      'Serves as the philosophical beacon and guiding model for the Karl Peace Legacy Foundation in Nigeria.'
    ]
  }
];
