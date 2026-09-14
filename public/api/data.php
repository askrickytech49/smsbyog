<?php
/**
 * Karl Peace Legacy Foundation — Content Data API
 * ------------------------------------------------
 * GET  /api/data.php              — fetch all public content
 * GET  /api/data.php?section=X    — fetch one section
 * POST /api/data.php              — update content (auth required)
 *
 * Sections: settings, programs, news, leaders, gallery, testimonials, faqs
 */
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];

// ── GET ───────────────────────────────────────────────────────
if ($method === 'GET') {
    $section = $_GET['section'] ?? 'all';
    $db = getDB();
    $data = [];

    if ($section === 'all' || $section === 'settings') {
        $row = $db->query('SELECT * FROM site_settings LIMIT 1')->fetch();
        if ($row) {
            $data['settings'] = dbRowToSettings($row);
        }
    }

    if ($section === 'all' || $section === 'programs') {
        $rows = $db->query('SELECT * FROM programs WHERE is_active = 1 ORDER BY display_order, created_at')->fetchAll();
        $data['programs'] = array_map('dbRowToProgram', $rows);
    }

    if ($section === 'all' || $section === 'news') {
        $rows = $db->query('SELECT * FROM news_articles WHERE is_published = 1 ORDER BY created_at DESC')->fetchAll();
        $data['news'] = array_map('dbRowToNews', $rows);
    }

    if ($section === 'all' || $section === 'leaders') {
        $rows = $db->query('SELECT * FROM team_members WHERE is_active = 1 ORDER BY display_order, created_at')->fetchAll();
        $data['leaders'] = array_map('dbRowToLeader', $rows);
    }

    if ($section === 'all' || $section === 'gallery') {
        $rows = $db->query('SELECT * FROM gallery_photos WHERE is_active = 1 ORDER BY display_order, created_at DESC')->fetchAll();
        $data['gallery'] = array_map('dbRowToGallery', $rows);
    }

    if ($section === 'all' || $section === 'testimonials') {
        $rows = $db->query('SELECT * FROM testimonials WHERE is_active = 1 ORDER BY is_featured DESC, created_at DESC')->fetchAll();
        $data['testimonials'] = array_map('dbRowToTestimonial', $rows);
    }

    if ($section === 'all' || $section === 'faqs') {
        $rows = $db->query('SELECT * FROM faqs WHERE is_active = 1 ORDER BY display_order, id')->fetchAll();
        $data['faqs'] = array_map('dbRowToFaq', $rows);
    }

    jsonResponse(['success' => true, 'source' => 'mysql', 'data' => $data]);
}

// ── POST — bulk update (admin) ────────────────────────────────
if ($method === 'POST') {
    $actor = requireAdmin();
    $body  = getRequestBody();

    $db = getDB();

    if (isset($body['settings'])) {
        upsertSettings($db, $body['settings'], $actor);
    }
    if (isset($body['programs'])) {
        syncCollection($db, 'programs', $body['programs'], 'upsertProgram', $actor);
    }
    if (isset($body['news'])) {
        syncCollection($db, 'news_articles', $body['news'], 'upsertNews', $actor);
    }
    if (isset($body['leaders'])) {
        syncCollection($db, 'team_members', $body['leaders'], 'upsertLeader', $actor);
    }
    if (isset($body['gallery'])) {
        syncCollection($db, 'gallery_photos', $body['gallery'], 'upsertGallery', $actor);
    }
    if (isset($body['testimonials'])) {
        syncCollection($db, 'testimonials', $body['testimonials'], 'upsertTestimonial', $actor);
    }
    if (isset($body['faqs'])) {
        syncFaqs($db, $body['faqs'], $actor);
    }

    jsonResponse(['success' => true, 'message' => 'Content saved.']);
}

// ── PUT/PATCH — single item upsert ────────────────────────────
if ($method === 'PUT' || $method === 'PATCH') {
    $actor   = requireAdmin();
    $body    = getRequestBody();
    $section = $_GET['section'] ?? '';
    $db      = getDB();

    switch ($section) {
        case 'settings':    upsertSettings($db, $body, $actor); break;
        case 'program':     upsertProgram($db, $body, $actor);  break;
        case 'news':        upsertNews($db, $body, $actor);     break;
        case 'leader':      upsertLeader($db, $body, $actor);   break;
        case 'gallery':     upsertGallery($db, $body, $actor);  break;
        case 'testimonial': upsertTestimonial($db, $body, $actor); break;
        case 'faq':         upsertFaq($db, $body, $actor);      break;
        default:
            jsonResponse(['success' => false, 'error' => 'Unknown section.'], 400);
    }

    jsonResponse(['success' => true, 'message' => 'Item saved.']);
}

