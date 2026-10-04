<?php

namespace Database\Seeders;

use App\Models\CoreArea;
use App\Models\Expert;
use App\Models\Faq;
use App\Models\Partner;
use App\Models\Post;
use App\Models\Project;
use App\Models\Publication;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Support\MeetingSlots;
use App\Models\Setting;
use App\Models\Stat;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RichContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->coreAreas();
        $this->serviceCatalogue();
        $this->stats();
        $this->experts();
        $this->publications();
        $this->partners();
        $this->testimonials();
        $this->faqs();
        $this->posts();
        $this->settings();
    }

    /* ------------------------------------------------------------------ */

    protected function coreAreas(): void
    {
        $areas = [
            [
                'title' => 'Research',
                'icon' => 'beaker',
                'tagline' => 'Evidence that stands up to scrutiny',
                'description' => 'UGV RICH supports fundamental, applied, interdisciplinary, and community-based research across diverse academic and professional disciplines.',
                'items' => [
                    'Research project development',
                    'Research design and methodology',
                    'Data collection and analysis',
                    'Academic research support',
                    'Policy research',
                    'Impact assessment',
                    'Research publication support',
                    'Research collaboration',
                ],
            ],
            [
                'title' => 'Innovation',
                'icon' => 'sparkles',
                'tagline' => 'Ideas engineered into outcomes',
                'description' => 'UGV RICH promotes innovative ideas, technologies, and solutions that address real-world problems.',
                'items' => [
                    'Educational innovation',
                    'Digital innovation',
                    'Social innovation',
                    'Technological solutions',
                    'Entrepreneurship and startups',
                    'Innovation-driven research',
                ],
            ],
            [
                'title' => 'Consultancy',
                'icon' => 'briefcase',
                'tagline' => 'Expertise you can commission',
                'description' => 'UGV RICH provides expert consultancy services drawing on the multidisciplinary expertise of UGV faculty and professionals.',
                'items' => [
                    'Engineering consultancy',
                    'Educational consultancy',
                    'Business and management consultancy',
                    'ICT and digital solutions',
                    'Environmental consultancy',
                    'Social development consultancy',
                    'Research and evaluation consultancy',
                    'Training and capacity development',
                ],
            ],
            [
                'title' => 'Hub',
                'icon' => 'globe',
                'tagline' => 'Where sectors meet',
                'description' => 'The Hub serves as a platform for collaboration and knowledge exchange between the university and the sectors it serves.',
                'items' => [
                    'UGV',
                    'Government',
                    'Industry',
                    'NGOs',
                    'Development Organizations',
                    'Researchers',
                    'Communities',
                ],
            ],
        ];

        foreach ($areas as $i => $area) {
            CoreArea::updateOrCreate(
                ['slug' => Str::slug($area['title'])],
                $area + ['sort_order' => $i, 'is_active' => true],
            );
        }
    }

    /* ------------------------------------------------------------------ */

    protected function serviceCatalogue(): void
    {
        $catalogue = [
            [
                'name' => 'Engineering & Technical Consultancy',
                'department' => 'CE',
                'icon' => 'wrench',
                'tagline' => 'Design, analysis and site expertise',
                'description' => 'Technical review, design and supervision services delivered by UGV engineering faculty and practising professionals, from concept through construction.',
                'services' => [
                    ['Civil Engineering Consultancy', 'Feasibility studies, design review and technical supervision for infrastructure and building works.'],
                    ['Structural Design & Analysis', 'Structural modelling, load analysis, retrofit assessment and code-compliance verification.'],
                    ['Architectural & Planning Support', 'Spatial planning, architectural documentation and development-approval support.'],
                    ['Construction Management', 'Programme control, quality assurance, cost monitoring and contract administration.'],
                    ['Environmental Engineering', 'Water, waste and pollution-control engineering with environmental impact assessment.'],
                    ['Electrical & Electronic Engineering', 'Power systems, building services, instrumentation and energy-efficiency audits.'],
                    ['Mechanical Engineering', 'HVAC, plant, thermal systems and mechanical design verification.'],
                    ['Surveying and Site Investigation', 'Topographic survey, geotechnical investigation and soil testing.'],
                ],
            ],
            [
                'name' => 'ICT & Digital Consultancy',
                'department' => 'CSE',
                'icon' => 'cpu',
                'tagline' => 'Software, data and digital transformation',
                'description' => 'Digital advisory and delivery capability covering applications, data platforms, security posture and emerging technology adoption.',
                'services' => [
                    ['Software Development', 'Custom systems built to requirement, from specification through deployment and handover.'],
                    ['Web & Mobile Application Development', 'Responsive web platforms and native or cross-platform mobile applications.'],
                    ['Cybersecurity', 'Security assessment, policy design, incident readiness and staff awareness training.'],
                    ['Data Management', 'Data architecture, governance, migration, warehousing and analytics pipelines.'],
                    ['Digital Transformation', 'Process digitisation, systems integration and organisational change support.'],
                    ['AI and Emerging Technologies', 'Applied machine learning, automation and technology-adoption roadmaps.'],
                ],
            ],
            [
                'name' => 'Business & Management Consultancy',
                'department' => 'BUS',
                'icon' => 'chart',
                'tagline' => 'Strategy, finance and organisational capability',
                'description' => 'Commercial and organisational advisory grounded in business research, financial analysis and management practice.',
                'services' => [
                    ['Business Planning', 'Business models, feasibility analysis and investment-ready planning documents.'],
                    ['Market Research', 'Market sizing, customer research, competitor analysis and demand assessment.'],
                    ['Financial Analysis', 'Financial modelling, costing, viability appraisal and performance review.'],
                    ['HR Consultancy', 'Workforce planning, competency frameworks, appraisal systems and HR policy.'],
                    ['Entrepreneurship Development', 'Startup incubation support, venture readiness and enterprise mentoring.'],
                    ['Strategic Management', 'Strategy formulation, performance frameworks and implementation review.'],
                    ['Organizational Development', 'Structure design, culture assessment and capacity-building programmes.'],
                ],
            ],
            [
                'name' => 'Education & Research Consultancy',
                'department' => 'RICH',
                'icon' => 'academic',
                'tagline' => 'Curriculum, OBE and institutional quality',
                'description' => 'Academic quality and capacity services for universities, colleges, training providers and education programmes.',
                'services' => [
                    ['Curriculum Development', 'Programme design, learning-outcome mapping and curriculum revision.'],
                    ['Educational Assessment', 'Assessment design, rubric development and examination quality review.'],
                    ['OBE Consultancy', 'Outcome-Based Education implementation, mapping and accreditation readiness.'],
                    ['Teacher Training', 'Pedagogy workshops, professional development and teaching-quality mentoring.'],
                    ['Research Methodology', 'Methodology training, research design clinics and supervisor development.'],
                    ['Monitoring & Evaluation', 'M&E frameworks, indicator design, baseline studies and evaluation reporting.'],
                    ['Institutional Development', 'Governance review, quality assurance systems and strategic institutional planning.'],
                ],
            ],
            [
                'name' => 'Social Science & Humanities Consultancy',
                'department' => 'IS',
                'icon' => 'users',
                'tagline' => 'Society, policy and community evidence',
                'description' => 'Social research and policy advisory that brings rigorous qualitative and quantitative evidence to development practice.',
                'services' => [
                    ['Social Research', 'Household surveys, field studies and mixed-method social investigation.'],
                    ['Gender Studies', 'Gender analysis, inclusion assessment and gender-responsive programme design.'],
                    ['Community Development', 'Participatory needs assessment, community mobilisation and programme design.'],
                    ['Communication Studies', 'Media analysis, behaviour-change communication and campaign evaluation.'],
                    ['Cultural Research', 'Heritage documentation, cultural mapping and ethnographic study.'],
                    ['Policy Analysis', 'Policy review, options appraisal and evidence briefs for decision-makers.'],
                    ['Qualitative and Quantitative Research', 'Interviews, focus groups, statistical modelling and integrated analysis.'],
                ],
            ],
        ];

        foreach ($catalogue as $i => $group) {
            $category = ServiceCategory::updateOrCreate(
                ['slug' => Str::slug($group['name'])],
                [
                    'name' => $group['name'],
                    // The sector that runs it; shown on every service beneath.
                    'department' => $group['department'] ?? null,
                    'icon' => $group['icon'],
                    'tagline' => $group['tagline'],
                    'description' => $group['description'],
                    'sort_order' => $i,
                    'is_active' => true,
                ],
            );

            foreach ($group['services'] as $j => [$name, $description]) {
                Service::updateOrCreate(
                    ['service_category_id' => $category->id, 'slug' => Str::slug($name)],
                    ['name' => $name, 'description' => $description, 'sort_order' => $j, 'is_active' => true],
                );
            }
        }
    }

    /* ------------------------------------------------------------------ */

    protected function stats(): void
    {
        $stats = [
            ['label' => 'Consultancy & research projects', 'value' => '120', 'suffix' => '+', 'icon' => 'briefcase'],
            ['label' => 'Faculty experts on call', 'value' => '80', 'suffix' => '+', 'icon' => 'users'],
            ['label' => 'Partner organisations', 'value' => '45', 'suffix' => '+', 'icon' => 'globe'],
            ['label' => 'Client satisfaction', 'value' => '98', 'suffix' => '%', 'icon' => 'star'],
        ];

        foreach ($stats as $i => $stat) {
            // Supporting figures: used across the site, but not in the home page's six.
            Stat::updateOrCreate(['label' => $stat['label']], $stat + ['sort_order' => $i, 'is_active' => true, 'show_on_home' => false]);
        }
    }

    /* ------------------------------------------------------------------ */

    protected function experts(): void
    {
        $catIds = ServiceCategory::pluck('id', 'slug');

        $experts = [
            [
                'name' => 'Dr. Mahmudul Hasan',
                'designation' => 'Professor',
                'department' => 'Department of Civil Engineering',
                'expertise' => ['Structural Engineering', 'Construction Management', 'Earthquake Engineering'],
                'research_interests' => 'Seismic performance of RC structures, low-cost housing systems, and resilient infrastructure for coastal regions.',
                'email' => 'mahmudul.hasan@ugv.edu.bd',
                'category' => 'engineering-technical-consultancy',
                'is_featured' => true,
            ],
            [
                'name' => 'Dr. Farhana Akter',
                'designation' => 'Associate Professor',
                'department' => 'Department of Computer Science & Engineering',
                'expertise' => ['Artificial Intelligence', 'Data Science', 'Cybersecurity'],
                'research_interests' => 'Applied machine learning for public services, secure data governance, and digital transformation in the public sector.',
                'email' => 'farhana.akter@ugv.edu.bd',
                'category' => 'ict-digital-consultancy',
                'is_featured' => true,
            ],
            [
                'name' => 'Dr. Rezaul Karim',
                'designation' => 'Professor',
                'department' => 'Department of Business Administration',
                'expertise' => ['Strategic Management', 'Entrepreneurship', 'Financial Analysis'],
                'research_interests' => 'SME growth strategy, entrepreneurial ecosystems, financial inclusion and micro-enterprise finance.',
                'email' => 'rezaul.karim@ugv.edu.bd',
                'category' => 'business-management-consultancy',
                'is_featured' => true,
            ],
            [
                'name' => 'Dr. Nusrat Jahan',
                'designation' => 'Professor',
                'department' => 'Department of Education',
                'expertise' => ['Curriculum Development', 'Outcome-Based Education', 'Educational Assessment'],
                'research_interests' => 'OBE implementation in higher education, assessment validity, and teacher professional development.',
                'email' => 'nusrat.jahan@ugv.edu.bd',
                'category' => 'education-research-consultancy',
                'is_featured' => true,
            ],
            [
                'name' => 'Dr. Anwar Hossain',
                'designation' => 'Associate Professor',
                'department' => 'Department of Sociology',
                'expertise' => ['Social Research', 'Community Development', 'Policy Analysis'],
                'research_interests' => 'Climate-induced migration, community resilience, and participatory development evaluation.',
                'email' => 'anwar.hossain@ugv.edu.bd',
                'category' => 'social-science-humanities-consultancy',
                'is_featured' => true,
            ],
            [
                'name' => 'Dr. Sabrina Rahman',
                'designation' => 'Assistant Professor',
                'department' => 'Department of Environmental Science',
                'expertise' => ['Environmental Engineering', 'Impact Assessment', 'Water Resources'],
                'research_interests' => 'Salinity intrusion, water quality monitoring, and environmental impact assessment methodology.',
                'email' => 'sabrina.rahman@ugv.edu.bd',
                'category' => 'engineering-technical-consultancy',
                'is_featured' => true,
            ],
            [
                'name' => 'Dr. Tanvir Ahmed',
                'designation' => 'Associate Professor',
                'department' => 'Department of Electrical & Electronic Engineering',
                'expertise' => ['Power Systems', 'Renewable Energy', 'Energy Audit'],
                'research_interests' => 'Solar mini-grids, energy efficiency in institutional buildings, and smart metering.',
                'email' => 'tanvir.ahmed@ugv.edu.bd',
                'category' => 'engineering-technical-consultancy',
            ],
            [
                'name' => 'Dr. Shirin Sultana',
                'designation' => 'Assistant Professor',
                'department' => 'Department of Economics',
                'expertise' => ['Market Research', 'Impact Evaluation', 'Development Economics'],
                'research_interests' => 'Evaluation of livelihood programmes, labour markets, and poverty measurement.',
                'email' => 'shirin.sultana@ugv.edu.bd',
                'category' => 'business-management-consultancy',
            ],
            [
                'name' => 'Dr. Imran Chowdhury',
                'designation' => 'Professor',
                'department' => 'Department of Public Health',
                'expertise' => ['Health Systems Research', 'Epidemiology', 'Programme Evaluation'],
                'research_interests' => 'Primary health service delivery, maternal health outcomes, and health policy evaluation.',
                'email' => 'imran.chowdhury@ugv.edu.bd',
                'category' => 'social-science-humanities-consultancy',
            ],
            [
                'name' => 'Dr. Lamia Haque',
                'designation' => 'Assistant Professor',
                'department' => 'Department of Architecture',
                'expertise' => ['Urban Planning', 'Sustainable Design', 'Heritage Conservation'],
                'research_interests' => 'Climate-responsive architecture, informal settlement upgrading, and heritage documentation.',
                'email' => 'lamia.haque@ugv.edu.bd',
                'category' => 'engineering-technical-consultancy',
            ],
        ];

        foreach ($experts as $i => $expert) {
            $category = $expert['category'];
            unset($expert['category']);

            Expert::updateOrCreate(
                ['slug' => Str::slug($expert['name'])],
                $expert + [
                    'service_category_id' => $catIds[$category] ?? null,
                    'sort_order' => $i,
                    'is_active' => true,
                ],
            );
        }
    }

    /* ------------------------------------------------------------------ */

    /* ------------------------------------------------------------------ */

    protected function publications(): void
    {
        $items = [
            ['title' => 'Seismic Vulnerability of Low-Rise RC School Buildings in Coastal Regions', 'authors' => 'M. Hasan, S. Rahman', 'venue' => 'Journal of Structural Engineering Research', 'year' => 2025, 'kind' => 'journal'],
            ['title' => 'Applied Machine Learning for Public Service Prioritisation', 'authors' => 'F. Akter, T. Ahmed', 'venue' => 'International Conference on Digital Government', 'year' => 2025, 'kind' => 'conference'],
            ['title' => 'Entrepreneurial Ecosystems and SME Growth in Secondary Cities', 'authors' => 'R. Karim, S. Sultana', 'venue' => 'South Asian Journal of Business Studies', 'year' => 2024, 'kind' => 'journal'],
            ['title' => 'Assessment Validity under Outcome-Based Education: A Multi-Programme Study', 'authors' => 'N. Jahan', 'venue' => 'Higher Education Quality Review', 'year' => 2024, 'kind' => 'journal'],
            ['title' => 'Community Resilience Fund — Multi-District Research Grant', 'authors' => 'A. Hossain (Principal Investigator)', 'venue' => 'National Research Council', 'year' => 2025, 'kind' => 'funded-project'],
            ['title' => 'Coastal Water Quality Monitoring Network', 'authors' => 'S. Rahman (Principal Investigator)', 'venue' => 'Ministry of Environment', 'year' => 2024, 'kind' => 'funded-project'],
        ];

        foreach ($items as $i => $item) {
            Publication::updateOrCreate(['title' => $item['title']], $item + ['sort_order' => $i, 'is_active' => true]);
        }
    }

    /* ------------------------------------------------------------------ */

    protected function partners(): void
    {
        $partners = [
            ['name' => 'Local Government Engineering Department', 'type' => 'Government agency'],
            ['name' => 'District Administration', 'type' => 'Government agency'],
            ['name' => 'National NGO Consortium', 'type' => 'NGO'],
            ['name' => 'Industrial Association', 'type' => 'Industry'],
            ['name' => 'Ministry of Environment', 'type' => 'Government agency'],
            ['name' => 'International Development Partner', 'type' => 'Development partner'],
            ['name' => 'Partner University', 'type' => 'University'],
            ['name' => 'National Research Council', 'type' => 'Research institution'],
        ];

        foreach ($partners as $i => $partner) {
            Partner::updateOrCreate(['name' => $partner['name']], $partner + ['sort_order' => $i, 'is_active' => true]);
        }
    }

    /* ------------------------------------------------------------------ */

    protected function testimonials(): void
    {
        $items = [
            [
                'name' => 'Md. Kamrul Islam',
                'designation' => 'Executive Engineer',
                'organization' => 'Local Government Engineering Department',
                'quote' => 'The structural assessment was thorough, well documented and delivered on schedule. The prioritised retrofit list gave us something we could act on immediately, rather than another report for the shelf.',
            ],
            [
                'name' => 'Ayesha Siddiqua',
                'designation' => 'Programme Director',
                'organization' => 'National NGO Consortium',
                'quote' => 'UGV RICH brought genuine methodological rigour to our evaluation. The team was transparent about limitations, which is rarer than it should be, and the findings held up under donor review.',
            ],
            [
                'name' => 'Shafiqur Rahman',
                'designation' => 'Additional Deputy Commissioner',
                'organization' => 'District Administration',
                'quote' => 'What set them apart was the service mapping done before a single line of code was written. They understood our processes first, then built for them.',
            ],
            [
                'name' => 'Dr. Nasreen Akhter',
                'designation' => 'Vice Chancellor',
                'organization' => 'Partner University',
                'quote' => 'The OBE rollout support was practical and faculty-friendly. Our accreditation self-assessment passed on first submission, which frankly we had not expected.',
            ],
            [
                'name' => 'Golam Mostafa',
                'designation' => 'General Secretary',
                'organization' => 'Industrial Association',
                'quote' => 'The energy audit paid for itself within the first quarter. Clear numbers, clear recommendations, no padding.',
            ],
        ];

        foreach ($items as $i => $item) {
            Testimonial::updateOrCreate(
                ['name' => $item['name']],
                $item + ['rating' => 5, 'sort_order' => $i, 'is_active' => true],
            );
        }
    }

    /* ------------------------------------------------------------------ */

    protected function faqs(): void
    {
        $faqs = [
            ['question' => 'Who can commission work from UGV RICH?', 'answer' => 'Government organisations, private companies, NGOs, development organisations, educational institutions, industries, corporate organisations, local communities, international organisations, and researchers and academic institutions can all engage UGV RICH.'],
            ['question' => 'How do I start a consultancy engagement?', 'answer' => 'Submit a consultancy request through the form on this site with a brief description of your requirement. Our coordination team reviews every request and responds with a proposed approach, an indicative team and a timeline.'],
            ['question' => 'How are consultancy teams assembled?', 'answer' => 'Each assignment is matched to faculty members and external professionals whose expertise fits the scope. Multidisciplinary assignments draw specialists from more than one department under a single lead consultant.'],
            ['question' => 'Is my information kept confidential?', 'answer' => 'Yes. All engagements are governed by professional standards covering quality, ethics, confidentiality and data protection. Non-disclosure arrangements can be formalised before work begins.'],
            ['question' => 'Can students be involved in projects?', 'answer' => 'Yes. Student research projects and supervised involvement in live assignments are part of the RICH mandate to develop research capacity, subject to client agreement and confidentiality requirements.'],
            ['question' => 'Do you support research publication and grant applications?', 'answer' => 'Yes. UGV RICH supports research project development, publication support, grant applications, and national and international collaborative research partnerships.'],
        ];

        foreach ($faqs as $i => $faq) {
            Faq::updateOrCreate(['question' => $faq['question']], $faq + ['sort_order' => $i, 'is_active' => true]);
        }
    }

    /* ------------------------------------------------------------------ */

    protected function posts(): void
    {
        $posts = [
            [
                'title' => 'UGV RICH Organizes Research Methodology Workshop',
                'type' => 'news',
                'category' => 'Capacity Building',
                'excerpt' => 'A three-day workshop on research design, sampling and mixed-method analysis drew faculty members and postgraduate researchers from across the university.',
                'body' => "UGV RICH hosted a three-day Research Methodology Workshop for faculty members and postgraduate researchers, covering research design, sampling strategy, instrument development, and mixed-method analysis.\n\nSessions were led by senior researchers from across UGV departments, with practical clinics where participants brought their own research questions for review. The workshop forms part of the RICH mandate to develop research capacity among faculty members and students.\n\nA follow-up clinic series is planned, focusing on publication strategy and grant application writing.",
                'author' => 'UGV RICH Communications',
                'published_at' => '-8 days',
                'is_featured' => true,
            ],
            [
                'title' => 'UGV RICH Signs Consultancy Agreement with District Administration',
                'type' => 'news',
                'category' => 'Partnership',
                'excerpt' => 'A new agreement formalises collaboration on digital service delivery, capacity building and evidence-based administrative planning.',
                'body' => "UGV RICH has signed a consultancy agreement with the District Administration to support digital service delivery, staff capacity building and evidence-based administrative planning.\n\nThe agreement covers a programme of work spanning service mapping, platform development and ongoing monitoring, drawing on UGV ICT and social research faculty.\n\nThe collaboration reflects the Hub role in connecting academic expertise with government practice.",
                'author' => 'UGV RICH Communications',
                'published_at' => '-21 days',
                'is_featured' => true,
            ],
            [
                'title' => 'Innovation Challenge Opens for Student Teams',
                'type' => 'news',
                'category' => 'Innovation',
                'excerpt' => 'Student teams are invited to submit technology and social innovation proposals addressing local development challenges.',
                'body' => "The UGV RICH Innovation Challenge is now open for submissions. Student teams from all departments are invited to propose technological or social innovations addressing local development challenges.\n\nShortlisted teams receive mentoring from RICH faculty, seed support, and the opportunity to develop their proposal into a funded pilot.\n\nSubmissions are reviewed on originality, feasibility and potential community impact.",
                'author' => 'UGV RICH Communications',
                'published_at' => '-35 days',
            ],
            [
                'title' => 'Seminar: Evidence for Policy — Bridging Research and Decision-Making',
                'type' => 'event',
                'category' => 'Seminar',
                'excerpt' => 'A half-day seminar bringing together researchers, policy officers and development practitioners.',
                'body' => "UGV RICH will host a half-day seminar on translating research evidence into policy decisions.\n\nThe programme includes panel discussions with policy officers, presentations of recent RICH policy research, and a workshop on writing effective evidence briefs.\n\nRegistration is open to researchers, government officers, NGO staff and development practitioners.",
                'author' => 'UGV RICH Events',
                'event_at' => '+18 days',
                'location' => 'UGV Campus Auditorium',
                'published_at' => '-3 days',
                'is_featured' => true,
            ],
            [
                'title' => 'Training: Monitoring & Evaluation Frameworks for Development Programmes',
                'type' => 'event',
                'category' => 'Training',
                'excerpt' => 'A practical two-day training on indicator design, baseline studies and evaluation reporting.',
                'body' => "This two-day training covers the design of monitoring and evaluation frameworks for development programmes.\n\nParticipants work through indicator design, baseline and endline study planning, data quality assurance, and evaluation reporting, using real programme documents as case material.\n\nThe training is aimed at NGO programme staff, government project officers and development partner teams.",
                'author' => 'UGV RICH Events',
                'event_at' => '+40 days',
                'location' => 'UGV RICH Training Room',
                'published_at' => '-6 days',
            ],
            [
                'title' => 'RICH Researchers Present at International Conference on Digital Government',
                'type' => 'news',
                'category' => 'Research',
                'excerpt' => 'Faculty presented applied machine learning research on public service prioritisation.',
                'body' => "UGV RICH faculty presented research on applied machine learning for public service prioritisation at the International Conference on Digital Government.\n\nThe paper drew on work carried out under the district digital service delivery programme, and examined how administrative case data can be used to forecast service demand.\n\nThe presentation opened discussions with two international research groups on future collaboration.",
                'author' => 'UGV RICH Communications',
                'published_at' => '-52 days',
            ],
        ];

        foreach ($posts as $post) {
            $post['published_at'] = now()->modify($post['published_at']);

            if (isset($post['event_at'])) {
                $post['event_at'] = now()->modify($post['event_at']);
            }

            Post::updateOrCreate(['slug' => Str::slug($post['title'])], $post);
        }
    }

    /* ------------------------------------------------------------------ */

    protected function settings(): void
    {
        $settings = [
            'site_name' => 'UGV RICH',
            'site_tagline' => 'Research, Innovation & Consultancy Hub',
            'site_motto' => 'Connecting Knowledge, Innovation and Expertise for Real-World Impact.',

            'hero_eyebrow' => 'University of Global Village',
            'hero_highlight' => 'innovation',
            'hero_heading' => 'Connecting knowledge, innovation and expertise for real-world impact',
            'hero_subheading' => 'UGV RICH is the institutional platform of the University of Global Village for research, innovation, professional consultancy and knowledge exchange — bringing academic expertise to the problems organisations actually face.',

            'about_intro' => 'UGV RICH (Research, Innovation, Consultancy & Hub) is an institutional platform of the University of Global Village dedicated to promoting research, innovation, professional consultancy, knowledge exchange, and community-oriented solutions.',
            'about_body' => 'UGV RICH brings together the academic expertise, professional experience, and research capabilities of UGV faculty members, researchers, students, and external professionals to address contemporary challenges and contribute to sustainable development. Through interdisciplinary collaboration and evidence-based approaches, UGV RICH aims to establish UGV as a centre for research excellence, innovation, consultancy, and knowledge-based services.',
            'vision' => 'To become a leading university-based platform for research, innovation, consultancy, and knowledge exchange that contributes to sustainable social and economic development.',
            'mission_intro' => 'UGV RICH is committed to:',

            'mission_points' => json_encode([
                'Promoting high-quality and interdisciplinary research.',
                'Encouraging innovation and creative problem-solving.',
                'Providing professional consultancy services.',
                'Connecting academia with industry, government, NGOs, and communities.',
                'Developing research capacity among faculty members and students.',
                'Facilitating national and international research collaboration.',
                'Supporting evidence-based policy and decision-making.',
                'Contributing to the Sustainable Development Goals (SDGs).',
            ]),

            'why_choose' => json_encode([
                ['title' => 'Multidisciplinary Expertise', 'description' => 'Access to experts from different academic disciplines within UGV.', 'icon' => 'users'],
                ['title' => 'Academic Excellence', 'description' => 'Consultancy and research services grounded in academic knowledge and evidence.', 'icon' => 'academic'],
                ['title' => 'Practical Solutions', 'description' => 'Focus on solutions that are applicable to real-world challenges.', 'icon' => 'wrench'],
                ['title' => 'Professional Approach', 'description' => 'Commitment to quality, ethics, confidentiality, and professional standards.', 'icon' => 'shield'],
                ['title' => 'Local Knowledge, Global Perspective', 'description' => 'Combining knowledge of local contexts with international research and professional practices.', 'icon' => 'globe'],
                ['title' => 'Industry–Academia Collaboration', 'description' => 'Creating meaningful connections between universities and professional sectors.', 'icon' => 'link'],
            ]),

            'who_we_serve' => json_encode([
                'Government organizations',
                'Private organizations',
                'NGOs',
                'Development organizations',
                'Educational institutions',
                'Industries',
                'Corporate organizations',
                'Local communities',
                'International organizations',
                'Researchers and academic institutions',
            ]),

            'research_activities' => json_encode([
                'Faculty research projects',
                'Student research projects',
                'Interdisciplinary research',
                'Collaborative research',
                'National and international partnerships',
                'Research grants',
                'Conferences and seminars',
                'Research publications',
                'Innovation challenges',
                'Community-based research',
            ]),

            'partnership_types' => json_encode([
                'Universities',
                'Research institutions',
                'Government agencies',
                'Industries',
                'NGOs',
                'Development partners',
                'International organizations',
            ]),

            'process_steps' => json_encode([
                ['title' => 'Enquiry & Scoping', 'description' => 'You submit a consultancy request. We clarify the problem, the decision it supports, and the evidence required.'],
                ['title' => 'Team & Proposal', 'description' => 'We assemble the right faculty and professional expertise, and return a proposed approach, team, timeline and cost.'],
                ['title' => 'Delivery & Handover', 'description' => 'Work is delivered against agreed milestones, with findings presented in a form your organisation can act on.'],
            ]),

            'contact_address' => 'University of Global Village, Barishal, Bangladesh',
            'contact_email' => 'rich@ugv.edu.bd',
            'contact_phone' => '+880 000 000000',
            'contact_website' => 'https://ugv.edu.bd',
            'contact_whatsapp' => '',
            'contact_hours' => 'Sunday – Thursday, 9:00 AM – 5:00 PM',

            // When the office takes consultancy meetings; see App\Support\MeetingSlots.
            'booking_opens' => MeetingSlots::DEFAULT_OPENS,
            'booking_closes' => MeetingSlots::DEFAULT_CLOSES,
            'booking_slot_minutes' => (string) MeetingSlots::DEFAULT_MINUTES,
            'booking_break_starts' => MeetingSlots::DEFAULT_BREAK_STARTS,
            'booking_break_ends' => MeetingSlots::DEFAULT_BREAK_ENDS,
            'booking_closed_days' => json_encode(MeetingSlots::DEFAULT_CLOSED_DAYS),

            'social_facebook' => 'https://facebook.com/',
            'social_linkedin' => 'https://linkedin.com/',
            'social_x' => 'https://x.com/',
            'social_youtube' => 'https://youtube.com/',
        ];

        foreach ($settings as $key => $value) {
            $group = match (true) {
                str_starts_with($key, 'contact_') => 'contact',
                str_starts_with($key, 'social_') => 'social',
                str_starts_with($key, 'hero_') => 'hero',
                str_starts_with($key, 'booking_') => 'booking',
                default => 'general',
            };

            Setting::updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'group' => $group,
                    'type' => str_starts_with((string) $value, '[') ? 'json' : 'text',
                ],
            );
        }
    }
}
