<?php

/*
 | The Research page: what RICH is, and what it is being built to become.
 |
 | The wording here is the page's own chrome — headings, labels, the names of
 | capabilities that do not exist yet. Everything factual comes from elsewhere:
 | the research framework document (config/research_framework.php) for the
 | wing's own words, and the database for counts. Nothing on this page invents
 | a number, a researcher or a publication.
 */

return [

    'hero' => [
        'eyebrow' => 'UGV RICH · Research Wing',
        'title' => 'Research, Innovation & <span class="text-highlight">Collaboration Hub</span>',
        'lead' => 'RICH is being built as the place where researchers, students, ideas, collaboration, funding, publication and research impact meet — one hub for the whole of UGV research.',
        'primary' => 'Start your research journey',
        'secondary' => 'Explore research areas',
        'note' => 'A vision for the research ecosystem. Some of what follows is in place today; the rest is what RICH is being designed to become.',
    ],

    'about' => [
        'eyebrow' => 'What is RICH',
        'title' => 'A central research ecosystem for UGV',
        'lead' => 'The long-term vision is a single hub that connects every part of research at the university, so a researcher never has to go looking for the pieces one at a time.',
        'brings_together' => 'Designed to bring together',
        'items' => [
            'Researchers',
            'Students',
            'Research ideas',
            'Research projects',
            'Collaboration',
            'Funding',
            'Research support',
            'Publications',
            'Innovation',
            'Research impact',
        ],
        'framework_link' => 'Read the full research framework',
    ],

    'lifecycle' => [
        'phases' => [
            'Shaping the question',
            'Doing the research',
            'Publishing and impact',
        ],
        'eyebrow' => 'The research lifecycle',
        'title' => 'From an idea to its impact',
        'lead' => 'RICH is envisioned to support a researcher across the whole of this journey, rather than at one point in it. Each stage below is a place where the hub could help.',
        'stages' => [
            ['Idea', 'A question worth asking, from the classroom, the field or the community.'],
            ['Collaboration', 'The people and departments the question needs.'],
            ['Proposal', 'The idea written up as a plan that can be reviewed and funded.'],
            ['Review', 'Peer and faculty reading, before the work starts rather than after.'],
            ['Ethics', 'Approval where human participants, data or fieldwork are involved.'],
            ['Funding', 'Internal seed support, or an external grant the proposal fits.'],
            ['Research', 'The work itself — fieldwork, laboratory, archive, survey.'],
            ['Data & analysis', 'Making sense of what was gathered, quantitative or qualitative.'],
            ['Manuscript', 'Writing it up for the readers it is meant for.'],
            ['Publication', 'Journal, conference or report, with the right venue chosen early.'],
            ['Impact', 'What changed because the work was done.'],
        ],
        'document_link' => 'The framework sets this out in nine steps',
    ],

    'offer' => [
        'eyebrow' => 'What RICH could offer',
        'title' => 'The modules the hub is being designed around',
        'lead' => 'Each of these is a capability RICH is planned to provide. They are described here as intent, not as something you can use today.',
        'cards' => [
            ['Research areas', 'Explore research themes by department and research field.', 'compass'],
            ['Find a collaborator', 'Connect researchers by expertise, methodology and research interest.', 'users'],
            ['Research proposal', 'A place to submit and manage research proposals.', 'document'],
            ['Research projects', 'One view of every project and how far it has got.', 'grid'],
            ['Research repository', 'A public record of UGV publications and research outputs.', 'academic'],
            ['Funding & grants', 'Internal and external funding opportunities, in one list.', 'briefcase'],
            ['Research support desk', 'Methodology, statistics, academic writing, journal selection.', 'heart'],
            ['Student research hub', 'Undergraduate research, assistantships, training and competitions.', 'star'],
        ],
    ],

    'areas' => [
        'eyebrow' => 'Research areas',
        'title' => 'Department → research field',
        'lead' => 'The research framework names the priority fields each department works in. Choose a department to see its fields, the SDGs they serve, and how well each matches current funding.',
        'fields' => 'research fields',
        'sdg_label' => 'SDGs',
        'funding_label' => 'Funding alignment',
        'all' => 'All departments',
        'count' => '{1} :count field|[2,*] :count fields',
    ],

    'collaboration' => [
        'eyebrow' => 'Collaboration',
        'title' => 'Find the right research collaborator',
        'lead' => 'A researcher would describe what the work needs, and the hub would suggest the people who fit. The criteria below are what the matching is being designed around.',
        'criteria_title' => 'What a request would describe',
        'criteria' => [
            ['Department', 'Which departments the question needs at the table.'],
            ['Research field', 'The field or sub-field the work sits in.'],
            ['Methodology', 'Quantitative, qualitative, mixed, experimental, design-based.'],
            ['Required expertise', 'The specific competence the work is missing.'],
            ['Number of collaborators', 'How many people the work has room for.'],
            ['Internal or external', 'Within UGV, or with another institution or industry.'],
        ],
        'examples_title' => 'How one problem draws in several departments',
        'examples_lead' => 'These examples are from the research framework — a single question, and the departments it needs.',
        'post_title' => 'Post a research idea',
        'post_lead' => 'A researcher could publish an idea and let others find it and express interest, instead of asking around one person at a time.',
        'post_example_label' => 'For example',
        'post_example' => 'AI-assisted assessment in Bangladeshi higher education',
        'post_interest' => 'Other researchers could register interest',
    ],

    'support' => [
        'eyebrow' => 'Research support',
        'title' => 'A support desk for the whole of the work',
        'lead' => 'The framework sets out help before, during and after a study. The hub is intended to make that help something a researcher can ask for in one place.',
        'items' => [
            'Proposal review',
            'Research design',
            'Methodology support',
            'Statistical support',
            'Qualitative analysis',
            'Academic writing',
            'Reference management',
            'Journal selection',
            'Plagiarism check',
            'AI-use guidance',
            'Manuscript editing',
            'Publication support',
        ],
        'cta' => 'Request research support',
    ],

    'impact' => [
        'eyebrow' => 'Research impact',
        'title' => 'Counting the work, and what it changed',
        'lead' => 'RICH is meant to record not only how much research happens but what it leads to. The figures below are what the site holds today — where there is nothing to count yet, it says so rather than guessing.',
        'soon' => 'Coming soon',
        'metrics' => [
            'research_projects' => 'Research projects',
            'publications' => 'Publications',
            'quartile' => 'Q1 / Q2 publications',
            'funding' => 'Research funding',
            'external_grants' => 'External grants',
            'conference_papers' => 'Conference papers',
            'patents' => 'Patents & IP',
            'industry' => 'Industry collaborations',
            'international' => 'International collaborations',
            'student_researchers' => 'Student researchers',
        ],
    ],

    'sdg' => [
        'eyebrow' => 'Sustainable Development Goals',
        'title' => 'UGV research against the SDGs',
        'lead' => 'Every priority field in the research framework already names the goals it serves. The hub could map each project and publication the same way, so the university can see its contribution as a whole.',
        'areas_label' => '{1} :count research field|[2,*] :count research fields',
        'goals' => [
            3 => 'Good health & well-being',
            4 => 'Quality education',
            5 => 'Gender equality',
            6 => 'Clean water & sanitation',
            7 => 'Affordable & clean energy',
            8 => 'Decent work & economic growth',
            9 => 'Industry, innovation & infrastructure',
            10 => 'Reduced inequalities',
            11 => 'Sustainable cities & communities',
            12 => 'Responsible consumption',
            13 => 'Climate action',
            14 => 'Life below water',
            15 => 'Life on land',
            16 => 'Peace, justice & strong institutions',
            17 => 'Partnerships for the goals',
        ],
    ],

    'ecosystem' => [
        'eyebrow' => 'The ecosystem',
        'title' => 'RICH as the connection point',
        'lead' => 'The point of a hub is that everything else can reach everything else through it.',
        'nodes' => [
            'Researchers',
            'Students',
            'Departments',
            'Funding',
            'Collaborators',
            'Publications',
            'Industry & external partners',
        ],
        'centre' => 'RICH',
    ],

    'future' => [
        'eyebrow' => 'The future of RICH',
        'title' => 'What is planned, and what is not built yet',
        'lead' => 'Named plainly, so nobody is left guessing which parts of the hub they can use today.',
        'status' => [
            'live' => 'Available',
            'planned' => 'Planned',
            'soon' => 'Coming soon',
        ],
        'modules' => [
            'Researcher profiles',
            'Collaboration hub',
            'Proposal submission',
            'Proposal tracking',
            'Ethics management',
            'Project dashboard',
            'Manuscript submission',
            'Research repository',
            'Funding & grants',
            'SDG research tracker',
            'Research KPI dashboard',
            'Annual research report',
            'Research achievement wall',
        ],
    ],

    'cta' => [
        'title' => 'Start your research journey',
        'lead' => 'Have a research idea? Looking for collaboration, support, or opportunities? RICH is designed to bring ideas, people and research opportunities together.',
        'primary' => 'Explore research',
        'secondary' => 'Connect & collaborate',
    ],

    'portal' => [
        'sign_in' => 'Researcher sign in',
        'dashboard' => 'Go to my dashboard',
        'join' => 'Join RICH as a researcher',
        'lead' => 'Register for the Researcher Portal to submit research ideas, develop them into proposals, and follow where they have got to.',
        'cta' => 'Register or sign in',
    ],

    'send' => [
        'eyebrow' => 'Send something in',
        'title' => 'Two things you can do today',
        'lead' => 'Everything above is what RICH is being built to become. These two are not concepts: send either one and a member of the Research Wing will read it.',
    ],

    'forms' => [
        'required' => 'Required',
        'optional' => 'Optional',
        'you' => 'About you',
        'name' => 'Your name',
        'email' => 'Email',
        'phone' => 'Phone',
        'role' => 'You are',
        'roles' => ['Faculty', 'Student', 'Research assistant', 'External researcher', 'Other'],
        'department' => 'Department',
        'choose' => 'Choose...',
        'document' => 'Attach a document',
        'document_help' => 'PDF or Word, up to 10 MB. Optional.',

        'errors' => [
            'details' => 'Please describe what you need in a little more detail (at least 20 characters).',
            'summary' => 'Please summarise the research in a little more detail (at least 40 characters).',
            'field_mismatch' => 'That research field does not belong to the department you chose.',
            'rejected' => 'Submission rejected.',
        ],

        'support' => [
            'eyebrow' => 'Research support',
            'title' => 'Request research support',
            'lead' => 'Tell us where you are and what you need. A member of the Research Wing will read it and come back to you - this is a request, not an automated service.',
            'what' => 'What do you need help with?',
            'what_help' => 'Choose as many as apply.',
            'about' => 'About the work',
            'work_title' => 'Research title or working title',
            'details' => 'What do you need?',
            'details_help' => 'What stage the work is at, what is blocking it, and what would help.',
            'stage' => 'Stage of the work',
            'stages' => ['Idea', 'Proposal', 'Data collection', 'Analysis', 'Writing up', 'Under review', 'Other'],
            'needed_by' => 'Needed by',
            'submit' => 'Send the request',
        ],

        'proposal' => [
            'eyebrow' => 'Research proposal',
            'title' => 'Submit a research proposal',
            'lead' => 'Place the work in the framework first - department, then field, then the research area it sits in - and then describe it. Proposals are read by the Research Wing; nothing is approved automatically.',
            'where' => 'Where the research sits',
            'where_help' => 'The departments, fields and areas below are the ones the research framework names.',
            'field' => 'Research field',
            'field_help' => 'Choose a department first.',
            'area' => 'Research area',
            'area_help' => 'From the research clusters your department takes part in.',
            'sdgs' => 'SDGs this field serves',
            'sdgs_help' => 'Taken from the framework for the field you chose.',
            'about' => 'The proposal',
            'proposal_title' => 'Proposal title',
            'summary' => 'Summary',
            'summary_help' => 'What the research asks, and why it matters.',
            'objectives' => 'Objectives',
            'methodology' => 'Methodology',
            'practicalities' => 'Practicalities',
            'duration' => 'Expected duration',
            'collaborators' => 'Collaborators needed',
            'collaborators_help' => 'How many, and from which departments.',
            'funding' => 'Funding needed',
            'funding_help' => 'A rough figure or range is enough at this stage.',
            'submit' => 'Send the proposal',
        ],

        'thanks' => [
            'support_title' => 'Your support request is with us',
            'proposal_title' => 'Your proposal is with us',
            'lead' => 'Thank you, :name. We have it, and somebody from the Research Wing will be in touch.',
            'reference' => 'Reference',
            'reference_note' => 'Keep this if you need to follow it up.',
            'back' => 'Back to Research',
            'other_support' => 'Request research support',
            'other_proposal' => 'Submit a proposal',
        ],
    ],

    'framework' => [
        'eyebrow' => 'The document',
        'title' => 'RICH Research Wing framework',
        'lead' => 'The full planning document the Research Wing works to — pillars, priority areas, clusters, funding, publication pathway, ethics and the roadmap.',
        'link' => 'Read the framework',
        'back' => 'Back to Research',
    ],
];