// ── DELETE ────────────────────────────────────────────────────
if ($method === 'DELETE') {
    $actor   = requireAdmin();
    $section = $_GET['section'] ?? '';
    $id      = $_GET['id']      ?? '';

    if (!$id) jsonResponse(['success' => false, 'error' => 'id required.'], 400);

    $db = getDB();
    $tableMap = [
        'program'     => ['programs',     'id'],
        'news'        => ['news_articles', 'id'],
        'leader'      => ['team_members',  'id'],
        'gallery'     => ['gallery_photos','id'],
        'testimonial' => ['testimonials',  'id'],
        'faq'         => ['faqs',          'id'],
    ];

    if (!isset($tableMap[$section])) {
        jsonResponse(['success' => false, 'error' => 'Unknown section.'], 400);
    }

    [$table, $col] = $tableMap[$section];
    $stmt = $db->prepare("DELETE FROM $table WHERE $col = ?");
    $stmt->execute([$id]);

    auditLog('deleted', $section, $id, null, null, $actor);
    jsonResponse(['success' => true, 'message' => 'Item deleted.']);
}

jsonResponse(['success' => false, 'error' => 'Method not allowed.'], 405);

// ══════════════════════════════════════════════════════════════
// UPSERT FUNCTIONS
// ══════════════════════════════════════════════════════════════

function upsertSettings(PDO $db, array $s, array $actor): void {
    $existing = $db->query('SELECT id FROM site_settings LIMIT 1')->fetch();
    $params = [
        $s['heroTitle']               ?? '',
        $s['heroSubtitle']            ?? '',
        $s['heroBadge']               ?? '',
        $s['contactEmail']            ?? '',
        $s['contactPhone']            ?? '',
        $s['officeAddress']           ?? '',
        $s['registeredAddress']       ?? null,
        $s['registrationNumber']      ?? null,
        $s['officialDomain']          ?? null,
        isset($s['scholarshipAlertActive']) ? (int)(bool)$s['scholarshipAlertActive'] : 1,
        $s['scholarshipAlertTitle']   ?? '',
        $s['scholarshipAlertText']    ?? '',
        $s['scholarshipAlertDeadline'] ?? '',
        $s['scholarshipAlertCycle']   ?? '',
        date('Y-m-d H:i:s'),
        $actor['email'],
    ];

    if ($existing) {
        $db->prepare(
            'UPDATE site_settings SET
             hero_title=?, hero_subtitle=?, hero_badge=?,
             contact_email=?, contact_phone=?, office_address=?,
             registered_address=?, registration_number=?, official_domain=?,
             scholarship_alert_active=?, scholarship_alert_title=?,
             scholarship_alert_text=?, scholarship_alert_deadline=?,
             scholarship_alert_cycle=?, updated_at=?, updated_by=?
             WHERE id = ' . $existing['id']
        )->execute($params);
    } else {
        $db->prepare(
            'INSERT INTO site_settings
             (hero_title, hero_subtitle, hero_badge, contact_email, contact_phone,
              office_address, registered_address, registration_number, official_domain,
              scholarship_alert_active, scholarship_alert_title, scholarship_alert_text,
              scholarship_alert_deadline, scholarship_alert_cycle, updated_at, updated_by)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        )->execute($params);
    }
    auditLog('updated', 'site_settings', '1', null, $s, $actor);
}

