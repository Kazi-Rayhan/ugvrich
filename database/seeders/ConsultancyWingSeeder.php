<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * The consultancy catalogue, from the Consultancy Wing's own planning document
 * (UGV-RICH Consultancy Wing — Consolidated Service Summary, 27 September 2026).
 *
 * Six wings, each run by one department, each holding the services that wing
 * actually offers. This replaces the placeholder catalogue the site shipped
 * with: the old categories are deactivated rather than deleted, so nothing is
 * lost and they can be removed in the admin once this is checked.
 *
 * What is deliberately NOT here: internal expert teams, external support
 * recommendations, MoU partners and pricing. That document is marked Internal
 * Planning, and naming prospective partners, revenue shares and rates on a
 * public website would publish a negotiating position. Those columns can be
 * added as admin-only notes if the wing wants them recorded — say so and they
 * will be, but not on the public page by default.
 */
class ConsultancyWingSeeder extends Seeder
{
    public function run(): void
    {
        $this->deactivateThePlaceholderCatalogue();

        foreach ($this->wings() as $i => $wing) {
            $category = ServiceCategory::updateOrCreate(
                ['slug' => Str::slug($wing['name'])],
                [
                    'name' => $wing['name'],
                    'department' => $wing['department'],
                    'icon' => $wing['icon'],
                    'tagline' => $wing['tagline'],
                    'description' => $wing['description'],
                    'sort_order' => $i,
                    'is_active' => true,
                ],
            );

            foreach ($wing['services'] as $j => $service) {
                Service::updateOrCreate(
                    ['service_category_id' => $category->id, 'slug' => Str::slug($service['name'])],
                    [
                        'name' => $service['name'],
                        'department' => $wing['department'],
                        'description' => $service['summary'],
                        'body' => $service['body'],
                        'highlights' => $service['scope'],
                        'icon' => $service['icon'],
                        'sort_order' => $j,
                        'is_active' => true,
                    ],
                );
            }
        }
    }

    /** The demo catalogue steps aside; it is kept in case anything referenced it. */
    protected function deactivateThePlaceholderCatalogue(): void
    {
        $keep = collect($this->wings())->map(fn (array $wing) => Str::slug($wing['name']));

        ServiceCategory::whereNotIn('slug', $keep)->update(['is_active' => false]);

        Service::whereHas('category', fn ($q) => $q->whereNotIn('slug', $keep))->update(['is_active' => false]);
    }

