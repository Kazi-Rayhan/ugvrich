<?php

/*
|--------------------------------------------------------------------------
| Shared vocabulary — English
|--------------------------------------------------------------------------
|
| The keys come from config/rich.php; this file carries the wording the site
| shows. lang/bn/rich.php is its Bangla twin.
|
*/

return [

    'research_departments' => [
        'English' => 'English',
        'BBA' => 'BBA',
        'CSE' => 'CSE',
        'Mechanical Engineering' => 'Mechanical Engineering',
        'Mechanical' => 'Mechanical',
        'Public Health' => 'Public Health',
        'Islamic Studies' => 'Islamic Studies',
        'Civil Engineering' => 'Civil Engineering',
        'Civil' => 'Civil',
        'EEE' => 'EEE',
    ],

    // code => name. The code is used in project IDs, e.g. RICH-CSE-2026-001.
    'departments' => [
        'CSE' => 'Computer Science & Engineering',
        'EEE' => 'Electrical & Electronic Engineering',
        'ME' => 'Mechanical Engineering',
        'CE' => 'Civil Engineering',
        'BUS' => 'Business Administration',
        'PH' => 'Public Health',
        'ENG' => 'English',
        'IS' => 'Islamic Studies & Humanities',
        'RICH' => 'UGV RICH (cross-departmental)',
    ],

    // The startup journey, in order: what happens to a submitted idea.
    'startup_stages' => [
        'idea' => 'Submit Idea',
        'evaluation' => 'Evaluation',
        'mentorship' => 'Mentorship',
        'prototype' => 'Prototype',
        'business_model' => 'Business Model',
        'funding' => 'Funding Support',
        'startup' => 'Startup',
        'market' => 'Market',
    ],

    // Who can submit an idea.
    'idea_categories' => [
        'engineering' => 'Engineering & Technology',
        'business' => 'Business & Entrepreneurship',
        'arts' => 'Arts, Humanities & Social Innovation',
        'other' => 'Others',
    ],

    'idea_roles' => [
        'student' => 'Student',
        'faculty' => 'Faculty member',
        'staff' => 'Staff',
        'alumni' => 'Alumni',
        'external' => 'External',
    ],

    // key => label, in pipeline order.
    'pipeline_stages' => [
        'idea' => 'Idea Submitted',
        'selected' => 'Selected',
        'research' => 'Research',
        'prototype' => 'Prototype',
        'testing' => 'Testing',
        'patent' => 'Patent / IP',
        'incubation' => 'Incubation',
        'commercialization' => 'Commercialization',
    ],

    // Publication kinds, in the order the Research page lists them.
    'publication_kinds' => [
        'journal' => 'Journal Article',
        'conference' => 'Conference Paper',
        'publication' => 'Other Publication',
        'funded-project' => 'Funded Project / Grant',
    ],

    'project_types' => [
        'innovation' => 'Innovation',
        'research' => 'Research',
        'consultancy' => 'Consultancy',
    ],

    'patent_statuses' => [
        'none' => 'Not applicable',
        'planned' => 'Planned',
        'filed' => 'Application filed',
        'published' => 'Application published',
        'granted' => 'Granted',
        'copyright' => 'Copyright registered',
        'design' => 'Industrial design registered',
    ],

    'commercialization_statuses' => [
        'none' => 'Not started',
        'exploring' => 'Exploring market',
        'licensing' => 'Available for licensing',
        'incubating' => 'In incubation',
        'startup' => 'Startup formed',
        'market' => 'On the market',
    ],
];