function upsertProgram(PDO $db, array $p, array $actor): void {
    $id = $p['id'] ?? generateId('prog_');
    $existing = $db->prepare('SELECT id FROM programs WHERE id = ?');
    $existing->execute([$id]);
    $keyPoints = isset($p['keyPoints']) ? json_encode($p['keyPoints']) : null;

    if ($existing->fetch()) {
        $db->prepare(
            'UPDATE programs SET title=?, category=?, badge=?, badge_color=?, image=?,
             description=?, key_points=?, detailed_narrative=?, eligibility_snippet=?,
             timeline=?, updated_at=NOW(), updated_by=? WHERE id=?'
        )->execute([
            $p['title'] ?? '', $p['category'] ?? 'scholarships',
            $p['badge'] ?? '', $p['badgeColor'] ?? '', $p['image'] ?? null,
            $p['description'] ?? '', $keyPoints,
            $p['detailedNarrative'] ?? null, $p['eligibilitySnippet'] ?? null,
            $p['timeline'] ?? null, $actor['email'], $id,
        ]);
        auditLog('updated', 'programs', $id, null, $p, $actor);
    } else {
        $db->prepare(
            'INSERT INTO programs (id, title, category, badge, badge_color, image,
             description, key_points, detailed_narrative, eligibility_snippet, timeline, updated_by)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?)'
        )->execute([
            $id, $p['title'] ?? '', $p['category'] ?? 'scholarships',
            $p['badge'] ?? '', $p['badgeColor'] ?? '', $p['image'] ?? null,
            $p['description'] ?? '', $keyPoints,
            $p['detailedNarrative'] ?? null, $p['eligibilitySnippet'] ?? null,
            $p['timeline'] ?? null, $actor['email'],
        ]);
        auditLog('created', 'programs', $id, null, $p, $actor);
    }
}

function upsertNews(PDO $db, array $n, array $actor): void {
    $id = $n['id'] ?? generateId('news_');
    $existing = $db->prepare('SELECT id FROM news_articles WHERE id = ?');
    $existing->execute([$id]);

    if ($existing->fetch()) {
        $db->prepare(
            'UPDATE news_articles SET title=?, category=?, category_type=?, cycle=?,
             summary=?, full_content=?, location=?, date=?, is_urgent=?,
             updated_at=NOW(), published_by=? WHERE id=?'
        )->execute([
            $n['title'] ?? '', $n['category'] ?? '', $n['categoryType'] ?? 'bulletin',
            $n['cycle'] ?? '', $n['summary'] ?? '', $n['fullContent'] ?? '',
            $n['location'] ?? '', $n['date'] ?? '',
            isset($n['isUrgent']) ? (int)(bool)$n['isUrgent'] : 0,
            $actor['email'], $id,
        ]);
        auditLog('updated', 'news_articles', $id, null, $n, $actor);
    } else {
        $db->prepare(
            'INSERT INTO news_articles (id, title, category, category_type, cycle,
             summary, full_content, location, date, is_urgent, published_by)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)'
        )->execute([
            $id, $n['title'] ?? '', $n['category'] ?? '', $n['categoryType'] ?? 'bulletin',
            $n['cycle'] ?? '', $n['summary'] ?? '', $n['fullContent'] ?? '',
            $n['location'] ?? '', $n['date'] ?? '',
            isset($n['isUrgent']) ? (int)(bool)$n['isUrgent'] : 0,
            $actor['email'],
        ]);
        auditLog('created', 'news_articles', $id, null, $n, $actor);
    }
}

function upsertLeader(PDO $db, array $l, array $actor): void {
    $id = $l['id'] ?? generateId('leader_');
    $existing = $db->prepare('SELECT id FROM team_members WHERE id = ?');
    $existing->execute([$id]);
    $contributions = isset($l['keyContributions']) ? json_encode($l['keyContributions']) : null;

    if ($existing->fetch()) {
        $db->prepare(
            'UPDATE team_members SET name=?, role=?, category=?, tagline=?, short_bio=?,
             full_bio=?, image=?, badge=?, badge_color=?, linkedin=?, twitter=?, email=?,
             key_contributions=?, department=?, quote=?, updated_at=NOW() WHERE id=?'
        )->execute([
            $l['name'] ?? '', $l['role'] ?? '', $l['category'] ?? 'operations',
            $l['tagline'] ?? '', $l['shortBio'] ?? '', $l['fullBio'] ?? '',
            $l['image'] ?? null, $l['badge'] ?? '', $l['badgeColor'] ?? null,
            $l['linkedin'] ?? null, $l['twitter'] ?? null, $l['email'] ?? null,
            $contributions, $l['department'] ?? null, $l['quote'] ?? null, $id,
        ]);
        auditLog('updated', 'team_members', $id, null, $l, $actor);
    } else {
        $db->prepare(
            'INSERT INTO team_members (id, name, role, category, tagline, short_bio,
             full_bio, image, badge, badge_color, linkedin, twitter, email,
             key_contributions, department, quote)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        )->execute([
            $id, $l['name'] ?? '', $l['role'] ?? '', $l['category'] ?? 'operations',
            $l['tagline'] ?? '', $l['shortBio'] ?? '', $l['fullBio'] ?? '',
            $l['image'] ?? null, $l['badge'] ?? '', $l['badgeColor'] ?? null,
            $l['linkedin'] ?? null, $l['twitter'] ?? null, $l['email'] ?? null,
            $contributions, $l['department'] ?? null, $l['quote'] ?? null,
        ]);
        auditLog('created', 'team_members', $id, null, $l, $actor);
    }
}

