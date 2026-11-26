<?php

declare(strict_types=1);

// First section: five students.
$sectionAStudents = [

    [
        'name' => 'Ayesha Noor',
        'roll_number' => 'SRA-001',
        'subject_marks' => [
            'English' => 92,
            'Mathematics' => 95,
            'General Science' => 90,
            'Computer Studies' => 98,
            'Social Studies' => 91
        ]
    ],

    [
        'name' => 'Hamza Ali',
        'roll_number' => 'SRA-002',
        'subject_marks' => [
            'English' => 85,
            'Mathematics' => 78,
            'General Science' => 82,
            'Computer Studies' => 88,
            'Social Studies' => 80
        ]
    ],

    [
        'name' => 'Fatima Zahra',
        'roll_number' => 'SRA-003',
        'subject_marks' => [
            'English' => 76,
            'Mathematics' => 81,
            'General Science' => 74,
            'Computer Studies' => 79,
            'Social Studies' => 77
        ]
    ],

    [
        'name' => 'Ahmed Raza',
        'roll_number' => 'SRA-004',
        'subject_marks' => [
            'English' => 68,
            'Mathematics' => 72,
            'General Science' => 65,
            'Computer Studies' => 70,
            'Social Studies' => 69
        ]
    ],

    [
        'name' => 'Maryam Khan',
        'roll_number' => 'SRA-005',
        'subject_marks' => [
            'English' => 55,
            'Mathematics' => 61,
            'General Science' => 58,
            'Computer Studies' => 63,
            'Social Studies' => 60
        ]
    ]

];


// Second section: four students.
$sectionBStudents = [

    [
        'name' => 'Bilal Ahmed',
        'roll_number' => 'SRA-006',
        'subject_marks' => [
            'English' => 39,
            'Mathematics' => 48,
            'General Science' => 52,
            'Computer Studies' => 43,
            'Social Studies' => 45
        ]
    ],

    [
        'name' => 'Zainab Ali',
        'roll_number' => 'SRA-007',
        'subject_marks' => [
            'English' => 88,
            'Mathematics' => 91,
            'General Science' => 86,
            'Computer Studies' => 84,
            'Social Studies' => 90
        ]
    ],

    [
        'name' => 'Usman Tariq',
        'roll_number' => 'SRA-008',
        'subject_marks' => [
            'English' => 42,
            'Mathematics' => 38,
            'General Science' => 55,
            'Computer Studies' => 49,
            'Social Studies' => 60
        ]
    ],

    [
        'name' => 'Hira Fatima',
        'roll_number' => 'SRA-009',
        'subject_marks' => [
            'English' => 72,
            'Mathematics' => 69,
            'General Science' => 75,
            'Computer Studies' => 71,
            'Social Studies' => 74
        ]
    ]

];


// Merge both sections into one student collection.
$studentRecords = array_merge(
    $sectionAStudents,
    $sectionBStudents
);


// Add the tenth student to the collection.
$newStudent = [
    'name' => 'Daniyal Shah',
    'roll_number' => 'SRA-010',
    'subject_marks' => [
        'English' => 30,
        'Mathematics' => 44,
        'General Science' => 38,
        'Computer Studies' => 50,
        'Social Studies' => 42
    ]
];

array_push($studentRecords, $newStudent);


// Work on a copy so array_pop() does not remove
// a student from the original collection.
$studentPreview = $studentRecords;

$latestEnrolledStudent = array_pop($studentPreview);