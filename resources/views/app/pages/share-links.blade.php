@extends('app.layouts.layouts')
@section('title', 'Share selected files')
@section('content')
    <div class="page-heading"><div><h1>Share selected files</h1><p class="page-description">Each file has its own secure link. Protected folders still require their password.</p></div></div>
    <label for="selectedShareLinks" class="form-label">Share links</label>
    <textarea id="selectedShareLinks" class="form-control mb-3" rows="8" readonly>@foreach ($links as $link){{ $link['name'] }}: {{ $link['url'] }}
@endforeach</textarea>
    <div class="d-flex gap-2 mb-4"><button class="btn btn-primary" type="button" data-copy-share-links>Copy links</button><button class="btn btn-outline-primary" type="button" data-native-share-links hidden>Share</button><span data-share-status role="status" aria-live="polite"></span></div>
    @foreach ($links as $link)
        <div class="card p-3 mb-2"><strong class="text-break">{{ $link['name'] }}</strong><a class="text-break my-2" href="{{ $link['url'] }}" rel="noreferrer">{{ $link['url'] }}</a><form method="POST" action="{{ route('app.shares.revoke', $link['id']) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" type="submit">Revoke link</button></form></div>
    @endforeach
@endsection
