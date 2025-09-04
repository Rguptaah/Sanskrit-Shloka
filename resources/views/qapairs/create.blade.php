@extends('layouts.app')

@section('title', 'Add New Q&A Pair')

@section('content')
<h1>Add New Q&A Pair</h1>

<form action="{{ route('qa-pairs.store') }}" method="POST">
    @csrf

    <div class="mb-3">
        <label for="shloka_id" class="form-label">Select Shloka</label>
        <select class="form-select @error('shloka_id') is-invalid @enderror" id="shloka_id" name="shloka_id" required>
            <option value="">-- Select Shloka --</option>
            @foreach($shlokas as $shloka)
                <option value="{{ $shloka->id }}" {{ old('shloka_id') == $shloka->id ? 'selected' : '' }}>
                    {{ $shloka->id }} | {{ Str::limit($shloka->sanskrit_shloka, 30) }}
                </option>
            @endforeach
        </select>
        @error('shloka_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="question" class="form-label">Question</label>
        <textarea class="form-control @error('question') is-invalid @enderror" id="question" name="question" rows="3" required>{{ old('question') }}</textarea>
        @error('question')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="answer" class="form-label">Answer</label>
        <textarea class="form-control @error('answer') is-invalid @enderror" id="answer" name="answer" rows="4" required>{{ old('answer') }}</textarea>
        @error('answer')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="keywords" class="form-label">Keywords (comma separated)</label>
        <input type="text" class="form-control" id="keywords" name="keywords" value="{{ old('keywords') }}">
        <div class="form-text">E.g., disease_origin, agni, ama</div>
    </div>

    <div class="mb-3">
        <label for="context" class="form-label">Context</label>
        <textarea class="form-control" id="context" name="context" rows="3">{{ old('context') }}</textarea>
        <div class="form-text">Any additional context related to this Q&A.</div>
    </div>

    <button type="submit" class="btn btn-success">Save Q&A Pair</button>
    <a href="{{ route('qa-pairs.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
