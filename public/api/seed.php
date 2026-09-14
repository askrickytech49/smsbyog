<?php
/**
 * Karl Peace Legacy Foundation — Database Seeder
 * -----------------------------------------------
 * Run once after install to populate all default data.
 * Access: http://localhost/karl-peace/public/api/seed.php?token=kplf_seed_2026
 *
 * IMPORTANT: Delete or disable this file after first run in production.
 */
require_once __DIR__ . '/config.php';

// Simple one-time seed token guard
$token = $_GET['token'] ?? '';
if ($token !== 'kplf_seed_2026') {
    jsonResponse(['success' => false, 'error' => 'Invalid seed token. Append ?token=kplf_seed_2026'], 403);
}

$db = getDB();
$results = [];

// ── 1. Super-admin user ───────────────────────────────────────
$existing = $db->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
if ((int)$existing === 0) {
    $db->prepare(
        'INSERT INTO admin_users (uid, email, display_name, password_hash, role, is_super_admin)
         VALUES (?, ?, ?, ?, ?, ?)'
    )->execute([
        'admin_' . bin2hex(random_bytes(8)),
        'admin@karlpeacelegacy.org',
        'Karl Peace Foundation Admin',
        password_hash('KarlPeace2026!', PASSWORD_BCRYPT),
        'admin',
        1,
    ]);
    $results[] = 'Created super-admin: admin@karlpeacelegacy.org / KarlPeace2026!';
} else {
    $results[] = 'Admin users already exist — skipped.';
}

// ── 2. Site settings ──────────────────────────────────────────
$settingsExist = $db->query('SELECT COUNT(*) FROM site_settings')->fetchColumn();
if ((int)$settingsExist === 0) {
    $db->prepare(
        'INSERT INTO site_settings
         (hero_title, hero_subtitle, hero_badge, contact_email, contact_phone,
          office_address, registered_address, registration_number, official_domain,
          scholarship_alert_active, scholarship_alert_title, scholarship_alert_text,
          scholarship_alert_deadline, scholarship_alert_cycle)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
    )->execute([
        'Empowering Nigerian Youth Through Education, Health, and Mentorship',
        'Honoring Dr. Karl E. Peace\'s lifelong legacy in public health and biostatistics by providing tertiary scholarships, executive career guidance, and community health interventions across Nigeria.',
        'Fostering Excellence • Expanding Horizons',
        'admin@karlpeacelegacy.org',
        '+234 800 000 0000',
        '25 Ediba Rd, Calabar, Cross River State, Nigeria',
        '25 Ediba Rd, Calabar, Cross River State, Nigeria',
        '9622998',
        'karlpeacelegacy.org',
        1,
        '2025/2026 Tertiary Scholarship Framework',
        'The official evaluation roadmap has been ratified by the board of trustees. Prospective Nigerian undergraduates may review verified eligibility requirements.',
        'Opening for Applications',
        '2025/2026 Academic Session',
    ]);
    $results[] = 'Site settings seeded.';
} else {
    $results[] = 'Site settings already exist — skipped.';
}