function upsertGallery(PDO $db, array $g, array $actor): void {
    $id = $g['id'] ?? generateId('gallery_');
    $existing = $db->prepare('SELECT id FROM gallery_photos WHERE id = ?');
    $existing->execute([$id]);

    if ($existing->fetch()) {
        $db->prepare(
            'UPDATE gallery_photos SET title=?, caption=?, category=?, image=?,
             location=?, date=?, uploaded_by=? WHERE id=?'
        )->execute([
            $g['title'] ?? '', $g['caption'] ?? '', $g['category'] ?? 'all',
            $g['image'] ?? '', $g['location'] ?? '', $g['date'] ?? '',
            $actor['email'], $id,
        ]);
        auditLog('updated', 'gallery_photos', $id, null, $g, $actor);
    } else {
        $db->prepare(
            'INSERT INTO gallery_photos (id, title, caption, category, image, location, date, uploaded_by)
             VALUES (?,?,?,?,?,?,?,?)'
        )->execute([
            $id, $g['title'] ?? '', $g['caption'] ?? '', $g['category'] ?? 'all',
            $g['image'] ?? '', $g['location'] ?? '', $g['date'] ?? '', $actor['email'],
        ]);
        auditLog('created', 'gallery_photos', $id, null, $g, $actor);
    }
}

function upsertTestimonial(PDO $db, array $t, array $actor): void {
    $id = $t['id'] ?? generateId('testi_');
    $existing = $db->prepare('SELECT id FROM testimonials WHERE id = ?');
    $existing->execute([$id]);

    if ($existing->fetch()) {
        $db->prepare(
            'UPDATE testimonials SET name=?, institution=?, field=?, quote=?, year=?,
             is_featured=?, updated_at=NOW() WHERE id=?'
        )->execute([
            $t['name'] ?? '', $t['institution'] ?? '', $t['field'] ?? '',
            $t['quote'] ?? '', $t['year'] ?? '',
            isset($t['isFeatured']) ? (int)(bool)$t['isFeatured'] : 0, $id,
        ]);
        auditLog('updated', 'testimonials', $id, null, $t, $actor);
    } else {
        $db->prepare(
            'INSERT INTO testimonials (id, name, institution, field, quote, year, is_featured)
             VALUES (?,?,?,?,?,?,?)'
        )->execute([
            $id, $t['name'] ?? '', $t['institution'] ?? '', $t['field'] ?? '',
            $t['quote'] ?? '', $t['year'] ?? '',
            isset($t['isFeatured']) ? (int)(bool)$t['isFeatured'] : 0,
        ]);
        auditLog('created', 'testimonials', $id, null, $t, $actor);
    }
}

function upsertFaq(PDO $db, array $f, array $actor): void {
    $id = $f['id'] ?? null;
    if ($id) {
        $existing = $db->prepare('SELECT id FROM faqs WHERE id = ?');
        $existing->execute([$id]);
        if ($existing->fetch()) {
            $db->prepare(
                'UPDATE faqs SET question=?, answer=?, category=?, display_order=?, updated_at=NOW() WHERE id=?'
            )->execute([
                $f['question'] ?? '', $f['answer'] ?? '',
                $f['category'] ?? 'general', $f['displayOrder'] ?? 0, $id,
            ]);
            auditLog('updated', 'faqs', (string)$id, null, $f, $actor);
            return;
        }
    }
    $db->prepare(
        'INSERT INTO faqs (question, answer, category, display_order) VALUES (?,?,?,?)'
    )->execute([
        $f['question'] ?? '', $f['answer'] ?? '',
        $f['category'] ?? 'general', $f['displayOrder'] ?? 0,
    ]);
    auditLog('created', 'faqs', (string)$db->lastInsertId(), null, $f, $actor);
}

