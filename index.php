<?php

declare(strict_types=1);


// Load school information and student records.
require_once __DIR__ . '/includes/students.php';

// Load reusable calculation and grading functions.
require_once __DIR__ . '/includes/functions.php';


// Calculate and prepare the result of every student.
// The anonymous callback runs prepareStudentResult() for each record.
$students = array_map(
    static fn(array $student): array => prepareStudentResult($student),
    $studentRecords
);


// Filter students who passed using array_filter().
$passedStudents = array_values(
    array_filter(
        $students,
        static fn(array $student): bool => $student['is_passed']
    )
);


// Filter students who failed.
$failedStudents = array_values(
    array_filter(
        $students,
        static function (array $student): bool {
            return !$student['is_passed'];
        }
    )
);


// Extract all student percentages using array_map().
$percentages = array_map(
    static fn(array $student): float => $student['percentage'],
    $students
);


// Calculate the average percentage using a while loop.
$percentageTotal = 0.0;

$index = 0;

while ($index < count($percentages)) {

    $percentageTotal += $percentages[$index];

    $index++;
}


// Calculate average safely.
$averagePercentage = count($percentages) > 0
    ? $percentageTotal / count($percentages)
    : 0.0;


// Identify the highest-scoring student using a for loop.
$topStudent = null;

for ($index = 0; $index < count($students); $index++) {

    $currentStudent = $students[$index];

    // Skip a record if its roll number is missing.
    if (trim($currentStudent['roll_number']) === '') {
        continue;
    }

    // Compare obtained marks with the current highest scorer.
    if (
        $topStudent === null ||
        $currentStudent['obtained_marks'] > $topStudent['obtained_marks']
    ) {
        $topStudent = $currentStudent;
    }

    // A perfect score cannot be beaten, so stop searching.
    if ($currentStudent['percentage'] >= 100.0) {
        break;
    }
}


// Prepare the latest enrollment result.
$latestEnrollment = prepareStudentResult(
    $latestEnrolledStudent
);


// Read the selected student filter.
// Null coalescing provides "all" when no filter is supplied.
$requestedView = $_GET['view'] ?? 'all';

// Only accept a string as a filter value.
$selectedView = is_string($requestedView)
    ? $requestedView
    : 'all';

// Define the permitted filter options.
$allowedViews = ['all', 'passed', 'failed'];

// Validate the selected filter.
if (!in_array($selectedView, $allowedViews, true)) {
    $selectedView = 'all';
}


// Select which students should appear in the main table.
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


// Get the names of all subjects from the first student.
$subjectNames = array_keys(
    $students[0]['subject_marks'] ?? []
);


// Calculate summary information.
$totalStudents = count($students);

$totalPassed = count($passedStudents);

$totalFailed = count($failedStudents);

$displayedStudents = count($displayStudents);

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

                        <?php else: ?>

                            <p>No student records available.</p>

                        <?php endif; ?>

                    </div>

                </div>


                <!-- Latest enrollment information -->
                <div class="highlight-card latest-card">

                    <p class="eyebrow">LATEST ENROLLMENT</p>

                    <h2><?= e($latestEnrollment['name']) ?></h2>

                    <p>
                        Roll Number:
                        <?= e($latestEnrollment['roll_number']) ?>
                    </p>

                    <span>
                        <?= e($latestEnrollment['status']) ?>
                    </span>

                </div>

            </section>


            <!-- Student records and filter controls -->
            <section class="results-section">

                <div class="section-heading">

                    <div>

                        <h2>Student Academic Records</h2>

                        <p>
                            Examination marks and calculated academic results.
                        </p>

                    </div>


                    <!-- Filter records through a GET request -->
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

                        <button type="submit">Apply Filter</button>

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


                                        <!-- Display every subject mark.
                                             do-while executes at least once when subjects exist. -->

                                        <?php

                                        $subjectIndex = 0;

                                        if ($subjectNames !== []) {

                                            do {

                                                $subjectName = $subjectNames[$subjectIndex];

                                                ?>

                                                <td>
                                                    <?= e($student['subject_marks'][$subjectName]) ?>
                                                </td>

                                                <?php

                                                $subjectIndex++;

                                            } while ($subjectIndex < count($subjectNames));

                                        }

                                        ?>


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

                                    <td colspan="<?= e(count($subjectNames) + 7) ?>" class="empty-message">

                                        No students found for this filter.

                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </section>


            <!-- Pass and fail student lists -->
            <section class="student-groups">

                <div class="group-card">

                    <h2>Passed Students</h2>

                    <p class="group-description">
                        Students who achieved the minimum passing marks in every subject.
                    </p>

                    <ul>

                        <?php foreach ($passedStudents as $student): ?>

                            <li>

                                <span>
                                    <?= e($student['name']) ?>
                                    <small><?= e($student['roll_number']) ?></small>
                                </span>

                                <strong>
                                    <?= e(number_format($student['percentage'], 2)) ?>%
                                </strong>

                            </li>

                        <?php endforeach; ?>

                    </ul>

                </div>


                <div class="group-card failed-group">

                    <h2>Failed Students</h2>

                    <p class="group-description">
                        Students who need additional academic support.
                    </p>

                    <ul>

                        <?php foreach ($failedStudents as $student): ?>

                            <li>

                                <span>
                                    <?= e($student['name']) ?>
                                    <small><?= e($student['roll_number']) ?></small>
                                </span>

                                <strong>
                                    <?= e(number_format($student['percentage'], 2)) ?>%
                                </strong>

                            </li>

                        <?php endforeach; ?>

                    </ul>

                </div>

            </section>


            <!-- Academic grading rules -->
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
                    to pass. Failure in any individual subject results in an F grade.
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