// ── 3. Programs ───────────────────────────────────────────────
$progCount = (int)$db->query('SELECT COUNT(*) FROM programs')->fetchColumn();
if ($progCount === 0) {
    $programs = [
        [
            'scholarships', 'Scholarships', 'Direct Aid', 'bg-[#FEF3C7] text-[#904d00]',
            'This program provides financial support for deserving students to pursue higher education across accredited Nigerian institutions.',
            json_encode(['Tuition Assistance & Registration Relief', 'Tertiary Grants for Undergraduates', 'Essential Academic Materials & Textbooks']),
            'The Karl Peace Legacy Tertiary Scholarship scheme directly relieves the crushing financial pressures on undergraduates enrolled in Nigerian federal, state, and accredited private tertiary institutions.',
            'Enrolled undergraduate students in recognized Nigerian Universities, Polytechnics, or Colleges of Education with a minimum CGPA of 3.0/5.0 or 2.5/4.0.',
            'Applications for 2025/2026 Academic Cycle opening shortly.', 1,
        ],
        [
            'mentorship', 'Mentorship & Youth Development', 'Leadership', 'bg-[#e3dfff] text-[#181445]',
            'This initiative connects young people with experienced guides for career growth, strengthening confidence, and ethical leadership.',
            json_encode(['One-on-One Career Advisory with Senior Professionals', 'Ethical Leadership & Civic Governance Seminars', 'Resume & Technical Portfolio Masterclasses', 'Graduate Transition & Internship Placement Guidance']),
            'Talent requires cultivation. Our mentorship pairings connect tertiary scholars and ambitious school leavers with vetted mentors across academia, healthcare, biotechnology, engineering, law, and business administration.',
            'Open to registered scholarship beneficiaries and high-potential Nigerian youth between ages 18 and 29.',
            'Rolling cohort enrollment and bi-annual leadership bootcamps.', 2,
        ],
        [
            'health', 'Public Health Initiatives', 'Community Wellness', 'bg-[#FDF1E7] text-[#0D9488]',
            'This campaign delivers vital health resources, hygiene education, and awareness to local communities and educational environments.',
            json_encode(['Preventative Health Literacy Workshops', 'Community Hygiene & Sanitation Supply Drives', 'Youth Mental Wellbeing & Resilience Circles', 'Campus Health Screening & Maternal Care Awareness']),
            'Good health is the bedrock of educational achievement. Guided by Dr. Karl E. Peace\'s lifelong contributions to biostatistics and public health science, our grassroots health drives equip underserved communities.',
            'Community organizations, student health unions, and local clinics across partner states.',
            'Quarterly field drives in Abuja, Lagos, and surrounding catchment areas.', 3,
        ],
        [
            'opportunities', 'Access to Opportunities', 'Empowerment', 'bg-[#ffdcc3] text-[#663500]',
            'Connecting young people with educational advancement, professional development, and practical career tools.',
            json_encode(['Digital Literacy & Applied Technical Training', 'Postgraduate Fellowship Preparation & GRE/IELTS Resources', 'Entrepreneurship Seed Incubators for Student Innovators', 'Civic Engagement & Community Service Fellowships']),
            'We actively dismantle the information asymmetry that holds back brilliant youth in developing economies.',
            'All enrolled fellows, alumni, and registered applicants across Nigeria.',
            'Continuous resource sharing and seasonal fellowship calls.', 4,
        ],
    ];

    $stmt = $db->prepare(
        'INSERT INTO programs (id, category, title, badge, badge_color, description,
         key_points, detailed_narrative, eligibility_snippet, timeline, display_order)
         VALUES (?,?,?,?,?,?,?,?,?,?,?)'
    );
    foreach ($programs as $p) {
        $stmt->execute(array_merge([$p[0]], $p));
    }
    $results[] = 'Programs seeded (4 records).';
} else {
    $results[] = "Programs already exist ($progCount records) — skipped.";
}

// ── 4. Team members ───────────────────────────────────────────
$leaderCount = (int)$db->query('SELECT COUNT(*) FROM team_members')->fetchColumn();
if ($leaderCount === 0) {
    $leaders = [
        [
            'founder-karl', 'Dr. Karl E. Peace', 'Founder & Namesake', 'founder',
            'World-Renowned Biostatistician & Philanthropist',
            'Dr. Karl E. Peace rose from humble origins in rural Georgia, USA, to become one of the most influential biostatisticians of the 20th century.',
            'Dr. Karl E. Peace is a Distinguished Research Professor Emeritus of Biostatistics at Georgia Southern University. His journey from sharecropper origins to global philanthropy has been an inspiration to millions.',
            'Distinguished Founder', 'bg-amber-100 text-amber-800', 1,
        ],
        [
            'exec-director', 'Foundation Executive Director', 'Executive Director', 'executive',
            'Leading Foundation Operations & Strategic Partnerships',
            'Our Executive Director oversees the day-to-day management of the Karl Peace Legacy Foundation, coordinating scholarship disbursements and program delivery.',
            'The Executive Director leads all programmatic and operational functions of the foundation, working closely with the board of trustees to achieve the foundation\'s mission.',
            'Executive Leadership', 'bg-indigo-100 text-indigo-800', 2,
        ],
        [
            'programs-coord', 'Programs Coordinator', 'Programs & Scholar Relations', 'operations',
            'Scholar Support & Program Delivery',
            'Our Programs Coordinator manages the scholarship pipeline and maintains relationships with scholars across Nigeria.',
            'Responsible for end-to-end scholarship program administration, from application review through to award disbursement and scholar follow-up.',
            'Operations', 'bg-teal-100 text-teal-800', 3,
        ],
    ];

    $stmt = $db->prepare(
        'INSERT INTO team_members (id, name, role, category, tagline, short_bio, full_bio, badge, badge_color, display_order)
         VALUES (?,?,?,?,?,?,?,?,?,?)'
    );
    foreach ($leaders as $l) {
        $stmt->execute($l);
    }
    $results[] = 'Team members seeded (3 records).';
} else {
    $results[] = "Team members already exist ($leaderCount records) — skipped.";
}

