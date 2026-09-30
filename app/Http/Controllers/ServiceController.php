<?php

namespace App\Http\Controllers;

use App\Models\Expert;
use App\Models\Project;
use App\Models\Service;
use App\Models\ServiceCategory;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::active()->with('category')->orderBy('sort_order')->get();

        return view('pages.services.index', [
            'categories' => ServiceCategory::active()->with('services')->orderBy('sort_order')->get(),
            'services' => $services,
            'serviceCount' => $services->count(),
        ]);
    }

    /**
     * One service, on a page of its own.
     *
     * Scoped to its main service, so /services/ict-digital-consultancy/cybersecurity
     * reads as the path it is, and a service cannot be reached under a parent it
     * does not belong to.
     */
    public function service(ServiceCategory $serviceCategory, Service $service)
    {
        abort_unless($serviceCategory->is_active && $service->is_active, 404);

        $siblings = $serviceCategory->services()->active()->whereKeyNot($service->id)->get();

        return view('pages.services.detail', [
            'category' => $serviceCategory,
            'service' => $service,
            'siblings' => $siblings,
            'experts' => Expert::with('category')->active()
                ->where('service_category_id', $serviceCategory->id)
                ->orderBy('sort_order')->take(3)->get(),
        ]);
    }

    public function show(ServiceCategory $serviceCategory)
    {
        abort_unless($serviceCategory->is_active, 404);

        $serviceCategory->load('services');

        return view('pages.services.show', [
            'category' => $serviceCategory,
            'siblings' => ServiceCategory::active()->with('services')->whereKeyNot($serviceCategory->id)->orderBy('sort_order')->get(),
            'experts' => Expert::with('category')->active()->where('service_category_id', $serviceCategory->id)->orderBy('sort_order')->take(3)->get(),
            'projects' => Project::with('category')->where('service_category_id', $serviceCategory->id)->orderBy('sort_order')->take(3)->get(),
            'expertCount' => Expert::active()->where('service_category_id', $serviceCategory->id)->count(),
            'projectCount' => Project::where('service_category_id', $serviceCategory->id)->count(),
        ]);
    }
}
