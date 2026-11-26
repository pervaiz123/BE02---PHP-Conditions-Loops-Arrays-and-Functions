<?php

declare(strict_types=1);


// Calculate the sum of all subject marks.
function calculateObtainedMarks(array $subjectMarks): int
{
    return (int) array_sum($subjectMarks);
}


// Calculate maximum possible marks.
// Default parameter: every subject carries 100 marks.
function calculateTotalMarks(
    array $subjectMarks,
    int $marksPerSubject = SUBJECT_MAX_MARKS
): int {

    return count($subjectMarks) * $marksPerSubject;
}


// Calculate the student's percentage.
function calculatePercentage(
    int $obtainedMarks,
    int $totalMarks
): float {

    // Prevent division by zero.
    if ($totalMarks <= 0) {
        return 0.0;
    }

    return ($obtainedMarks / $totalMarks) * 100;
}


// Determine whether a student has passed.
// A student must score at least 40 in every subject.
function determinePassFailStatus(
    array $subjectMarks,
    int $minimumPassMark = MINIMUM_PASS_MARKS
): bool {

    // An empty subject list cannot qualify as a pass.
    if ($subjectMarks === []) {
        return false;
    }

    foreach ($subjectMarks as $mark) {

        // Validate every subject mark.
        if (
            !is_int($mark) ||
            $mark < $minimumPassMark ||
            $mark > SUBJECT_MAX_MARKS
        ) {
            return false;
        }
    }

    return true;
}


// Assign a grade according to percentage and pass status.
function determineGrade(
    float $percentage,
    bool $hasPassed = true
): string {

    // A student who fails any subject receives F.
    if (!$hasPassed) {
        return 'F';
    }

    // Grade classification using conditional statements.
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
    } else {
        return 'F';
    }
}


// Prepare a complete result record for one student.
// This function combines all four reusable calculations.
function prepareStudentResult(array $student): array
{
    $obtainedMarks = calculateObtainedMarks(
        $student['subject_marks']
    );

    $totalMarks = calculateTotalMarks(
        $student['subject_marks']
    );

    $percentage = calculatePercentage(
        $obtainedMarks,
        $totalMarks
    );

    $hasPassed = determinePassFailStatus(
        $student['subject_marks']
    );

    // Add calculated information to the original student record.
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


// Reusable HTML escaping function.
// It prevents student information from being interpreted as HTML.
function e(string|int|float|null $value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}