// ── 5. News articles ──────────────────────────────────────────
$newsCount = (int)$db->query('SELECT COUNT(*) FROM news_articles')->fetchColumn();
if ($newsCount === 0) {
    $db->prepare(
        'INSERT INTO news_articles (id, title, category, category_type, cycle, summary, full_content, location, date, is_urgent)
         VALUES (?,?,?,?,?,?,?,?,?,?)'
    )->execute([
        'announcement-1',
        '2025/2026 Tertiary Scholarship Framework Announced',
        'Official Bulletin',
        'scholarship',
        'Academic Cycle 2025/2026',
        'The Foundation board has established the criteria, verification safeguards, and allocation roadmap for the upcoming academic cycle.',
        "The Board of Trustees of the Karl Peace Legacy Foundation is pleased to announce the formal ratification of the 2025/2026 Tertiary Scholarship Framework for Nigerian students.\n\nEligible applicants must be enrolled in accredited Nigerian tertiary institutions and meet the minimum academic requirements. Applications will open shortly — prospective scholars are advised to prepare their documentation in advance.",
        'Calabar, Cross River State, Nigeria',
        'August 28, 2025',
        1,
    ]);
    $results[] = 'News articles seeded (1 record).';
} else {
    $results[] = "News articles already exist ($newsCount records) — skipped.";
}

// ── 6. FAQs ───────────────────────────────────────────────────
$faqCount = (int)$db->query('SELECT COUNT(*) FROM faqs')->fetchColumn();
if ($faqCount === 0) {
    $faqs = [
        ['Who can apply for the Karl Peace Legacy Foundation scholarship?', 'Nigerian citizens currently enrolled as full-time undergraduate students in accredited federal, state, or approved private universities, polytechnics, or colleges of education are eligible to apply. Applicants must maintain a minimum CGPA of 3.0/5.0 (or 2.5/4.0 equivalent).', 'scholarships', 1],
        ['What documents are required for the scholarship application?', 'Required documents include: current student ID, official academic transcript, admission letter, passport photograph, and optionally a recommendation letter from a faculty adviser or community leader.', 'scholarships', 2],
        ['How are scholarship recipients selected?', 'Selection is based on academic merit (CGPA), financial need, quality of personal statement, career goals alignment with foundation values, and community service record. All shortlisted candidates undergo a review by the board of trustees.', 'scholarships', 3],
        ['Is the mentorship program open to non-scholarship recipients?', 'The mentorship program is primarily open to current scholarship beneficiaries. However, high-potential Nigerian youth between ages 18 and 29 may apply for mentorship placement during open enrollment periods.', 'mentorship', 4],
        ['How do I contact the foundation for partnership or donation inquiries?', 'Please use the Contact Us form on our website and select the appropriate inquiry type (Partnership, Donor, or Media). Our team responds within 3–5 business days.', 'general', 5],
        ['When will applications open for the 2025/2026 cycle?', 'The official application window for the 2025/2026 academic cycle will be announced on our website and via our notification system. We encourage prospective applicants to sign up for alerts to be notified immediately.', 'scholarships', 6],
    ];

    $stmt = $db->prepare('INSERT INTO faqs (question, answer, category, display_order) VALUES (?,?,?,?)');
    foreach ($faqs as $f) {
        $stmt->execute($f);
    }
    $results[] = 'FAQs seeded (6 records).';
} else {
    $results[] = "FAQs already exist ($faqCount records) — skipped.";
}

// ── 7. Testimonials ───────────────────────────────────────────
$testiCount = (int)$db->query('SELECT COUNT(*) FROM testimonials')->fetchColumn();
if ($testiCount === 0) {
    $testimonials = [
        ['testi-001', 'Adaeze Okonkwo', 'University of Nigeria, Nsukka', 'Medicine & Surgery', 'The Karl Peace Legacy scholarship didn\'t just pay my fees — it gave me the confidence to believe my goals were reachable. I\'m now in my final year of medical school.', '2024', 1],
        ['testi-002', 'Emeka Nwachukwu', 'Ahmadu Bello University', 'Electrical Engineering', 'I was on the verge of dropping out in my 300 level due to financial pressures. This foundation stepped in at the right moment. I graduated with a 4.6 CGPA.', '2023', 1],
        ['testi-003', 'Fatima Aliyu', 'Bayero University, Kano', 'Biochemistry', 'The mentorship program connected me with a research scientist in the UK. That single connection changed my entire career trajectory. I now have a postgraduate offer abroad.', '2024', 0],
    ];

    $stmt = $db->prepare(
        'INSERT INTO testimonials (id, name, institution, field, quote, year, is_featured) VALUES (?,?,?,?,?,?,?)'
    );
    foreach ($testimonials as $t) {
        $stmt->execute($t);
    }
    $results[] = 'Testimonials seeded (3 records).';
} else {
    $results[] = "Testimonials already exist ($testiCount records) — skipped.";
}

jsonResponse([
    'success' => true,
    'message' => 'Database seeded successfully.',
    'note'    => 'Default password is KarlPeace2026! — change it immediately after first login.',
    'results' => $results,
]);
