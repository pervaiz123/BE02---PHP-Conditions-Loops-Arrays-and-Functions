<?php

declare(strict_types=1);

// Load application settings, student records, and reusable functions.
require_once __DIR__ . '/includes/config.php';

require_once __DIR__ . '/includes/students.php';

require_once __DIR__ . '/includes/functions.php';


// Protect the dashboard if the student collection is empty or unavailable.
$studentRecords = isset($studentRecords) && is_array($studentRecords)
    ? $studentRecords
    : [];


// Calculate all student results using a reusable function.
$students = array_map(
    static fn(array $student): array => prepareStudentResult($student),
    $studentRecords
);


// Separate passed students.
$passedStudents = array_values(
    array_filter(
        $students,
        static fn(array $student): bool => $student['is_passed']
    )
);


// Separate failed students.
$failedStudents = array_values(
    array_filter(
        $students,
        static fn(array $student): bool => !$student['is_passed']
    )
);


// Collect subject names from every student.
// This prevents undefined-key warnings when students have different subjects.
$subjectNames = [];

foreach ($students as $student) {

    foreach (array_keys($student['subject_marks']) as $subjectName) {

        if (!in_array($subjectName, $subjectNames, true)) {
            $subjectNames[] = $subjectName;
        }
    }
}


// Extract each student's percentage.
$percentages = array_map(
    static fn(array $student): float => $student['percentage'],
    $students
);


// Calculate the class average using a while loop.
$percentageTotal = 0.0;

$index = 0;

while ($index < count($percentages)) {

    $percentageTotal += $percentages[$index];

    $index++;
}


// Calculate the average safely.
$averagePercentage = count($percentages) > 0
    ? $percentageTotal / count($percentages)
    : 0.0;


// Find the highest-performing student using a for loop.
// Percentage is used for fair comparison when subject counts differ.
$topStudent = null;

for ($index = 0; $index < count($students); $index++) {

    $currentStudent = $students[$index];

    if (
        $topStudent === null ||
        $currentStudent['percentage'] > $topStudent['percentage']
    ) {
        $topStudent = $currentStudent;
    }

    // A score of 100% cannot be exceeded.
    if ($currentStudent['percentage'] >= 100.0) {
        break;
    }
}


// array_pop() returns null when the student collection is empty.
// Check the value before preparing its result.
$latestEnrollment = is_array($latestEnrolledStudent ?? null)
    ? prepareStudentResult($latestEnrolledStudent)
    : null;


// Receive the selected result filter through GET.
$requestedView = $_GET['view'] ?? 'all';

$selectedView = is_string($requestedView)
    ? $requestedView
    : 'all';


// Define permitted filter values.
$allowedViews = ['all', 'passed', 'failed'];

// Reject invalid filter values.
if (!in_array($selectedView, $allowedViews, true)) {
    $selectedView = 'all';
}


// Select which students should appear in the table.
switch ($selectedView) {

    case 'passed':

        $displayStudents = $passedStudents;

        break;

    case 'failed':

        $displayStudents = $failedStudents;

        break;

    default:

        $displayStudents = $students;

        break;
}


// Prepare dashboard summary values.
$totalStudents = count($students);

$totalPassed = count($passedStudents);

$totalFailed = count($failedStudents);

$displayedStudents = count($displayStudents);


