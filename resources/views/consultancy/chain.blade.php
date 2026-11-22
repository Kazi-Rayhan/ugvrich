<x-filament-panels::page>
    @include('consultancy.admin-styles')

    <div class="consultancy-admin">
        <header class="consultancy-hero consultancy-chain-hero">
            <div>
                <h2>Consultancy approval workflow</h2>
                <p>The Consultancy Wing reviews first. On approval, the application moves to the Director for a final decision.</p>
            </div>
        </header>

        <ol class="consultancy-grid consultancy-grid--equal consultancy-chain-grid" aria-label="Approval stages">
            @foreach ($stages as $stage)
                <li class="consultancy-chain-card">
                    <div class="consultancy-chain-card-header">
                        <div class="consultancy-chain-stage">
                            <span aria-hidden="true" class="consultancy-stage-number">{{ $loop->iteration }}</span>
                            <div>
                                <h2>{{ $stage['label'] }}</h2>
                                <p class="consultancy-muted">{{ $loop->first ? 'Initial review' : 'Final decision' }}</p>
                            </div>
                        </div>
                        <x-filament::badge :color="$stage['pending'] ? 'warning' : 'gray'">{{ $stage['pending'] }} pending</x-filament::badge>
                    </div>

                    <div class="consultancy-chain-card-body">
                        <div class="consultancy-chain-card-toolbar">
                            <h3>Assigned users <span class="consultancy-muted">({{ $stage['reviewers']->count() }})</span></h3>
                            {{ $this->getAction($stage['action'], isMounting: false) }}
                        </div>

                        <ul class="consultancy-chain-users">
                            @forelse ($stage['reviewers'] as $reviewer)
                                <li>
                                    <span class="consultancy-chain-user-icon" aria-hidden="true"><x-filament::icon icon="heroicon-o-user" class="h-4 w-4" /></span>
                                    <div>
                                        <p class="consultancy-entry-title">{{ $reviewer->name }}</p>
                                        <p class="consultancy-muted">{{ $reviewer->email }}</p>
                                    </div>
                                <div class="consultancy-chain-user-action">
                                    {{ ($this->getAction('removeUser', isMounting: false))(['stage' => $stage['key'], 'user' => $reviewer->id]) }}
                                </div>
                                </li>
                            @empty
                                <li class="consultancy-chain-empty consultancy-muted">No users assigned to this stage.</li>
                            @endforelse
                        </ul>
                    </div>
                </li>
            @endforeach
        </ol>

        <x-filament::section heading="Decision rules" icon="heroicon-o-shield-check">
            <ul class="consultancy-entries">
                <li>Only users assigned to a stage can approve or decline at that stage.</li>
                <li>The Director must be a different person from the Wing reviewer who approved the application.</li>
                <li>After final approval, set the contract and onboarding terms in Consultancy Engagements.</li>
            </ul>
        </x-filament::section>
    </div>
</x-filament-panels::page>
