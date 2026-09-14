<?php
/**
 * Karl Peace Legacy Foundation — Applications Management (Admin)
 * --------------------------------------------------------------
 * GET    /api/applications.php              — list all (auth)
 * GET    /api/applications.php?id=X         — single full record (auth)
 * PATCH  /api/applications.php?id=X         — update status / notes (auth)
 * DELETE /api/applications.php?id=X         — delete (auth)
 */
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$actor  = requireAdmin();
$db     = getDB();

// ── GET ───────────────────────────────────────────────────────
if ($method === 'GET') {
    $id = $_GET['id'] ?? null;

    if ($id) {
        $stmt = $db->prepare('SELECT * FROM scholarship_applications WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) jsonResponse(['success' => false, 'error' => 'Not found.'], 404);
        jsonResponse(['success' => true, 'application' => dbRowToFullApplication($row)]);
    }

    // List with filters
    $status = $_GET['status'] ?? null;
    $cycle  = $_GET['cycle']  ?? null;
    $search = $_GET['search'] ?? null;
    $limit  = min((int)($_GET['limit'] ?? 100), 500);
    $offset = (int)($_GET['offset'] ?? 0);

    $where  = ['1=1'];
    $params = [];

    if ($status) { $where[] = 'status = ?';           $params[] = $status; }
    if ($cycle)  { $where[] = 'scholarship_cycle = ?'; $params[] = $cycle;  }
    if ($search) {
        $where[] = '(first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR institution LIKE ? OR department LIKE ?)';
        $t = '%' . $search . '%';
        $params = array_merge($params, [$t, $t, $t, $t, $t]);
    }

    $whereClause = implode(' AND ', $where);

    // Stats
    $statsStmt = $db->prepare(
        "SELECT status, COUNT(*) as cnt FROM scholarship_applications
         WHERE $whereClause GROUP BY status"
    );
    $statsStmt->execute($params);
    $statsRows = $statsStmt->fetchAll();
    $stats = ['total' => 0, 'submitted' => 0, 'under_review' => 0,
              'shortlisted' => 0, 'approved' => 0, 'rejected' => 0, 'draft' => 0];
    foreach ($statsRows as $s) {
        $stats[$s['status']] = (int)$s['cnt'];
        $stats['total'] += (int)$s['cnt'];
    }

    $countStmt = $db->prepare("SELECT COUNT(*) FROM scholarship_applications WHERE $whereClause");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    $params[] = $limit;
    $params[] = $offset;
    $stmt = $db->prepare(
        "SELECT id, first_name, last_name, email, phone, institution, department,
                academic_level, cgpa, cgpa_scale, scholarship_cycle, status,
                submitted_at, created_at, reviewed_at, awarded_amount
         FROM scholarship_applications
         WHERE $whereClause
         ORDER BY FIELD(status,'submitted','under_review','shortlisted','draft','approved','rejected','withdrawn'),
                  submitted_at DESC
         LIMIT ? OFFSET ?"
    );
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    jsonResponse([
        'success'      => true,
        'applications' => array_map('dbRowToApplicationSummary', $rows),
        'stats'        => $stats,
        'total'        => $total,
        'limit'        => $limit,
        'offset'       => $offset,
    ]);
}

// ── PATCH — update status / review notes ─────────────────────
if ($method === 'PATCH') {
    $id   = $_GET['id'] ?? '';
    $body = getRequestBody();

    if (!$id) jsonResponse(['success' => false, 'error' => 'id required.'], 400);

    // Fetch current
    $current = $db->prepare('SELECT * FROM scholarship_applications WHERE id = ?');
    $current->execute([$id]);
    $old = $current->fetch();
    if (!$old) jsonResponse(['success' => false, 'error' => 'Application not found.'], 404);

    $sets   = [];
    $params = [];

    $validStatuses = ['draft','submitted','under_review','shortlisted','approved','rejected','withdrawn'];

    if (isset($body['status'])) {
        if (!in_array($body['status'], $validStatuses, true)) {
            jsonResponse(['success' => false, 'error' => 'Invalid status.'], 400);
        }
        $sets[]   = 'status = ?';
        $params[] = $body['status'];

        if (in_array($body['status'], ['approved','rejected','shortlisted'], true)) {
            $sets[]   = 'reviewed_at = NOW()';
            $sets[]   = 'reviewer_id = ?';
            $params[] = $actor['id'];
        }
    }

    if (isset($body['reviewerNotes'])) {
        $sets[]   = 'reviewer_notes = ?';
        $params[] = mb_substr(trim($body['reviewerNotes']), 0, 5000);
    }

    if (isset($body['awardedAmount'])) {
        $sets[]   = 'awarded_amount = ?';
        $params[] = (float) $body['awardedAmount'];
    }

    if (isset($body['awardNotes'])) {
        $sets[]   = 'award_notes = ?';
        $params[] = mb_substr(trim($body['awardNotes']), 0, 3000);
    }

    if (empty($sets)) jsonResponse(['success' => false, 'error' => 'No fields to update.'], 400);

    $sets[]   = 'updated_at = NOW()';
    $params[] = $id;

    $db->prepare('UPDATE scholarship_applications SET ' . implode(', ', $sets) . ' WHERE id = ?')
       ->execute($params);

    auditLog('status_changed', 'scholarship_applications', $id,
        ['status' => $old['status']],
        ['status' => $body['status'] ?? $old['status'], 'reviewer' => $actor['email']],
        $actor
    );

    jsonResponse(['success' => true, 'message' => 'Application updated.']);
}

// ── DELETE ────────────────────────────────────────────────────
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? '';
    if (!$id) jsonResponse(['success' => false, 'error' => 'id required.'], 400);

    $db->prepare('DELETE FROM scholarship_applications WHERE id = ?')->execute([$id]);
    auditLog('deleted', 'scholarship_applications', $id, null, null, $actor);
    jsonResponse(['success' => true, 'message' => 'Application deleted.']);
}