function syncFaqs(PDO $db, array $faqs, array $actor): void {
    foreach ($faqs as $f) {
        upsertFaq($db, $f, $actor);
    }
}

function syncCollection(PDO $db, string $table, array $items, string $fn, array $actor): void {
    foreach ($items as $item) {
        $fn($db, $item, $actor);
    }
}

// ══════════════════════════════════════════════════════════════
// ROW → FRONTEND SHAPE MAPPERS
// ══════════════════════════════════════════════════════════════

function dbRowToSettings(array $r): array {
    return [
        'heroTitle'               => $r['hero_title'],
        'heroSubtitle'            => $r['hero_subtitle'],
        'heroBadge'               => $r['hero_badge'],
        'contactEmail'            => $r['contact_email'],
        'contactPhone'            => $r['contact_phone'],
        'officeAddress'           => $r['office_address'],
        'registeredAddress'       => $r['registered_address'],
        'registrationNumber'      => $r['registration_number'],
        'officialDomain'          => $r['official_domain'],
        'scholarshipAlertActive'  => (bool) $r['scholarship_alert_active'],
        'scholarshipAlertTitle'   => $r['scholarship_alert_title'],
        'scholarshipAlertText'    => $r['scholarship_alert_text'],
        'scholarshipAlertDeadline'=> $r['scholarship_alert_deadline'],
        'scholarshipAlertCycle'   => $r['scholarship_alert_cycle'],
        'updatedAt'               => $r['updated_at'],
        'updatedBy'               => $r['updated_by'],
    ];
}

function dbRowToProgram(array $r): array {
    return [
        'id'                 => $r['id'],
        'title'              => $r['title'],
        'category'           => $r['category'],
        'badge'              => $r['badge'],
        'badgeColor'         => $r['badge_color'],
        'image'              => $r['image'],
        'description'        => $r['description'],
        'keyPoints'          => $r['key_points'] ? json_decode($r['key_points'], true) : [],
        'detailedNarrative'  => $r['detailed_narrative'],
        'eligibilitySnippet' => $r['eligibility_snippet'],
        'timeline'           => $r['timeline'],
    ];
}

function dbRowToNews(array $r): array {
    return [
        'id'           => $r['id'],
        'title'        => $r['title'],
        'category'     => $r['category'],
        'categoryType' => $r['category_type'],
        'cycle'        => $r['cycle'],
        'summary'      => $r['summary'],
        'fullContent'  => $r['full_content'],
        'location'     => $r['location'],
        'date'         => $r['date'],
        'isUrgent'     => (bool) $r['is_urgent'],
    ];
}

function dbRowToLeader(array $r): array {
    return [
        'id'               => $r['id'],
        'name'             => $r['name'],
        'role'             => $r['role'],
        'category'         => $r['category'],
        'tagline'          => $r['tagline'],
        'shortBio'         => $r['short_bio'],
        'fullBio'          => $r['full_bio'],
        'image'            => $r['image'],
        'badge'            => $r['badge'],
        'badgeColor'       => $r['badge_color'],
        'linkedin'         => $r['linkedin'],
        'twitter'          => $r['twitter'],
        'email'            => $r['email'],
        'keyContributions' => $r['key_contributions'] ? json_decode($r['key_contributions'], true) : [],
        'department'       => $r['department'],
        'quote'            => $r['quote'],
    ];
}

function dbRowToGallery(array $r): array {
    return [
        'id'       => $r['id'],
        'title'    => $r['title'],
        'caption'  => $r['caption'],
        'category' => $r['category'],
        'image'    => $r['image'],
        'location' => $r['location'],
        'date'     => $r['date'],
    ];
}

function dbRowToTestimonial(array $r): array {
    return [
        'id'          => $r['id'],
        'name'        => $r['name'],
        'institution' => $r['institution'],
        'field'       => $r['field'],
        'quote'       => $r['quote'],
        'year'        => $r['year'],
        'isFeatured'  => (bool) $r['is_featured'],
    ];
}

function dbRowToFaq(array $r): array {
    return [
        'id'           => $r['id'],
        'question'     => $r['question'],
        'answer'       => $r['answer'],
        'category'     => $r['category'],
        'displayOrder' => (int) $r['display_order'],
    ];
}
