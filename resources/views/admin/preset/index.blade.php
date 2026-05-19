@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4 admin-page-wrapper">
        <div class="d-flex justify-content-between align-items-center my-4 admin-page-title">
            <div>
                <h1 class="m-0">Presets</h1>
                <p class="text-muted mb-0">Manage and review preset status.</p>
            </div>
            <a href="{{ route('admin.preset.create') }}"
                class="btn btn-outline-secondary px-4 py-2 d-inline-flex align-items-center align-items-center gap-2"><i
                    class="fi fi-rr-square-plus mt-1"></i>
                <span>Add Preset</span>
            </a>
        </div>

        <div class="admin-table-wrapper">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">No</th>
                            <th scope="col">Preset Name</th>
                            <th scope="col">Preset File Path</th>
                            <th scope="col">Is Active</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($presets as $preset)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $preset->preset_name }}</td>
                                <td>{{ $preset->preset_file_path }}</td>
                                <td>
                                    @if ($preset->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.preset.edit', $preset) }}"
                                        class="btn btn-sm btn-outline-primary"><i class="fi fi-rr-edit"></i></a>
                                    <form action="{{ route('admin.preset.destroy', $preset) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Are you sure you want to delete this preset?')"><i
                                                class="fi fi-rr-trash"></i></button></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No presets found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $presets->links() }}
            </div>
        </div>
    </div>
@endsection