jsonResponse(['success' => false, 'error' => 'Method not allowed.'], 405);

// ── MAPPERS ───────────────────────────────────────────────────
function dbRowToApplicationSummary(array $r): array {
    return [
        'id'               => $r['id'],
        'firstName'        => $r['first_name'],
        'lastName'         => $r['last_name'],
        'email'            => $r['email'],
        'phone'            => $r['phone'],
        'institution'      => $r['institution'],
        'department'       => $r['department'],
        'academicLevel'    => $r['academic_level'],
        'cgpa'             => $r['cgpa'],
        'cgpaScale'        => $r['cgpa_scale'],
        'scholarshipCycle' => $r['scholarship_cycle'],
        'status'           => $r['status'],
        'awardedAmount'    => $r['awarded_amount'],
        'submittedAt'      => $r['submitted_at'],
        'reviewedAt'       => $r['reviewed_at'],
        'createdAt'        => $r['created_at'],
    ];
}

function dbRowToFullApplication(array $r): array {
    return array_merge(dbRowToApplicationSummary($r), [
        'dateOfBirth'        => $r['date_of_birth'],
        'gender'             => $r['gender'],
        'stateOfOrigin'      => $r['state_of_origin'],
        'lga'                => $r['lga'],
        'homeAddress'        => $r['home_address'],
        'institutionType'    => $r['institution_type'],
        'faculty'            => $r['faculty'],
        'matricNumber'       => $r['matric_number'],
        'academicYear'       => $r['academic_year'],
        'personalStatement'  => $r['personal_statement'],
        'whyDeserve'         => $r['why_deserve'],
        'careerGoals'        => $r['career_goals'],
        'transcriptUrl'      => $r['transcript_url'],
        'idCardUrl'          => $r['id_card_url'],
        'admissionLetterUrl' => $r['admission_letter_url'],
        'passportPhotoUrl'   => $r['passport_photo_url'],
        'recommendationUrl'  => $r['recommendation_url'],
        'reviewerNotes'      => $r['reviewer_notes'],
        'awardNotes'         => $r['award_notes'],
    ]);
}
