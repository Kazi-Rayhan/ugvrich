<?php

// English labels for data entry. The approved Bangla print templates use work_orders.php.
return [
    'provider' => [
        'name' => 'UGV Research, Innovation and Consultation Hub',
        'university' => 'University of Global Village, Barishal',
    ],
    'modes' => ['site' => 'On site', 'online' => 'Online', 'both' => 'Both'],
    'fee_lines' => [
        '1. Approved consultancy and professional fees',
        '2. Materials / third-party services (if agreed)',
        '3. Travel / testing / platform costs (if agreed)',
    ],
    'payment_steps' => [
        ['label' => 'On acceptance / at the start of work', 'proof' => 'Signed work order / invoice'],
        ['label' => 'After approval of the interim milestone', 'proof' => 'Progress or delivery approval'],
        ['label' => 'After final acceptance and handover', 'proof' => 'Final acceptance letter / invoice'],
    ],
    'checklist' => [
        'scope' => 'Scope defined',
        'price' => 'Price approved',
        'attachments' => 'Attachments included',
        'security' => 'Required security and privacy checks completed',
    ],
    'sectors' => [
        'ict' => [
            'department' => 'Department of Computer Science and Engineering',
            'summary_hint' => 'Describe the digital product requirement, intended users, and business objectives.',
            'services' => [
                'website' => 'Institutional website development', 'portal' => 'Portal development',
                'mobile_app' => 'Mobile app development', 'ecommerce' => 'E-commerce websites and platforms',
                'ai' => 'Artificial intelligence and automation', 'cyber' => 'Cybersecurity',
                'cloud' => 'Cloud services', 'network' => 'Networking and ISP solutions', 'cctv' => 'CCTV and surveillance',
            ],
            'info_groups' => [
                'Deployment environment', 'System / platform', 'Users and access',
                'Integrations and data', 'Infrastructure', 'Security baseline',
            ],
            'info_labels' => [
                'env_cloud' => 'Cloud', 'env_own' => 'Own server', 'env_hybrid' => 'Hybrid', 'env_undecided' => 'Undecided',
                'platform' => 'Web, mobile, CMS, ERP, network, or CCTV',
                'users' => 'Expected users', 'roles' => 'Roles', 'languages' => 'Languages',
                'integrations' => 'API, payments, SSO, and legacy data migration',
                'infrastructure' => 'Domain, hosting, licensing, and equipment ownership',
                'security' => 'Access, backups, privacy, and authorization to test',
            ],
            'stages' => [
                ['Requirements assessment', 'Finalize the requirements baseline'],
                ['Design / configuration', 'Approve the design'],
                ['Development / implementation', 'Review the demo and progress'],
                ['Testing and acceptance', 'Approve UAT and close the work'],
            ],
            'attachments' => [
                'sow' => 'Approved requirements / statement of work', 'mockup' => 'Design mockup / system diagram',
                'licence' => 'Third-party license and hosting details', 'uat' => 'UAT, security, or data management plan',
            ],
        ],
        'eee' => [
            'department' => 'Department of Electrical and Electronic Engineering',
            'summary_hint' => 'Describe the electrical system issue or the efficiency, safety, or installation objective.',
            'services' => [
                'load_audit' => 'Load optimization and energy audit', 'safety' => 'Electrical safety, earthing, and BNBC compliance',
                'solar' => 'Solar and renewable energy', 'automation' => 'Automation and control',
                'smart_building' => 'Smart buildings, energy monitoring, and IoT', 'maintenance' => 'Repair and maintenance',
            ],
            'info_groups' => [
                'Site / facility', 'Electrical system', 'Equipment details', 'Inspection type', 'Site access', 'Technical baseline',
            ],
            'info_labels' => [
                'site_address' => 'Site address', 'site_area' => 'Area / floor', 'supply_voltage' => 'Supply voltage (V)',
                'single_phase' => 'Single-phase', 'three_phase' => 'Three-phase', 'load' => 'Load (kW / kVA)',
                'equipment' => 'Panels, feeders, inverters, PLCs, motors, and meter IDs',
                'doc_review' => 'Document review', 'site_survey' => 'Site survey', 'measurement' => 'Measurement',
                'commissioning' => 'Commissioning', 'access_time' => 'Scheduled time', 'access_person' => 'Responsible person',
                'standards' => 'Existing single-line diagrams, operating data, BNBC, and applicable standards',
            ],
            'stages' => [
                ['Preparation and document review', 'Confirm site access and baseline information'],
                ['Inspection and measurement', 'Record site logs and observations'],
                ['Engineering / implementation', 'Review the design or corrective recommendations'],
                ['Verification and handover', 'Obtain the signed report / commissioning record'],
            ],
            'attachments' => [
                'site_access' => 'Site access permission and safety policy', 'sld' => 'Single-line diagrams and previous test reports',
                'boq' => 'Installation material list / bill of quantities', 'checklist' => 'Testing and commissioning checklist',
            ],
        ],
        'civ' => [
            'department' => 'Department of Civil Engineering',
            'summary_hint' => 'Describe the project site and the purpose of the infrastructure design or testing.',
            'services' => [
                'architecture' => 'Architectural and building assistance', 'structural' => 'Structural engineering',
                'material_testing' => 'Materials testing', 'construction' => 'Construction advisory',
                'project_management' => 'Project management consultancy', 'survey_gis' => 'Surveying and GIS',
            ],
            'info_groups' => [
                'Project site', 'Land and building details', 'Existing documents',
                'Technical inputs', 'Approval authority', 'Design standards',
            ],
            'info_labels' => [
                'site_address' => 'Plot / holding / site address', 'land_area' => 'Land area', 'built_area' => 'Built area',
                'floors_use' => 'Floors / use', 'deed_survey' => 'Deed / survey', 'soil_test' => 'Soil test',
                'old_drawings' => 'Existing drawings', 'photos' => 'Photos',
                'technical_input' => 'Structure type, test samples, or survey benchmark',
                'authority' => 'Applicable approvals and authorities', 'standards' => 'Approved brief, BNBC, testing standards, and local requirements',
            ],
            'stages' => [
                ['Information gathering and site verification', 'Verify the baseline documents'],
                ['Preliminary design / concept', 'Approve the design brief'],
                ['Detailed design / testing', 'Prepare the draft for review'],
                ['Final approval and issue', 'Record the report / drawings in the register'],
            ],
            'attachments' => [
                'land' => 'Land, deed, survey, and site details', 'soil' => 'Soil test and existing drawings',
                'rules' => 'Applicable regulations and approved design brief', 'testing' => 'Sample testing / quality verification / bill of quantities',
            ],
        ],
        'mec' => [
            'department' => 'Department of Mechanical Engineering',
            'summary_hint' => 'Describe the intended purpose of the machine, part, or vehicle design.',
            'services' => ['autocad' => 'AutoCAD 2D and 3D services', 'solidworks' => 'SolidWorks modeling and simulation'],
            'info_groups' => ['Part / project', 'Engineering inputs', 'Design objective', 'CAD scope', 'Dimensions and tolerances', 'File formats'],
            'info_labels' => [
                'part' => 'Part, machine, or vehicle model', 'engineering_input' => 'Sketches, dimensions, materials, and loads',
                'concept' => 'Concept', 'remodel' => 'Remodel an existing part', 'production' => 'Production',
                'modification' => 'Modification', 'drawing_2d' => '2D drawings', 'part_3d' => '3D part',
                'assembly' => 'Assembly', 'fea' => 'FEA / motion analysis', 'units' => 'Units',
                'tolerance' => 'Critical dimensions / tolerances', 'dwg' => 'DWG / DXF', 'sldprt' => 'SLDPRT / SLDASM',
                'step' => 'STEP', 'pdf' => 'PDF', 'other_format' => 'Other format',
            ],
            'stages' => [
                ['Finalize design inputs', 'Approve the baseline information'],
                ['Preliminary modeling', 'Review the concept geometry / first drawing'],
                ['Engineering review', 'Review design revisions or simulation results'],
                ['Final CAD handover', 'Deliver source files and controlled drawings'],
            ],
            'attachments' => [
                'sketch' => 'Design sketches, photos, and CAD inputs', 'dimensions' => 'Dimensions, tolerances, and material list',
                'template' => 'Drawing template and file formats', 'review' => 'Review comments and revision list',
            ],
        ],
        'bba' => [
            'department' => 'Department of Business Administration and Management',
            'summary_hint' => 'Describe the tax return, business plan, or new venture support needed.',
            'services' => ['tax' => 'Income tax advisory', 'startup' => 'Startup and entrepreneurship support'],
            'info_groups' => [
                'Client / organization type', 'Tax / financial year', 'Tax services',
                'Business support', 'Authorization and identity', 'Required documents',
            ],
            'info_labels' => [
                'individual' => 'Individual', 'sole' => 'Sole proprietorship', 'partnership' => 'Partnership', 'company' => 'Company',
                'income_year' => 'Income year', 'assessment_year' => 'Assessment year', 'return' => 'Return preparation',
                'efiling' => 'E-filing', 'advice' => 'Advisory', 'review' => 'Review', 'model' => 'Business model',
                'registration' => 'Registration support', 'expansion' => 'Expansion', 'representative' => 'Authorized representative',
                'appointment' => 'Engagement authorization', 'documents' => 'Income, expenses, salary, investments, and organizational records',
            ],
            'stages' => [
                ['Engagement and document intake', 'Verify identification and the document checklist'],
                ['Initial review', 'Record missing information'],
                ['Draft tax return / advice', 'Obtain the client’s review and approval'],
                ['Filing / handover', 'Deliver final documents and next steps'],
            ],
            'attachments' => [
                'appointment' => 'Signed client authorization / engagement letter', 'tax_docs' => 'Documents required for the tax year',
                'consent' => 'Privacy and data consent documents', 'receipt' => 'Approved draft / filing acknowledgement',
            ],
        ],
        'lan' => [
            'department' => 'Department of English and Language Education',
            'summary_hint' => 'Describe the goals for language learning, teacher development, or online instruction.',
            'services' => ['teacher_support' => 'Online English teacher support', 'online_school' => 'Online school'],
            'info_groups' => ['Course / cohort', 'Participants', 'Teaching format', 'Class schedule', 'Content and objectives', 'Assessment methods'],
            'info_labels' => [
                'course' => 'Course or training name', 'batch' => 'Cohort', 'learners' => 'Learners', 'trainers' => 'Instructors',
                'level' => 'Age / level', 'live' => 'Live online', 'recorded' => 'Recorded', 'hybrid' => 'Hybrid',
                'platform' => 'Platform', 'weekly_classes' => 'Classes per week', 'duration' => 'Duration', 'start_end' => 'Start / end',
                'syllabus' => 'Syllabus, CEFR or local framework, and learning outcomes', 'quiz' => 'Quiz',
                'oral' => 'Oral', 'written' => 'Written', 'attendance' => 'Attendance / progress',
            ],
            'stages' => [
                ['Needs assessment / enrollment', 'Confirm the initial assessment and learner list'],
                ['Curriculum and scheduling', 'Approve the course plan'],
                ['Class delivery', 'Record attendance and learning progress'],
                ['Assessment / completion', 'Record results and the completion report'],
            ],
            'attachments' => [
                'needs' => 'Curriculum / training needs assessment', 'schedule' => 'Approved class schedule / session plan',
                'guardian' => 'Guardian consent and access policy', 'rubric' => 'Assessment rubric / progress report',
            ],
        ],
    ],
];
