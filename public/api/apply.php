<?php
/**
 * Karl Peace Legacy Foundation — Scholarship Application Submission
 * -----------------------------------------------------------------
 * POST /api/apply.php   — public: submit application
 * GET  /api/apply.php?email=X&cycle=X — public: check if already applied
 */
require_once __DIR__ . '/config.php';

$method = $_SERVER['REQUEST_METHOD'];
$db     = getDB();

// ── GET — check existing application ─────────────────────────
if ($method === 'GET') {
    checkRateLimit('apply_check_' . ($_SERVER['REMOTE_ADDR'] ?? 'x'), 20, 60);

    $email = strtolower(sanitizeString($_GET['email'] ?? '', 191));
    $cycle = sanitizeString($_GET['cycle'] ?? '', 64);

    if (!$email || !validateEmail($email)) {
        jsonResponse(['success' => false, 'error' => 'Valid email required.'], 400);
    }

    $stmt = $db->prepare(
        'SELECT id, status, submitted_at FROM scholarship_applications
         WHERE email = ?' . ($cycle ? ' AND scholarship_cycle = ?' : '') . ' LIMIT 1'
    );
    $params = [$email];
    if ($cycle) $params[] = $cycle;
    $stmt->execute($params);
    $row = $stmt->fetch();

    jsonResponse([
        'success'      => true,
        'hasApplied'   => (bool) $row,
        'status'       => $row['status'] ?? null,
        'submittedAt'  => $row['submitted_at'] ?? null,
        'applicationId'=> $row['id'] ?? null,
    ]);
}

// ── POST — submit application ─────────────────────────────────
if ($method === 'POST') {
    checkRateLimit('apply_submit_' . ($_SERVER['REMOTE_ADDR'] ?? 'x'), 3, 300);

    $body = getRequestBody();

    // ── Required fields validation ────────────────────────────
    $required = ['firstName', 'lastName', 'email', 'phone', 'institution',
                 'department', 'academicLevel', 'scholarshipCycle'];
    $missing  = [];
    foreach ($required as $field) {
        if (empty(trim($body[$field] ?? ''))) {
            $missing[] = $field;
        }
    }
    if (!empty($missing)) {
        jsonResponse([
            'success' => false,
            'error'   => 'Missing required fields: ' . implode(', ', $missing),
        ], 400);
    }

    $email = strtolower(sanitizeString($body['email'], 191));
    if (!validateEmail($email)) {
        jsonResponse(['success' => false, 'error' => 'Invalid email address.'], 400);
    }

    $cycle = sanitizeString($body['scholarshipCycle'], 64);

    // ── Duplicate check ───────────────────────────────────────
    $dup = $db->prepare(
        'SELECT id, status FROM scholarship_applications
         WHERE email = ? AND scholarship_cycle = ? LIMIT 1'
    );
    $dup->execute([$email, $cycle]);
    $existing = $dup->fetch();

    if ($existing && in_array($existing['status'], ['submitted','under_review','shortlisted','approved'], true)) {
        jsonResponse([
            'success'       => false,
            'error'         => 'You have already submitted an application for this scholarship cycle.',
            'applicationId' => $existing['id'],
            'status'        => $existing['status'],
        ], 409);
    }

    // ── CGPA validation ───────────────────────────────────────
    $cgpa      = isset($body['cgpa']) ? (float)$body['cgpa'] : null;
    $cgpaScale = in_array($body['cgpaScale'] ?? '5.0', ['4.0', '5.0']) ? $body['cgpaScale'] : '5.0';
    $minCgpa   = $cgpaScale === '4.0' ? 2.5 : 3.0;

    if ($cgpa !== null && $cgpa < $minCgpa) {
        jsonResponse([
            'success' => false,
            'error'   => "Minimum CGPA of $minCgpa/$cgpaScale required for scholarship eligibility.",
        ], 422);
    }

    $id     = generateId('app_');
    $isDraft = ($body['isDraft'] ?? false) === true || ($body['isDraft'] ?? 'false') === 'true';
    $status = $isDraft ? 'draft' : 'submitted';

    $db->prepare(
        'INSERT INTO scholarship_applications
         (id, first_name, last_name, email, phone, date_of_birth, gender,
          state_of_origin, lga, home_address,
          institution, institution_type, faculty, department, matric_number,
          academic_level, cgpa, cgpa_scale, academic_year, scholarship_cycle,
          personal_statement, why_deserve, career_goals,
          transcript_url, id_card_url, admission_letter_url,
          passport_photo_url, recommendation_url,
          status, ip_address, submitted_at)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
    )->execute([
        $id,
        sanitizeString($body['firstName'],    128),
        sanitizeString($body['lastName'],     128),
        $email,
        sanitizeString($body['phone'],         32),
        $body['dateOfBirth'] ?? null,
        in_array($body['gender'] ?? '', ['male','female','prefer_not_to_say'])
            ? $body['gender'] : null,
        sanitizeString($body['stateOfOrigin'] ?? '', 128),
        sanitizeString($body['lga']           ?? '', 128),
        mb_substr(trim($body['homeAddress']   ?? ''), 0, 500),
        sanitizeString($body['institution'],  255),
        in_array($body['institutionType'] ?? '', ['university','polytechnic','college_of_education','other'])
            ? $body['institutionType'] : 'university',
        sanitizeString($body['faculty']       ?? '', 255),
        sanitizeString($body['department'],   255),
        sanitizeString($body['matricNumber']  ?? '', 64),
        in_array($body['academicLevel'], ['100','200','300','400','500','postgraduate'])
            ? $body['academicLevel'] : '100',
        $cgpa,
        $cgpaScale,
        sanitizeString($body['academicYear']      ?? '', 16),
        $cycle,
        mb_substr(trim($body['personalStatement'] ?? ''), 0, 3000),
        mb_substr(trim($body['whyDeserve']        ?? ''), 0, 3000),
        mb_substr(trim($body['careerGoals']       ?? ''), 0, 3000),
        sanitizeString($body['transcriptUrl']         ?? '', 512),
        sanitizeString($body['idCardUrl']             ?? '', 512),
        sanitizeString($body['admissionLetterUrl']    ?? '', 512),
        sanitizeString($body['passportPhotoUrl']      ?? '', 512),
        sanitizeString($body['recommendationUrl']     ?? '', 512),
        $status,
        $_SERVER['REMOTE_ADDR'] ?? null,
        $isDraft ? null : date('Y-m-d H:i:s'),
    ]);

    // Fetch back for response
    $row = $db->prepare('SELECT * FROM scholarship_applications WHERE id = ?');
    $row->execute([$id]);

    jsonResponse([
        'success'     => true,
        'message'     => $isDraft
            ? 'Application saved as draft.'
            : 'Application submitted successfully. We will contact you at ' . $email . '.',
        'application' => dbRowToApplication($row->fetch()),
    ], 201);
}

jsonResponse(['success' => false, 'error' => 'Method not allowed.'], 405);

// ── MAPPER ────────────────────────────────────────────────────
function dbRowToApplication(array $r): array {
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
        'submittedAt'      => $r['submitted_at'],
        'createdAt'        => $r['created_at'],
    ];
}