    /** @return array<int, array<string, mixed>> */
    protected function wings(): array
    {
        return [
            [
                'name' => 'Smart ICT Services',
                'department' => 'CSE',
                'icon' => 'cpu',
                'tagline' => 'Software, platforms and digital infrastructure',
                'description' => 'The CSE Wing delivers its consultancy as one cluster service: nine defined lines covering websites and institutional portals, mobile and commerce platforms, AI, security, cloud, networks and surveillance.',
                'services' => [
                    [
                        'name' => 'Institutional Website Development',
                        'icon' => 'globe',
                        'summary' => 'Modern, responsive institutional websites for universities, colleges, schools, hospitals, NGOs and businesses.',
                        'body' => "Design and development of modern, responsive institutional websites for universities, colleges, schools, hospitals, NGOs and businesses, including content architecture, CMS, domain and hosting setup, and post-launch maintenance.",
                        'scope' => [
                            'Requirement analysis and information architecture',
                            'UI/UX and responsive front end',
                            'CMS and admin panel',
                            'Faculty, department, news and event modules',
                            'SEO, accessibility, SSL and analytics',
                            'Domain, hosting, backup and maintenance',
                        ],
                    ],
                    [
                        'name' => 'Portal Development',
                        'icon' => 'grid',
                        'summary' => 'Integrated institutional portals — student information, ERP, HRM, accounts, admissions and examinations.',
                        'body' => "Development of integrated institutional portals such as student information systems, ERP, HRM and payroll, accounts, admissions, examination, attendance and workflow portals, with role-based access and reporting.",
                        'scope' => [
                            'Business process mapping',
                            'ERP, SIS, HRM and accounts modules',
                            'Database architecture and API integration',
                            'SSO and role-based access',
                            'Payment and notification integration',
                            'UAT, migration, deployment and support',
                        ],
                    ],
                    [
                        'name' => 'Mobile App Development',
                        'icon' => 'cpu',
                        'summary' => 'Android and iOS applications for education, attendance, notifications, events and field operations.',
                        'body' => "Android and iOS applications for education, attendance, notifications, events, service delivery, field operations and client-specific workflows, including app-store publishing and update support.",
                        'scope' => [
                            'UI/UX design',
                            'Android (Kotlin/Java) and iOS (Swift or cross-platform)',
                            'API and backend integration',
                            'Push notifications',
                            'Authentication and analytics',
                            'Testing, publishing and maintenance',
                        ],
                    ],
                    [
                        'name' => 'E-Commerce Website & Platform',
                        'icon' => 'briefcase',
                        'summary' => 'End-to-end commerce platforms with catalogue, inventory, checkout, logistics and secure digital payments.',
                        'body' => "End-to-end e-commerce platforms for product catalogues, inventory, cart and checkout, order management, customer accounts, logistics integration and secure digital payments.",
                        'scope' => [
                            'Storefront and admin portal',
                            'Product, inventory and order modules',
                            'Payment gateway integration',
                            'Coupons, discounts and reporting',
                            'Delivery and logistics integration',
                            'Security, backup and maintenance',
                        ],
                    ],
                    [
                        'name' => 'AI & Automation',
                        'icon' => 'sparkles',
                        'summary' => 'Chatbots, document processing, workflow automation and knowledge assistants for institutions and business.',
                        'body' => "AI-enabled business and institutional solutions including chatbots, document processing, workflow automation, knowledge assistants and selected analytics and decision-support features.",
                        'scope' => [
                            'AI chatbot and assistant',
                            'OCR and NLP document processing',
                            'Workflow and RPA-style automation',
                            'RAG and knowledge-base integration',
                            'Model and API integration',
                            'Monitoring, prompt and model evaluation',
                        ],
                    ],
                    [
                        'name' => 'Cybersecurity Services',
                        'icon' => 'shield',
                        'summary' => 'Authorised assessment and improvement of websites, applications and institutional networks.',
                        'body' => "Authorised cybersecurity assessment and improvement services for websites, applications and institutional networks, with a clear written scope and client permission agreed before any testing begins.",
                        'scope' => [
                            'Vulnerability assessment',
                            'Authorised penetration testing',
                            'Web, application and network security review',
                            'Risk assessment',
                            'Backup and security policy design',
                            'Incident-response planning and awareness training',
                        ],
                    ],
                    [
                        'name' => 'Cloud Services',
                        'icon' => 'globe',
                        'summary' => 'Provisioning, migration, hosting, backup administration and monitoring for the move off on-premise servers.',
                        'body' => "Cloud provisioning, migration, hosting, database and backup administration, and monitoring for institutions and businesses moving from on-premise infrastructure to managed cloud environments.",
                        'scope' => [
                            'Cloud server setup',
                            'Database hosting',
                            'Automated backup and disaster recovery',
                            'Email, domain and DNS administration',
                            'Migration',
                            'Monitoring and cost optimisation',
                        ],
                    ],
                    [
                        'name' => 'Networking & ISP Solutions',
                        'icon' => 'cpu',
                        'summary' => 'Campus and office networks, structured cabling, Wi-Fi, server rooms and ISP coordination.',
                        'body' => "Design and deployment of campus and office networks, structured cabling, Wi-Fi, server-room infrastructure and ISP coordination. UGV RICH does not itself resell internet bandwidth.",
                        'scope' => [
                            'LAN and WAN design',
                            'Structured cabling and fibre planning',
                            'Wi-Fi survey and deployment',
                            'Router, switch and firewall setup',
                            'Server-room and rack planning',
                            'Monitoring, maintenance and ISP coordination',
                        ],
                    ],
                    [
                        'name' => 'CCTV & Surveillance',
                        'icon' => 'shield',
                        'summary' => 'IP CCTV and surveillance planning for institutional, commercial and residential facilities.',
                        'body' => "IP CCTV and surveillance planning for institutional, commercial and residential facilities, integrated with network, storage, access-control and remote-monitoring requirements.",
                        'scope' => [
                            'Site survey and camera placement',
                            'IP camera, NVR and VMS design',
                            'Storage and retention sizing',
                            'PoE and network design',
                            'Remote monitoring',
                            'Access-control and UPS integration, and maintenance',
                        ],
                    ],
                ],
            ],

            [
                'name' => 'Smart Electrical Systems & Automation Services',
                'department' => 'EEE',
                'icon' => 'bolt',
                'tagline' => 'Energy, safety, automation and smart systems',
                'description' => 'Energy audits and load optimisation, electrical safety and code compliance, solar and renewables, industrial automation, smart-building systems and equipment maintenance.',
                'services' => [
                    [
                        'name' => 'Load Optimization & Energy Audits',
                        'icon' => 'chart',
                        'summary' => 'Consumption profiling, load optimisation and energy-saving retrofits with payback analysis.',
                        'body' => "Energy consumption profiling, load optimisation, power-factor assessment and correction, cable rating selection, substation and single-line-diagram review, and energy-saving retrofit recommendations with ROI and payback analysis.",
                        'scope' => [
                            'Energy consumption profiling',
                            'Load optimisation and power-factor correction',
                            'Cable rating selection',
                            'Substation and single line diagram review',
                            'Retrofit recommendations with ROI and payback analysis',
                        ],
                    ],
                    [
                        'name' => 'Electrical Safety, Grounding & BNBC Compliance',
                        'icon' => 'shield',
                        'summary' => 'Electrical and fire-risk assessment, earthing tests, surge protection and preventive maintenance planning.',
                        'body' => "Electrical and fire-risk assessment, earthing and grounding tests, surge protection planning, and panel and preventive maintenance recommendations against the Bangladesh National Building Code.",
                        'scope' => [
                            'Electrical and fire-risk assessment',
                            'Earthing and grounding tests',
                            'Surge protection planning',
                            'Panel and preventive maintenance recommendations',
                        ],
                    ],
                    [
                        'name' => 'Solar & Renewable Energy',
                        'icon' => 'sun',
                        'summary' => 'Rooftop solar feasibility, system sizing, net metering support and installation supervision.',
                        'body' => "Rooftop solar feasibility, shading analysis, grid-tied and off-grid sizing, irrigation and street-light systems, technical specifications, payback analysis, net-metering support and installation supervision.",
                        'scope' => [
                            'Rooftop feasibility and shading analysis',
                            'Grid-tied and off-grid sizing',
                            'Irrigation and street-light systems',
                            'Technical specifications and payback analysis',
                            'Net-metering support and installation supervision',
                        ],
                    ],
                    [
                        'name' => 'Automation & Control',
                        'icon' => 'cog',
                        'summary' => 'Motor control, PLC programming, panel design, SCADA integration and commissioning.',
                        'body' => "Motor control, PLC programming, motor control centre and PLC panel design, HMI and SCADA integration, commissioning, troubleshooting and modernisation of industrial automation systems.",
                        'scope' => [
                            'Motor control and PLC programming',
                            'MCC and PLC panel design',
                            'HMI and SCADA integration',
                            'Commissioning and troubleshooting',
                            'Modernisation of existing automation systems',
                        ],
                    ],
                    [
                        'name' => 'Smart Building, Energy Monitoring & IoT',
                        'icon' => 'building',
                        'summary' => 'Smart lighting, IoT metering, access control and building management system planning.',
                        'body' => "Integrated smart building solutions including smart lighting, low-cost IoT electricity metering, access control, and building automation and management system planning and subsystem integration, with technical support and recurring monitoring and maintenance.",
                        'scope' => [
                            'Smart lighting',
                            'Low-cost IoT electricity metering',
                            'Access control',
                            'BAS/BMS planning and subsystem integration',
                            'Recurring monitoring and maintenance',
                        ],
                    ],
                    [
                        'name' => 'Repair & Maintenance',
                        'icon' => 'wrench',
                        'summary' => 'Industrial controller troubleshooting, control-panel repair and institutional AMC support.',
                        'body' => "Industrial controller troubleshooting, control-panel and wiring repair, institutional annual maintenance contract support, and after-warranty service for UPS, IPS, projector, printer and monitor equipment.",
                        'scope' => [
                            'Industrial controller troubleshooting',
                            'Control-panel and wiring repair',
                            'Institutional AMC support',
                            'After-warranty service for UPS, IPS and office equipment',
                        ],
                    ],
                ],
            ],

            [
                'name' => 'Smart Infrastructure Services',
                'department' => 'CE',
                'icon' => 'building',
                'tagline' => 'Design, structure, environment and construction',
                'description' => 'Architectural and structural design, environmental compliance, material testing, construction and project management consultancy, and survey and GIS.',
                'services' => [
                    [
                        'name' => 'Architectural & Building Support',
                        'icon' => 'building',
                        'summary' => 'Site feasibility, space planning, working drawings, 3D visualisation and renovation support.',
                        'body' => "Site feasibility, space planning, conceptual and schematic design, working architectural drawings, 3D visualisation, interiors, code and setback checks, and renovation or extension support.",
                        'scope' => [
                            'Site feasibility and space planning',
                            'Conceptual and schematic design',
                            'Working architectural drawings',
                            '3D visualisation and interiors',
                            'Code and setback checks',
                            'Renovation and extension support',
                        ],
                    ],
                    [
                        'name' => 'Structural Engineering',
                        'icon' => 'cog',
                        'summary' => 'RCC and steel design, independent review, seismic analysis, retrofitting and safety audit.',
                        'body' => "RCC and steel structural design, independent review, gravity, wind and seismic analysis, retrofitting, safety audit, and investigation of cracks and structural distress.",
                        'scope' => [
                            'RCC and steel structural design',
                            'Independent design review',
                            'Gravity, wind and seismic analysis',
                            'Retrofitting and safety audit',
                            'Investigation of cracks and distress',
                        ],
                    ],
                    [
                        'name' => 'Environmental Engineering',
                        'icon' => 'leaf',
                        'summary' => 'EIA and IEE support, ETP and STP design review, waste management and compliance audits.',
                        'body' => "Environmental impact assessment and initial environmental examination support, effluent and sewage treatment plant design review, waste-management planning, water and air testing coordination, environmental compliance audits and pollution-control solutions.",
                        'scope' => [
                            'EIA and IEE support',
                            'ETP and STP design review',
                            'Waste-management planning',
                            'Water and air testing coordination',
                            'Environmental compliance audits',
                            'Pollution-control solutions',
                        ],
                    ],
                    [
                        'name' => 'Material Testing',
                        'icon' => 'beaker',
                        'summary' => 'Concrete, soil, aggregate, brick and cement testing with calibrated equipment and standards-based reporting.',
                        'body' => "Testing and coordination for concrete, soil, aggregate, brick and cement to support design and quality assurance, with calibrated equipment and clear reporting against the applicable standards.",
                        'scope' => [
                            'Concrete testing',
                            'Soil and aggregate testing',
                            'Brick and cement testing',
                            'Calibrated equipment and quality assurance',
                            'Reporting against applicable standards',
                        ],
                    ],
                    [
                        'name' => 'Construction Consultancy',
                        'icon' => 'document',
                        'summary' => 'BOQ and cost estimation, tender documentation, site supervision and contract administration.',
                        'body' => "Bill of quantities and quantity take-off, cost estimation, tender documentation, site supervision, QA/QC monitoring and contract-administration support for building and infrastructure works.",
                        'scope' => [
                            'BOQ and quantity take-off',
                            'Cost estimation',
                            'Tender documentation',
                            'Site supervision and QA/QC monitoring',
                            'Contract administration support',
                        ],
                    ],
                    [
                        'name' => 'Project Management Consultancy',
                        'icon' => 'chart',
                        'summary' => 'Planning, scheduling, cost control, progress dashboards and risk management.',
                        'body' => "Project planning, work breakdown structure, CPM and Gantt scheduling, resource planning, cost control, progress dashboards, risk management and stakeholder coordination.",
                        'scope' => [
                            'Project planning and work breakdown structure',
                            'CPM and Gantt scheduling',
                            'Resource planning and cost control',
                            'Progress dashboards',
                            'Risk management and stakeholder coordination',
                        ],
                    ],
                    [
                        'name' => 'Survey & GIS',
                        'icon' => 'compass',
                        'summary' => 'Topographic and boundary survey, GIS analysis, elevation models and drone mapping.',
                        'body' => "Topographic and boundary survey support, GIS site analysis, digital elevation models, drone mapping and orthophotos, and thematic mapping for planning and development.",
                        'scope' => [
                            'Topographic and boundary survey support',
                            'GIS site analysis',
                            'Digital elevation models',
                            'Drone mapping and orthophotos',
                            'Thematic mapping for planning and development',
                        ],
                    ],
                ],
            ],

            [
                'name' => 'Smart Mechanical & Automobile Services',
                'department' => 'ME',
                'icon' => 'cog',
                'tagline' => 'CAD, modelling and simulation',
                'description' => 'Two focused service lines: AutoCAD 2D and 3D drafting, and SolidWorks parametric modelling, assembly design and simulation-driven product development.',
                'services' => [
                    [
                        'name' => 'AutoCAD 2D & 3D Services',
                        'icon' => 'document',
                        'summary' => 'Precise 2D drawings and 3D models of mechanical components, assemblies and systems.',
                        'body' => "AutoCAD is a computer-aided design package used for precise 2D drawings and 3D models of mechanical components, assemblies and systems. In mechanical engineering it is the foundation for translating design concepts into manufacturable, standards-compliant technical drawings.",
                        'scope' => [
                            '2D orthographic, sectional and auxiliary drawings with full dimensioning',
                            '3D solid modelling',
                            'Parametric, constraint-driven design for iterative revisions',
                            'Associative 2D drafting generated from 3D models',
                            'Drawing standardisation (ISO/ANSI)',
                        ],
                    ],
                    [
                        'name' => 'SolidWorks Services',
                        'icon' => 'cog',
                        'summary' => 'Parametric modelling, assembly design and simulation from concept to manufacturing documentation.',
                        'body' => "SolidWorks is a parametric, feature-based 3D CAD package widely used in mechanical engineering for solid modelling, assembly design and simulation-driven product development, from first concept through to manufacturing documentation.",
                        'scope' => [
                            'Feature-based part modelling (extrude, revolve, sweep, loft, patterns)',
                            'Sheet metal design and surfacing',
                            'Top-down and bottom-up assembly design',
                            'Exploded views and assembly documentation',
                            'Associative 2D drawings from 3D models',
                            'Dimensioning, GD&T and BOM/title block automation',
                            'SolidWorks Simulation: static stress, thermal and fatigue analysis',
                            'Motion analysis and interference detection',
                            'Basic CFD and flow simulation',
                        ],
                    ],
                ],
            ],

            [
                'name' => 'Business Advisory & Income Tax Services',
                'department' => 'BUS',
                'icon' => 'briefcase',
                'tagline' => 'Tax compliance and enterprise growth',
                'description' => 'Two service lines for Barishal Division: NBR-compliant tax advisory and e-filing, and structured incubation and growth advisory for startups and micro-enterprises.',
                'services' => [
                    [
                        'name' => 'Income Tax Consultancy Services',
                        'icon' => 'document',
                        'summary' => 'NBR-compliant tax advisory and e-filing for individuals, sole proprietors, professionals and SMEs.',
                        'body' => "A tax clinic offering NBR-compliant advisory and e-filing support across Barishal Division, for river-port traders, doctors, members of the district bar, public servants, bank officials and SME owners.\n\nThe service runs as a student-assisted front office with faculty quality assurance. Formal representation before a tax circle is carried out through partner Income Tax Practitioners; UGV RICH provides preparation and advisory.",
                        'scope' => [
                            'TIN acquisition and basic compliance',
                            'Individual and salaried return e-filing',
                            'Sole proprietor and professional filing with profit & loss and balance sheet',
                            'Financial statement compilation',
                            'TDS and advance tax quarterly advisory',
                            'Assessment hearing support',
                            'Secure archiving with six-year retention',
                        ],
                    ],
                    [
                        'name' => 'Start-up & Entrepreneurial Support',
                        'icon' => 'rocket',
                        'summary' => 'Incubation, venture formalisation and growth advisory for youth, agro-value chain and women-led businesses.',
                        'body' => "A structured incubation hub for registered startups and informal micro-enterprises across the six districts of Barishal Division, with linkages to BSCIC, BIDA, the SME Foundation and JICA projects.\n\nSupport runs from idea validation through formalisation to investor readiness, with a fast-track for women entrepreneurs. UGV RICH advises; it is not an RJSC, BIDA or BSTI authority.",
                        'scope' => [
                            'Idea validation and business model canvas',
                            'Full feasibility study and investor-ready business plan',
                            'Venture formalisation: trade licence, RJSC, TIN/BIN/VAT, BSTI and trademark advisory',
                            'Financial systems and cost control setup',
                            'Investor readiness and pitch preparation',
                            'Three- and six-month incubation retainer with desk and mentorship',
                            'Women Entrepreneur Fast-Track',
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Language Services',
                'department' => 'ENG',
                'icon' => 'academic',
                'tagline' => 'Teaching support and online learning',
                'description' => 'Support for English language teachers and structured online learning for school students, from lesson planning and assessment to live classes and progress monitoring.',
                'services' => [
                    [
                        'name' => "Online English Teachers' Support",
                        'icon' => 'users',
                        'summary' => 'Language teaching, methodology, lesson planning, assessment and classroom management support.',
                        'body' => "English language teaching, vocabulary development, grammar, pronunciation, speaking and communication skills, academic and professional writing, lesson planning, classroom management, assessment techniques and modern teaching methodologies.",
                        'scope' => [
                            'Vocabulary, grammar and pronunciation',
                            'Speaking and communication skills',
                            'Academic and professional writing',
                            'Lesson planning and classroom management',
                            'Assessment techniques',
                            'Modern teaching methodologies',
                        ],
                    ],
                    [
                        'name' => 'Online School',
                        'icon' => 'academic',
                        'summary' => 'Live and recorded classes, subject courses, digital materials and progress monitoring for school students.',
                        'body' => "Online learning support for school students through live and recorded classes, subject-based courses, digital learning materials, assignments, assessments, interactive activities and student progress monitoring.",
                        'scope' => [
                            'Live and recorded classes',
                            'Subject-based courses',
                            'Digital learning materials',
                            'Assignments and assessments',
                            'Interactive activities',
                            'Student progress monitoring',
                        ],
                    ],
                ],
            ],
        ];
    }
}
