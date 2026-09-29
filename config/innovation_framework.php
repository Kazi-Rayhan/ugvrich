<?php

/*
|--------------------------------------------------------------------------
| RICH – Innovation Wing | Comprehensive Innovation Plan
|--------------------------------------------------------------------------
|
| The proposal document, transcribed section by section so the Innovation
| page renders it from one source. Headings and numbering are the document's
| own; nothing is added and nothing is left out.
|
| lang/bn/innovation_framework.php is its Bangla twin.
|
*/

return [

    'headings' => [
        'summary' => '1. Executive Summary',
        'purpose' => '2. Purpose & Strategic Direction',
        'focus' => '3. Vision, Mission & Focus',
        'current' => '4. Current Innovations (Prototype to Field Testing)',
        'proposed' => '5. Proposed Innovations – Next Phase (6 Projects)',
        'process' => '6. Innovation Process Framework (TRL 1 to 9)',
        'organization' => '7. Organization & Mentorship',
        'kpis' => '8. KPIs – Next 12 Months',
        'abbreviations' => '9. Abbreviations & Short Forms',
        'funding' => '10. Funding & Sustainability',
        'conclusion' => '11. Conclusion',

        'section' => 'Section :number',

        // Table column heads, as the document writes them.
        'pillar' => 'Pillar',
        'direction' => 'Direction',
        'innovation' => 'Innovation',
        'lead_support' => 'Lead & Support',
        'next' => 'Next',
        'key_highlights' => 'Key Highlights',
        'stage' => 'Stage',
        'what_happens' => 'What Happens',
        'output' => 'Output / TRL',
        'role' => 'Role',
        'responsibility' => 'Responsibility',
        'kpi' => 'KPI',
        'target' => 'Target',
        'abbreviation' => 'Abbreviation',
        'full_form' => 'Full Form',

        // The labels each proposed project is written under.
        'tagline' => 'Tagline',
        'concept' => 'Concept',
        'how' => 'How It Works',
        'why' => 'Why Innovative & Market',
        'departments' => 'Lead Departments',
        'sdg' => 'SDG Alignment',
    ],

    'title' => 'RICH – Innovation Wing',
    'subtitle' => 'Comprehensive Innovation Plan | University of Global Village (UGV)',
    'strapline' => 'Strategic Framework: Innovation • Prototyping • Technology Transfer • Impact',

    // 1. Executive Summary
    'summary' => 'The RICH Innovation Wing is UGV’s central platform to convert real-world problems into low-cost, sustainable, and scalable solutions. It operates on an interdisciplinary model where all nine departments—Mechanical, EEE, Civil, CSE, BBA, MPH, English, Islamic Studies, and GED—co-create, prototype, and commercialize innovations. This proposal presents three field-tested prototypes and six next-phase innovations aligned with SDGs, national priorities, and market demand.',

    // 2. Purpose & Strategic Direction
    'purpose' => 'To build a frugal, technology-driven, and impact-oriented ecosystem that moves ideas beyond labs into functional prototypes, patents, startups, and community deployment— focusing on clean energy, rural mobility, women safety, health, education, and ethical technology.',

    // 3. Vision, Mission & Focus — [pillar, direction]
    'focus' => [
        ['Vision', 'Establish UGV as a nationally recognized hub for frugal, sustainable, and socially impactful innovation solving rural, urban, industrial, and educational challenges.'],
        ['Mission', 'Foster interdisciplinary culture; convert student ideas into high-TRL prototypes (TRL 1-9); secure IP; incubate startups; transfer technology to SMEs and communities.'],
        ['Innovation Culture', 'From occasional departmental projects → regular, funded, mentored innovation practice across all 9 departments.'],
        ['Development Path', 'Idea → Prototype → Patent → Product → Startup.'],
        ['Impact Focus', 'SDG 3,4,5,7,9,11,12,13,16: Health, Education, Gender, Clean Energy, Infrastructure, Sustainable Communities, Climate Action.'],
    ],

    /*
     | Covers, by position. Presentation only — the document itself names no
     | imagery. Where a photograph exists it is used; the rest fall back to the
     | generated blueprint art, seeded by the item's own name.
     */
    'current_covers' => [
        'projects/sun-car.jpg',
        null,
        null,
    ],
    'proposed_covers' => [null, null, null, null, null, null],

    // 4. Current Innovations — [innovation, lead & support, key highlights]
    'current' => [
        [
            'SUN CAR – Solar Utility Vehicle',
            'Mechanical & EEE (Lead), All Depts.',
            'Dual-mode solar + battery, 5-hr night backup, regenerative braking, 2-3 passengers. Low-cost last-mile transport for campus/rural areas. Next: lightweight chassis, IoT GPS, solar charging station, patent.',
        ],
        [
            'She Safe – Women Safety Wearable',
            'EEE (Lead), All Depts.',
            'SOS with live location SMS, real-time tracking, non-lethal deterrent with safety-lock, auto audio-to-cloud. Bracelet/pendant form. Next: miniaturization, UGV security app integration, IP filing.',
        ],
        [
            'Floating Bridge – Modular Rural Connectivity',
            'Civil (Lead), All Depts.',
            'Buoyant drum/steel-deck system, quick install by local labor, for pedestrians/bikes/rickshaws. No river blockage. Next: Load & current testing, pilot in Barishal with LGED.',
        ],
    ],

    // 5. Proposed Innovations – Next Phase
    'proposed_intro' => 'Two new high-demand clean mobility projects lead this phase, followed by four previously proposed projects. All are low-cost, locally maintainable, and designed for commercialization.',

    'proposed' => [
        [
            'no' => '5.1.',
            'name' => 'Solar Scooty',
            'native' => 'সোলার স্কুটি',
            'tagline' => 'Clean Ride, Sun-Powered',
            'concept' => 'Low-cost electric scooty powered by lithium-ion battery and charged via solar system installed at owner’s home. Designed for students, commuters, and rural users.',
            'how' => 'Home solar charging unit + swappable lithium-ion battery. Extra battery option for long tours. Max speed 40–50 km/h, range ~100 km per charge. Battery-swap <1 minute.',
            'why' => 'Zero fuel cost, zero emission, 80% lower running cost vs petrol scooty. Home charging solves infrastructure gap. Extra battery enables 200km tours. High demand in campus towns.',
            'departments' => 'EEE & Mechanical (Lead – power system, battery management, chassis); CSE – BMS app; BBA – EMI/ownership model; Others – safety & documentation.',
            'sdg' => 'SDG 7 (Clean Energy), SDG 11 (Sustainable Transport), SDG 13 (Climate Action)',
        ],
        [
            'no' => '5.2.',
            'name' => 'Solar Powered Boat',
            'native' => 'সোলার বোট',
            'tagline' => 'Silent Water Transport, Powered by Sun',
            'concept' => 'Fully solar-electric boat for fishing and recreational use in rivers, haor, and coastal chars. No gasoline engine – 100% solar dependent.',
            'how' => 'Rooftop solar panels charge onboard lithium-ion battery pack. Dual-battery system (main + emergency reserve). Runs 50 km on full charge even at night or emergency. Solar charges while moored or during daytime fishing. Pure electric motor – no fuel, no oil.',
            'why' => '100% solar-electric, no gasoline engine. Zero fuel cost, zero emission, zero oil spillage in water. Silent operation does not scare fish, improving catch. Ideal for Barishal region. Safety ensured by reserve battery for night return. Recreational version for eco-tourism resorts and campus lakes.',
            'departments' => 'EEE & Mechanical (Lead – marine electric propulsion, solar integration); Civil – hull stability; CSE – GPS & energy monitor; BBA – cooperative/rental model; MPH – safety & livelihood.',
            'sdg' => 'SDG 7, SDG 14 (reduced water pollution), SDG 8 (Economic Growth)',
        ],
        [
            'no' => '5.3.',
            'name' => 'ALOKON',
            'native' => 'আলোকন',
            'subtitle' => 'Mobile Solar Power Station',
            'tagline' => 'Alo Chhoriye Onusthan – Spreading Light Through Events',
            'concept' => 'Mobile high-capacity solar power station for outdoor events, fairs, waz mahfils, concerts, and disaster relief – replacing diesel generators.',
            'how' => 'Foldable solar array + battery bank on trolley/van. Setup 30 mins. Powers sound, lights, LED screens, mobile charging. IoT dashboard for energy monitoring.',
            'why' => 'Silent, clean, no fuel. Low maintenance. Rental model generates revenue. Pays back in 18-24 months vs generator fuel.',
            'departments' => 'EEE & Mechanical – power & trolley; Civil – structure; CSE – IoT; BBA – rental model.',
            'sdg' => 'SDG 7, 12, 13',
        ],
        [
            'no' => '5.4.',
            'name' => 'SUCHI',
            'native' => 'শুচি',
            'subtitle' => 'IoT Smart Sanitary Pad Vending Machine',
            'tagline' => 'Purity, Dignity, Access',
            'concept' => 'Low-cost IoT vending machine ensuring 24/7 menstrual hygiene access in universities, schools, garment factories, public toilets.',
            'how' => 'QR/mobile/coin payment. Sensors track inventory, auto SMS for refill. App to find nearest machine. Solar + grid with backup.',
            'why' => 'Solves privacy gap. 40% cheaper than imports. Data analytics for demand. Revenue: sales + ads + NGO sponsorship.',
            'departments' => 'EEE & CSE – hardware & IoT; Mechanical – enclosure; BBA – distribution; MPH – health; Islamic Studies – dignity guidance.',
            'sdg' => 'SDG 3, 5, 6',
        ],
        [
            'no' => '5.5.',
            'name' => 'AL-BAYAN',
            'native' => 'আল-বয়ান',
            'subtitle' => 'Bangla-English-Arabic Islamic Knowledge Search',
            'tagline' => 'Clear Answers from Authentic Sources',
            'concept' => 'Think of it as Google for Islamic sources – but it understands meaning, not just keywords. Ask in Bangla, get relevant Quran, Hadith, Fatwa from Arabic books instantly.',
            'how' => 'We digitize Quran, Tafsir, 6 Hadith books, Fiqh & Fatwa archives. AI understands Bangla/English/Arabic intent. Example: Type ‘loan interest rules’ in Bangla → shows Quran verses + authentic Hadith + scholar consensus with authenticity tag. Features: Voice search in Bangla/English/Arabic, side-by-side Tafsir, source verification. Web + App + API for madrasas.',
            'why' => 'First cross-lingual semantic search for Bangla speakers. Reduces misinformation, helps imams & students. Licensing potential worldwide. Preserves heritage digitally.',
            'departments' => 'CSE (Lead – AI/NLP), Islamic Studies – authenticity, English & GED – translation, BBA – sustainability.',
            'sdg' => 'SDG 4 (Quality Education), SDG 16 (Peace & Strong Institutions)',
        ],
        [
            'no' => '5.6.',
            'name' => 'JIBON-BINDU',
            'native' => 'জীবন-বিন্দু',
            'subtitle' => 'Smartphone-Based Diagnostic Device',
            'tagline' => 'A Drop Tells Your Health',
            'concept' => 'Low-cost fingertip device + app to measure SpO2, pulse, and estimate hemoglobin for health workers and rural clinics.',
            'how' => 'Clip-on optical sensor + Bluetooth. SpO2 via PPG light method. Hemoglobin via fingertip image + AI. App: color alerts, WHO anemia classification, history graph, PDF report, offline mode.',
            'why' => 'Anemia affects >40% women in Bangladesh. Uses existing phones. 70% cheaper than imports. Ideal for health camps, schools.',
            'departments' => 'EEE & CSE – sensor & AI; MPH – clinical validation; Mechanical – casing; BBA – distribution.',
            'sdg' => 'SDG 3, 10',
        ],
    ],

    // 6. Innovation Process Framework — [stage, what happens, output / TRL]
    'process' => [
        ['1. Ideation', 'Field visit, problem ID, interdisciplinary brainstorm', 'Concept Note – TRL 1'],
        ['2. Design', 'CAD, circuit, software, feasibility, budget', 'Design Doc – TRL 2'],
        ['3. Prototyping', 'Build in FabLab/workshop', 'Working Prototype – TRL 3-4'],
        ['4. Testing', 'Lab + field test, user feedback', 'Validated Prototype – TRL 5'],
        ['5. IP Protection', 'Prior art search, patent filing', 'Patent Filed – TRL 6'],
        ['6. Incubation', 'MVP, business model, startup registration', 'MVP + Startup – TRL 7'],
        ['7. Commercialization', 'Industry licensing, community deployment', 'Market Product – TRL 8-9'],
    ],

    // 7. Organization & Mentorship — [role, responsibility]
    'organization' => [
        ['Director, RICH', 'Leadership, funding, strategy'],
        ['Deputy Director (Innovation)', 'Portfolio management, prototype review, IP'],
        ['Dept. Coordinators (9)', 'Idea scouting, team formation, mentoring'],
        ['Student Team (3-5, multi-dept)', 'Prototype + business plan + documentation'],
        ['Industry Mentor', 'Market feedback, deployment'],
        ['IP, English, Islamic Studies, GED Mentors', 'Patent, communication, ethics, integration'],
    ],

    // 8. KPIs – Next 12 Months — [kpi, target]
    'kpis' => [
        ['Prototypes', '9 (3 existing + 6 new)'],
        ['Patent Filings', '3'],
        ['Startup Incubations', '2'],
        ['Industry MoUs', '3'],
        ['National Competitions', '2'],
        ['Community Deployment', '1 Floating Bridge + 3 SUCHI + 1 Solar Boat pilot'],
    ],

    // 9. Abbreviations & Short Forms — [abbreviation, full form]
    'abbreviations' => [
        ['RICH', 'Research, Innovation and Consultancy Hub'],
        ['UGV', 'University of Global Village'],
        ['EEE', 'Electrical and Electronic Engineering'],
        ['CSE', 'Computer Science and Engineering'],
        ['BBA', 'Bachelor of Business Administration'],
        ['MPH', 'Master of Public Health'],
        ['GED', 'General Education Department'],
        ['SDG', 'Sustainable Development Goal'],
        ['TRL', 'Technology Readiness Level (1 = idea, 9 = market-ready)'],
        ['IoT', 'Internet of Things'],
        ['BMS', 'Battery Management System'],
        ['GPS', 'Global Positioning System'],
        ['EMI', 'Equated Monthly Installment'],
        ['LGED', 'Local Government Engineering Department'],
        ['MVP', 'Minimum Viable Product (simplest usable version)'],
        ['IP', 'Intellectual Property'],
        ['AI', 'Artificial Intelligence'],
        ['NLP', 'Natural Language Processing'],
        ['SpO2', 'Blood Oxygen Saturation'],
        ['PPG', 'Photoplethysmography (light-based blood flow measurement)'],
        ['WHO', 'World Health Organization'],
        ['API', 'Application Programming Interface'],
        ['QR', 'Quick Response (code)'],
        ['SMS', 'Short Message Service'],
        ['LED', 'Light Emitting Diode'],
        ['NGO', 'Non-Governmental Organization'],
        ['UGC', 'University Grants Commission'],
        ['ICT', 'Information and Communication Technology'],
        ['CSR', 'Corporate Social Responsibility'],
        ['MoU', 'Memorandum of Understanding'],
        ['KPI', 'Key Performance Indicator'],
    ],

    // 10. Funding & Sustainability
    'funding' => [
        'Sources: UGV Internal Innovation Fund, UGC/World Bank, ICT Division Innovation Fund, Industry Sponsorship, CSR.',
        'Revenue Sharing: Student Innovators + Contributing Departments + RICH Fund as per university policy.',
    ],

    // 11. Conclusion
    'conclusion' => 'RICH Innovation Wing will transform UGV from teaching-focused to innovation-driven university. With three proven prototypes and six new market-ready concepts led by fully solar Solar Scooty and 100% solar-electric Solar Boat (no gasoline engine), UGV will establish a strong footprint in frugal engineering, clean mobility, and social entrepreneurship.',
];
