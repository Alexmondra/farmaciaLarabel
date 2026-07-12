@foreach (['success' => 'success', 'warning' => 'warning', 'error' => 'danger'] as $key => $type)
    @if (session($key))
        <div class="alert alert-{{ $type }}">{{ session($key) }}</div>
    @endif
@endforeach

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Revisa la informacion ingresada.</strong>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
