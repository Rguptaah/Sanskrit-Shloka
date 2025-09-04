@extends('layouts.app')

@section('title', 'Add New Shloka')

@section('content')
<h1>Add New Shloka</h1>

<form action="{{ route('shlokas.store') }}" method="POST">
    @csrf

    <div class="mb-3">
        <label for="id" class="form-label">Shloka ID</label>
        <input type="text" class="form-control @error('id') is-invalid @enderror" id="id" name="id" value="{{ old('id') }}" required>
        @error('id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <div class="form-text">E.g., CS_SUT_25.40</div>
    </div>

    <div class="mb-3">
        <label for="sanskrit_shloka" class="form-label">Sanskrit Shloka (Devanagari)</label>
        <textarea class="form-control @error('sanskrit_shloka') is-invalid @enderror" id="sanskrit_shloka" name="sanskrit_shloka" rows="3" required>{{ old('sanskrit_shloka') }}</textarea>
        @error('sanskrit_shloka')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <button type="button" class="btn btn-outline-secondary mt-1" onclick="convertToUnicode()">Convert to Unicode</button>
    </div>

    <div class="mb-3">
        <label for="unicode" class="form-label">Unicode (auto-filled)</label>
        <textarea readonly class="form-control" id="unicode" name="unicode" rows="2">{{ old('unicode') }}</textarea>
    </div>

    <div class="mb-3">
        <label for="transliteration" class="form-label">Transliteration (IAST)</label>
        <textarea class="form-control @error('transliteration') is-invalid @enderror" id="transliteration" name="transliteration" rows="2">{{ old('transliteration') }}</textarea>
        @error('transliteration')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <button type="button" class="btn btn-outline-secondary mt-1" onclick="transliterateToIAST()">Convert to IAST</button>
    </div>

    <div class="mb-3">
        <label for="translations_hindi" class="form-label">Translation (Hindi)</label>
        <textarea class="form-control @error('translations.hindi') is-invalid @enderror" name="translations[hindi]" id="translations_hindi" rows="2">{{ old('translations.hindi') }}</textarea>
        @error('translations.hindi')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="translations_english" class="form-label">Translation (English)</label>
        <textarea class="form-control @error('translations.english') is-invalid @enderror" name="translations[english]" id="translations_english" rows="2">{{ old('translations.english') }}</textarea>
        @error('translations.english')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <h5>Metadata / Source</h5>

    <div class="row mb-3">
        <div class="col-md-4">
            <label for="source_text_name" class="form-label">Text Name</label>
            <input type="text" name="source_text_name" id="source_text_name" class="form-control" value="{{ old('source_text_name') }}" required>
        </div>
        <div class="col-md-4">
            <label for="source_section" class="form-label">Section</label>
            <input type="text" name="source_section" id="source_section" class="form-control" value="{{ old('source_section') }}">
        </div>
        <div class="col-md-2">
            <label for="source_chapter" class="form-label">Chapter</label>
            <input type="number" name="source_chapter" id="source_chapter" class="form-control" value="{{ old('source_chapter') }}">
        </div>
        <div class="col-md-2">
            <label for="source_verse" class="form-label">Verse</label>
            <input type="number" name="source_verse" id="source_verse" class="form-control" value="{{ old('source_verse') }}">
        </div>
    </div>

    <div class="mb-3">
        <label for="keywords" class="form-label">Keywords (comma separated)</label>
        <input type="text" name="keywords" id="keywords" class="form-control" value="{{ old('keywords') }}">
        <div class="form-text">E.g., agni, digestion, disease_causes</div>
    </div>

    <div class="mb-3">
        <label for="category" class="form-label">Category</label>
        <input type="text" name="category" id="category" class="form-control" value="{{ old('category') }}">
    </div>

    <div class="mb-3">
        <label for="commentaries" class="form-label">Commentaries (comma separated)</label>
        <input type="text" name="commentaries" id="commentaries" class="form-control" value="{{ old('commentaries') }}">
    </div>

    <button type="submit" class="btn btn-success">Save Shloka</button>
    <a href="{{ route('shlokas.index') }}" class="btn btn-secondary">Cancel</a>
</form>

@push('scripts')
{{-- <script src="https://cdn.jsdelivr.net/npm/sanscript@1.0.2/sanscript.min.js"></script> --}}
{{-- <script src="https://cdn.jsdelivr.net/npm/sanscript@1.0.0/sanscript.min.js"></script> --}}
<script src="https://cdn.jsdelivr.net/npm/sanscriptjs@0.0.1/sanscript.min.js"></script>
<script>
    console.log(typeof Sanscript);
    function convertToUnicode() {
        const input = document.getElementById('sanskrit_shloka').value.trim();

        if (!input) {
            alert("Please enter some text.");
            return;
        }

        let unicodeStr = '';
        for (let char of input) {
            let hex = char.charCodeAt(0).toString(16).padStart(4, '0');
            unicodeStr += '\\u' + hex;
        }

        document.getElementById('unicode').value = unicodeStr;
        document.getElementById('output').innerText = `Characters and Unicode:\n\n` + 
            [...input].map(c => `${c} → \\u${c.charCodeAt(0).toString(16).padStart(4, '0')}`).join('\n');
    }

    function copyUnicode() {
        const unicodeText = document.getElementById('unicode');
        unicodeText.select();
        unicodeText.setSelectionRange(0, 99999);
        document.execCommand("copy");
        alert("Unicode copied to clipboard!");
    }

    function transliterateToIAST() {
        const input = document.getElementById('sanskrit_shloka').value.trim();
        if (!input) {
            alert("Please enter a Sanskrit shloka.");
            return;
        }
        const translit = Sanscript.t(input, "devanagari", "iast");
        document.getElementById('transliteration').value = translit;
    }

    function copyTransliteration() {
        const output = document.getElementById('transliteration');
        output.select();
        document.execCommand("copy");
        alert("IAST transliteration copied to clipboard!");
    }
</script>
@endpush
@endsection
