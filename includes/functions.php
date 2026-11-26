<?php

declare(strict_types=1);

// Load grading settings independently.
require_once __DIR__ . '/config.php';


// Calculate the sum of all subject marks.
function calculateObtainedMarks(array $subjectMarks): int
{
    return (int) array_sum($subjectMarks);
}


// Calculate maximum possible marks.
// Each subject carries 100 marks by default.
function calculateTotalMarks(
    array $subjectMarks,
    int $marksPerSubject = SUBJECT_MAX_MARKS
): int {

    if ($marksPerSubject <= 0) {
        return 0;
    }

    return count($subjectMarks) * $marksPerSubject;
}


// Calculate percentage without dividing by zero.
function calculatePercentage(
    int $obtainedMarks,
    int $totalMarks
): float {

    if ($totalMarks <= 0) {
        return 0.0;
    }

    return ($obtainedMarks / $totalMarks) * 100;
}


// A student must pass every subject and meet the overall percentage.
function determinePassFailStatus(
    array $subjectMarks,
    int $minimumPassMark = MINIMUM_PASS_MARKS,
    float $minimumPassPercentage = MINIMUM_PASS_PERCENTAGE
): bool {

    // An empty subject list cannot qualify as a pass.
    if ($subjectMarks === []) {
        return false;
    }

    foreach ($subjectMarks as $mark) {

        // Marks must be whole numbers within the allowed range.
        if (
            !is_int($mark) ||
            $mark < 0 ||
            $mark > SUBJECT_MAX_MARKS
        ) {
            return false;
        }

        // A student fails if any individual subject is below 40.
        if ($mark < $minimumPassMark) {
            return false;
        }
    }

    // Calculate the student's overall percentage.
    $obtainedMarks = calculateObtainedMarks($subjectMarks);

    $totalMarks = calculateTotalMarks($subjectMarks);

    $percentage = calculatePercentage(
        $obtainedMarks,
        $totalMarks
    );

    // The student must also achieve at least 50% overall.
    return $percentage >= $minimumPassPercentage;
}


// Assign a grade using the same pass/fail decision.
function determineGrade(
    float $percentage,
    bool $hasPassed
): string {

    // A failed student always receives F.
    if (!$hasPassed) {
        return 'F';
    }

    if ($percentage >= 90) {
        return 'A+';

    } elseif ($percentage >= 80) {
        return 'A';

    } elseif ($percentage >= 70) {
        return 'B';

    } elseif ($percentage >= 60) {
        return 'C';

    } elseif ($percentage >= 50) {
        return 'D';
    }

    // Defensive fallback if the supplied status and percentage disagree.
    return 'F';
}


// Prepare the calculated result for one student.
function prepareStudentResult(array $student): array
{
    // Use an empty array if subject marks are unavailable.
    $subjectMarks = $student['subject_marks'] ?? [];

    if (!is_array($subjectMarks)) {
        $subjectMarks = [];
    }

    // Provide safe defaults for student identity fields.
    $student['name'] = is_string($student['name'] ?? null)
        ? $student['name']
        : 'Unknown student';

    $student['roll_number'] = is_string($student['roll_number'] ?? null)
        ? $student['roll_number']
        : 'Not provided';

    $student['subject_marks'] = $subjectMarks;

    // Calculate marks and percentage.
    $obtainedMarks = calculateObtainedMarks($subjectMarks);

    $totalMarks = calculateTotalMarks($subjectMarks);

    $percentage = calculatePercentage(
        $obtainedMarks,
        $totalMarks
    );

    // Determine final pass/fail status.
    $hasPassed = determinePassFailStatus($subjectMarks);

    // Add calculated information to the student record.
    $student['obtained_marks'] = $obtainedMarks;

    $student['total_marks'] = $totalMarks;

    $student['percentage'] = $percentage;

    $student['grade'] = determineGrade(
        $percentage,
        $hasPassed
    );

    $student['is_passed'] = $hasPassed;

    $student['status'] = $hasPassed ? 'PASS' : 'FAIL';

    return $student;
}


// Escape dynamic values before displaying them in HTML.
function e(string|int|float|null $value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}