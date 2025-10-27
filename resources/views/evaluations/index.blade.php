@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold">My Performance Evaluation</h2>
            <p class="text-muted">Track your performance based on task completion, quality, and timeliness</p>
        </div>
    </div>

    <!-- Period Selector -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="btn-group" role="group">
                <a href="{{ route('evaluations.index', ['period' => 'week']) }}" 
                   class="btn {{ $period == 'week' ? 'btn-primary' : 'btn-outline-primary' }}">
                    This Week
                </a>
                <a href="{{ route('evaluations.index', ['period' => 'month']) }}" 
                   class="btn {{ $period == 'month' ? 'btn-primary' : 'btn-outline-primary' }}">
                    This Month
                </a>
                <a href="{{ route('evaluations.index', ['period' => 'quarter']) }}" 
                   class="btn {{ $period == 'quarter' ? 'btn-primary' : 'btn-outline-primary' }}">
                    This Quarter
                </a>
                <a href="{{ route('evaluations.index', ['period' => 'year']) }}" 
                   class="btn {{ $period == 'year' ? 'btn-primary' : 'btn-outline-primary' }}">
                    This Year
                </a>
            </div>
        </div>
    </div>

    <!-- Overall Score Card -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center border-primary">
                <div class="card-body">
                    <h1 class="display-1 fw-bold text-primary">{{ $evaluation['overall_score'] }}</h1>
                    <p class="card-text text-muted">Overall Score</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center border-success">
                <div class="card-body">
                    <h1 class="display-1 fw-bold text-success">{{ $evaluation['grade'] }}</h1>
                    <p class="card-text text-muted">Grade</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center border-info">
                <div class="card-body">
                    <h1 class="display-1 fw-bold text-info">
                        #{{ $evaluation['rank'] ?? 'N/A' }}
                    </h1>
                    <p class="card-text text-muted">Team Rank</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Score Breakdown -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Score Breakdown</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <h6>Completion Score</h6>
                            <div class="progress mb-3">
                                <div class="progress-bar" style="width: {{ $evaluation['scores']['completion_score'] }}%">{{ $evaluation['scores']['completion_score'] }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h6>Quality Score</h6>
                            <div class="progress mb-3">
                                <div class="progress-bar bg-success" style="width: {{ $evaluation['scores']['quality_score'] }}%">{{ $evaluation['scores']['quality_score'] }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h6>Timeliness Score</h6>
                            <div class="progress mb-3">
                                <div class="progress-bar bg-warning" style="width: {{ $evaluation['scores']['timeliness_score'] }}%">{{ $evaluation['scores']['timeliness_score'] }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h6>Productivity Score</h6>
                            <div class="progress mb-3">
                                <div class="progress-bar bg-info" style="width: {{ $evaluation['scores']['productivity_score'] }}%">{{ $evaluation['scores']['productivity_score'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Metrics -->
    <div class="row">
        @foreach($evaluation['metrics'] as $key => $value)
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3 class="fw-bold">{{ $value }}</h3>
                    <p class="text-muted mb-0">{{ str_replace('_', ' ', ucwords($key)) }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if(auth()->user()->isManager() && !empty($allEvaluations))
    <!-- Team Comparison -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Team Comparison</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Name</th>
                                    <th>Score</th>
                                    <th>Grade</th>
                                    <th>Completed</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($allEvaluations as $index => $eval)
                                <tr>
                                    <td>#{{ $index + 1 }}</td>
                                    <td>{{ $eval['user']->name }}</td>
                                    <td>{{ $eval['overall_score'] }}</td>
                                    <td><span class="badge bg-primary">{{ $eval['grade'] }}</span></td>
                                    <td>{{ $eval['metrics']['completed_tasks'] }}</td>
                                    <td>
                                        <a href="{{ route('evaluations.show', $eval['user']->id) }}" class="btn btn-sm btn-primary">View</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