// Configure passed and failed student cards.
// Both cards use the same HTML template below.
$studentGroups = [

    [
        'title' => 'Passed Students',
        'description' => 'Students who met all passing requirements.',
        'students' => $passedStudents,
        'class' => ''
    ],

    [
        'title' => 'Failed Students',
        'description' => 'Students who require additional academic support.',
        'students' => $failedStudents,
        'class' => 'failed-group'
    ]

];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?= e(SCHOOL_NAME) ?> | Academic Results</title>

    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

    <div class="page-container">

        <!-- School identity and application header -->
        <header class="school-header">

            <div class="school-logo">
                SRA
            </div>

            <div class="school-information">

                <p class="eyebrow">EDUCATION AND ACADEMIC RECORDS</p>

                <h1><?= e(SCHOOL_NAME) ?></h1>

                <p><?= e(SCHOOL_TAGLINE) ?></p>

            </div>

        </header>


        <main>

            <!-- Dashboard introduction -->
            <section class="page-introduction">

                <div>

                    <p class="eyebrow">STUDENT PERFORMANCE</p>

                    <h2>Academic Results Dashboard</h2>

                    <p>
                        Student records, examination results and academic performance.
                    </p>

                </div>

                <div class="report-label">
                    Academic Result Report
                </div>

            </section>


            <!-- Overall academic summary -->
            <section class="summary-grid">

                <div class="summary-card total-card">

                    <p>Total Students</p>

                    <h3><?= e($totalStudents) ?></h3>

                    <span>Registered student records</span>

                </div>


                <div class="summary-card pass-card">

                    <p>Passed Students</p>

                    <h3><?= e($totalPassed) ?></h3>

                    <span>Successfully passed</span>

                </div>


                <div class="summary-card fail-card">

                    <p>Failed Students</p>

                    <h3><?= e($totalFailed) ?></h3>

                    <span>Require improvement</span>

                </div>


                <div class="summary-card average-card">

                    <p>Average Percentage</p>

                    <h3><?= e(number_format($averagePercentage, 2)) ?>%</h3>

                    <span>Overall class performance</span>

                </div>

            </section>


            <!-- Highest-scoring student -->
            <section class="highlight-grid">

                <div class="highlight-card top-student-card">

                    <div class="highlight-icon">★</div>

                    <div>

                        <p class="eyebrow">TOP PERFORMER</p>

                        <?php if ($topStudent !== null): ?>

                            <h2><?= e($topStudent['name']) ?></h2>

                            <p>
                                Roll Number:
                                <?= e($topStudent['roll_number']) ?>
                            </p>

                            <strong>
                                <?= e($topStudent['obtained_marks']) ?>
                                /
                                <?= e($topStudent['total_marks']) ?>
                                Marks
                            </strong>

                            <p>
                                Percentage:
                                <?= e(number_format($topStudent['percentage'], 2)) ?>%
                            </p>

                        <?php else: ?>

                            <p>No student records available.</p>

                        <?php endif; ?>

                    </div>

                </div>


                <!-- Latest enrollment information -->
                <div class="highlight-card latest-card">

                    <p class="eyebrow">LATEST ENROLLMENT</p>

                    <?php if ($latestEnrollment !== null): ?>

                        <h2><?= e($latestEnrollment['name']) ?></h2>

                        <p>
                            Roll Number:
                            <?= e($latestEnrollment['roll_number']) ?>
                        </p>

                        <span>
                            <?= e($latestEnrollment['status']) ?>
                        </span>

                    <?php else: ?>

                        <p>No enrollment records available.</p>

                    <?php endif; ?>

                </div>

            </section>


            <!-- Student results and filters -->
            <section class="results-section">

                <div class="section-heading">

                    <div>

                        <h2>Student Academic Records</h2>

                        <p>
                            Examination marks and calculated academic results.
                        </p>

                    </div>


                    <!-- Filter student records using GET -->
                    <form method="GET" action="" class="filter-form">

                        <label for="view">Filter Students</label>

                        <select name="view" id="view">

                            <option value="all" <?= $selectedView === 'all' ? 'selected' : '' ?>>
                                All Students
                            </option>

                            <option value="passed" <?= $selectedView === 'passed' ? 'selected' : '' ?>>
                                Passed Students
                            </option>

                            <option value="failed" <?= $selectedView === 'failed' ? 'selected' : '' ?>>
                                Failed Students
                            </option>

                        </select>

                        <button type="submit">
                            Apply Filter
                        </button>

                    </form>

                </div>


                <p class="record-count">

                    Showing <?= e($displayedStudents) ?>
                    of <?= e($totalStudents) ?> students

                </p>


                <div class="table-wrapper">

                    <table>

                        <thead>

                            <tr>

                                <th>Student Name</th>

                                <th>Roll Number</th>

                                <?php foreach ($subjectNames as $subjectName): ?>

                                    <th><?= e($subjectName) ?></th>

                                <?php endforeach; ?>

                                <th>Obtained / Total</th>

                                <th>Percentage</th>

                                <th>Grade</th>

                                <th>Status</th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php if ($displayStudents !== []): ?>

                                <?php foreach ($displayStudents as $student): ?>

                                    <tr>

                                        <td class="student-name">

                                            <?= e($student['name']) ?>

                                        </td>


                                        <td>

                                            <?= e($student['roll_number']) ?>

                                        </td>


                                        <!-- Display all subject columns.
                                         A missing subject is displayed as a dash. -->

                                        <?php foreach ($subjectNames as $subjectName): ?>

                                            <?php

                                            $subjectMark = $student['subject_marks'][$subjectName] ?? null;

                                            ?>

                                            <td>

                                                <?= $subjectMark === null ? '—' : e($subjectMark) ?>

                                            </td>

                                        <?php endforeach; ?>


                                        <td class="obtained-marks">

                                            <?= e($student['obtained_marks']) ?>

                                            /

                                            <?= e($student['total_marks']) ?>

                                        </td>


                                        <td>

                                            <?= e(number_format($student['percentage'], 2)) ?>%

                                        </td>


                                        <td>

                                            <span class="grade-badge">

                                                <?= e($student['grade']) ?>

                                            </span>

                                        </td>


                                        <td>

                                            <span class="<?= $student['is_passed'] ? 'status-pass' : 'status-fail' ?>">

                                                <?= e($student['status']) ?>

                                            </span>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>

                                    <td colspan="<?= e(count($subjectNames) + 6) ?>" class="empty-message">

                                        No students found for this filter.

                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </section>


            <!-- Reusable passed and failed student lists -->
            <section class="student-groups">

                <?php foreach ($studentGroups as $group): ?>

                    <div class="group-card <?= e($group['class']) ?>">

                        <h2><?= e($group['title']) ?></h2>

                        <p class="group-description">

                            <?= e($group['description']) ?>

                        </p>

                        <ul>

                            <?php if ($group['students'] === []): ?>

                                <li>No students in this group.</li>

                            <?php else: ?>

                                <?php foreach ($group['students'] as $student): ?>

                                    <li>

                                        <span>

                                            <?= e($student['name']) ?>

                                            <small>

                                                <?= e($student['roll_number']) ?>

                                            </small>

                                        </span>

                                        <strong>

                                            <?= e(number_format($student['percentage'], 2)) ?>%

                                        </strong>

                                    </li>

                                <?php endforeach; ?>

                            <?php endif; ?>

                        </ul>

                    </div>

                <?php endforeach; ?>

            </section>


            <!-- Academic grading policy -->
            <section class="grading-section">

                <h2>Academic Grading Policy</h2>

                <div class="grading-grid">

                    <span>A+ : 90% and above</span>

                    <span>A : 80% – 89.99%</span>

                    <span>B : 70% – 79.99%</span>

                    <span>C : 60% – 69.99%</span>

                    <span>D : 50% – 59.99%</span>

                    <span>F : Below 50%</span>

                </div>

                <p>

                    Students must obtain at least 40 marks in every subject
                    and 50% overall to pass. Failure in either requirement
                    results in an F grade.

                </p>

            </section>

        </main>


        <!-- School footer -->
        <footer>

            <p><?= e(SCHOOL_NAME) ?></p>

            <span>
                Academic Records and Student Performance Management
            </span>

        </footer>

    </div>

</body>

</html>